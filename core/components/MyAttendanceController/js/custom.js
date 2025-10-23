// My Attendance (student view) — monthly list + exact SY pill + holiday label
(function(){
  var base   = (typeof URL_BASED !== 'undefined' ? URL_BASED : '/');
  var module = base + 'component/my-attendance/';

  function setDisabled(el, state){ if (el) el.disabled = !!state; }

  function send(action, formData, cb){
    if (!(window.main && typeof main.send_ajax === 'function')) {
      console.error('main.send_ajax not available');
      cb({status:false, message:'AJAX not available'}); return;
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

  function fetchAll(params, cb){
    var fd = new FormData();
    if (params.section_id) fd.append('section_id', params.section_id);
    if (params.date)       fd.append('date', params.date);
    if (params.student_id) fd.append('student_id', params.student_id);
    send('fetch', fd, cb);
  }

  // Format "YYYY-MM-DD" → "YYYY-M-DD"
  function fmtDate(ymd){
    var m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(String(ymd || ''));
    if (!m) return String(ymd || '');
    var y = m[1], mm = String(parseInt(m[2], 10)), dd = m[3];
    return y + '-' + mm + '-' + dd;
  }

  function badge(val){
    if (val === 'P') return '<span class="badge ok">Present</span>';
    if (val === 'A') return '<span class="badge warn">Absent</span>';
    return '<span class="badge mute">—</span>';
  }

  function renderDays(tbody, days){
    days = Array.isArray(days) ? days : [];
    if (!days.length){
      tbody.innerHTML = '<tr><td colspan="5" class="text-center" style="color:#94a3b8;padding:22px;">No entries for this month.</td></tr>';
      return;
    }
    var html = days.map(function(r){
      var isHol = !!r.is_holiday;
      var dateText = fmtDate(r.date || '');
      if (isHol) dateText += ' - Holiday';
      return ''+
      '<tr>'+
        '<td>'+ dateText +'</td>'+
        '<td>'+ (r.weekday||'') +'</td>'+
        '<td class="text-center">'+ (isHol ? '<span class="badge mute">—</span>' : badge(r.am_status)) +'</td>'+
        '<td class="text-center">'+ (isHol ? '<span class="badge mute">—</span>' : badge(r.pm_status)) +'</td>'+
        '<td>'+ (isHol ? '—' : (r.remarks ? String(r.remarks) : '—')) +'</td>'+
      '</tr>';
    }).join('');
    tbody.innerHTML = html;
  }

  document.addEventListener('DOMContentLoaded', function(){
    var $section = document.getElementById('maeSection');
    var $date    = document.getElementById('maeDate');
    var $tbody   = document.querySelector('#maeTable tbody');
    var $sy      = document.getElementById('maeSY');
    var $autoLbl = document.getElementById('maeAutoNote');

    var b = window.__MAE_BOOT__ || {};
    function labelSection(it){ return (it.grade_name ? ('Grade '+it.grade_name+' - ') : '') + (it.name||''); }
    function renderSelect(el, items, valKey, labelFn, current){
      if (!el) return;
      items = Array.isArray(items) ? items : [];
      var html = items.map(function(it){
        var v = it[valKey];
        var lbl = labelFn(it);
        var sel = (String(v) === String(current)) ? ' selected' : '';
        return '<option value="'+v+'"'+sel+'>'+lbl+'</option>';
      }).join('');
      el.innerHTML = html;
      if (current != null) el.value = String(current);
    }

    renderSelect($section, b.sections||[], 'id', labelSection, b.section_id);
    if ($date && b.date) $date.value = b.date;
    if ($sy) $sy.textContent = b.school_year || '—';
    renderDays($tbody, b.days || []);

    function reload(){
      setDisabled($section,true); setDisabled($date,true);
      var params = {
        section_id: $section && $section.value,
        date:       $date && $date.value,
        student_id: b.student_id || null
      };
      fetchAll(params, function(data){
        setDisabled($section,false); setDisabled($date,false);
        if (!data || !data.status){
          var msg = (data && data.message) ? data.message : 'Failed to load';
          if (window.main && main.alertMessage) main.alertMessage('danger', msg); else alert(msg);
          return;
        }
        renderSelect($section, data.sections||[], 'id', labelSection, data.section_id);
        if ($date && data.date) $date.value = data.date;
        if ($sy) $sy.textContent = data.school_year || '—';
        renderDays($tbody, data.days || []);
      });
    }

    if ($section) $section.addEventListener('change', reload);
    if ($date)    $date.addEventListener('change', reload);

    // optional: light auto-refresh ticker
    var POLL_MS = 20000, timer=null;
    function startPolling(){
      stopPolling();
      timer = setInterval(function(){
        var a = document.activeElement;
        if (a && (a === $date || a === $section)) return;
        reload();
      }, POLL_MS);
      if ($autoLbl) $autoLbl.style.opacity = '0.75';
    }
    function stopPolling(){ if (timer){ clearInterval(timer); timer=null; } }

    if ($date){ $date.addEventListener('focus', stopPolling); $date.addEventListener('blur', startPolling); }
    if ($section){ $section.addEventListener('focus', stopPolling); $section.addEventListener('blur', startPolling); }

    reload();
    startPolling();
  });
})();
