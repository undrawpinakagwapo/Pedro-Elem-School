// core/components/SfReportsController/js/custom.js

// ---------- Base paths ----------
const SF_BASE       = (typeof URL_BASED !== 'undefined' ? URL_BASED : '/');
const SF_MODULE     = `${SF_BASE}component/sf-reports/`;              // source, listSections, listCurricula, listStudents
const SF_STUDATT    = `${SF_BASE}component/student-attendance/`;      // StudentAttendanceController endpoints (SF2 export)
const SG_ENTRY_BASE = `${SF_BASE}component/student-grade-entry/`;     // ExportSf9Controller / ExportSf10Controller

(function () {
  /* -------------------------------------------------------------
   * Small Utilities
   * ----------------------------------------------------------- */
  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));
  const toJSON = (x) => {
    if (!x) return null;
    if (typeof x === 'object') return x;
    try { return JSON.parse(x); } catch { return null; }
  };

  // Month → {num, half} helpers (half: 'first' or 'second' of the SY)
  const MONTH_MAP = {
    '01': { num: 1,  half: 'second' }, '02': { num: 2,  half: 'second' }, '03': { num: 3,  half: 'second' },
    '04': { num: 4,  half: 'second' }, '05': { num: 5,  half: 'second' },
    '06': { num: 6,  half: 'first'  }, '07': { num: 7,  half: 'first'  }, '08': { num: 8,  half: 'first'  },
    '09': { num: 9,  half: 'first'  }, '10': { num: 10, half: 'first'  }, '11': { num: 11, half: 'first'  },
    '12': { num: 12, half: 'first'  },
  };

  function schoolYearToCalendarYear(syText, monthVal) {
    const m = String(monthVal || '').padStart(2, '0');
    const halves = (String(syText || '').match(/(\d{4})\s*[-–]\s*(\d{4})/) || []);
    const y1 = parseInt(halves[1] || '', 10);
    const y2 = parseInt(halves[2] || '', 10);
    if (!y1 || !y2 || !MONTH_MAP[m]) return null;
    return (MONTH_MAP[m].half === 'first') ? y1 : y2;
  }

  /* -------------------------------------------------------------
   * Slim, framework-free modal (overlay + host container)
   * ----------------------------------------------------------- */
  let escBound = false;

  function ensureModalHost() {
    let host = document.getElementById('sfr-modal-host');
    if (host) return host;

    if (!document.getElementById('sfr-modal-css')) {
      const css = `
      .sfr-overlay{position:fixed;inset:0;background:rgba(15,23,42,.45);
        backdrop-filter:saturate(120%) blur(2px);z-index:9998;opacity:0;visibility:hidden;pointer-events:none;
        transition:opacity .18s ease, visibility .18s ease}
      .sfr-overlay.show{opacity:1;visibility:visible;pointer-events:auto}
      .sfr-host{position:fixed;inset:0;padding:24px;overflow:auto;z-index:9999;pointer-events:none;display:block}
      .sfr-inner{max-width:720px;margin:40px auto 80px;pointer-events:auto}
      .sfr-inner > .sge-modal-card{border-radius:16px;overflow:hidden}
      body.sfr-modal-open .modal, body.sfr-modal-open .modal-backdrop{display:none !important}`;
      const tag = document.createElement('style');
      tag.id = 'sfr-modal-css';
      tag.textContent = css;
      document.head.appendChild(tag);
    }

    host = document.createElement('div');
    host.id = 'sfr-modal-host';
    host.innerHTML = `
      <div class="sfr-overlay" aria-hidden="true"></div>
      <div class="sfr-host" role="dialog" aria-modal="true" aria-labelledby="sfr-modal-title">
        <div class="sfr-inner"></div>
      </div>`;
    document.body.appendChild(host);

    const overlay = host.querySelector('.sfr-overlay');
    overlay.addEventListener('click', closeModal);

    if (!escBound) {
      escBound = true;
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeModal();
      });
    }
    return host;
  }

  function openModalWithHTML(html) {
    const host = ensureModalHost();
    const inner = host.querySelector('.sfr-inner');
    const overlay = host.querySelector('.sfr-overlay');

    inner.innerHTML = html;
    document.body.classList.add('sfr-modal-open');
    overlay.classList.add('show');

    $$('.sfr-close,[data-sfr-close]', inner).forEach(btn =>
      btn.addEventListener('click', closeModal)
    );

    const exportBtn = $('#sfr_export_btn', inner) || $('.btn.btn-primary[onclick="sfReportsDoExport()"]', inner);
    if (exportBtn) {
      exportBtn.addEventListener('click', (ev) => {
        ev.preventDefault();
        sfReportsDoExport();
      });
    }

    // Bind cascading selects
    bindModalCascades();

    // Auto-populate students for SF9/SF10 when section+curriculum are present
    const formVal = $('#sfr_form', inner)?.value || '';
    if (formVal === 'sf9' || formVal === 'sf10') {
      const sectionId = $('#sfr_section')?.value || '';
      const currId    = $('#sfr_curriculum')?.value || '';
      if (sectionId && currId) fetchStudentsForSf(sectionId, currId);
    }
  }

  function closeModal() {
    const host = document.getElementById('sfr-modal-host');
    if (!host) return;
    const overlay = host.querySelector('.sfr-overlay');
    const inner   = host.querySelector('.sfr-inner');

    overlay.classList.remove('show');
    setTimeout(() => {
      inner.innerHTML = '';
      document.body.classList.remove('sfr-modal-open');
    }, 160);
  }

  /* -------------------------------------------------------------
   * Data → Modal open (fetches server HTML)
   * ----------------------------------------------------------- */
  function openExportModal(form) {
    const fd = new FormData();
    fd.append('form', form);

    if (!(window.main && typeof main.send_ajax === 'function')) {
      alert('Core AJAX helper not available.');
      return;
    }

    const req = main.send_ajax(fd, SF_MODULE + 'source', 'POST', true);
    req.done(function (resp) {
      const data = toJSON(resp) || resp;
      if (!data || data.status !== true || !data.html) {
        const msg = (data && data.message) ? data.message : 'Failed to open modal';
        if (main.alertMessage) main.alertMessage('danger', msg); else alert(msg);
        return;
      }
      openModalWithHTML(data.html);
    });
    req.fail(function () {
      if (main.alertMessage) main.alertMessage('danger', 'Request failed.');
      else alert('Request failed.');
    });
  }

  /* -------------------------------------------------------------
   * Cascading selects + Students loader (SF9/SF10)
   * ----------------------------------------------------------- */
  function renderOptions(selectEl, rows, valKey, labelKey) {
    if (!selectEl) return;
    const list = Array.isArray(rows) ? rows : [];
    selectEl.innerHTML = list.map(r => `<option value="${r[valKey]}">${r[labelKey]}</option>`).join('');
    selectEl.dispatchEvent(new Event('change', { bubbles: true }));
  }

  // UPDATED: generalized for SF9 and SF10
  function fetchStudentsForSf(sectionId, curriculumId) {
    const studentSel = document.getElementById('sfr_student');
    if (!studentSel) return;

    const fd = new FormData();
    fd.append('section_id', sectionId);
    fd.append('curriculum_id', curriculumId);

    const r = main.send_ajax(fd, SF_MODULE + 'listStudents', 'POST', true);
    r.done(function (resp) {
      const data = toJSON(resp) || resp;
      if (!data || data.status !== true) {
        studentSel.innerHTML = '<option value="">No students found</option>';
        return;
      }
      renderOptions(studentSel, data.students, 'id', 'name');
    });
    r.fail(function () {
      studentSel.innerHTML = '<option value="">Failed to load students</option>';
    });
  }

  function bindModalCascades() {
    const $grade    = document.getElementById('sfr_grade');
    const $section  = document.getElementById('sfr_section');
    const $curr     = document.getElementById('sfr_curriculum');
    const formValue = document.getElementById('sfr_form')?.value || '';

    // Grade -> Sections -> (auto) Curricula
    if ($grade) $grade.addEventListener('change', function () {
      const fd = new FormData();
      fd.append('grade_id', this.value);

      const r = main.send_ajax(fd, SF_MODULE + 'listSections', 'POST', true);
      r.done(function (resp) {
        const data = toJSON(resp) || resp;
        if (!data || data.status !== true) return;
        renderOptions($section, data.sections, 'id', 'name');

        if ($section && $section.value) {
          const fd2 = new FormData();
          fd2.append('section_id', $section.value);
          const r2 = main.send_ajax(fd2, SF_MODULE + 'listCurricula', 'POST', true);
          r2.done(function (resp2) {
            const d2 = toJSON(resp2) || resp2;
            if (!d2 || d2.status !== true) return;
            renderOptions($curr, d2.curricula, 'id', 'school_year');

            if ((formValue === 'sf9' || formValue === 'sf10') && $section.value && $curr.value) {
              fetchStudentsForSf($section.value, $curr.value);
            }
          });
        }
      });
    });

    // Section -> Curricula (+ students if SF9/SF10)
    if ($section) $section.addEventListener('change', function () {
      const fd = new FormData();
      fd.append('section_id', this.value);

      const r = main.send_ajax(fd, SF_MODULE + 'listCurricula', 'POST', true);
      r.done(function (resp) {
        const data = toJSON(resp) || resp;
        if (!data || data.status !== true) return;
        renderOptions($curr, data.curricula, 'id', 'school_year');

        if ((formValue === 'sf9' || formValue === 'sf10') && $section.value && $curr.value) {
          fetchStudentsForSf($section.value, $curr.value);
        }
      });
    });

    // Curriculum -> Students (SF9/SF10)
    if ($curr && (formValue === 'sf9' || formValue === 'sf10')) {
      $curr.addEventListener('change', function () {
        const sid = $section?.value || '';
        const cid = $curr?.value || '';
        if (sid && cid) fetchStudentsForSf(sid, cid);
      });
    }
  }

  /* -------------------------------------------------------------
   * Export handler (SF2 + SF9 + SF10)
   * ----------------------------------------------------------- */
  window.sfReportsDoExport = function () {
    const form      = document.getElementById('sfr_form')?.value || '';

    const sectionId = document.getElementById('sfr_section')?.value || '';
    const currId    = document.getElementById('sfr_curriculum')?.value || '';

    if (form === 'sf2') {
      const monthVal  = document.getElementById('sfr_month')?.value || '';
      if (!sectionId || !currId || !monthVal) {
        const msg = 'Please select Grade Level, Section, School Year, and Month.';
        if (main.alertMessage) main.alertMessage('warning', msg); else alert(msg);
        return;
      }
      const currSel   = $('#sfr_curriculum');
      const syText    = currSel ? (currSel.options[currSel.selectedIndex]?.text || '') : '';
      const yearNum   = schoolYearToCalendarYear(syText, monthVal);
      if (!yearNum) {
        const msg = 'Invalid School Year format. Expected "YYYY-YYYY".';
        if (main.alertMessage) main.alertMessage('danger', msg); else alert(msg);
        return;
      }
      const date = `${yearNum}-${String(monthVal).padStart(2, '0')}-01`;
      const qs = new URLSearchParams({ section_id: sectionId, curriculum_id: currId, date }).toString();
      closeModal();
      window.location.href = `${SF_STUDATT}export?${qs}`;
      return;
    }

    if (form === 'sf9' || form === 'sf10') {
      const studentId = document.getElementById('sfr_student')?.value || '';
      if (!sectionId || !currId || !studentId) {
        const msg = 'Please select Grade Level, Section, School Year, and Student.';
        if (main.alertMessage) main.alertMessage('warning', msg); else alert(msg);
        return;
      }
      // Stream from ExportSf9Controller::export() or ExportSf10Controller::export()
      const qs = new URLSearchParams({
        section_id: sectionId,
        curriculum_id: currId,
        student_id: studentId
      }).toString();
      closeModal();
      const endpoint = (form === 'sf10') ? 'exportSf10' : 'exportSf9';
      window.location.href = `${SG_ENTRY_BASE}${endpoint}?${qs}`;
      return;
    }

    if (main.alertMessage) main.alertMessage('danger', 'Unknown form type.');
    else alert('Unknown form type.');
  };

  /* -------------------------------------------------------------
   * Entry points (cards)
   * ----------------------------------------------------------- */
  document.addEventListener('DOMContentLoaded', function () {
    const c2  = document.getElementById('sf2-card');
    const c9  = document.getElementById('sf9-card');
    const c10 = document.getElementById('sf10-card');

    if (c2)  c2.addEventListener('click',  () => openExportModal('sf2'));
    if (c9)  c9.addEventListener('click',  () => openExportModal('sf9'));
    if (c10) c10.addEventListener('click', () => openExportModal('sf10'));
  });
})();
