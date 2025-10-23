// components/CurriculumnController/js/custom.js
(function(){
  var base   = (typeof URL_BASED !== 'undefined' ? URL_BASED : '/');
  var module = base + 'component/curriculumn/';

  /* ---------- utils ---------- */
  function byId(id){ return document.getElementById(id); }
  function $all(sel,root){ return Array.prototype.slice.call((root||document).querySelectorAll(sel)); }
  function ajax(formData, action, cb){
    if (window.main && typeof main.send_ajax === 'function') {
      var req = main.send_ajax(formData, module + action, 'POST', true);
      req.done(function(resp){
        try { cb(typeof resp==='string' ? JSON.parse(resp) : resp); } catch(e){ cb({status:false,message:'Bad JSON'}); }
      });
      req.fail(function(){ cb({status:false,message:'Request failed'}); });
      return;
    }
    if (typeof $ !== 'undefined' && $.ajax) {
      $.ajax({url: module+action, method:'POST', data: formData, contentType:false, processData:false})
       .done(function(resp){ try{ cb(typeof resp==='string'? JSON.parse(resp) : resp); }catch(e){ cb({status:false,message:'Bad JSON'}); } })
       .fail(function(){ cb({status:false,message:'Request failed'}); });
    } else {
      fetch(module+action, {method:'POST', body:formData})
        .then(r=>r.text())
        .then(t=>{ try{ cb(JSON.parse(t)); }catch(e){ cb({status:false,message:'Bad JSON'}); } })
        .catch(()=>cb({status:false,message:'Request failed'}));
    }
  }

  /* ---------- modal control (no Bootstrap) ---------- */
  var $overlay, $body, $btnCloseX, $btnCancel, $btnPrimary;

  function openModal(html, primary){
    if (!$overlay) {
      $overlay    = byId('cv-overlay');
      $body       = byId('cv-body');
      $btnCloseX  = byId('cv-close-x');
      $btnCancel  = byId('cv-cancel');
      $btnPrimary = byId('cv-primary');

      if ($btnCloseX) $btnCloseX.addEventListener('click', closeModal);
      if ($btnCancel) $btnCancel.addEventListener('click', closeModal);
      if ($overlay) {
        $overlay.addEventListener('click', function(ev){
          if (ev.target === $overlay) closeModal();
        });
      }
      document.addEventListener('keydown', function(ev){
        if (ev.key === 'Escape') closeModal();
      });
    }

    if ($body) $body.innerHTML = html || '';

    if (primary && primary.text && primary.onclick) {
      $btnPrimary.style.display = '';
      $btnPrimary.textContent = primary.text;
      $btnPrimary.onclick = primary.onclick;
    } else {
      $btnPrimary.style.display = 'none';
      $btnPrimary.textContent = '';
      $btnPrimary.onclick = null;
    }

    if ($overlay){
      $overlay.classList.add('show');
      $overlay.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
    }
  }
  function closeModal(){
    if ($overlay){
      $overlay.classList.remove('show');
      $overlay.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
    }
  }

  /* ---------- open curriculum details ---------- */
  function openDetails(id){
    var fd = new FormData();
    fd.append('id', id);
    ajax(fd, 'source', function(resp){
      if(!resp || !resp.status){
        var msg = (resp && resp.message) ? resp.message : 'Failed to load details';
        if (window.main && main.alertMessage) main.alertMessage('danger', msg); else alert(msg);
        return;
      }
      openModal(resp.html || '');
    });
  }

  /* ---------- filtering ---------- */
  function applyFilters(){
    var fy = byId('f-year'), fg = byId('f-grade'), fs = byId('f-section');
    var year = fy ? (fy.value || '').toLowerCase() : '';
    var grade = fg ? (fg.value || '').toLowerCase() : '';
    var sect = fs ? (fs.value || '').toLowerCase() : '';

    var cards = $all('.cm-card');
    var any = 0;
    cards.forEach(function(card){
      var y = (card.getAttribute('data-year') || '').toLowerCase();
      var g = (card.getAttribute('data-grade') || '').toLowerCase();
      var s = (card.getAttribute('data-section') || '').toLowerCase();

      var ok =
        (year === '' || y === year) &&
        (grade === '' || g === grade) &&
        (sect === '' || s === sect);

      card.style.display = ok ? '' : 'none';
      if (ok) any++;
    });

    var empty = byId('cm-empty'), chip = byId('cm-count-chip');
    if (empty) empty.style.display = any ? 'none' : '';
    if (chip) chip.textContent = any ? (any + ' result' + (any>1?'s':'')) : 'No results';
  }

  function resetFilters(){
    var fy = byId('f-year'), fg = byId('f-grade'), fs = byId('f-section');
    if (fy) fy.selectedIndex = 0;
    if (fg) fg.selectedIndex = 0;
    if (fs) fs.selectedIndex = 0;
    // Make sure section options are reset to ALL on reset
    rebuildSectionOptions('');
    applyFilters();
  }

  /* ---------- grade -> section dependency ---------- */
  var gradeToSections = Object.create(null);
  var allSectionsSorted = [];

  function buildGradeSectionMap(){
    gradeToSections = Object.create(null);
    var setAll = new Set();
    $all('.cm-card').forEach(function(card){
      var g = (card.getAttribute('data-grade') || '').trim();
      var s = (card.getAttribute('data-section') || '').trim();
      if (!g || !s) return;
      if (!gradeToSections[g]) gradeToSections[g] = new Set();
      gradeToSections[g].add(s);
      setAll.add(s);
    });
    allSectionsSorted = Array.from(setAll).sort(function(a,b){
      // natural-ish sort
      return a.localeCompare(b, undefined, {numeric:true, sensitivity:'base'});
    });
  }

  function rebuildSectionOptions(grade){
    var fs = byId('f-section');
    if (!fs) return;

    var preserve = fs.value; // try to preserve if still valid
    // Clear options
    while (fs.firstChild) fs.removeChild(fs.firstChild);

    // Always include "All Sections"
    var optAll = document.createElement('option');
    optAll.value = '';
    optAll.textContent = 'All Sections';
    fs.appendChild(optAll);

    var list = [];
    if (!grade) {
      list = allSectionsSorted.slice();
    } else {
      var set = gradeToSections[grade] || new Set();
      list = Array.from(set).sort(function(a,b){
        return a.localeCompare(b, undefined, {numeric:true, sensitivity:'base'});
      });
    }

    list.forEach(function(name){
      var o = document.createElement('option');
      o.value = name;
      o.textContent = name;
      fs.appendChild(o);
    });

    // Restore previous selection if still valid under new grade; else reset to All
    var canKeep = (!grade && preserve) ? allSectionsSorted.indexOf(preserve) !== -1
                  : (grade && gradeToSections[grade] && gradeToSections[grade].has(preserve));
    fs.value = canKeep ? preserve : '';
  }

  /* ---------- boot ---------- */
  document.addEventListener('DOMContentLoaded', function(){
    // Build grade->sections map from the cards present on the page
    buildGradeSectionMap();

    // Delegated click for opening details + reset button
    document.body.addEventListener('click', function(ev){
      var t = ev.target.closest('.btn-view-curriculum');
      if (t){
        var id = t.getAttribute('data-id');
        if (id) openDetails(id);
        return;
      }
      var r = ev.target.closest('#f-reset');
      if (r){ resetFilters(); }
    });

    // Filter change handlers
    var fy = byId('f-year'), fg = byId('f-grade'), fs = byId('f-section');

    if (fg) {
      fg.addEventListener('change', function(){
        // When grade changes, rebuild Section options to only those under that grade
        var selectedGrade = fg.value || '';
        rebuildSectionOptions(selectedGrade);
        // After options update, run filtering
        applyFilters();
      });
    }

    if (fy) fy.addEventListener('change', applyFilters);
    if (fs) fs.addEventListener('change', applyFilters);

    // Initial populate of Sections (based on current Grade selection, if any)
    rebuildSectionOptions(fg ? (fg.value || '') : '');
    // Initial count
    applyFilters();
  });

})();
