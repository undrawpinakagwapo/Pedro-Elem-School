// Student Dashboard page scripts
(function () {
  const BASE = (typeof URL_BASED !== 'undefined' ? URL_BASED : '/');
  const MY_GRADES_BASE = `${BASE}component/my-grades/`;             // subjects/grades
  const STUD_ATT_BASE  = `${BASE}component/student-attendance/`;    // recent attendance

  const $ = (sel, root=document) => root.querySelector(sel);

  /* =========================
   * Courses (subjects) panel
   * =======================*/
  function badgeLetter(name) {
    return (String(name || '').trim()[0] || '•').toUpperCase();
  }

  function renderCourses(data) {
  const listEl  = document.querySelector('#sd-course-list');
  const labelEl = document.querySelector('#sd-class-label');
  if (!listEl) return;

  // make the list a 2-col grid
  listEl.classList.add('sd-courses-grid');

  // Header label: Grade • Section • SY
  if (labelEl && data && data.student) {
    const parts = [
      data.student.grade_name || '',
      data.student.section_name || '',
      data.student.school_year || ''
    ].filter(Boolean);
    labelEl.textContent = parts.join(' • ');
  }

  const subjects = Array.isArray(data?.subjects) ? data.subjects : [];
  if (!subjects.length) {
    listEl.innerHTML = `
      <div class="sd-empty">No subjects found for this school year.</div>
    `;
    return;
  }

  // Render ONLY subject badge + name (no grades/progress)
  listEl.innerHTML = subjects.map((subj) => `
    <div class="sd-course">
      <div class="sd-badge">${(String(subj.name || '').trim()[0] || '•').toUpperCase()}</div>
      <div class="sd-subject-name">${subj.name ?? ''}</div>
    </div>
  `).join('');
}


  async function fetchSubjects({ curriculumId, rev }) {
    if (window.main && typeof main.send_ajax === 'function') {
      const fd = new FormData();
      if (curriculumId) fd.append('curriculum_id', curriculumId);
      if (rev)          fd.append('rev', rev);
      return new Promise((resolve, reject) => {
        const req = main.send_ajax(fd, MY_GRADES_BASE + 'fetch', 'POST', true);
        req.done((resp) => { try { resolve(typeof resp === 'object' ? resp : JSON.parse(resp)); } catch { resolve(resp); } });
        req.fail(reject);
      });
    } else {
      const fd = new FormData();
      if (curriculumId) fd.append('curriculum_id', curriculumId);
      if (rev)          fd.append('rev', rev);
      const res = await fetch(MY_GRADES_BASE + 'fetch', { method:'POST', body:fd });
      return res.json();
    }
  }

  async function initCourses() {
    const card = $('#sd-courses-card');
    if (!card) return;

    const curriculumId = card.getAttribute('data-curriculum-id') || '';
    const rev          = card.getAttribute('data-rev') || '';

    try {
      const data = await fetchSubjects({ curriculumId, rev });
      if (!data || data.status !== true || data.not_modified) return;
      renderCourses(data);
    } catch (e) {
      const listEl = $('#sd-course-list');
      if (listEl) {
        listEl.innerHTML = `
          <div class="sd-course">
            <div class="sd-badge">!</div>
            <div class="sd-muted">Failed to load subjects.</div>
          </div>`;
      }
    }
  }

  /* =========================
   * Recent Attendance panel
   * =======================*/
  function renderAttendance(items) {
    const list = document.querySelector('#sd-att-list');
    if (!list) return;

    if (!Array.isArray(items) || !items.length) {
      list.innerHTML = `<div class="sd-result"><div class="sd-muted">No recent school days found.</div></div>`;
      return;
    }

    list.innerHTML = items.map(it => {
      // Only show chips if we actually have a record for that day
      const hasRecord = (it.am !== null || it.pm !== null || (it.remarks && it.remarks !== ''));

      const amOK = (it.am === 'Present');
      const pmOK = (it.pm === 'Present');

      const chips = hasRecord
        ? `
          <div class="d-flex gap-2">
            <span class="sd-chip ${amOK ? 'ok':'no'}">AM: ${amOK ? 'P':'A'}</span>
            <span class="sd-chip ${pmOK ? 'ok':'no'}">PM: ${pmOK ? 'P':'A'}</span>
          </div>`
        : `<div></div>`; // blank (no record)

      const remarksHTML = it.remarks
        ? `<span class="${it.remarks.toUpperCase()==='ABSENT' ? 'text-danger fw-semibold' : 'sd-muted'}">${it.remarks}</span>`
        : ''; // blank if no remarks

      return `
        <div class="sd-result">
          <div class="sd-date">
            <div class="fw-bold">${it.month} ${it.date}, ${it.day}, ${it.year}</div>
          </div>
          ${chips}
          <div>${remarksHTML}</div>
        </div>`;
    }).join('');
  }

  async function fetchRecentAttendance(limit) {
    if (window.main && typeof main.send_ajax === 'function') {
      const fd = new FormData();
      if (limit) fd.append('limit', String(limit));
      return new Promise((resolve, reject) => {
        const req = main.send_ajax(fd, STUD_ATT_BASE + 'recent', 'POST', true);
        req.done((resp) => { try { resolve(typeof resp === 'object' ? resp : JSON.parse(resp)); } catch { resolve(resp); } });
        req.fail(reject);
      });
    } else {
      const fd = new FormData();
      if (limit) fd.append('limit', String(limit));
      const res = await fetch(STUD_ATT_BASE + 'recent', { method:'POST', body:fd });
      return res.json();
    }
  }

  async function initAttendance() {
    const card = $('#sd-att-card');
    if (!card) return;
    const limit = parseInt(card.getAttribute('data-limit') || '7', 10);

    try {
      const data = await fetchRecentAttendance(limit);
      if (!data || data.status !== true) {
        const list = $('#sd-att-list');
        if (list) list.innerHTML = `<div class="sd-result"><div class="sd-muted">${(data && data.message) ? data.message : 'Failed to load attendance.'}</div></div>`;
        return;
      }
      renderAttendance(data.items || []);
    } catch (e) {
      const list = $('#sd-att-list');
      if (list) list.innerHTML = `<div class="sd-result"><div class="sd-muted">Network error loading attendance.</div></div>`;
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    initCourses();
    initAttendance();
    console.log("Student Dashboard JS loaded.");
  });
})();
