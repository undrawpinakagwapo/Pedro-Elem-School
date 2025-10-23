(function () {
  var base = typeof URL_BASED !== "undefined" ? URL_BASED : "/";
  var sep = (base && !base.endsWith('/')) ? '/' : '';
  var module = base + sep + "component/supplementary-classes/";

  var CAN_EDIT = false; // set from boot/fetch

  /* ---------------- AJAX ---------------- */
  function ajax(path, fd, cb) {
    if (!(window.main && typeof main.send_ajax === "function")) {
      console.error("main.send_ajax not available");
      cb({ status: false, message: "AJAX not available" });
      return;
    }
    var req = main.send_ajax(fd, module + path, "POST", true);
    req.done(function (resp) {
      try {
        cb(typeof resp === "string" ? JSON.parse(resp) : resp);
      } catch (e) {
        cb({ status: false, message: "Bad JSON" });
      }
    });
    req.fail(function (xhr) {
      cb({
        status: false,
        message: (xhr && xhr.responseText) || "Request failed",
      });
    });
  }

  /* --------------- helpers --------------- */
  function renderSelect(el, items, valKey, labelFn, current) {
    if (!el) return;
    items = Array.isArray(items) ? items : [];
    var html = items
      .map(function (it) {
        var v = it[valKey];
        var lbl = labelFn(it);
        var sel = String(v) === String(current) ? " selected" : "";
        return '<option value="' + v + '"' + sel + ">" + lbl + "</option>";
      })
      .join("");
    el.innerHTML = html;
    if (current != null) el.value = String(current);
  }

  function labelSection(it) {
    return (
      (it.grade_name ? "Grade " + it.grade_name + " - " : "") + (it.name || "")
    );
  }
  function labelSY(it) {
    return it.school_year || "";
  }
  function escapeHtml(s) {
    return String(s || "").replace(/[&<>"']/g, function (m) {
      return {
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#39;",
      }[m];
    });
  }

  function actionCellTpl(r) {
    if (r.status === "retained") {
      return '<span class="sc-badge sc-badge-danger">RETAINED</span>';
    }
    if (!CAN_EDIT) {
      return '<span class="sc-badge sc-badge-muted" title="Only the adviser can encode remedials.">Adviser only</span>';
    }
    return (
      '<button type="button" class="scx-btn-ghost sc-rem-btn" data-name="' +
      escapeHtml(r.full_name || "") +
      '">Remedial</button>'
    );
  }

  function rowTpl(r) {
    return (
      "" +
      '<tr data-student="' +
      r.id +
      '">' +
      "<td>" +
      escapeHtml(r.full_name || "") +
      "</td>" +
      "<td>" +
      escapeHtml(r.LRN || "") +
      "</td>" +
      "<td>" +
      escapeHtml((r.grade_name || "") + " - " + (r.section_name || "")) +
      "</td>" +
      '<td class="text-center">' +
      (r.failed_count || 0) +
      "</td>" +
      "<td>" +
      escapeHtml(r.subjects_text || "") +
      "</td>" +
      '<td class="text-center">' +
      actionCellTpl(r) +
      "</td>" +
      "</tr>"
    );
  }

  /* --------------- reload list --------------- */
  function reload() {
    var $sec = document.getElementById("scSection");
    var $cur = document.getElementById("scCurriculum");
    var $q = document.getElementById("scSearch");
    var $lt = document.getElementById("scListType");

    var fd = new FormData();
    if ($sec && $sec.value) fd.append("section_id", $sec.value);
    if ($cur && $cur.value) fd.append("curriculum_id", $cur.value);
    if ($q) fd.append("q", $q.value || "");
    if ($lt) fd.append("list_type", $lt.value || "summer");

    ajax("fetch", fd, function (resp) {
      if (!resp || !resp.status) {
        if (window.main && main.alertMessage)
          main.alertMessage(
            "danger",
            (resp && resp.message) || "Failed to load."
          );
        return;
      }

      renderSelect(
        document.getElementById("scSection"),
        resp.sections || [],
        "id",
        labelSection,
        resp.section_id
      );
      renderSelect(
        document.getElementById("scCurriculum"),
        resp.curricula || [],
        "id",
        labelSY,
        resp.curriculum_id
      );

      var lt = document.getElementById("scListType");
      if (lt && resp.list_type) lt.value = resp.list_type;

      CAN_EDIT = !!resp.can_edit;

      var tbody = document.getElementById("scTbody");
      var rows = resp.rows || [];
      if (!rows.length) {
        var msg =
          resp.list_type === "retained"
            ? "No retained students."
            : "No students eligible for summer classes.";
        tbody.innerHTML =
          '<tr><td colspan="6" class="text-center" style="color:#94a3b8;padding:22px;">' +
          msg +
          "</td></tr>";
        return;
      }
      tbody.innerHTML = rows.map(rowTpl).join("");
    });
  }

  /* --------------- remedial modal --------------- */
  var currentStudentId = null;

  function openRem() {
    var b = document.getElementById("scRemBackdrop");
    var m = document.getElementById("scRemModal");
    if (b) {
      b.style.display = "block";
      b.setAttribute("aria-hidden", "false");
    }
    if (m) {
      m.style.display = "block";
      m.setAttribute("aria-hidden", "false");
    }
  }
  function closeRem() {
    var b = document.getElementById("scRemBackdrop");
    var m = document.getElementById("scRemModal");
    if (b) {
      b.style.display = "none";
      b.setAttribute("aria-hidden", "true");
    }
    if (m) {
      m.style.display = "none";
      m.setAttribute("aria-hidden", "true");
    }
  }

  function remedialRowTpl(r) {
    var fr = r.final_rating != null ? Number(r.final_rating).toFixed(2) : "—";
    var rm = r.remedial_mark != null ? Number(r.remedial_mark) : "";
    // These are now empty, as they will be calculated by JavaScript
    var rec = "";
    var rem = "";

    return (
      "" +
      '<tr data-subject="' +
      r.subject_id +
      '">' +
      "<td>" +
      escapeHtml(r.subject_label) +
      "</td>" +
      // Final Rating (display only)
      '<td class="sc-final-rating">' +
      fr +
      "</td>" +
      // Remedial Mark (the ONLY input)
      '<td><input type="number" step="0.01" min="0" max="100" class="scm-input sc-rem-mark" value="' +
      rm +
      '"></td>' +
      // Recomputed Grade (now a display span)
      '<td class="text-center sc-recomputed-grade">' +
      rec +
      "</td>" +
      // Remarks (now a display span)
      '<td class="text-center sc-remarks">' +
      rem +
      "</td>" +
      "</tr>"
    );
  }

  function recalculateRemedialRow(tr) {
    // Get the elements from the table row
    var finalRatingEl = tr.querySelector(".sc-final-rating");
    var remedialMarkInput = tr.querySelector(".sc-rem-mark");
    var recomputedGradeEl = tr.querySelector(".sc-recomputed-grade");
    var remarksEl = tr.querySelector(".sc-remarks");

    // Get the values, converting them to numbers
    var finalRating = parseFloat(finalRatingEl.textContent);
    var remedialMark = parseFloat(remedialMarkInput.value);

    // If there's no remedial mark, clear the calculated fields
    if (isNaN(remedialMark)) {
      recomputedGradeEl.textContent = "";
      remarksEl.textContent = "";
      remarksEl.style.color = "";
      return;
    }

    // --- Perform the calculations ---
    var recomputedGrade = (finalRating + remedialMark) / 2;
    var roundedGrade = Math.round(recomputedGrade); // Round to nearest whole number

    // Update the "Recomputed Final Grade" cell
    recomputedGradeEl.textContent = roundedGrade;

    // Update the "Remarks" cell based on the grade
    if (roundedGrade >= 75) {
      remarksEl.textContent = "Passed";
      remarksEl.style.color = "#16a34a"; // Green color for pass
    } else {
      remarksEl.textContent = "Failed";
      remarksEl.style.color = "#dc2626"; // Red color for fail
    }
  }

  // Load rows for the selected student (server enforces adviser-only)
  function loadRemedialList(studentId) {
    // Do not load remedials on retained list (there is no button there anyway)
    var lt = document.getElementById("scListType");
    if (lt && lt.value === "retained") {
      return;
    }

    var $sec = document.getElementById("scSection");
    var $cur = document.getElementById("scCurriculum");

    var fd = new FormData();
    fd.append("section_id", $sec.value);
    fd.append("curriculum_id", $cur.value);
    fd.append("student_id", studentId);

    var $tb = document.getElementById("scRemTbody");
    $tb.innerHTML =
      '<tr><td colspan="5" class="text-center" style="color:#94a3b8;padding:12px;">Loading…</td></tr>';

    ajax("fetchRemedial", fd, function (resp) {
      if (!resp || !resp.status) {
        var msg = resp && resp.message ? resp.message : "Failed to load.";
        $tb.innerHTML =
          '<tr><td colspan="5" style="color:#ef4444;padding:12px;">' +
          msg +
          "</td></tr>";
        return;
      }
      var list = resp.rows || [];
      if (!list.length) {
        $tb.innerHTML =
          '<tr><td colspan="5" class="text-center" style="color:#94a3b8;padding:12px;">No failed subjects found for this student.</td></tr>';
        return;
      }

      // Render the table rows
      $tb.innerHTML = list.map(remedialRowTpl).join("");

      // --- NEW LOGIC ---
      // Loop through each new row
      $tb.querySelectorAll("tr[data-subject]").forEach(function (tr) {
        var input = tr.querySelector(".sc-rem-mark");

        // Run the calculation immediately to show any existing data
        recalculateRemedialRow(tr);

        // Add a listener to recalculate every time the user types
        input.addEventListener("input", function () {
          recalculateRemedialRow(tr);
        });
      });
    });
  }

  function collectPayload() {
    var out = [];
    document
      .querySelectorAll("#scRemTbody tr[data-subject]")
      .forEach(function (tr) {
        var subjectId = tr.getAttribute("data-subject");
        var frTxt = tr.querySelector(".sc-final-rating").textContent.trim();
        var mk = tr.querySelector(".sc-rem-mark").value;

        // Read from the text content of the display elements
        var rc = tr.querySelector(".sc-recomputed-grade").textContent.trim();
        var rm = tr.querySelector(".sc-remarks").textContent.trim();

        out.push({
          subject_id: Number(subjectId),
          final_rating: frTxt === "—" ? null : Number(frTxt),
          remedial_mark: mk === "" ? "" : Number(mk),
          recomputed_final: rc === "" ? "" : Number(rc),
          remarks: rm,
        });
      });
    return out;
  }

  /* --------------- boot --------------- */
  document.addEventListener("DOMContentLoaded", function () {
    var b = window.__SC_BOOT__ || {};
    renderSelect(
      document.getElementById("scSection"),
      b.sections || [],
      "id",
      labelSection,
      b.section_id
    );
    renderSelect(
      document.getElementById("scCurriculum"),
      b.curricula || [],
      "id",
      labelSY,
      b.curriculum_id
    );
    var ltSel = document.getElementById("scListType");
    if (ltSel) ltSel.value = b.list_type || "summer";
    CAN_EDIT = !!b.can_edit;

    // initial refresh (SSR table exists but ensure up-to-date)
    reload();

    var $sec = document.getElementById("scSection");
    var $cur = document.getElementById("scCurriculum");
    var $q = document.getElementById("scSearch");

    if ($sec) $sec.addEventListener("change", reload);
    if ($cur) $cur.addEventListener("change", reload);
    if (ltSel) ltSel.addEventListener("change", reload);
    if ($q)
      $q.addEventListener("input", function () {
        clearTimeout(this.__t);
        this.__t = setTimeout(reload, 300);
      });

    // Open remedial modal (button only rendered if CAN_EDIT && list_type=summer)
    document.body.addEventListener("click", function (e) {
      var btn = e.target.closest && e.target.closest(".sc-rem-btn");
      if (!btn) return;

      var listType =
        (document.getElementById("scListType") || {}).value || "summer";
      if (listType === "retained") return;

      var tr = btn.closest("tr");
      currentStudentId = tr && tr.getAttribute("data-student");
      var name = btn.getAttribute("data-name") || "Student";
      var $name = document.getElementById("scRemStudentName");
      if ($name) $name.textContent = name;

      openRem();
      loadRemedialList(currentStudentId);
    });

    // Close modal
    var $close = document.getElementById("scRemClose");
    var $bd = document.getElementById("scRemBackdrop");
    if ($close) $close.addEventListener("click", closeRem);
    if ($bd) $bd.addEventListener("click", closeRem);

    // Save remedials
    var $save = document.getElementById("scRemSave");
    if ($save)
      $save.addEventListener("click", function () {
        var listType =
          (document.getElementById("scListType") || {}).value || "summer";
        if (listType === "retained") return;

        if (!currentStudentId) {
          if (window.main && main.alertMessage)
            main.alertMessage("warning", "No student selected.");
          else alert("No student selected.");
          return;
        }
        var $sec = document.getElementById("scSection");
        var $cur = document.getElementById("scCurriculum");
        var from = document.getElementById("scRemFrom").value;
        var to = document.getElementById("scRemTo").value;

        var fd = new FormData();
        fd.append("section_id", $sec.value);
        fd.append("curriculum_id", $cur.value);
        fd.append("student_id", currentStudentId);
        fd.append("conducted_from", from || "");
        fd.append("conducted_to", to || "");
        fd.append("rows", JSON.stringify(collectPayload()));

        $save.disabled = true;
        ajax("saveRemedial", fd, function (resp) {
          $save.disabled = false;
          if (!resp || !resp.status) {
            if (window.main && main.alertMessage)
              main.alertMessage(
                "danger",
                (resp && resp.message) || "Save failed"
              );
            else alert((resp && resp.message) || "Save failed");
            return;
          }
          if (window.main && main.alertMessage)
            main.alertMessage("success", "Remedial records saved.");
          closeRem();
          reload(); // refresh counts & list
        });
      });
  });
})();
