(function(){
  var base   = (typeof URL_BASED !== 'undefined' ? URL_BASED : '/');
  var module = base + 'component/my-grades/';

  function qs(name){
    try { return (new URLSearchParams(window.location.search)).get(name); }
    catch(e){ return null; }
  }

  function renderSelect(el, items, current){
    if (!el) return;
    items = Array.isArray(items) ? items : [];
    var html = items.map(function(it){
      var sel = (String(it.curriculum_id) === String(current)) ? ' selected' : '';
      return '<option value="'+it.curriculum_id+'"'+sel+'>'+ (it.school_year || '') +'</option>';
    }).join('');
    el.innerHTML = html;
    if (current != null) el.value = String(current);
  }

  function fmt(v){ return (v==null || v==='') ? '—' : Number(v).toFixed(2); }

  function renderMeta(metaEl, s){
    if (!metaEl) return;
    if (!s){ metaEl.innerHTML = ''; return; }
    metaEl.innerHTML =
      '<div><b>Student:</b> '+ (s.full_name||'') +'</div>'+
      '<div><b>LRN:</b> '+ (s.LRN||'') +'</div>'+
      '<div><b>Grade &amp; Section:</b> '+ (s.grade_name||'') +' - '+ (s.section_name||'') +'</div>'+
      '<div><b>School Year:</b> '+ (s.school_year||'') +'</div>';
  }

  function renderStudentLine(el, s){
    if (!el) return;
    el.textContent = s ? ((s.full_name||'') + (s.LRN ? (' • LRN: '+s.LRN) : '')) : '';
  }

  function renderTable(tbody, subjects){
    if (!tbody) return;
    subjects = Array.isArray(subjects) ? subjects : [];
    var rows = subjects.map(function(it){
      var avg = fmt(it.final_average);
      var cls = (avg==='—') ? 'mute' : (Number(avg) >= 75 ? 'ok' : 'warn');
      return ''+
        '<tr>'+
          '<td>'+ ((it.code? it.code+' - ' : '')+(it.name||'')) +'</td>'+
          '<td class="text-center">'+fmt(it.q1)+'</td>'+
          '<td class="text-center">'+fmt(it.q2)+'</td>'+
          '<td class="text-center">'+fmt(it.q3)+'</td>'+
          '<td class="text-center">'+fmt(it.q4)+'</td>'+
          '<td class="text-center"><span class="myg-pill '+cls+'">'+avg+'</span></td>'+
        '</tr>';
    }).join('');
    tbody.innerHTML = rows || '<tr><td colspan="6" class="text-center" style="color:#94a3b8;padding:22px;">No subjects found.</td></tr>';
  }

  function setDenied(vis){
    var d = document.getElementById('mygDenied');
    if (d) d.style.display = vis ? 'block' : 'none';
  }

  function send(action, formData, cb){
    if (!(window.main && typeof main.send_ajax === 'function')) {
      console.error('main.send_ajax not available');
      cb({status:false, message:'AJAX not available'});
      return;
    }
    var req = main.send_ajax(formData, module + action, 'POST', true);
    req.done(function(resp){
      try { cb(typeof resp==='string' ? JSON.parse(resp) : resp); }
      catch(e){ console.error('parse error', e, resp); cb({status:false, message:'Bad JSON'}); }
    });
    req.fail(function(xhr){
      console.error('AJAX failed', {status:xhr && xhr.status, text:xhr && xhr.responseText});
      cb({status:false, message:'Request failed'});
    });
  }

  function fetchGrades(studentId, curriculumId, rev, cb){
    var fd = new FormData();
    if (studentId)    fd.append('student_id', studentId);
    if (curriculumId) fd.append('curriculum_id', curriculumId);
    if (rev != null)  fd.append('rev', rev);
    send('fetch', fd, cb);
  }

  function printPage(){ window.print(); }

  document.addEventListener('DOMContentLoaded', function(){
    var b = window.__MYGRADES_BOOT__ || {};
    var $curr     = document.getElementById('mygCurriculum');
    var $meta     = document.getElementById('mygMeta');
    var $tbody    = document.getElementById('mygTbody');
    var $studLine = document.getElementById('mygStudentLine');
    var $print    = document.getElementById('mygPrint');

    // Prefer boot.student_id; if missing (e.g., teacher/admin), use ?student_id=
    var bootStudentId = b.student_id || qs('student_id');
    var currentCurrId = b.curriculum_id || null;
    var currentRev    = b.rev || null;

    // Polling knobs
    var BASE_INTERVAL = 10000; // 10s
    var MAX_INTERVAL  = 45000; // cap backoff
    var interval      = BASE_INTERVAL;
    var timer         = null;

    function clearPoll(){ if (timer) { clearTimeout(timer); timer = null; } }
    function schedulePoll(delay){ clearPoll(); timer = setTimeout(pollOnce, delay != null ? delay : interval); }

    function hydrate(payload){
      if (!payload || payload.status === false) {
        setDenied(true);
        renderTable($tbody, []);
        renderMeta($meta, null);
        if (window.main && main.alertMessage) {
          main.alertMessage('danger', (payload && payload.message) ? payload.message : 'Failed to load.');
        } else {
          alert((payload && payload.message) ? payload.message : 'Failed to load.');
        }
        return;
      }

      // On 304-like response: nothing to update
      if (payload.not_modified) return;

      setDenied(false);
      currentCurrId = payload.curriculum_id || currentCurrId;
      currentRev    = payload.rev || null;

      renderSelect($curr, payload.enrollments || [], currentCurrId);
      renderMeta($meta, payload.student || null);
      renderStudentLine($studLine, payload.student || null);
      renderTable($tbody, payload.subjects || []);
    }

    function pollOnce(){
      if (document.hidden) { schedulePoll(interval); return; }
      fetchGrades(bootStudentId, currentCurrId, currentRev, function(resp){
        if (!resp || resp.status === false) {
          // backoff on errors
          interval = Math.min(MAX_INTERVAL, Math.floor(interval * 1.6));
          schedulePoll(interval);
          return;
        }
        if (resp.not_modified) {
          interval = BASE_INTERVAL; // stable; keep snappy
          schedulePoll(interval);
          return;
        }
        // changed → update + reset interval
        hydrate(resp);
        interval = BASE_INTERVAL;
        schedulePoll(interval);
      });
    }

    // Initial render from boot
    renderSelect($curr, b.enrollments || [], b.curriculum_id);
    renderMeta($meta, b.student || null);
    renderStudentLine($studLine, b.student || null);
    renderTable($tbody, b.subjects || []);

    // First authoritative fetch (may set currentRev), then start polling
    fetchGrades(bootStudentId, currentCurrId, currentRev, function(r){ hydrate(r); schedulePoll(BASE_INTERVAL); });

    if ($curr) $curr.addEventListener('change', function(){
      currentCurrId = this.value;
      currentRev = null;           // reset revision when switching SY
      interval  = BASE_INTERVAL;   // reset poll interval
      clearPoll();
      fetchGrades(bootStudentId, currentCurrId, currentRev, function(r){ hydrate(r); schedulePoll(BASE_INTERVAL); });
    });

    if ($print) $print.addEventListener('click', printPage);

    // Pause when hidden; nudge an immediate poll when the tab regains focus
    document.addEventListener('visibilitychange', function(){
      if (!document.hidden) { clearPoll(); pollOnce(); }
    });
    window.addEventListener('focus', function(){ clearPoll(); pollOnce(); });
  });
})();
