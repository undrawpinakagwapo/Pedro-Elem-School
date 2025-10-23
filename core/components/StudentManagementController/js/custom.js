const module = URL_BASED + (URL_BASED && !URL_BASED.endsWith('/') ? '/' : '') + 'component/student-management/';
const component = 'component/student-management/';

/** Inject a one-time style to hide any stray close buttons */
(function injectModalStyleOnce() {
  if (document.getElementById("hide-modal-x-style")) return;
  const css = `
    /* Hide all common close buttons in modals */
    .modal .modal-header .btn-close,
    .modal .modal-header .close,
    .modal .modal-content > .btn-close,
    .modal .modal-content > .close {
      display: none !important;
    }
  `;
  const style = document.createElement("style");
  style.id = "hide-modal-x-style";
  style.textContent = css;
  document.head.appendChild(style);
})();

/** Remove X and trim empty header (robust: runs multiple times, multiple selectors) */
function polishModalHeader(expectedHeaderText) {
  // run a few times to defeat animations / delayed DOM
  const attempts = [0, 50, 200];
  attempts.forEach((delay) => {
    setTimeout(function () {
      const $modal = $(".modal:visible").last();
      if (!$modal.length) return;

      // Remove all flavors of close buttons (theme-safe)
      $modal.find(".btn-close, button.close, a.close").remove();

      const $header = $modal.find(".modal-header");
      if (!$header.length) return;

      const titleText = $.trim($header.find(".modal-title").text() || "");
      const shouldRemoveHeader =
        (typeof expectedHeaderText !== "undefined" &&
          $.trim(expectedHeaderText) === "") ||
        titleText === "";

      if (shouldRemoveHeader) {
        $header.remove();
      }
    }, delay);
  });
}

/** Also hook Bootstrap's shown event so we run after transitions */
$(document).on("shown.bs.modal", ".modal", function () {
  polishModalHeader();
});

/* =========================
   OPEN IMPORT MODAL
   ========================= */
$(document).on("click", ".importsutdentmodal", function () {
  var action = module + "modalimport";

  var formData = new FormData(); // (no payload)

  var request = main.send_ajax(formData, action, "POST", true);
  request.done(function (data) {
    action = component + data.action;

    main.modalOpen(data.header, data.html, data.button, action, "modal-xl");

    // remove the "X" and empty header
    polishModalHeader(data.header);
  });
});

/* =========================
   OPEN ADD/EDIT DETAILS MODAL
   ========================= */
$(document).on("click", ".openmodaldetails-modal", function () {
  var action = module + "source";

  var dataObj = {
    action: $(this).data("type"),
    id: $(this).data("id"),
  };

  var formData = new FormData();
  for (const key in dataObj) {
    if (Object.prototype.hasOwnProperty.call(dataObj, key)) {
      formData.append(key, dataObj[key]);
    }
  }

  var request = main.send_ajax(formData, action, "POST", true);
  request.done(function (data) {
    action = component + data.action;

    main.modalOpen(data.header, data.html, data.button, action, "modal-xl");

    // remove the "X" and empty header
    polishModalHeader(data.header);
  });
});

/* =========================
   DELETE RECORD
   ========================= */
$(document).on("click", ".delete", function () {
  var dataObj = { id: $(this).data("id") };

  var formData = new FormData();
  for (const key in dataObj) {
    if (Object.prototype.hasOwnProperty.call(dataObj, key)) {
      formData.append(key, dataObj[key]);
    }
  }

  main.confirmMessage(
    "warning",
    "DELETE RECORD",
    "Are you sure you want to delete this record? ",
    "deleteRecord",
    formData
  );
});

function deleteRecord(formData) {
  var action = module + "delete";

  var request = main.send_ajax(formData, action, "POST", true);
  request.done(function (data) {
    if (data.status) {
      main.confirmMessage(
        "success",
        "Successfully Deleted!",
        "Are you sure you want to reload this page? ",
        "reloadPage",
        ""
      );
    } else {
      main.alertMessage("danger", "Failed to Delete!", "");
    }
  });
}

