(function () {
  var base   = (typeof URL_BASED !== 'undefined' ? URL_BASED : '/');
  var module = base + 'component/student-grading-management/';

  function renderSelect(el, items, valKey, labelFn, current) {
    if (!el) return;
    items = Array.isArray(items) ? items : [];
    var html = items.map(function (it) {
      var v = it[valKey];
      var lbl = labelFn(it);
      var sel = (String(v) === String(current)) ? ' selected' : '';
      return '<option value="' + v + '"' + sel + '>' + lbl + '</option>';
    }).join('');
    el.innerHTML = html;
    if (current != null) el.value = String(current);
  }

  function renderRows(tbody, rows) {
    if (!tbody) return;
    rows = Array.isArray(rows) ? rows : [];
    var html = rows.map(function (r) {
      var avg = (r.final_average === null || r.final_average === undefined)
        ? '—' : Number(r.final_average).toFixed(2);
      var avgClass = 'badge-secondary';
      if (avg !== '—') avgClass = (Number(avg) >= 75) ? 'badge-success' : 'badge-warning';
      return '' +
        '<tr>' +
          '<td>' + (r.full_name || '') + '</td>' +
          '<td>' + (r.LRN || '') + '</td>' +
          '<td>' + (r.grade_name ? ('Grade ' + r.grade_name + ' - ') : '') + (r.section_name || '') + '</td>' +
          '<td>' + (r.school_year || '') + '</td>' +
          '<td>' + (r.subject_code ? (r.subject_code + ' - ') : '') + (r.subject_name || '') + '</td>' +
          '<td class="text-center">' + (r.q1 == null ? '—' : r.q1) + '</td>' +
          '<td class="text-center">' + (r.q2 == null ? '—' : r.q2) + '</td>' +
          '<td class="text-center">' + (r.q3 == null ? '—' : r.q3) + '</td>' +
          '<td class="text-center">' + (r.q4 == null ? '—' : r.q4) + '</td>' +
          '<td class="text-center"><span class="badge ' + avgClass + '">' + avg + '</span></td>' +
        '</tr>';
    }).join('');
    tbody.innerHTML = html || '<tr><td colspan="10" class="text-center text-muted">No records.</td></tr>';
  }

  function setDisabled(el, state) { if (el) el.disabled = !!state; }

  function fetchAll(params, cb) {
    var fd = new FormData();
    if (params && params.section_id)    fd.append('section_id', params.section_id);
    if (params && params.curriculum_id) fd.append('curriculum_id', params.curriculum_id);
    if (params && params.subject_id)    fd.append('subject_id', params.subject_id);
    if (params && params.q)             fd.append('q', params.q);

    var req = (window.main && typeof main.send_ajax === 'function')
      ? main.send_ajax(fd, module + 'fetch', 'POST', true)
      : null;

    if (!req) {
      console.error('main.send_ajax not available');
      cb({ status: false, message: 'AJAX not available' });
      return;
    }

    req.done(function (resp) {
      try {
        cb(typeof resp === 'string' ? JSON.parse(resp) : resp);
      } catch (e) {
        console.error('JSON parse error:', e, 'Raw:', resp);
        cb({ status: false, message: 'Bad JSON' });
      }
    });
    req.fail(function (xhr) {
      console.error('AJAX failed', { status: xhr && xhr.status, text: xhr && xhr.responseText });
      cb({ status: false, message: 'Request failed' });
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    var $section = document.getElementById('sgmSection');
    var $curr    = document.getElementById('sgmCurriculum');
    var $subj    = document.getElementById('sgmSubject');
    var $q       = document.getElementById('sgmSearch');
    var $tbody   = document.querySelector('#sgmTable tbody');

    function labelSection(it) {
      var g = it.grade_name ? ('Grade ' + it.grade_name + ' - ') : '';
      return g + (it.name || '');
    }
    function labelSubject(it) {
      return (it.code ? it.code + ' - ' : '') + (it.name || '');
    }

    // Boot data from PHP
    var b = window.__SGM_BOOT__ || {};
    renderSelect($section, b.sections || [],  'id', labelSection, b.section_id);
    renderSelect($curr,    b.curricula || [], 'id', function (it) { return it.school_year; }, b.curriculum_id);
    renderSelect($subj,    b.subjects || [],  'id', labelSubject, b.subject_id);
    renderRows($tbody, b.rows || []);

    var loading = false;
    function reload(withParams) {
      if (loading) return;
      loading = true;
      setDisabled($section, true);
      setDisabled($curr, true);
      setDisabled($subj, true);

      var params = withParams || {
        section_id: $section && $section.value,
        curriculum_id: $curr && $curr.value,
        subject_id: $subj && $subj.value,
        q: $q && $q.value
      };

      fetchAll(params, function (data) {
        var denied = !data || data.status === false;
        // If access denied, retry letting backend pick allowed defaults
        if (denied && data && /not assigned/i.test(data.message || '')) {
          // 1) keep section, drop curriculum/subject
          return fetchAll({ section_id: params.section_id, q: params.q }, function (d2) {
            if (!d2 || d2.status === false) {
              // 2) drop all filters, backend picks everything
              return fetchAll({ q: params.q }, function (d3) { finish(d3, true); });
            }
            finish(d2, true);
          });
        }
        finish(data, false);
      });
    }

    function finish(data, warningShown) {
      loading = false;
      setDisabled($section, false);
      setDisabled($curr, false);
      setDisabled($subj, false);

      if (!data || !data.status) {
        var msg = (data && data.message) ? data.message : 'Failed to load';
        if (window.main && main.alertMessage && !warningShown) main.alertMessage('danger', msg);
        else if (!warningShown) alert(msg);
        return;
      }

      // Always trust server-selected ids & lists
      renderSelect($section, data.sections || [],  'id', labelSection, data.section_id);
      renderSelect($curr,    data.curricula || [], 'id', function (it) { return it.school_year; }, data.curriculum_id);
      renderSelect($subj,    data.subjects || [],  'id', labelSubject, data.subject_id);
      renderRows($tbody, data.rows || []);
    }

    function onChange() { reload(); }
    if ($section) $section.addEventListener('change', onChange);
    if ($curr)    $curr.addEventListener('change', onChange);
    if ($subj)    $subj.addEventListener('change', onChange);
    if ($q) {
      $q.addEventListener('input', function () {
        clearTimeout(this.__t);
        this.__t = setTimeout(reload, 300);
      });
    }

    // Ensure UI matches server defaults right away
    reload();
  });
})();
