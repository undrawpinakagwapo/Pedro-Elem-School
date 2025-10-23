(function(){
  var base   = (typeof URL_BASED !== 'undefined' ? URL_BASED : '/');
  var module = base + 'component/student-grade-entry/';

  // ---- state for locking inputs after save ----
  var __gradesLocked = false;

  /* ---------- PERSISTED LOCK STATE (per section/curriculum/subject[/user]) ---------- */
  function getUserKey(){
    // If your app exposes a stable user id/username, include it here to avoid cross-user leakage.
    // Example integrations (uncomment/adjust as needed):
    // return String(window.__USER_ID__ || window.__USERNAME__ || (window.main && main.currentUserId) || '');
    return String(window.__USER_ID__ || '');
  }

  function makeLockKey(params){
    var u = getUserKey();
    var s = params && params.section_id != null ? String(params.section_id) : '';
    var c = params && params.curriculum_id != null ? String(params.curriculum_id) : '';
    var sb= params && params.subject_id != null ? String(params.subject_id) : '';
    // key shape: sge:locked:[user:]section|curriculum|subject
    return 'sge:locked:' + (u ? (u + ':') : '') + s + '|' + c + '|' + sb;
  }

  function getPersistedLock(params){
    try {
      var v = localStorage.getItem(makeLockKey(params));
      return v === '1';
    } catch(e){ return false; }
  }

  function setPersistedLock(params, state){
    try {
      localStorage.setItem(makeLockKey(params), state ? '1' : '0');
    } catch(e){}
  }

  /* ---------- helpers ---------- */
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

  function normalizeGender(g){
    g = String(g || '').trim().toUpperCase();
    if (g === 'MALE' || g === 'M' || g === 'BOY') return 'M';
    if (g === 'FEMALE' || g === 'F' || g === 'GIRL') return 'F';
    return '';
  }

  function filterRowsByGender(rows, want){
    if (!want || want === 'ALL') return rows;
    return (rows || []).filter(function(r){
      return normalizeGender(r.gender) === want;
    });
  }

  function renderRows(tbody, rows, currentGender){
    rows = Array.isArray(rows) ? rows : [];
    if (currentGender && currentGender !== 'ALL') {
      rows = filterRowsByGender(rows, currentGender);
    }
    var html = rows.map(function(r){
      var avg = (r.final_average == null) ? '—' : Number(r.final_average).toFixed(2);
      var avgCls = (avg === '—') ? 'mute' : (Number(avg) >= 75 ? 'ok' : 'warn');
      var g = normalizeGender(r.gender);
      return ''+
      '<tr data-student="'+r.student_id+'" data-gender="'+g+'">'+
        '<td><div style="font-weight:600;">'+(r.full_name||'')+'</div>'+
            '<div style="color:#64748b; font-size:12.5px;">LRN: '+(r.LRN||'')+'</div></td>'+
        '<td class="text-center"><input type="number" step="0.01" class="sgx-num sge-q" data-q="q1" min="0" max="100" value="'+(r.q1==null?'':Number(r.q1).toFixed(2))+'"></td>'+
        '<td class="text-center"><input type="number" step="0.01" class="sgx-num sge-q" data-q="q2" min="0" max="100" value="'+(r.q2==null?'':Number(r.q2).toFixed(2))+'"></td>'+
        '<td class="text-center"><input type="number" step="0.01" class="sgx-num sge-q" data-q="q3" min="0" max="100" value="'+(r.q3==null?'':Number(r.q3).toFixed(2))+'"></td>'+
        '<td class="text-center"><input type="number" step="0.01" class="sgx-num sge-q" data-q="q4" min="0" max="100" value="'+(r.q4==null?'':Number(r.q4).toFixed(2))+'"></td>'+
        '<td class="text-center"><button type="button" class="sgx-btn-xs sge-core-btn" data-student="'+r.student_id+'">Core Values</button></td>'+
        '<td class="text-center"><span class="sgx-pill '+avgCls+'">'+avg+'</span></td>'+
      '</tr>';
    }).join('');
    tbody.innerHTML = html || '<tr><td colspan="7" class="text-center" style="color:#94a3b8;padding:22px;">No students found.</td></tr>';
  }

  function recomputeFinal(tr){
    var qs = tr.querySelectorAll('.sge-q');
    var vals = [], i;
    for(i=0;i<qs.length;i++){
      var v = parseFloat(qs[i].value);
      if (!isNaN(v)) vals.push(v);
    }
    var pill = tr.querySelector('.sgx-pill');
    if (!pill) return;
    if (!vals.length){ pill.textContent='—'; pill.className='sgx-pill mute'; return; }
    var avg = (vals.reduce((a,b)=>a+b,0)/vals.length).toFixed(2);
    pill.textContent = avg;
    pill.className = 'sgx-pill ' + (avg >= 75 ? 'ok' : 'warn');
  }

  function collectRows(tbody){
    var out = [];
    tbody.querySelectorAll('tr[data-student]').forEach(function(tr){
      function numOrEmpty(sel){
        var vEl = tr.querySelector(sel);
        var v = vEl ? vEl.value : '';
        return (v===''? '': Number(v));
      }
      out.push({
        student_id: Number(tr.getAttribute('data-student')),
        q1: numOrEmpty('input[data-q="q1"]'),
        q2: numOrEmpty('input[data-q="q2"]'),
        q3: numOrEmpty('input[data-q="q3"]'),
        q4: numOrEmpty('input[data-q="q4"]')
      });
    });
    return out;
  }

  function setDisabled(el, state){ if (el) el.disabled = !!state; }

  // Lock/unlock helpers (PERSIST + APPLY)
  function setInputsLocked(state, paramsForPersist){
    __gradesLocked = !!state;

    // Apply to current inputs/buttons
    document.querySelectorAll('.sge-q').forEach(function(inp){
      inp.disabled = __gradesLocked;
    });
    var $save = document.getElementById('sgeSave');
    setDisabled($save, __gradesLocked);

    // Persist for the current context
    var p = paramsForPersist;
    if (!p){
      var $section = document.getElementById('sgeSection');
      var $curr    = document.getElementById('sgeCurriculum');
      var $subj    = document.getElementById('sgeSubject');
      p = {
        section_id: $section && $section.value,
        curriculum_id: $curr && $curr.value,
        subject_id: $subj && $subj.value
      };
    }
    setPersistedLock(p, __gradesLocked);
  }

  function applyLockToNewRows(){
    if (__gradesLocked){
      document.querySelectorAll('.sge-q').forEach(function(inp){ inp.disabled = true; });
      var $save = document.getElementById('sgeSave');
      setDisabled($save, true);
    }
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

  function fetchAll(params, cb){
    var fd = new FormData();
    if (params.section_id)    fd.append('section_id', params.section_id);
    if (params.curriculum_id) fd.append('curriculum_id', params.curriculum_id);
    if (params.subject_id)    fd.append('subject_id', params.subject_id);
    if (params.gender)        fd.append('gender', params.gender);
    if (params.q)             fd.append('q', params.q);
    send('fetch', fd, cb);
  }

  // ---- Grade slip endpoint
  function fetchGradeSlip(params, cb){
    var fd = new FormData();
    if (params.section_id)    fd.append('section_id', params.section_id);
    if (params.curriculum_id) fd.append('curriculum_id', params.curriculum_id);
    if (params.student_id)    fd.append('student_id', params.student_id);
    send('gradeSlip', fd, cb);
  }

  /* ---------- Core Values endpoints ---------- */
  function fetchCoreValues(params, cb){
    var fd = new FormData();
    if (params.section_id)    fd.append('section_id', params.section_id);
    if (params.curriculum_id) fd.append('curriculum_id', params.curriculum_id);
    if (params.subject_id)    fd.append('subject_id', params.subject_id);
    if (params.student_id)    fd.append('student_id', params.student_id);
    send('fetchCoreValues', fd, cb);
  }

  function saveCoreValues(params, cb){
    var fd = new FormData();
    fd.append('section_id',    params.section_id);
    fd.append('curriculum_id', params.curriculum_id);
    if (params.subject_id) fd.append('subject_id', params.subject_id);
    fd.append('student_id',    params.student_id);

    // Build from DOM → rows → values
    var rows   = params.rows || [];
    var values = rowsToValues(rows);

    // Send BOTH for forward/backward compat
    fd.append('values', JSON.stringify(values)); // controller uses this
    fd.append('rows',   JSON.stringify(rows));   // harmless extra

    send('saveCoreValues', fd, cb);
  }

  // ---- Slip rendering
  function renderSlipParts(payload){
    if (!payload || !payload.status) {
      var msg = (payload && payload.message) ? payload.message : 'Failed to load grade slip.';
      return { meta: '', table: '<div style="color:#ef4444; padding:12px;">'+msg+'</div>' };
    }

    var s = payload.student || {};
    var meta =
      '<div><b>Student:</b> '+ (s.full_name||'') +'</div>'+
      '<div><b>LRN:</b> '+ (s.LRN||'') +'</div>'+
      '<div><b>Grade &amp; Section:</b> '+ (s.grade_name||'') +' - '+ (s.section_name||'') +'</div>'+
      '<div><b>School Year:</b> '+ (s.school_year||'') +'</div>';

    var rows = (payload.subjects||[]).map(function(it){
      var fmt = function(v){ return (v==null || v==='') ? '—' : Number(v).toFixed(2); }
      var subj = (it.code ? it.code+' - ' : '') + (it.name||'');
      return ''+
        '<tr>'+
          '<td>'+ subj +'</td>'+
          '<td class="text-center">'+ fmt(it.q1) +'</td>'+
          '<td class="text-center">'+ fmt(it.q2) +'</td>'+
          '<td class="text-center">'+ fmt(it.q3) +'</td>'+
          '<td class="text-center">'+ fmt(it.q4) +'</td>'+
          '<td class="text-center sge-strong">'+ fmt(it.final_average) +'</td>'+
        '</tr>';
    }).join('');

    var table =
      '<table class="sge-table">'+
        '<thead>'+
          '<tr>'+
            '<th>Subject</th>'+
            '<th class="text-center">Q1</th>'+
            '<th class="text-center">Q2</th>'+
            '<th class="text-center">Q3</th>'+
            '<th class="text-center">Q4</th>'+
            '<th class="text-center">Final Avg</th>'+
          '</tr>'+
        '</thead>'+
        '<tbody>'+ rows +'</tbody>'+
      '</table>';

    return { meta: meta, table: table };
  }

  /* ---------- Print (modal-only) ---------- */
  function buildPrintableHTML(){
    var metaEl  = document.getElementById('sgeSlipMeta');
    var tableEl = document.getElementById('sgeSlipTableWrap');
    var currSel = document.getElementById('sgeCurriculum');

    var schoolYear   = currSel && currSel.options.length ? currSel.options[currSel.selectedIndex].text : '';

    var metaHTML  = metaEl  ? metaEl.innerHTML  : '';
    var tableHTML = tableEl ? tableEl.innerHTML : '';

    var styles = '\
      <style>\
        @page { margin: 18mm; }\
        * { box-sizing: border-box; }\
        body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji", "Segoe UI Emoji"; color:#0f172a; }\
        h1 { font-size: 20px; margin: 0 0 6px 0; }\
        .subtitle { color:#64748b; font-size: 12.5px; margin-bottom: 16px; }\
        .meta { display:grid; grid-template-columns: repeat(2, minmax(220px, 1fr)); gap:8px 16px; margin: 10px 0 14px; }\
        .meta > div { font-size: 13px; color:#475569; }\
        .meta b { color:#0f172a; }\
        .wrap { border:1px solid #e5e7eb; border-radius:10px; overflow:hidden; }\
        table { width:100%; border-collapse:separate; border-spacing:0; font-size: 12.5px; }\
        thead th { text-align:left; padding:10px 12px; border-bottom:1px solid #e5e7eb; background:#f8fafc; color:#64748b; }\
        tbody td { padding:10px 12px; border-bottom:1px solid #f1f5f9; }\
        tbody tr:nth-child(odd){ background:#ffffff; }\
        tbody tr:nth-child(even){ background:#fcfdff; }\
        .text-center { text-align:center; }\
        .strong { font-weight:700; }\
        .footer-note { margin-top: 10px; color:#94a3b8; font-size:11.5px; }\
      </style>';

    var html = '\
      <!doctype html>\
      <html>\
      <head><meta charset="utf-8">'+styles+'<title>Grade Slip</title></head>\
      <body>\
        <h1>Grade Slip</h1>\
        <div class="subtitle">'+(schoolYear ? ('School Year: ' + schoolYear) : '')+'</div>\
        <div class="meta">'+metaHTML+'</div>\
        <div class="wrap">'+tableHTML+'</div>\
        <div class="footer-note">Generated via system • '+new Date().toLocaleString()+'</div>\
      </body>\
      </html>';
    return html;
  }

  function printSlip(){
    var html = buildPrintableHTML();
    var iframe = document.createElement('iframe');
    iframe.style.position = 'fixed';
    iframe.style.right = '0';
    iframe.style.bottom = '0';
    iframe.style.width = '0';
    iframe.style.height = '0';
    iframe.style.border = '0';
    document.body.appendChild(iframe);

    var doc = iframe.contentWindow || iframe.contentDocument;
    if (doc.document) doc = doc.document;

    doc.open();
    doc.write(html);
    doc.close();

    iframe.onload = function(){
      setTimeout(function(){
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
        setTimeout(function(){
          document.body.removeChild(iframe);
        }, 500);
      }, 50);
    };
  }

  /* ---------- Polished modal helpers (accessible) ---------- */
  var __lastFocus = null;

  function openModal(){
    var b = document.getElementById('sgeModalBackdrop');
    var m = document.getElementById('sgeModal');
    var sel = document.getElementById('sgeSlipStudent');

    __lastFocus = document.activeElement;

    if (b) { b.style.display = 'block'; b.setAttribute('aria-hidden','false'); }
    if (m) {
      m.style.display = 'block';
      m.setAttribute('aria-hidden','false');
      setTimeout(function(){ if (sel) sel.focus(); }, 0);
    }

    document.addEventListener('keydown', escHandler);
    document.addEventListener('focus', focusTrap, true);
  }

  function closeModal(){
    var b = document.getElementById('sgeModalBackdrop');
    var m = document.getElementById('sgeModal');
    if (b) { b.style.display = 'none'; b.setAttribute('aria-hidden','true'); }
    if (m) { m.style.display = 'none'; m.setAttribute('aria-hidden','true'); }

    document.removeEventListener('keydown', escHandler);
    document.removeEventListener('focus', focusTrap, true);

    if (__lastFocus && typeof __lastFocus.focus === 'function') {
      __lastFocus.focus();
    }
  }

  function escHandler(e){
    if (e.key === 'Escape') closeModal();
  }

  function focusTrap(e){
    var m = document.getElementById('sgeModal');
    if (!m || m.getAttribute('aria-hidden') === 'true') return;
    if (!m.contains(e.target)) {
      e.stopPropagation();
      var sel = document.getElementById('sgeSlipStudent');
      if (sel) sel.focus();
    }
  }

  // Build student dropdown options from the table
  function buildStudentOptionsFromTable($tbody){
    var list = [];
    $tbody.querySelectorAll('tr[data-student]').forEach(function(tr){
      var id = tr.getAttribute('data-student');
      var name = tr.querySelector('td div[style*="font-weight:600"]');
      var lrnEl = tr.querySelector('td div[style*="color:#64748b"]');
      var fullName = name ? name.textContent.trim() : ('#'+id);
      var lrn = lrnEl ? (lrnEl.textContent.replace('LRN:','').trim()) : '';
      list.push({ id: id, full_name: fullName, LRN: lrn });
    });
    return list;
  }

  function populateSlipStudentSelect(opts, selectedId){
    var sel = document.getElementById('sgeSlipStudent');
    if (!sel) return;
    sel.innerHTML = (opts||[]).map(function(o){
      var lbl = o.full_name + (o.LRN ? (' — LRN: '+o.LRN) : '');
      var selAttr = (String(o.id)===String(selectedId)) ? ' selected ' : '';
      return '<option value="'+o.id+'"'+selAttr+'>'+lbl+'</option>';
    }).join('');
  }

  function loadSlipFor(studentId){
    var $slipMeta = document.getElementById('sgeSlipMeta');
    var $slipWrap = document.getElementById('sgeSlipTableWrap');

    if (!studentId){
      if ($slipMeta) $slipMeta.innerHTML = '';
      if ($slipWrap) $slipWrap.innerHTML = '<div class="sge-mini">Select a student to view their grade slip.</div>';
      return;
    }

    // skeleton while loading
    if ($slipMeta) $slipMeta.innerHTML = '';
    if ($slipWrap) $slipWrap.innerHTML = '<div class="sge-skeleton"></div>';

    var $section = document.getElementById('sgeSection');
    var $curr    = document.getElementById('sgeCurriculum');

    var params = {
      section_id: $section && $section.value,
      curriculum_id: $curr && $curr.value,
      student_id: studentId
    };

    fetchGradeSlip(params, function(resp){
      var parts = renderSlipParts(resp);
      if ($slipMeta) $slipMeta.innerHTML = parts.meta;
      if ($slipWrap) $slipWrap.innerHTML = parts.table;
    });
  }

  /* ---------- Export SF10 modal helpers ---------- */
  function openSF10Modal(){
    var b = document.getElementById('sgeSF10Backdrop');
    var m = document.getElementById('sgeSF10Modal');
    if (b) { b.style.display = 'block'; b.setAttribute('aria-hidden','false'); }
    if (m) { m.style.display = 'block'; m.setAttribute('aria-hidden','false'); }
  }
  function closeSF10Modal(){
    var b = document.getElementById('sgeSF10Backdrop');
    var m = document.getElementById('sgeSF10Modal');
    if (b) { b.style.display = 'none'; b.setAttribute('aria-hidden','true'); }
    if (m) { m.style.display = 'none'; m.setAttribute('aria-hidden','true'); }
  }

  /* ---------- Export SF9 modal helpers (new) ---------- */
  function openSF9Modal(){
    var b = document.getElementById('sgeSF9Backdrop');
    var m = document.getElementById('sgeSF9Modal');
    if (b) { b.style.display = 'block'; b.setAttribute('aria-hidden','false'); }
    if (m) { m.style.display = 'block'; m.setAttribute('aria-hidden','false'); }
  }
  function closeSF9Modal(){
    var b = document.getElementById('sgeSF9Backdrop');
    var m = document.getElementById('sgeSF9Modal');
    if (b) { b.style.display = 'none'; b.setAttribute('aria-hidden','true'); }
    if (m) { m.style.display = 'none'; m.setAttribute('aria-hidden','true'); }
  }

  /* ---------- Core Values modal helpers (new) ---------- */
  function openCoreModal(){
    var b = document.getElementById('sgeCoreBackdrop');
    var m = document.getElementById('sgeCoreModal');
    if (b) { b.style.display = 'block'; b.setAttribute('aria-hidden','false'); }
    if (m) { m.style.display = 'block'; m.setAttribute('aria-hidden','false'); }
  }
  function closeCoreModal(){
    var b = document.getElementById('sgeCoreBackdrop');
    var m = document.getElementById('sgeCoreModal');
    if (b) { b.style.display = 'none'; b.setAttribute('aria-hidden','true'); }
    if (m) { m.style.display = 'none'; m.setAttribute('aria-hidden','true'); }
  }

  // Collect rows per core (supports multiple behavior statements per core)
  function getCoreRows(){
    var cores = ['maka_diyos','makatao','maka_kalikasan','maka_bansa'];
    var rows = [];
    cores.forEach(function(core){
      // number of behavior rows = # of Q1 selects for that core
      var count = document.querySelectorAll('#sgeCoreModal .sge-cv[data-core="'+core+'"][data-q="q1"]').length || 1;
      for (var b = 1; b <= count; b++){
        var row = { core_name: core, behavior_index: b, q1:'', q2:'', q3:'', q4:'' };
        ['q1','q2','q3','q4'].forEach(function(q){
          var list = document.querySelectorAll('#sgeCoreModal .sge-cv[data-core="'+core+'"][data-q="'+q+'"]');
          var sel  = list[b-1]; // 0-based index for behavior row
          if (sel) row[q] = sel.value || '';
        });
        rows.push(row);
      }
    });
    return rows;
  }

  function rowsToValues(rows){
    var out = {
      maka_diyos: [],
      makatao: [],
      maka_kalikasan: [],
      maka_bansa: []
    };
    (rows || []).forEach(function(r){
      if (!out[r.core_name]) out[r.core_name] = [];
      out[r.core_name].push({
        behavior_index: parseInt(r.behavior_index, 10) || 1,
        q1: r.q1 || '',
        q2: r.q2 || '',
        q3: r.q3 || '',
        q4: r.q4 || ''
      });
    });
    // sort each core by behavior_index to be tidy
    Object.keys(out).forEach(function(k){
      out[k].sort(function(a,b){ return (a.behavior_index||1)-(b.behavior_index||1); });
    });
    return out;
  }

  // Accept array rows, object-of-arrays, legacy object
  function setCoreSelects(data){
    document.querySelectorAll('#sgeCoreModal .sge-cv').forEach(function(sel){ sel.value=''; });

    if (Array.isArray(data)){
      data.forEach(function(r){
        var b = parseInt(r.behavior_index, 10) || 1;
        ['q1','q2','q3','q4'].forEach(function(q){
          var sel = document.querySelector('#sgeCoreModal .sge-cv[data-core="'+r.core_name+'"][data-q="'+q+'"][data-beh="'+b+'"]');
          if (sel) sel.value = r[q] || '';
        });
      });
      return;
    }

    if (data && typeof data === 'object'){
      var looksLikeArrays = ['maka_diyos','makatao','maka_kalikasan','maka_bansa']
        .some(function(k){ return Array.isArray(data[k]); });

      if (looksLikeArrays){
        ['maka_diyos','makatao','maka_kalikasan','maka_bansa'].forEach(function(core){
          var list = Array.isArray(data[core]) ? data[core] : [];
          list.forEach(function(row){
            var b = parseInt(row.behavior_index,10) || 1;
            ['q1','q2','q3','q4'].forEach(function(q){
              var sel = document.querySelector('#sgeCoreModal .sge-cv[data-core="'+core+'"][data-q="'+q+'"][data-beh="'+b+'"]');
              if (sel) sel.value = row[q] || '';
            });
          });
        });
        return;
      }

      // legacy { core: {q1..q4} }
      document.querySelectorAll('#sgeCoreModal .sge-cv[data-beh="1"]').forEach(function(sel){
        var core = sel.getAttribute('data-core');
        var q    = sel.getAttribute('data-q');
        var vObj = data && data[core];
        var v    = vObj && vObj[q];
        sel.value = v || '';
      });
    }
  }

  /* ---------- main boot ---------- */
  document.addEventListener('DOMContentLoaded', function(){
    var $section = document.getElementById('sgeSection');
    var $curr    = document.getElementById('sgeCurriculum');
    var $subj    = document.getElementById('sgeSubject');
    var $gender  = document.getElementById('sgeGender');
    var $q       = document.getElementById('sgeSearch');
    var $tbody   = document.querySelector('#sgeTable tbody');
    var $save    = document.getElementById('sgeSave');
    var $edit    = document.getElementById('sgeEditGrades');

    var $gradeSlipBtn = document.getElementById('sgeGradeSlip');
    var $modalClose   = document.getElementById('sgeModalClose');
    var $backdrop     = document.getElementById('sgeModalBackdrop');
    var $slipSel      = document.getElementById('sgeSlipStudent');
    var $printBtn     = document.getElementById('sgeModalPrint');

    // SF10
    var $exportSF10Btn = document.getElementById('sgeExportSF10');
    var $sf10Backdrop  = document.getElementById('sgeSF10Backdrop');
    var $sf10Close     = document.getElementById('sgeSF10Close');
    var $sf10Student   = document.getElementById('sgeSF10Student');
    var $sf10ExportBtn = document.getElementById('sgeSF10ExportBtn');

    // SF9
    var $exportSF9Btn  = document.getElementById('sgeExportSF9');
    var $sf9Backdrop   = document.getElementById('sgeSF9Backdrop');
    var $sf9Close      = document.getElementById('sgeSF9Close');
    var $sf9Student    = document.getElementById('sgeSF9Student');
    var $sf9ExportBtn  = document.getElementById('sgeSF9ExportBtn');

    // Core Values
    var $coreBackdrop  = document.getElementById('sgeCoreBackdrop');
    var $coreClose     = document.getElementById('sgeCoreClose');
    var $coreSave      = document.getElementById('sgeCoreSave');
    var $coreStudent   = document.getElementById('sgeCoreStudentName');
    var currentCoreStudentId = null;

    function labelSection(it){ return (it.grade_name ? ('Grade '+it.grade_name+' - ') : '') + (it.name||''); }
    function labelSY(it){ return it.school_year; }
    function labelSubject(it){ return (it.code? it.code+' - ' : '') + (it.name||''); }

    // boot
    var b = window.__SGE_BOOT__ || {};
    renderSelect($section, b.sections||[],  'id', labelSection, b.section_id);
    renderSelect($curr,    b.curricula||[], 'id', labelSY,      b.curriculum_id);
    renderSelect($subj,    b.subjects||[],  'id', labelSubject, b.subject_id);
    renderRows($tbody, b.rows || [], 'ALL');

    // Start with persisted lock for the initial selection
    (function(){
      var params = {
        section_id: $section && $section.value,
        curriculum_id: $curr && $curr.value,
        subject_id: $subj && $subj.value
      };
      var locked = getPersistedLock(params);
      setInputsLocked(locked, params);
    })();

    // live recompute
    document.body.addEventListener('input', function(e){
      if (e.target && e.target.classList.contains('sge-q')) {
        var tr = e.target.closest('tr'); if (tr) recomputeFinal(tr);
      }
    });

    // reload from server, then render w/ gender filter
    function reload(withParams){
      setDisabled($section,true); setDisabled($curr,true); setDisabled($subj,true); setDisabled($save,true);
      var params = withParams || {
        section_id: $section && $section.value,
        curriculum_id: $curr && $curr.value,
        subject_id: $subj && $subj.value,
        gender: $gender && $gender.value,
        q: $q && $q.value
      };

      // Ensure __gradesLocked reflects persisted state for this context (pre-render)
      __gradesLocked = getPersistedLock(params);

      fetchAll(params, function(data){
        var denied = !data || data.status === false;
        if (denied && data && /not assigned/i.test(data.message || '')) {
          return fetchAll({ section_id: params.section_id, q: params.q, gender: params.gender }, function(d2){
            if (!d2 || d2.status === false) return fetchAll({ q: params.q, gender: params.gender }, finish);
            finish(d2);
          });
        }
        finish(data);
      });
    }

    function finish(data){
      setDisabled($section,false); setDisabled($curr,false); setDisabled($subj,false);
      // Save enabled only if unlocked
      setDisabled($save, __gradesLocked);

      if (!data || !data.status){
        var msg = (data && data.message) ? data.message : 'Failed to load';
        if (window.main && main.alertMessage) main.alertMessage('danger', msg); else alert(msg);
        return;
      }
      renderSelect($section, data.sections||[],  'id', labelSection, data.section_id);
      renderSelect($curr,    data.curricula||[], 'id', labelSY,      data.curriculum_id);
      renderSelect($subj,    data.subjects||[],  'id', labelSubject, data.subject_id);

      // Update lock state using the (possibly server-adjusted) ids
      __gradesLocked = getPersistedLock({
        section_id: data.section_id,
        curriculum_id: data.curriculum_id,
        subject_id: data.subject_id
      });

      var want = ($gender && $gender.value) || 'ALL';
      renderRows($tbody, data.rows || [], want);

      // Re-apply lock state to freshly rendered inputs
      applyLockToNewRows();
    }

    function syncLockFromCurrentSelects(){
      var params = {
        section_id: $section && $section.value,
        curriculum_id: $curr && $curr.value,
        subject_id: $subj && $subj.value
      };
      var locked = getPersistedLock(params);
      setInputsLocked(locked, params);
    }

    if ($section) $section.addEventListener('change', function(){ syncLockFromCurrentSelects(); reload(); });
    if ($curr)    $curr.addEventListener('change',    function(){ syncLockFromCurrentSelects(); reload(); });
    if ($subj)    $subj.addEventListener('change',    function(){ syncLockFromCurrentSelects(); reload(); });
    if ($gender)  $gender.addEventListener('change',  function(){ reload(); });
    if ($q)       $q.addEventListener('input', function(){ clearTimeout(this.__t); this.__t=setTimeout(reload,300); });

    // SAVE → lock inputs after successful save (and persist)
    if ($save) $save.addEventListener('click', function(){
      var fd = new FormData();
      fd.append('section_id',    $section.value);
      fd.append('curriculum_id', $curr.value);
      fd.append('subject_id',    $subj.value);
      fd.append('rows',          JSON.stringify(collectRows($tbody)));
      setDisabled($save,true);
      send('save', fd, function(resp){
        if (!resp || !resp.status){
          setDisabled($save,false);
          var msg = (resp && resp.message) ? resp.message : 'Save failed';
          if (window.main && main.alertMessage) main.alertMessage('danger', msg); else alert(msg);
          return;
        }
        if (window.main && main.alertMessage) main.alertMessage('success','Grades saved successfully.');
        // lock and keep them locked after reload (persisted per selection)
        setInputsLocked(true, {
          section_id: $section && $section.value,
          curriculum_id: $curr && $curr.value,
          subject_id: $subj && $subj.value
        });
        reload();
      });
    });

    // EDIT GRADES → unlock inputs for editing (and persist)
    if ($edit) $edit.addEventListener('click', function(){
      setInputsLocked(false, {
        section_id: $section && $section.value,
        curriculum_id: $curr && $curr.value,
        subject_id: $subj && $subj.value
      });
      // focus first input if available
      var first = document.querySelector('.sge-q');
      if (first) first.focus();
    });

    // ===== Grade Slip modal logic =====
    if ($gradeSlipBtn) $gradeSlipBtn.addEventListener('click', function(){
      var opts = buildStudentOptionsFromTable($tbody);
      if (!opts.length){
        if (window.main && main.alertMessage) main.alertMessage('warning','No students to show.');
        else alert('No students to show.');
        return;
      }
      populateSlipStudentSelect(opts, opts[0].id);
      openModal();
      loadSlipFor(opts[0].id);
    });

    if ($slipSel) $slipSel.addEventListener('change', function(){ loadSlipFor(this.value); });
    if ($modalClose) $modalClose.addEventListener('click', closeModal);
    if ($backdrop)   $backdrop.addEventListener('click', closeModal);

    // ---- Print button
    if ($printBtn) $printBtn.addEventListener('click', function(){
      var tableEl = document.getElementById('sgeSlipTableWrap');
      if (!tableEl || !tableEl.innerHTML.trim()){
        if (window.main && main.alertMessage) main.alertMessage('warning','Please select a student first.');
        else alert('Please select a student first.');
        return;
      }
      printSlip();
    });

    // ===== Export SF10 modal logic =====
    if ($exportSF10Btn) $exportSF10Btn.addEventListener('click', function(){
      var opts = buildStudentOptionsFromTable($tbody);
      if (!opts.length){
        if (window.main && main.alertMessage) main.alertMessage('warning','No students to export.');
        else alert('No students to export.');
        return;
      }
      if ($sf10Student) {
        $sf10Student.innerHTML = opts.map(function(o){
          var lbl = o.full_name + (o.LRN ? (' — LRN: '+o.LRN) : '');
          return '<option value="'+o.id+'">'+lbl+'</option>';
        }).join('');
      }
      openSF10Modal();
    });

    if ($sf10Close)   $sf10Close.addEventListener('click', closeSF10Modal);
    if ($sf10Backdrop)$sf10Backdrop.addEventListener('click', closeSF10Modal);

    if ($sf10ExportBtn) $sf10ExportBtn.addEventListener('click', function(){
      var $section = document.getElementById('sgeSection');
      var $curr    = document.getElementById('sgeCurriculum');
      var studentId= $sf10Student && $sf10Student.value;

      if (!studentId){
        if (window.main && main.alertMessage) main.alertMessage('warning','Please choose a student.');
        else alert('Please choose a student.');
        return;
      }

      var form = document.createElement('form');
      form.method = 'POST';
      form.action = (typeof URL_BASED !== 'undefined' ? URL_BASED : '/') +
                    'component/student-grade-entry/exportSf10';
      form.target = '_blank';

      var f1 = document.createElement('input'); f1.type='hidden'; f1.name='section_id';    f1.value = $section && $section.value;
      var f2 = document.createElement('input'); f2.type='hidden'; f2.name='curriculum_id'; f2.value = $curr && $curr.value;
      var f3 = document.createElement('input'); f3.type='hidden'; f3.name='student_id';    f3.value = studentId;

      form.appendChild(f1); form.appendChild(f2); form.appendChild(f3);
      document.body.appendChild(form);
      form.submit();
      setTimeout(function(){ document.body.removeChild(form); }, 1000);

      closeSF10Modal();
    });

    // ===== Export SF9 modal logic =====
    if ($exportSF9Btn) $exportSF9Btn.addEventListener('click', function(){
      var opts = buildStudentOptionsFromTable($tbody);
      if (!opts.length){
        if (window.main && main.alertMessage) main.alertMessage('warning','No students to export.');
        else alert('No students to export.');
        return;
      }
      if ($sf9Student) {
        $sf9Student.innerHTML = opts.map(function(o){
          var lbl = o.full_name + (o.LRN ? (' — LRN: '+o.LRN) : '');
          return '<option value="'+o.id+'">'+lbl+'</option>';
        }).join('');
      }
      openSF9Modal();
    });

    if ($sf9Close)    $sf9Close.addEventListener('click', closeSF9Modal);
    if ($sf9Backdrop) $sf9Backdrop.addEventListener('click', closeSF9Modal);

    if ($sf9ExportBtn) $sf9ExportBtn.addEventListener('click', function(){
      var $section = document.getElementById('sgeSection');
      var $curr    = document.getElementById('sgeCurriculum');
      var studentId= $sf9Student && $sf9Student.value;

      if (!studentId){
        if (window.main && main.alertMessage) main.alertMessage('warning','Please choose a student.');
        else alert('Please choose a student.');
        return;
      }

      var form = document.createElement('form');
      form.method = 'POST';
      form.action = (typeof URL_BASED !== 'undefined' ? URL_BASED : '/') +
                    'component/student-grade-entry/exportSf9';
      form.target = '_blank';

      var f1 = document.createElement('input'); f1.type='hidden'; f1.name='section_id';    f1.value = $section && $section.value;
      var f2 = document.createElement('input'); f2.type='hidden'; f2.name='curriculum_id'; f2.value = $curr && $curr.value;
      var f3 = document.createElement('input'); f3.type='hidden'; f3.name='student_id';    f3.value = studentId;

      form.appendChild(f1); form.appendChild(f2); form.appendChild(f3);
      document.body.appendChild(form);
      form.submit();
      setTimeout(function(){ document.body.removeChild(form); }, 1000);

      closeSF9Modal();
    });

    /* ===== Core Values logic ===== */
    document.body.addEventListener('click', function(e){
      var btn = e.target.closest && e.target.closest('.sge-core-btn');
      if (!btn) return;
      var tr = btn.closest('tr');
      var nameEl = tr && tr.querySelector('td div[style*="font-weight:600"]');
      var name = nameEl ? nameEl.textContent.trim() : 'Student';
      currentCoreStudentId = btn.getAttribute('data-student');
      if ($coreStudent) $coreStudent.textContent = name;

      // clear selects first
      setCoreSelects(null);

      // fetch existing values to prefill
      var params = {
        section_id: $section && $section.value,
        curriculum_id: $curr && $curr.value,
        subject_id: $subj && $subj.value,
        student_id: currentCoreStudentId
      };
      fetchCoreValues(params, function(resp){
        if (resp && resp.status) {
          setCoreSelects(resp.rows || resp.values || []);
        }
        openCoreModal();
      });
    });

    if ($coreClose)    $coreClose.addEventListener('click', closeCoreModal);
    if ($coreBackdrop) $coreBackdrop.addEventListener('click', closeCoreModal);

    if ($coreSave) $coreSave.addEventListener('click', function(){
      if (!currentCoreStudentId){
        if (window.main && main.alertMessage) main.alertMessage('warning','No student selected.');
        else alert('No student selected.');
        return;
      }
      var params = {
        section_id: $section && $section.value,
        curriculum_id: $curr && $curr.value,
        subject_id: $subj && $subj.value,
        student_id: currentCoreStudentId,
        rows: getCoreRows()
      };
      saveCoreValues(params, function(resp){
        if (!resp || !resp.status){
          var msg = (resp && resp.message) ? resp.message : 'Save failed';
          if (window.main && main.alertMessage) main.alertMessage('danger', msg); else alert(msg);
          return;
        }
        if (window.main && main.alertMessage) main.alertMessage('success','Core Values saved.');
        closeCoreModal();
      });
    });

    // initial sync with server
    reload();
  });
})();
