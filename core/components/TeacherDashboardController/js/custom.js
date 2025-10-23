// components/TeacherDashboardController/js/custom.js
(function(){
  var base   = (typeof URL_BASED !== 'undefined' ? URL_BASED : '/');
  var module = base + 'component/teacher-dashboard/';
  var grMod  = base + 'component/student-grade-entry/';

  /* ============ DOM & helpers ============ */
  function byId(id){ return document.getElementById(id); }
  function todayYMD(){ return new Date().toISOString().slice(0,10); }
  function ymd(d){ var y=d.getFullYear(), m=String(d.getMonth()+1).padStart(2,'0'), dd=String(d.getDate()).padStart(2,'0'); return y+'-'+m+'-'+dd; }
  function monthRangeFrom(dateStr){
    var d = dateStr ? new Date(dateStr) : new Date();
    var from = new Date(d.getFullYear(), d.getMonth(), 1);
    var to   = new Date(d.getFullYear(), d.getMonth()+1, 0);
    return { from: ymd(from), to: ymd(to) };
  }
  function esc(s){
    return String(s==null?'':s)
      .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
      .replace(/"/g,'&quot;').replace(/'/g,'&#39;');
  }
  function initials(name){
    var parts = String(name||'').replace(/,/g,' ').trim().split(/\s+/).filter(Boolean);
    var a = parts[0] ? parts[0][0] : '';
    var b = parts.length>1 ? parts[parts.length-1][0] : '';
    return (a+b || a || '•').toUpperCase();
  }
  function renderSelect(el, items, valKey, labelFn, current){
    if (!el) return;
    items = Array.isArray(items) ? items : [];
    var html = items.map(function(it){
      var v = it[valKey], lbl = labelFn(it), sel=(String(v)===String(current))?' selected':'';
      return '<option value="'+v+'"'+sel+'>'+esc(lbl)+'</option>';
    }).join('');
    el.innerHTML = html;
    if (current != null) el.value = String(current);
  }
  function labelSection(it){ return (it.grade_name ? ('Grade '+it.grade_name+' - ') : '') + (it.name||''); }
  function labelSY(it){ return it.school_year; }

  /* ============ AJAX wrappers ============ */
  function send(action, formData, cb){
    if (!(window.main && typeof main.send_ajax === 'function')) { cb({status:false, message:'AJAX not available'}); return; }
    var req = main.send_ajax(formData, module + action, 'POST', true);
    req.done(function(resp){
      try {
        if (typeof resp === 'object' && resp !== null) return cb(resp);
        var txt = String(resp || '');
        if (txt.trim().startsWith('<')) return cb({status:false, message:'Bad JSON: server returned HTML.'});
        cb(JSON.parse(txt));
      } catch(e){ cb({status:false, message:'Bad JSON'}); }
    });
    req.fail(function(){ cb({status:false, message:'Request failed'}); });
  }
  function sendGrades(action, formData, cb){
    if (!(window.main && typeof main.send_ajax === 'function')) { cb({status:false, message:'AJAX not available'}); return; }
    var req = main.send_ajax(formData, grMod + action, 'POST', true);
    req.done(function(resp){ try{ cb(typeof resp==='string'? JSON.parse(resp) : resp); }catch(e){ cb({status:false,message:'Bad JSON'}); } });
    req.fail(function(){ cb({status:false, message:'Request failed'}); });
  }

  /* ============ Renderers ============ */
  function renderAbsentList(container, items, ctxMeta){
    if (!container) return;
    items = Array.isArray(items) ? items : [];
    if (!items.length){
      container.innerHTML = '<div class="sd-empty-row">No absent students.</div>';
      return;
    }

    var meta = '';
    if (ctxMeta && ctxMeta.grade_name && ctxMeta.section_name) {
      meta = 'Grade ' + ctxMeta.grade_name + ' • ' + ctxMeta.section_name;
    }

    container.innerHTML = items.map(function(it){
      var nm  = it.full_name || it.name || '—';
      var lrn = it.LRN ? ('LRN: ' + it.LRN) : '';
      var sub = lrn && meta ? (lrn + ' • ' + meta) : (lrn || meta);

      return '' +
        '<div class="sd-item">' +
          '<div class="sd-left">' +
            '<div class="sd-avatar">' + esc(initials(nm)) + '</div>' +
            '<div>' +
              '<div class="sd-name">' + esc(nm) + '</div>' +
              (sub ? '<div class="sd-meta">' + esc(sub) + '</div>' : '') +
            '</div>' +
          '</div>' +
          '<span class="sd-chip">Absent</span>' +
        '</div>';
    }).join('');
  }

  function renderTop10List(container, items){
    if (!container) return;
    items = Array.isArray(items) ? items.slice() : [];
    items.sort(function(a,b){ return (+b.avg||0) - (+a.avg||0); });
    items = items.slice(0, 10);

    if (!items.length){
      container.innerHTML = '<div class="sd-empty-row">No data.</div>';
      return;
    }

    container.innerHTML = items.map(function(s, i){
      var name = s.full_name || s.name || '—';
      var lrn  = s.LRN ? ('LRN: ' + s.LRN) : '';
      var avg  = (s.avg==null ? '—' : Number(s.avg).toFixed(2));
      return ''+
        '<div class="sd-item">'+
          '<div class="sd-left">'+
            '<div class="sd-rank">'+(i+1)+'</div>'+
            '<div>'+
              '<div class="sd-name">'+esc(name)+'</div>'+
              (lrn ? '<div class="sd-meta">'+esc(lrn)+'</div>' : '')+
            '</div>'+
          '</div>'+
          '<span class="sd-pill">'+esc(avg)+'</span>'+
        '</div>';
    }).join('');
  }

  function renderUnder75List(container, items){
    if (!container) return;
    items = Array.isArray(items) ? items.slice() : [];

    var filtered = items.filter(function(s){ return s.avg != null && Number(s.avg) < 75; });
    filtered.sort(function(a,b){
      var aa = (a.avg==null? Infinity : +a.avg), bb = (b.avg==null? Infinity : +b.avg);
      if (aa === bb) return String(a.full_name||'').localeCompare(String(b.full_name||''));
      return aa - bb;
    });

    if (!filtered.length){
      container.innerHTML = '<div class="sd-empty-row">None below 75 for this quarter.</div>';
      return;
    }

    container.innerHTML = filtered.map(function(s){
      var name = s.full_name || s.name || '—';
      var lrn  = s.LRN ? ('LRN: ' + s.LRN) : '';
      var avg  = (s.avg==null ? '—' : Number(s.avg).toFixed(2));
      return ''+
        '<div class="sd-item">'+
          '<div class="sd-left">'+
            '<div class="sd-avatar">'+esc(initials(name))+'</div>'+
            '<div>'+
              '<div class="sd-name">'+esc(name)+'</div>'+
              (lrn ? '<div class="sd-meta">'+esc(lrn)+'</div>' : '')+
            '</div>'+
          '</div>'+
          '<span class="sd-bad">'+esc(avg)+'</span>'+
        '</div>';
    }).join('');
  }

  /* ========= New: Announcements (latest 4) ========= */
  function formatDateShort(iso){
    if (!iso) return '';
    var d = new Date(String(iso).replace(' ', 'T'));
    if (isNaN(d.getTime())) return '';
    try {
      return d.toLocaleDateString(undefined, { month:'short', day:'numeric' });
    } catch(e){
      return '';
    }
  }

  function renderAnnouncements(container, items){
    if (!container) return;
    items = Array.isArray(items) ? items : [];
    if (!items.length){
      container.innerHTML = '<div class="sd-empty-row">No announcements.</div>';
      return;
    }
    container.innerHTML =
      '<div class="sd-anno-list">' +
      items.map(function(it){
        var title = it.title || 'Untitled';
        var when  = it.start_date || it.created_at || '';
        var dateS = formatDateShort(when);
        return '' +
          '<a class="sd-anno" href="/component/announcement/index">' +
            '<i>📣</i>' +
            '<span class="sd-anno-title" title="'+esc(title)+'">'+esc(title)+'</span>' +
            (dateS ? '<span class="sd-anno-date">'+esc(dateS)+'</span>' : '') +
          '</a>';
      }).join('') +
      '</div>';
  }

  function fetchLatestAnnouncements(){
    var url = base + 'component/announcement/latest?limit=4';
    fetch(url, { credentials:'include' })
      .then(function(r){ return r.json().catch(function(){ return {status:false}; }); })
      .then(function(data){
        var el = byId('sd-ann-list');
        if (!el) return;
        if (!data || !data.status) {
          el.innerHTML = '<div class="sd-empty-row">Failed to load announcements.</div>';
          return;
        }
        renderAnnouncements(el, data.items || []);
      })
      .catch(function(){
        var el = byId('sd-ann-list');
        if (el) el.innerHTML = '<div class="sd-empty-row">Failed to load announcements.</div>';
      });
  }

  /* ============ Boot ============ */
  document.addEventListener('DOMContentLoaded', function(){
    var boot = window.__TD_BOOT__ || {};

    var $section = byId('tdSection'),
        $curr    = byId('tdCurriculum'),
        $date    = byId('tdDate');

    var $cardTotal   = byId('cardTotal'),
        $cardPresent = byId('cardPresent'),
        $cardAbsent  = byId('cardAbsent');

    var $absList     = byId('sd-absent-list');

    var $sdTopList   = byId('sd-top10-list'),
        $sdTopQ      = byId('sd-top10-q'),
        _topByQ      = {}; 

    var $sdU75List   = byId('sd-under75-list'),
        $sdU75Q      = byId('sd-under75-q'),
        _u75ByQ      = {}; 

    function renderBoot(){
      renderSelect($section, boot.sections||[],  'id', labelSection, boot.section_id);
      renderSelect($curr,    boot.curricula||[], 'id', labelSY,      boot.curriculum_id);
      if ($date && boot.date) $date.value = boot.date;
    }

    function fetchGradeSummary(){
      var sid = $section && $section.value;
      var cid = $curr && $curr.value;
      if (!sid || !cid) return;

      var fd = new FormData();
      fd.append('section_id', sid);
      fd.append('curriculum_id', cid);

      sendGrades('summary', fd, function(resp){
        if(!resp || !resp.status){
          var msg = (resp && resp.message) ? resp.message : 'Failed to load grade summary';
          if (window.main && main.alertMessage) main.alertMessage('danger', msg);
          return;
        }

        _topByQ = resp.top_by_quarter || {};
        if ($sdTopQ){
          $sdTopQ.value = 'q1'; // force Q1
          renderTop10List($sdTopList, _topByQ['q1'] || []);
          if (!$sdTopQ._bound){
            $sdTopQ._bound = true;
            $sdTopQ.addEventListener('change', function(){
              var k = $sdTopQ.value;
              renderTop10List($sdTopList, _topByQ[k] || []);
            });
          }
        }

        _u75ByQ = resp.under75_by_quarter || {};
        if ($sdU75Q){
          $sdU75Q.value = 'q1'; // force Q1
          renderUnder75List($sdU75List, _u75ByQ['q1'] || []);
          if (!$sdU75Q._bound){
            $sdU75Q._bound = true;
            $sdU75Q.addEventListener('change', function(){
              var k = $sdU75Q.value;
              renderUnder75List($sdU75List, _u75ByQ[k] || []);
            });
          }
        }
      });
    }

    function reload(){
      var dateVal = ($date && $date.value) ? $date.value : todayYMD();
      var rng = monthRangeFrom(dateVal);

      var fd = new FormData();
      fd.append('section_id',    $section.value);
      fd.append('curriculum_id', $curr.value || '');
      fd.append('date', dateVal);
      fd.append('from', rng.from);
      fd.append('to',   rng.to);

      send('fetch', fd, function(resp){
        if(!resp || !resp.status){
          var msg=(resp && resp.message) ? resp.message : 'Failed to load dashboard';
          if (window.main && main.alertMessage) main.alertMessage('danger', msg); else alert(msg);
          return;
        }

        if (Array.isArray(resp.curricula)) {
          renderSelect($curr, resp.curricula, 'id', labelSY, resp.curriculum_id);
        }

        var total=+resp.total_students||0;
        var t=resp.today||{};
        var presentToday=(+t.present_full||0)+(+t.present_am_only||0)+(+t.present_pm_only||0);
        var absentToday=(+t.absent_full||0);

        if($cardTotal)   $cardTotal.textContent   = String(total);
        if($cardPresent) $cardPresent.textContent = String(presentToday);
        if($cardAbsent)  $cardAbsent.textContent  = String(absentToday);

        renderAbsentList($absList, resp.absent_today_list||[], {
          grade_name: resp.grade_name || '',
          section_name: resp.section_name || ''
        });

        var sy=resp.school_year||'', badge=document.getElementById('syBadge');
        if (sy){
          if(!badge){
            var span=document.createElement('span'); span.id='syBadge'; span.className='sy-badge'; span.textContent=sy;
            var hero=document.querySelector('.sd-hero h1');
            if (hero && hero.parentNode) hero.parentNode.insertBefore(span, hero.nextSibling);
          } else { badge.textContent=sy; }
        }

        fetchGradeSummary();
      });
    }

    if($section) $section.addEventListener('change', function(){ if ($curr) $curr.value=''; reload(); });
    if($curr)    $curr.addEventListener('change', reload);
    if($date)    $date.addEventListener('change', reload);

    renderBoot();
    reload();
    fetchLatestAnnouncements();
  });
})();
