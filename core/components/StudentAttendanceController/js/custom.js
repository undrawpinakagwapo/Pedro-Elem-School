// custom.js
(function(){
  var base   = (typeof URL_BASED !== 'undefined' ? URL_BASED : '/');
  var module = base + 'component/student-attendance/';

  // Auto-save each scan to the server via controller->punch()
  var SCAN_PUNCH_TO_SERVER = true;

  // Prevent rapid duplicate scans of the same QR (per slot)
  var RECENT_TTL_MS = 2000; // 2 seconds
  var recentHits = new Map(); // key: slot + ':' + normalized LRN -> timestamp

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

  // Normalize gender for consistent sorting/filtering
  function normGender(g){
    g = String(g||'').trim().toUpperCase().replace(/\s+/g,'');
    if (g==='M' || g==='MALE' || g==='BOY') return 'M';
    if (g==='F' || g==='FEMALE' || g==='GIRL') return 'F';
    return '';
  }

  // ====== Auto-remarks helpers ======
  // Returns "", "Absent AM", "Absent PM", or "Absent"
  function computeAutoRemarks(amPresent, pmPresent){
    var am = !!amPresent, pm = !!pmPresent;
    if (am && pm) return '';            // fully present
    if (!am && !pm) return 'Absent';    // whole-day absent
    if (!am && pm)  return 'Absent AM';
    if (am && !pm)  return 'Absent PM';
    return '';
  }

  // Build the remarks <select> with “auto” options (disabled) + user option “Tardy”
  function buildRemarksSelectHTML(initialValue){
    var v = String(initialValue == null ? '' : initialValue);
    var options = [
      { v: '',          l: '—',          disabled: false },
      { v: 'Absent',    l: 'Absent',     disabled: true  },
      { v: 'Absent AM', l: 'Absent AM',  disabled: true  },
      { v: 'Absent PM', l: 'Absent PM',  disabled: true  },
      { v: 'Tardy',     l: 'Tardy',      disabled: false }
    ];
    var html = options.map(function(o){
      var sel = (o.v === v) ? ' selected' : '';
      var dis = o.disabled ? ' disabled' : '';
      return '<option value="'+o.v+'"'+sel+dis+'>'+o.l+'</option>';
    }).join('');
    return '<select class="sgx-remarks" data-k="remarks" aria-label="Remarks">'+html+'</select>';
  }

  // === AM/PM checkboxes + Remarks dropdown ===
  function renderRows(tbody, rows){
    rows = Array.isArray(rows) ? rows : [];

    function presentBox(key, isPresent, sid){
      var name = key + '_' + String(sid);
      var checked = isPresent ? ' checked' : '';
      var aria = (key === 'am_status' ? 'Mark AM present' : 'Mark PM present');
      return '<input type="checkbox" class="sae-chk" name="'+name+'" data-k="'+key+'"'+checked+' aria-label="'+aria+'">';
    }

    var html = rows.map(function(r){
      var amIsPresent = String(r.am_status||'') === 'P';
      var pmIsPresent = String(r.pm_status||'') === 'P';
      var lrn = String(r.LRN || '').replace(/\D+/g,'');

      // Decide what the select should show initially:
      // - Keep Tardy if provided
      // - else compute auto (“Absent…”) or “—”
      var initialRemarks = (r.remarks === 'Tardy')
        ? 'Tardy'
        : computeAutoRemarks(amIsPresent, pmIsPresent) || '';

      return ''+
      '<tr data-student="'+r.student_id+'" data-lrn="'+lrn+'">'+
        '<td><div style="font-weight:600;">'+(r.full_name||'')+'</div>'+
            '<div style="color:#64748b; font-size:12.5px;">LRN: '+(r.LRN||'')+'</div></td>'+
        '<td class="text-center">'+presentBox('am_status', amIsPresent, r.student_id)+'</td>'+
        '<td class="text-center">'+presentBox('pm_status', pmIsPresent, r.student_id)+'</td>'+
        '<td>'+buildRemarksSelectHTML(initialRemarks)+'</td>'+
      '</tr>';
    }).join('');

    tbody.innerHTML = html || '<tr><td colspan="4" class="text-center" style="color:#94a3b8;padding:22px;">No students found.</td></tr>';

    // If your PHP still renders a separate auto-remarks chip, hide it:
    tbody.querySelectorAll('.sae-auto-remarks').forEach(function(el){ el.style.display = 'none'; });
  }

  // Keep the remarks select synced with the AM/PM checkboxes,
  // except when user explicitly picked Tardy.
  function syncRemarksForRow(row){
    if (!row) return;
    var am  = row.querySelector('input[data-k="am_status"]');
    var pm  = row.querySelector('input[data-k="pm_status"]');
    var sel = row.querySelector('select[data-k="remarks"]');
    if (!sel) return;

    var amPresent = !!(am && am.checked);
    var pmPresent = !!(pm && pm.checked);

    if (sel.value === 'Tardy') {
      // Respect the user's Tardy choice; do not override.
      return;
    }

    var auto = computeAutoRemarks(amPresent, pmPresent) || '';
    ensureAutoOptions(sel); // in case options drifted
    sel.value = auto;       // set to computed auto remark (or "—")
  }

  // Ensure the select has the exact options we expect (—, Absent*, Tardy)
  function ensureAutoOptions(selectEl){
    var wanted = [
      { v: '',          text: '—',         disabled: false },
      { v: 'Absent',    text: 'Absent',    disabled: true  },
      { v: 'Absent AM', text: 'Absent AM', disabled: true  },
      { v: 'Absent PM', text: 'Absent PM', disabled: true  },
      { v: 'Tardy',     text: 'Tardy',     disabled: false }
    ];

    var have = {};
    Array.prototype.slice.call(selectEl.options).forEach(function(opt){
      have[opt.value] = opt;
    });

    wanted.forEach(function(w, idx){
      if (!have[w.v]){
        var o = document.createElement('option');
        o.value = w.v;
        o.textContent = w.text;
        o.disabled = !!w.disabled;
        if (idx >= selectEl.options.length) selectEl.add(o);
        else selectEl.add(o, selectEl.options[idx]);
      }else{
        have[w.v].disabled = !!w.disabled;
        have[w.v].textContent = w.text;
      }
    });

    // Remove any unexpected options
    Array.prototype.slice.call(selectEl.options).forEach(function(opt){
      if (!wanted.some(function(w){ return w.v === opt.value; })){
        selectEl.removeChild(opt);
      }
    });
  }

  function collectRows(tbody){
    var out = [];
    tbody.querySelectorAll('tr[data-student]').forEach(function(tr){
      var am  = tr.querySelector('input[data-k="am_status"]');
      var pm  = tr.querySelector('input[data-k="pm_status"]');
      var sel = tr.querySelector('select[data-k="remarks"]');

      var amPresent = !!(am && am.checked);
      var pmPresent = !!(pm && pm.checked);
      var finalRemarks = sel ? String(sel.value || '') : '';

      out.push({
        student_id: Number(tr.getAttribute('data-student')),
        am_status: amPresent ? 'P' : 'A',
        pm_status: pmPresent ? 'P' : 'A',
        remarks: finalRemarks
      });
    });
    return out;
  }

  function setDisabled(el, state){ if (el) el.disabled = !!state; }

  function send(action, formData, cb){
    if (!(window.main && typeof main.send_ajax === 'function')) {
      // Fallback to jQuery if present
      if (window.$) {
        $.ajax({
          url: module + action, type:'POST', data: formData,
          contentType: false, processData: false
        }).done(function(resp){ try{ cb(typeof resp==='string'?JSON.parse(resp):resp); }catch(e){ cb({status:false,message:'Bad JSON'}); } })
          .fail(function(){ cb({status:false,message:'Request failed'}); });
        return;
      }
      cb({status:false, message:'AJAX not available'});
      return;
    }
    var req = main.send_ajax(formData, module + action, 'POST', true);
    req.done(function(resp){
      try { cb(typeof resp==='string' ? JSON.parse(resp) : resp); }
      catch(e){ cb({status:false, message:'Bad JSON'}); }
    });
    req.fail(function(){ cb({status:false, message:'Request failed'}); });
  }

  function fetchAll(params, cb){
    var fd = new FormData();
    if (params.section_id)    fd.append('section_id', params.section_id);
    if (params.curriculum_id) fd.append('curriculum_id', params.curriculum_id);
    if (params.date)          fd.append('date', params.date);
    if (params.q)             fd.append('q', params.q);
    send('fetch', fd, cb);
  }

  // ---- Local storage helpers for holiday toggle (per date) ----
  function holKey(sectionId, curriculumId, date){
    return 'saeHoliday::' + String(sectionId||'') + '::' + String(curriculumId||'') + '::' + String(date||'');
  }
  function holGet(sectionId, curriculumId, date){
    try { return localStorage.getItem(holKey(sectionId, curriculumId, date)) === '1'; } catch(e){ return false; }
  }
  function holSet(sectionId, curriculumId, date, state){
    try { localStorage.setItem(holKey(sectionId, curriculumId, date), state ? '1' : '0'); } catch(e){}
  }
  function holList(sectionId, curriculumId, yearMonth){
    var out = [];
    try {
      var prefix = 'saeHoliday::' + String(sectionId||'') + '::' + String(curriculumId||'') + '::';
      for (var i=0; i<localStorage.length; i++){
        var k = localStorage.key(i);
        if (k && k.indexOf(prefix) === 0){
          var d = k.substring(prefix.length);
          if (d && d.slice(0,7) === yearMonth && localStorage.getItem(k) === '1'){
            out.push(d);
          }
        }
      }
    } catch(e){}
    out.sort();
    return out;
  }

  // ---- PER-DATE LOCK: helpers (per Section + Curriculum + Date) ----
  function lockKey(sectionId, curriculumId, date){
    return 'saeLocked::' + String(sectionId||'') + '::' + String(curriculumId||'') + '::' + String(date||'');
  }
  function lockGet(sectionId, curriculumId, date){
    try { return localStorage.getItem(lockKey(sectionId, curriculumId, date)) === '1'; } catch(e){ return false; }
  }
  function lockSet(sectionId, curriculumId, date, state){
    try { localStorage.setItem(lockKey(sectionId, curriculumId, date), state ? '1' : '0'); } catch(e){}
  }

  // ===== QR SCANNER =====
  var qrOverlay, qrVideo, qrCloseBtn, qrSwitchBtn, qrSlotLabel;
  var currentStream = null;
  var currentDeviceId = null;
  var deviceIds = [];
  var scanning = false;
  var usingZXing = false;     // track which engine we use
  var activeSlot = 'AM';      // 'AM' | 'PM'
  var detector = null;        // BarcodeDetector instance if supported
  var zxingReader = null;     // ZXing fallback
  var lastScanCtx = null;     // keep ctx for camera switching

  function el(id){ return document.getElementById(id); }
  function showToast(kind, msg){
    if (window.main && typeof main.alertMessage==='function'){ main.alertMessage(kind, msg); }
    else { console.log(kind.toUpperCase()+':', msg); }
  }

  // success feedback: toast + short beep + vibration
  function feedbackSuccess(msg){
    showToast('success', msg);
    try { if (navigator.vibrate) navigator.vibrate(80); } catch(e){}
    try {
      var ctx = new (window.AudioContext || window.webkitAudioContext)();
      var o = ctx.createOscillator(); var g = ctx.createGain();
      o.type = 'sine'; o.frequency.value = 880;
      g.gain.value = 0.06;
      o.connect(g); g.connect(ctx.destination);
      o.start();
      setTimeout(function(){ o.stop(); ctx.close(); }, 150);
    } catch(e){}
  }

  function onlyDigits(s){ return String(s||'').replace(/\D+/g,''); }
  function normLRN(s){ var t = onlyDigits(s); return t.length===12 ? t : null; }

  function findRowByLRN(tbody, lrn){
    if (!tbody) return null;
    var norm = normLRN(lrn);
    if (!norm) return null;
    return tbody.querySelector('tr[data-lrn="'+norm+'"]') || null;
  }

  function studentNameFromRow(row){
    if (!row) return '';
    var elName = row.querySelector('td > div:first-child');
    return (elName && elName.textContent) ? elName.textContent.trim() : '';
  }

  function tickRow(row, slot){
    if (!row) return;
    var cb = row.querySelector('input[data-k="'+ (slot==='AM'?'am_status':'pm_status') +'"]');
    if (cb){ cb.checked = true; }
    syncRemarksForRow(row);
    row.classList.add('sae-hit');
    setTimeout(function(){ row.classList.remove('sae-hit'); }, 1200);
    row.scrollIntoView({behavior:'smooth', block:'center'});
  }

  function markAndAllow(text, slot){
    var lrn = normLRN(text);
    if (!lrn) return { ok:false, reason:'Invalid QR: expected 12-digit LRN.' };

    var key = slot + ':' + lrn;
    var now = Date.now();
    var last = recentHits.get(key) || 0;
    if (now - last < RECENT_TTL_MS) {
      return { ok:false, reason:'Duplicate scan ignored.' };
    }
    recentHits.set(key, now);
    return { ok:true, lrn: lrn };
  }

  function punchToServer(lrn, slot, sectionId, curriculumId, date){
    return new Promise(function(resolve){
      if (!SCAN_PUNCH_TO_SERVER){
        return resolve({status:false, message:'punch disabled'});
      }
      var fd = new FormData();
      fd.append('lrn', lrn);
      fd.append('slot', slot);
      fd.append('section_id', sectionId);
      fd.append('curriculum_id', curriculumId);
      fd.append('date', date);
      send('punch', fd, function(resp){
        resolve(resp || {status:false});
      });
    });
  }

  async function handleScanPayload(rawText, slot, ctx){
    // Respect per-date lock
    if (lockGet(ctx.section_id, ctx.curriculum_id, ctx.date)) {
      showToast('info','Attendance is locked for this date. Click "Edit Attendance" to modify.');
      return;
    }

    var check = markAndAllow(rawText, slot);
    if (!check.ok){
      if (check.reason && check.reason.indexOf('Invalid') === 0) showToast('warning', check.reason);
      return;
    }
    var lrn = check.lrn;

    var $tbody = document.querySelector('#saeTable tbody');
    var row = findRowByLRN($tbody, lrn);
    if (row){
      tickRow(row, slot);
      var name = studentNameFromRow(row) || ('LRN '+lrn);
      feedbackSuccess('Scanned '+slot+' for '+name);
    }else{
      showToast('warning','Student not in current list (LRN '+lrn+').');
    }

    punchToServer(lrn, slot, ctx.section_id, ctx.curriculum_id, ctx.date).then(function(punched){
      if (!punched || !punched.status){
        if (punched && punched.message){
          showToast('info', punched.message + ' — checkbox ticked locally; click Save to persist.');
        }
      }
    });
  }

  async function getVideoDevices(){
    try{
      var devices = await navigator.mediaDevices.enumerateDevices();
      deviceIds = devices.filter(function(d){ return d.kind === 'videoinput'; }).map(function(d){ return d.deviceId; });
    }catch(e){ deviceIds = []; }
  }

  async function startStream(deviceId){
    var constraints = { video: deviceId ? {deviceId:{exact: deviceId}} : {facingMode: 'environment'}, audio:false };
    var stream = await navigator.mediaDevices.getUserMedia(constraints);
    qrVideo.srcObject = stream;
    currentStream = stream;
    currentDeviceId = deviceId || null;
    await videoReady(qrVideo);
  }

  function stopStream(){
    if (currentStream){
      currentStream.getTracks().forEach(function(t){ try{ t.stop(); }catch(e){} });
      currentStream = null;
    }
  }

  function videoReady(videoEl){
    return new Promise(function(resolve){
      function done(){
        if (videoEl.videoWidth > 0 && videoEl.videoHeight > 0) resolve();
      }
      if (videoEl.readyState >= 2 && videoEl.videoWidth > 0) return resolve();
      videoEl.addEventListener('loadedmetadata', done, {once:true});
      videoEl.addEventListener('canplay', done, {once:true});
      setTimeout(done, 800);
    });
  }

  function hasBarcodeDetector(){
    return ('BarcodeDetector' in window) && typeof window.BarcodeDetector === 'function';
  }

  async function startBarcodeLoop(ctx){
    if (!detector){
      try {
        detector = new window.BarcodeDetector({formats: ['qr_code']});
      } catch(e){
        throw new Error('BarcodeDetector init failed');
      }
    }
    scanning = true;

    async function step(){
      if (!scanning) return;

      try{
        if ('createImageBitmap' in window){
          var bitmap = await createImageBitmap(qrVideo);
          var codes = await detector.detect(bitmap);
          if (bitmap && bitmap.close) bitmap.close();
          if (codes && codes.length){
            var text = codes[0].rawValue || '';
            handleScanPayload(text, activeSlot, ctx);
          }
        }else{
          var canvas = document.createElement('canvas');
          canvas.width = qrVideo.videoWidth || 640;
          canvas.height = qrVideo.videoHeight || 480;
          var ctx2d = canvas.getContext('2d');
          ctx2d.drawImage(qrVideo, 0, 0, canvas.width, canvas.height);
          var codes2 = await detector.detect(canvas);
          if (codes2 && codes2.length){
            var text2 = codes2[0].rawValue || '';
            handleScanPayload(text2, activeSlot, ctx);
          }
        }
      }catch(e){
        // ignore and retry
      }
      setTimeout(step, 120);
    }
    step();
  }

  function loadZXing(){
    return new Promise(function(resolve, reject){
      if (window.ZXing){ return resolve(); }
      var s = document.createElement('script');
      s.src = 'https://unpkg.com/@zxing/library@0.21.3';
      s.async = true;
      s.onload = function(){ resolve(); };
      s.onerror = function(){ reject(new Error('ZXing load failed')); };
      document.head.appendChild(s);
    });
  }

  async function startZXing(ctx, deviceId){
    await loadZXing();
    usingZXing = true;
    zxingReader = new ZXing.BrowserMultiFormatReader();
    scanning = true;
    zxingReader.decodeFromVideoDevice(deviceId || undefined, 'qrVideo', function(result/*, err*/){
      if (!scanning) return;
      if (result && result.getText){
        var text = result.getText();
        handleScanPayload(text, activeSlot, ctx);
      }
    });
  }

  async function openScanner(slot, ctx){
    // Respect per-date lock
    if (lockGet(ctx.section_id, ctx.curriculum_id, ctx.date)) {
      showToast('info','Attendance is locked for this date. Click "Edit Attendance" to modify.');
      return;
    }

    activeSlot = (slot === 'PM') ? 'PM' : 'AM';
    lastScanCtx = ctx;
    if (qrSlotLabel) qrSlotLabel.textContent = '('+activeSlot+')';
    if (qrOverlay) qrOverlay.hidden = false;

    recentHits.clear();

    try { await getVideoDevices(); } catch(e){}

    try{
      usingZXing = false;
      if (hasBarcodeDetector()){
        await startStream(null);
        await startBarcodeLoop(ctx);
      }else{
        stopStream();
        await startZXing(ctx, currentDeviceId);
      }
    }catch(e){
      showToast('danger','Unable to start scanner: ' + (e && e.message ? e.message : 'Unknown error'));
      closeScanner();
    }
  }

  function closeScanner(){
    scanning = false;
    if (usingZXing && zxingReader){ try{ zxingReader.reset(); }catch(e){} }
    stopStream();
    if (qrOverlay) qrOverlay.hidden = true;
  }

  async function switchCamera(){
    await getVideoDevices();
    if (deviceIds.length < 2){
      showToast('info','Only one camera detected.');
      return;
    }
    var idx = deviceIds.indexOf(currentDeviceId);
    var nextId = deviceIds[(idx + 1) % deviceIds.length];

    try{
      if (usingZXing){
        if (zxingReader){ try{ zxingReader.reset(); }catch(e){} }
        await startZXing(lastScanCtx || {}, nextId);
        currentDeviceId = nextId;
      }else{
        stopStream();
        await startStream(nextId);
      }
    }catch(e){
      showToast('warning','Failed to switch camera.');
    }
  }

  document.addEventListener('DOMContentLoaded', function(){
    var $section = document.getElementById('saeSection');
    var $curr    = document.getElementById('saeCurriculum');
    var $date    = document.getElementById('saeDate');
    var $q       = document.getElementById('saeSearch');
    var $tbody   = document.querySelector('#saeTable tbody');
    var $save    = document.getElementById('saeSave');
    var $export  = document.getElementById('saeExport');
    var editBtn  = document.getElementById('saeEdit');

    // Holiday UI
    var $holidayBtn   = document.getElementById('saeHoliday');
    var $holidayBadge = document.getElementById('saeHolidayBadge');

    // "Check All" header buttons
    var $amAll   = document.getElementById('saeCheckAllAM');
    var $pmAll   = document.getElementById('saeCheckAllPM');

    // Gender filter + cached rows
    var $sort = document.getElementById('saeSortGender');
    var __rows = [];

    // QR elements (bind to the globals)
    qrOverlay   = el('qrOverlay');
    qrVideo     = el('qrVideo');
    qrCloseBtn  = el('qrCloseBtn');
    qrSwitchBtn = el('qrSwitchBtn');
    qrSlotLabel = el('qrSlotLabel');

    var $scanAM = el('saeScanAM');
    var $scanPM = el('saeScanPM');

    function labelSection(it){ return (it.grade_name ? ('Grade '+it.grade_name+' - ') : '') + (it.name||''); }
    function labelSY(it){ return it.school_year; }

    // current context helper
    function ctx(){
      return {
        section_id: $section && $section.value,
        curriculum_id: $curr && $curr.value,
        date: $date && $date.value
      };
    }

    // Apply lock based on current context
    function applyLockForCurrentDate(){
      var c = ctx();
      setAttendanceLocked(lockGet(c.section_id, c.curriculum_id, c.date));
    }

    // boot
    var b = window.__SAE_BOOT__ || {};
    renderSelect($section, b.sections||[],  'id', labelSection, b.section_id);
    renderSelect($curr,    b.curricula||[], 'id', labelSY,      b.curriculum_id);
    renderRows($tbody, b.rows || []);
    if ($date && b.date) $date.value = b.date;

    // holiday state
    function computeHoliday(){
      var c = ctx();
      return holGet(c.section_id, c.curriculum_id, c.date) || !!b.is_holiday;
    }
    var isHoliday = computeHoliday();

    function setHolidayUI(state){
      isHoliday = !!state;
      if ($holidayBtn) $holidayBtn.textContent = isHoliday ? 'Unmark Holiday' : 'Mark as Holiday';
      if ($holidayBadge) $holidayBadge.style.display = isHoliday ? 'inline-block' : 'none';
    }
    setHolidayUI(isHoliday);

    // Filter rows by gender
    function sortFilterRows(rows){
      var mode = $sort ? $sort.value : 'ALL';
      var list = Array.isArray(rows) ? rows.slice() : [];
      list.forEach(function(r){ r.__g = normGender(r.gender); });

      if (mode === 'M')  list = list.filter(function(r){ return r.__g==='M'; });
      if (mode === 'F')  list = list.filter(function(r){ return r.__g==='F'; });

      list.sort(function(a,b){
        return String(a.full_name||'').localeCompare(String(b.full_name||'')); // name sort
      });

      return list;
    }

    function reload(withParams){
      setDisabled($section,true); setDisabled($curr,true); setDisabled($date,true);
      setDisabled($save,true); setDisabled($holidayBtn,true);
      var p = withParams || {
        section_id: $section && $section.value,
        curriculum_id: $curr && $curr.value,
        date: $date && $date.value,
        q: $q && $q.value
      };
      fetchAll(p, function(data){
        var denied = !data || data.status === false;
        if (denied && data && /not assigned/i.test(data.message || '')) {
          return fetchAll({ section_id: p.section_id, date: p.date, q: p.q }, function(d2){
            if (!d2 || d2.status === false) return fetchAll({ date: p.date, q: p.q }, finish);
            finish(d2);
          });
        }
        finish(data);
      });
    }

    function finish(data){
      setDisabled($section,false); setDisabled($curr,false); setDisabled($date,false);
      setDisabled($save,false); setDisabled($holidayBtn,false);
      if (!data || !data.status){
        var msg = (data && data.message) ? data.message : 'Failed to load';
        if (window.main && main.alertMessage) main.alertMessage('danger', msg); else alert(msg);
        return;
      }
      renderSelect($section, data.sections||[],  'id', labelSection, data.section_id);
      renderSelect($curr,    data.curricula||[], 'id', labelSY,      data.curriculum_id);
      if ($date && data.date) $date.value = data.date;

      __rows = data.rows || [];
      renderRows($tbody, sortFilterRows(__rows));

      setHolidayUI(holGet($section.value, $curr.value, $date.value));
      updateToggleLabels();

      // After (re)render, ensure remarks reflect current AM/PM
      $tbody.querySelectorAll('tr[data-student]').forEach(syncRemarksForRow);

      // Re-apply per-date lock state after render
      applyLockForCurrentDate();
    }

    if ($section) $section.addEventListener('change', function(){ b.is_holiday=false; reload(); });
    if ($curr)    $curr.addEventListener('change',    function(){ b.is_holiday=false; reload(); });
    if ($date)    $date.addEventListener('change',    function(){ b.is_holiday=false; reload(); });
    if ($q)       $q.addEventListener('input', function(){ clearTimeout(this.__t); this.__t=setTimeout(reload,300); });

    if ($sort) $sort.addEventListener('change', function(){
      renderRows($tbody, sortFilterRows(__rows));
      updateToggleLabels();
      $tbody.querySelectorAll('tr[data-student]').forEach(syncRemarksForRow);
      applyLockForCurrentDate(); // keep lock when switching Male/Female
    });

    if ($save) $save.addEventListener('click', function(){
      var fd = new FormData();
      fd.append('section_id',    $section.value);
      fd.append('curriculum_id', $curr.value);
      fd.append('date',          $date.value);
      fd.append('rows',          JSON.stringify(collectRows($tbody)));

      setDisabled($save,true);
      send('save', fd, function(resp){
        setDisabled($save,false);
        if (!resp || !resp.status){
          var msg = (resp && resp.message) ? resp.message : 'Save failed';
          if (window.main && main.alertMessage) main.alertMessage('danger', msg); else alert(msg);
          return;
        }

        if (window.main && main.alertMessage) main.alertMessage('success','Attendance saved successfully.');

        // Lock only the CURRENT date/section/curriculum
        lockSet($section.value, $curr.value, $date.value, true);
        applyLockForCurrentDate();

        reload();
        try { window.dispatchEvent(new Event('mae:saved')); window.dispatchEvent(new Event('attendance:saved')); } catch(e){}
        try { localStorage.setItem('attendance:lastSaved', String(Date.now())); } catch(e){}
      });
    });

    // Edit (unlock ONLY this date)
    if (editBtn) editBtn.addEventListener('click', function(){
      lockSet($section.value, $curr.value, $date.value, false);
      applyLockForCurrentDate();
    });

    // Holiday
    if ($holidayBtn) $holidayBtn.addEventListener('click', function(){
      var next = !isHoliday;
      holSet($section.value, $curr.value, $date.value, next);
      setHolidayUI(next);
      if (window.main && main.alertMessage) {
        main.alertMessage('success', next ? 'Marked as HOLIDAY.' : 'HOLIDAY removed.');
      }
    });

    // Export
    if ($export) $export.addEventListener('click', function(){
      if (!$section || !$curr || !$date || !$section.value || !$curr.value || !$date.value) {
        var msg = 'Please select Section, School Year, and Date before exporting.';
        if (window.main && main.alertMessage) main.alertMessage('warning', msg); else alert(msg);
        return;
      }
      var params = new URLSearchParams({
        section_id:    $section.value,
        curriculum_id: $curr.value,
        date:          $date.value
      });
      var ym = ($date.value || '').slice(0,7);
      var list = holList($section.value, $curr.value, ym);
      if (list.length) params.append('holidays', list.join(','));
      if (holGet($section.value, $curr.value, $date.value)) params.append('holiday', '1');
      window.location.href = module + 'export?' + params.toString();
    });

    // Toggleable "Check All" / "Uncheck All"
    function getBoxes(key){
      if (!$tbody) return [];
      return Array.prototype.slice.call($tbody.querySelectorAll('.sae-chk[data-k="'+key+'"]'));
    }
    function areAllChecked(key){
      var boxes = getBoxes(key);
      if (boxes.length === 0) return false;
      return boxes.every(function(cb){ return cb.checked; });
    }
    function setAll(key, checked){
      // Respect per-date lock
      if (lockGet($section.value, $curr.value, $date.value)) return;
      getBoxes(key).forEach(function(cb){
        cb.checked = !!checked;
        var row = cb.closest('tr[data-student]');
        syncRemarksForRow(row);
      });
    }
    function setLabelFor(key, btn){ if (btn) btn.textContent = areAllChecked(key) ? 'Uncheck All' : 'Check All'; }
    function updateToggleLabels(){
      setLabelFor('am_status', $amAll);
      setLabelFor('pm_status', $pmAll);
    }
    function toggleColumn(key, btn){
      var all = areAllChecked(key);
      setAll(key, !all);
      setLabelFor(key, btn);
    }

    if ($amAll) $amAll.addEventListener('click', function(){ toggleColumn('am_status', $amAll); });
    if ($pmAll) $pmAll.addEventListener('click', function(){ toggleColumn('pm_status', $pmAll); });

    // Row change listeners
    if ($tbody) $tbody.addEventListener('change', function(e){
      // If locked for this date, block manual changes to checkboxes/remarks
      if (lockGet($section.value, $curr.value, $date.value)) {
        e.preventDefault();
        // Re-apply lock to restore disabled state in case something slipped
        applyLockForCurrentDate();
        return;
      }
      if (e.target && e.target.classList && e.target.classList.contains('sae-chk')){
        updateToggleLabels();
        var row = e.target.closest('tr[data-student]');
        syncRemarksForRow(row);
      }
      if (e.target && e.target.matches('select[data-k="remarks"]')){
        var row2 = e.target.closest('tr[data-student]');
        if (e.target.value !== 'Tardy') syncRemarksForRow(row2);
      }
    });

    // Scanner buttons
    if ($scanAM) $scanAM.addEventListener('click', function(){
      var c = ctx();
      if (!c.section_id || !c.curriculum_id || !c.date){ showToast('warning','Select Section, School Year and Date first.'); return; }
      openScanner('AM', c);
    });
    if ($scanPM) $scanPM.addEventListener('click', function(){
      var c = ctx();
      if (!c.section_id || !c.curriculum_id || !c.date){ showToast('warning','Select Section, School Year and Date first.'); return; }
      openScanner('PM', c);
    });
    if (qrCloseBtn)  qrCloseBtn.addEventListener('click', closeScanner);
    if (qrOverlay)   qrOverlay.addEventListener('click', function(e){ if (e.target === qrOverlay) closeScanner(); });
    if (qrSwitchBtn) qrSwitchBtn.addEventListener('click', switchCamera);

    // initial sync & render
    updateToggleLabels();
    reload();

    // Apply lock state for the initial date
    applyLockForCurrentDate();
  });

  /* === Lock / Unlock Attendance (UI) ===
     Locks ONLY: AM/PM checkboxes, QR Scan buttons, and Check All buttons.
     Leaves Section/SY/Date/Gender/Search ALWAYS enabled. */
  function setAttendanceLocked(isLocked) {
    // Buttons that get locked
    ['#saeScanAM', '#saeScanPM', '#saeCheckAllAM', '#saeCheckAllPM'].forEach(function(sel){
      var el = document.querySelector(sel);
      if (el) el.disabled = !!isLocked;
    });

    // Row inputs that get locked (checkboxes + remarks)
    document.querySelectorAll('.sae-chk, .sgx-remarks').forEach(function(el){
      el.disabled = !!isLocked;
    });

    // Always enabled: top filters
    ['#saeSection', '#saeCurriculum', '#saeDate', '#saeSortGender', '#saeSearch'].forEach(function(sel){
      var el = document.querySelector(sel);
      if (el) el.disabled = false;
    });

    // Toggle Save/Edit visibility
    var saveBtn = document.getElementById('saeSave');
    var editBtn = document.getElementById('saeEdit');
    if (saveBtn && editBtn) {
      saveBtn.style.display = isLocked ? 'none' : '';
      editBtn.style.display = isLocked ? '' : 'none';
    }
  }

})();