/* ===========================================================
   EXCEL UPLOAD
   - Delegated so it works whether the form is on the page or inside a modal
   - No batch/set_group inputs anymore; server derives from T4/AE4/AM4
   =========================================================== */
$(document).on("submit", "#excelForm", function (e) {
  e.preventDefault();

  // Build FormData from the form (includes file input only)
  var form = this;
  var fd = new FormData(form);

  // Basic guarding for file presence
  if (!fd.get("excel_file")) {
    main.alertMessage("warning", "Please choose an Excel file.", "");
    return;
  }

  $.ajax({
    url: "upload_excel",
    type: "POST",
    data: fd,
    contentType: false,
    processData: false,
  })
    .done(function (response) {
      if ($("#table_result").length) {
        $("#table_result").html(response);
      } else {
        // if inside modal, place preview into modal content
        $(".modal:visible .modal-body, .modal:visible .card-block")
          .first()
          .html(response);
      }
    })
    .fail(function () {
      main.alertMessage("danger", "Upload failed!", "");
    });
});

/* ===========================================================
   FILTERS: School Year & Grade & Section using DataTables API
   - Remove "Show X entries" dropdown
   - Selecting a filter shows ALL matching rows (page length = -1)
   - Clearing filters restores default page length
   =========================================================== */
(function initDataTablesFilters() {
  if (typeof $.fn.DataTable === "undefined") {
    // DataTables not loaded; bail out gracefully
    return;
  }

  const DEFAULT_LEN = 10; // internal default (user can't change length now)
  const tableSelector = "#mainTable";

  // Initialize or fetch existing instance
  let table;
  if ($.fn.dataTable.isDataTable(tableSelector)) {
    table = $(tableSelector).DataTable();
    // Remove the length dropdown if already rendered
    const $wrapper = $(tableSelector).closest(".dataTables_wrapper");
    $wrapper.find(".dataTables_length").remove();
  } else {
    table = $(tableSelector).DataTable({
      pageLength: DEFAULT_LEN,
      lengthChange: false, // 🚫 hide "Show entries"
      dom: "frtip", // no 'l' control => no length dropdown
      // lengthMenu can stay; users won't see it, but we can still change length via API
      lengthMenu: [
        [10, 25, 50, 100, -1],
        [10, 25, 50, 100, "All"],
      ],
    });
  }

  // Column indexes (0-based) — adjust if the table structure changes
  // 0 No., 1 LRN, 2 Student Name, 3 Birth Date, 4 Gender, 5 School Year, 6 Grade & Section, ...
  const COL_BATCH = 5;
  const COL_SET = 6;

  // Ensure the "All" option is present and selectable
  function ensureAllOption($sel, placeholder) {
    if (!$sel.length) return;
    let $opt = $sel.find('option[value=""]');
    if (!$opt.length) {
      $opt = $("<option/>", { value: "", text: placeholder });
      $sel.prepend($opt);
    }
    $opt.prop("disabled", false);
  }

  const $batchSel = $("#filterBatch");
  const $setSel = $("#filterSet");

  ensureAllOption($batchSel, "All School Years");
  ensureAllOption($setSel, "All Grade & Sections");

  function toExactRegex(v) {
    return v ? "^" + $.fn.dataTable.util.escapeRegex(v) + "$" : "";
  }

  function applyFilters() {
    const batchVal = ($batchSel.val() || "").trim();
    const setVal = ($setSel.val() || "").trim();

    // Exact match on those columns
    table.column(COL_BATCH).search(toExactRegex(batchVal), true, false);
    table.column(COL_SET).search(toExactRegex(setVal), true, false);

    const anyActive = !!batchVal || !!setVal;

    // If any filter is active => show all results (-1)
    table.page.len(anyActive ? -1 : DEFAULT_LEN).draw();
  }

  // Hook up filter change
  $batchSel.on("change", applyFilters);
  $setSel.on("change", applyFilters);

  // Initial state (no filters => default length)
  applyFilters();
})();

// Automatically copy LRN to Username field in the modal
$(document).on("input", "#LRN", function () {
  // Get the current value of the LRN input
  var lrnValue = $(this).val();

  // Set the username input to have the same value
  $("#username").val(lrnValue);
});
