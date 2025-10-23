/* components/ManageRegistrationController/js/custom.js */

/* Base endpoints */
const module = URL_BASED + (URL_BASED && !URL_BASED.endsWith('/') ? '/' : '') + 'component/manage-registration/';
const component = 'component/manage-registration/';

/* Small helper to build FormData quickly */
function fd(obj = {}) {
  const f = new FormData();
  Object.keys(obj).forEach(k => f.append(k, obj[k]));
  return f;
}

/* Track the current AJAX request so we can abort stale ones */
let currentStudentsReq = null;

/* ============================================================
   One-time style injections
   ============================================================ */

/** Hide modal "X" */
(function injectModalStyleOnce(){
  if (document.getElementById('hide-modal-x-style')) return;
  const css = `
    .modal .modal-header .btn-close,
    .modal .modal-header .close,
    .modal .modal-content > .btn-close,
    .modal .modal-content > .close { display: none !important; }
    /* fallback wide styling */
    .student-dropdown--wide {
      width: 95vw !important;
      max-width: 1100px !important;
    }
  `;
  const style = document.createElement('style');
  style.id = 'hide-modal-x-style';
  style.textContent = css;
  document.head.appendChild(style);
})();

/* ============================================================
   Modal header clean-up
   ============================================================ */
function polishModalHeader(expectedHeaderText) {
  const attempts = [0, 50, 200];
  attempts.forEach(delay => {
    setTimeout(function () {
      const $modal = $('.modal:visible').last();
      if (!$modal.length) return;

      $modal.find('.btn-close, button.close, a.close').remove();

      const $header = $modal.find('.modal-header');
      if (!$header.length) return;

      const titleText = $.trim($header.find('.modal-title').text() || '');
      const shouldRemoveHeader =
        (typeof expectedHeaderText !== 'undefined' && $.trim(expectedHeaderText) === '') ||
        titleText === '';

      if (shouldRemoveHeader) $header.remove();
    }, delay);
  });
}

$(document).on('shown.bs.modal', '.modal', function () {
  polishModalHeader('');
});

/* ============================================================
   Dynamic dropdown sizing (viewport-based, no sticky)
   ============================================================ */
function setDropdownMaxHeight(menuEl, triggerEl) {
  if (!menuEl || !triggerEl) return;

  const gapBottom = 16;
  const rect = triggerEl.getBoundingClientRect();

  // space from trigger bottom to viewport bottom
  const available = Math.floor(window.innerHeight - rect.bottom - gapBottom);
  const maxH = Math.max(320, available); // ensure decent minimum
  menuEl.style.maxHeight = maxH + 'px';
  menuEl.style.overflowY = 'auto';

  // width: ~95% of viewport, capped
  const vw = window.innerWidth;
  const maxW = Math.min(1100, Math.floor(vw * 0.95));
  menuEl.style.width = maxW + 'px';
}

function prepareStudentDropdownUI(menuEl) {
  if (!menuEl) return;
  const triggerEl = menuEl.closest('.dropdown')?.querySelector('.btn-students-trigger');
  if (!triggerEl) return;

  menuEl.classList.add('student-dropdown--wide');
  menuEl.scrollTop = 0;
  setDropdownMaxHeight(menuEl, triggerEl);
}

/* Recompute height on resize while menu is open */
window.addEventListener('resize', () => {
  const menu = document.querySelector('.modal:visible .student-dropdown') || document.querySelector('.student-dropdown.show');
  if (menu) {
    const trigger = menu.closest('.dropdown')?.querySelector('.btn-students-trigger');
    setDropdownMaxHeight(menu, trigger);
  }
});

/* ============================================================
   Checkbox dropdown initializer (students)
   ============================================================ */
