/* components/ManageSectionController/js/custom.js */
const module = URL_BASED + (URL_BASED && !URL_BASED.endsWith('/') ? '/' : '') + 'component/manage-section/';
const component = 'component/manage-section/';

/* ---------- Modal chrome remover (hide header + close buttons) ---------- */
(function injectNoCloseStyle() {
  if (document.getElementById("no-modal-x-style")) return;
  const style = document.createElement("style");
  style.id = "no-modal-x-style";
  style.textContent = `
    .modal .btn-close,
    .modal button.close,
    .modal a.close,
    .modal [data-dismiss="modal"].close,
    .modal [data-bs-dismiss="modal"].btn-close { display:none!important; visibility:hidden!important; }
  `;
  document.head.appendChild(style);
})();
function stripModalChrome() {
  const $m = $(".modal:visible").last();
  if (!$m.length) return;
  $m.find(".modal-header").remove();
  $m.find(".btn-close, .close").remove();
  $m.find(".modal-title").empty();
}
function stripModalChromeWithRetries() {
  [0, 50, 200, 500].forEach((d) => setTimeout(stripModalChrome, d));
}
$(document).on("shown.bs.modal", ".modal", stripModalChromeWithRetries);

/* ---------- Data (list) utilities ---------- */
function fetchSections() {
  return fetch(module + "list", { method: "GET", credentials: "same-origin" })
    .then((r) => r.json())
    .catch(() => []);
}

/* ---------- Table sorting (by Grade Level col) ---------- */
function findColIndexByHeader(tableSelector, needleText) {
  const $ths = $(tableSelector + " thead th");
  const needle = (needleText || "").toLowerCase();
  let idx = -1;
  $ths.each(function (i) {
    const t = ($(this).text() || "").trim().toLowerCase();
    if (t === needle || t.indexOf(needle) !== -1) {
      idx = i;
      return false;
    }
  });
  return idx;
}
function initSectionTableSort(tableSelector = "#mainTable") {
  if (!$(tableSelector).length) return;
  const gradeIdx = findColIndexByHeader(tableSelector, "Grade Level");
  if (gradeIdx === -1) return;

  // Prefer DataTables when available
  if (typeof $.fn.DataTable !== "undefined") {
    if ($.fn.dataTable.isDataTable(tableSelector)) {
      const dt = $(tableSelector).DataTable();
      dt.order([gradeIdx, "asc"]).draw();
    } else {
      const lastCol = $(tableSelector + " thead th").length - 1;
      const defs = [];
      if (lastCol >= 0) defs.push({ targets: 0, orderable: false });
      if (lastCol >= 0) defs.push({ targets: lastCol, orderable: false });
      $(tableSelector).DataTable({
        order: [[gradeIdx, "asc"]],
        columnDefs: defs,
      });
    }
    return;
  }

  // Vanilla fallback
  const $tbody = $(tableSelector + " tbody");
  const rows = $tbody.find("tr").get();
  rows.sort(function (a, b) {
    const ta = $(a).children("td").eq(gradeIdx).text().trim();
    const tb = $(b).children("td").eq(gradeIdx).text().trim();
    const na = parseFloat(ta),
      nb = parseFloat(tb);
    if (!isNaN(na) && !isNaN(nb)) return na - nb;
    return ta.localeCompare(tb, undefined, {
      numeric: true,
      sensitivity: "base",
    });
  });
  $.each(rows, (_, r) => $tbody.append(r));
}

/* ---------- Web Component (<section-widget>) ---------- */
class SectionWidget extends HTMLElement {
  connectedCallback() {
    this.renderSkeleton();
    this.loadData();
  }

  renderSkeleton() {
    // Keep id="mainTable" for compatibility with your old scripts
    this.innerHTML = `
      <button class="btn waves-effect waves-light btn-primary openmodaldetails-modal" data-type="add">
        <i class="fa fa-plus"></i>&nbsp;Add New
      </button>

      <div class="card table-card mt-2">
        <div class="card-header">
          <h5>Manage Section</h5>
          <div class="card-header-right">
            <ul class="list-unstyled card-option">
              <li><i class="fa fa fa-wrench open-card-option"></i></li>
              <li><i class="fa fa-window-maximize full-card"></i></li>
              <li><i class="fa fa-minus minimize-card"></i></li>
              <li><i class="fa fa-refresh reload-card"></i></li>
              <li><i class="fa fa-trash close-card"></i></li>
            </ul>
          </div>
        </div>
        <div class="card-block">
          <div class="table-responsive">
            <table id="mainTable" class="table table-hover">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Section Code</th>
                  <th>Section Name</th>
                  <th>Grade Level</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody><tr><td colspan="5" class="text-center">Loading…</td></tr></tbody>
            </table>
          </div>
        </div>
      </div>
    `;
  }

  loadData() {
    const tbody = this.querySelector("#mainTable tbody");
    fetchSections().then((rows) => {
      if (!Array.isArray(rows) || rows.length === 0) {
        tbody.innerHTML = ""; // Make the table body empty
        initSectionTableSort("#mainTable"); // Let DataTables handle the empty message
        return;
      }

      tbody.innerHTML = rows
        .map((r, i) => {
          const id = r.id ?? "";
          const code = (r.code ?? "").toString();
          const name = (r.name ?? "").toString();
          const gl = (r.gradeName ?? "").toString();
          return `
          <tr>
            <td>${i + 1}</td>
            <td>${escapeHtml(code)}</td>
            <td>${escapeHtml(name)}</td>
            <td>${escapeHtml(gl)}</td>
            <td>
              <button class="btn waves-effect waves-light btn-grd-primary btn-sm openmodaldetails-modal" data-type="edit" data-id="${id}">
                <i class="fa fa-edit"></i>
              </button>
              |
              <button class="btn waves-effect waves-light btn-grd-danger btn-sm delete" data-id="${id}">
                <i class="fa fa-times"></i>
              </button>
            </td>
          </tr>`;
        })
        .join("");

      // sort after rows are in DOM
      initSectionTableSort("#mainTable");
    });
  }
}
function escapeHtml(s) {
  return String(s == null ? "" : s)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#39;");
}
if (!customElements.get("section-widget")) {
  customElements.define("section-widget", SectionWidget);
}

/* ---------- Modal open (no header/title) ---------- */
$(document).on("click", ".openmodaldetails-modal", function () {
  const action = module + "source";
  const dataObj = { action: $(this).data("type"), id: $(this).data("id") };

  const formData = new FormData();
  Object.keys(dataObj).forEach((k) => formData.append(k, dataObj[k]));

  const request = main.send_ajax(formData, action, "POST", true);
  request.done(function (data) {
    const submitAction = component + data.action;
    // open with empty header to avoid Bootstrap title area
    main.modalOpen("", data.html, data.button, submitAction, "modal-l");
    stripModalChromeWithRetries();
  });
});

/* ---------- Delete flow ---------- */
$(document).on("click", ".delete", function () {
  const formData = new FormData();
  formData.append("id", $(this).data("id"));
  main.confirmMessage(
    "warning",
    "DELETE RECORD",
    "Are you sure you want to delete this record? ",
    "deleteRecord",
    formData
  );
});

function deleteRecord(formData) {
  const action = module + "delete";
  const request = main.send_ajax(formData, action, "POST", true);
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