function initStudentCheckboxDropdown(preselectedIds) {
  const listEl   = document.getElementById('student_checkbox_list');
  const hiddenEl = document.getElementById('student_hidden_inputs');
  const labelEl  = document.getElementById('student_btn_label');
  const filterEl = document.getElementById('student_filter');
  const btnAll   = document.getElementById('student_select_all');
  const btnClear = document.getElementById('student_clear');

  if (!listEl || !hiddenEl || !labelEl) return;

  const dropdownMenu = listEl.closest('.dropdown-menu.student-dropdown');
  if (dropdownMenu) {
    const stop = (e) => e.stopPropagation();
    dropdownMenu.addEventListener('click', stop);
    dropdownMenu.addEventListener('input', stop);
    dropdownMenu.addEventListener('keydown', stop);
    dropdownMenu.addEventListener('scroll', stop);
    prepareStudentDropdownUI(dropdownMenu);
  }

  function syncHiddenInputs() {
    hiddenEl.innerHTML = '';
    const checked = listEl.querySelectorAll('.student-check:checked');

    checked.forEach(chk => {
      const input = document.createElement('input');
      input.type  = 'hidden';
      input.name  = 'student_id[]';
      input.value = chk.value;
      hiddenEl.appendChild(input);
    });

    if (!checked.length) {
      labelEl.textContent = 'Select students…';
    } else {
      const arr = Array.from(checked);
      const names = arr.slice(0, 2).map(chk => chk.parentElement.querySelector('.student-name').textContent.trim());
      labelEl.textContent = arr.length <= 2 ? names.join(', ') : names.join(', ') + ' +' + (arr.length - 2);
    }
  }

  // Preselect in edit mode
  if (Array.isArray(preselectedIds) && preselectedIds.length) {
    preselectedIds.forEach(id => {
      const node = listEl.querySelector('.student-check[value="'+String(id)+'"]');
      if (node) node.checked = true;
    });
  }
  syncHiddenInputs();

  // checkbox change
  listEl.addEventListener('change', (e) => {
    if (e.target && e.target.classList.contains('student-check')) syncHiddenInputs();
  });

  // filter
  if (filterEl) {
    filterEl.addEventListener('keydown', (e) => { if (e.key === 'Enter') e.preventDefault(); });
    filterEl.addEventListener('input', () => {
      const q = filterEl.value.toLowerCase();
      listEl.querySelectorAll('label').forEach(lab => {
        lab.style.display = lab.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  }

  // select all / clear
  if (btnAll) {
    btnAll.addEventListener('click', () => {
      listEl.querySelectorAll('.student-check:not(:checked)').forEach(c => c.checked = true);
      syncHiddenInputs();
    });
  }
  if (btnClear) {
    btnClear.addEventListener('click', () => {
      listEl.querySelectorAll('.student-check:checked').forEach(c => c.checked = false);
      syncHiddenInputs();
    });
  }
}

/* ============================================================
   Fetch & render students by filters
   ============================================================ */
function fetchAndRenderStudents() {
  const $modal = $('.modal:visible').last();
  if (!$modal.length) return;

  const batch        = ($modal.find('#filterBatch').val() || '').trim();
  const set_group    = ($modal.find('#filterSet').val() || '').trim();
  const curriculumId = ($modal.find('#curriculum_id').val() || '').trim();

  const preselectedIds = Array.from($modal.find('#student_hidden_inputs input[name="student_id[]"]'))
    .map(i => i.value);

  const menuEl = $modal.find('.student-dropdown').get(0);
  if (menuEl) { menuEl.scrollTop = 0; prepareStudentDropdownUI(menuEl); }

  $modal.find('#student_checkbox_list').html('<div class="text-muted small px-1">Loading…</div>');

  try { currentStudentsReq?.abort?.(); } catch(e) {}

  currentStudentsReq = main.send_ajax(fd({
    type: 'students',
    batch,
    set_group,
    curriculum_id: curriculumId
  }), module + 'getDetailsSource', 'POST', true);

  currentStudentsReq.done(function(res) {
    $modal.find('#student_checkbox_list').html(res.content || '');
    initStudentCheckboxDropdown(preselectedIds);

    const menu = $modal.find('.student-dropdown').get(0);
    if (menu) {
      prepareStudentDropdownUI(menu);
      const input = menu.querySelector('#student_filter');
      if (input) setTimeout(() => input.focus(), 0);
    }
  });
}

/* ============================================================
   Modal helpers
   ============================================================ */
function closeClosestModal(el) {
  try {
    const $modal = $(el).closest('.modal');
    if ($modal.length && typeof $modal.modal === 'function') {
      $modal.modal('hide');
      return;
    }
  } catch (e) {}
  if (typeof main !== 'undefined' && typeof main.modalClose === 'function') {
    try { main.modalClose(); return; } catch (e) {}
  }
  const modal = el.closest('.modal');
  if (modal && modal.parentNode) modal.parentNode.removeChild(modal);
}

$(document).on('click', '.js-modal-cancel, [data-dismiss="modal"], [data-bs-dismiss="modal"]', function (e) {
  e.preventDefault();
  closeClosestModal(this);
});

/* ============================================================
   AJAX: open modals
   ============================================================ */
$(document).on('click', '.importsutdentmodal', function() {
  const action = module + 'modalimport';
  const request = main.send_ajax(fd({}), action, 'POST', true);
  request.done(function (data) {
    const submitAction = component + data.action;
    main.modalOpen('', data.html, data.button, submitAction, 'modal-xl');
    polishModalHeader('');
    initStudentCheckboxDropdown([]);

    const menu = $('.modal:visible').last().find('.student-dropdown').get(0);
    if (menu) { prepareStudentDropdownUI(menu); }
  });
});

$(document).on('click', '.openmodaldetails-modal', function() {
  const action = module + 'source';
  const request = main.send_ajax(fd({
    action : $(this).data('type'),
    id     : $(this).data('id')
  }), action, 'POST', true);

  request.done(function (data) {
    const submitAction = component + data.action;
    main.modalOpen('', data.html, data.button, submitAction, 'modal-xl');
    polishModalHeader('');

    const preselected = [];
    if (data.details_student_id) preselected.push(String(data.details_student_id));
    initStudentCheckboxDropdown(preselected);

    const $modal = $('.modal:visible').last();
    const $set   = $modal.find('#filterSet');

    function ensureAllSetsOption() {
      const first = $set.find('option').first();
      if (!first.length || first.val() !== '') { $set.prepend('<option value="">All Sets</option>'); }
      else { first.text('All Sets'); }
    }

    $(document).off('change.mr', '#filterBatch')
               .on('change.mr', '#filterBatch', function() {
      const b = ($(this).val() || '').trim();
      $set.html('<option value="">All Sets</option>').val('');

      const menu = $modal.find('.student-dropdown').get(0);
      if (menu) { prepareStudentDropdownUI(menu); }

      if (!b) { fetchAndRenderStudents(); return; }

      const req = main.send_ajax(fd({ type: 'setsByBatch', val: b }), module + 'getDetailsSource', 'POST', true);
      req.done(function(res) {
        const html = (res.content || '').trim();
        $set.html(html || '<option value="">All Sets</option>');
        ensureAllSetsOption();
        $set.val('');
        fetchAndRenderStudents();
      });
    });

    $(document).off('change.mr', '#filterSet')
               .on('change.mr', '#filterSet', function() {
      const menu = $modal.find('.student-dropdown').get(0);
      if (menu) { prepareStudentDropdownUI(menu); }
      fetchAndRenderStudents();
    });

    $(document).off('change.mr', '#curriculum_id')
               .on('change.mr', '#curriculum_id', function() {
      const menu = $modal.find('.student-dropdown').get(0);
      if (menu) { prepareStudentDropdownUI(menu); }
      fetchAndRenderStudents();
      $('.schoolyear').text( $('#curriculum_id option:selected').text() );
    });

    if (data.header === "Edit") {
      $('.select-change-modal').trigger('change');
      $('#defult_id_curriculum').val(data.curriculum_id);
    }

    fetchAndRenderStudents();

    const menu = $modal.find('.student-dropdown').get(0);
    if (menu) { prepareStudentDropdownUI(menu); }
  });
});

/* When the dropdown becomes visible: compute height, focus search */
$(document).on('shown.bs.dropdown', '.student-dropdown', function () {
  prepareStudentDropdownUI(this);
  const input = this.querySelector('#student_filter');
  if (input) setTimeout(() => input.focus(), 0);
  fetchAndRenderStudents();
});

/* ============================================================
   Delete flow
   ============================================================ */
$(document).on('click', '.delete', function() {
  const formData = fd({ id : $(this).data('id') });
  main.confirmMessage('warning', 'DELETE RECORD', 'Are you sure you want to delete this record? ', 'deleteRecord' ,formData );
});

function deleteRecord(formData) {
  const action = module + 'delete';
  const request = main.send_ajax(formData, action, 'POST', true);
  request.done(function (data) {
    if (data.status) {
      main.confirmMessage('success', 'Successfully Deleted!', 'Are you sure you want to reload this page? ', 'reloadPage' ,'' );
    } else {
      main.alertMessage('danger', 'Failed to Delete!', '');
    }
  });
}

/* ============================================================
   Dependent selects (Section → Curriculum, Curriculum → Subjects)
   ============================================================ */
$(document).on('change', '.select-change-modal', function() {
  const appendID = $(this).data('append');
  const type     = $(this).data('type');
  const action   = module + 'getDetailsSource';

  const request = main.send_ajax(fd({ type, val: $(this).val() }), action, 'POST', true);
  request.done(function (data) {
    if (type === "curriculum") {
      $('.schoolyear').text( $('#curriculum_id option:selected').text() );
    } else {
      $('.schoolyear').text('');
    }
    $('#' + appendID).html(data.content);

    const curr = $('#defult_id_curriculum').val();
    if (curr !== '') {
      $('#' + appendID).val(curr);
      $('#curriculum_id').trigger('change.mr');
    }

    fetchAndRenderStudents();

    const menu = $('.modal:visible').last().find('.student-dropdown').get(0);
    if (menu) { prepareStudentDropdownUI(menu); }
  });
});

$(document).on('change', '#curriculum_id', function() {
  const appendID = $(this).data('append');
  const type     = $(this).data('type');
  const action   = module + 'getDetailsSource';

  const request = main.send_ajax(fd({ type, val: $(this).val() }), action, 'POST', true);
  request.done(function (data) {
    if (type === "curriculum") {
      $('.schoolyear').text( $('#curriculum_id option:selected').text() );
    } else {
      $('.schoolyear').text('');
    }
    $('#' + appendID).html(data.content);

    fetchAndRenderStudents();

    const menu = $('.modal:visible').last().find('.student-dropdown').get(0);
    if (menu) { prepareStudentDropdownUI(menu); }
  });
});
