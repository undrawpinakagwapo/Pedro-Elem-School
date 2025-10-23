const module = `${URL_BASED}component/manage-subjects/`;
const component = `component/manage-subjects/`;

/* ---------- Modal chrome: remove header + X completely ---------- */
(function injectNoCloseStyle() {
  if (document.getElementById('no-modal-x-style')) return;
  const style = document.createElement('style');
  style.id = 'no-modal-x-style';
  style.textContent = `
    .modal .btn-close,
    .modal button.close,
    .modal a.close,
    .modal [data-dismiss="modal"].close,
    .modal [data-bs-dismiss="modal"].btn-close,
    .modal .pc-close,
    .modal .md-close,
    .modal .close-modal { display:none !important; visibility:hidden !important; }
  `;
  document.head.appendChild(style);
})();
function stripModalChrome() {
  const $m = $('.modal.show, .modal').last();
  if (!$m.length) return;
  $m.find('.modal-header, .card-header, .header, .md-header').remove();
  $m.find('.btn-close, button.close, a.close, [data-dismiss="modal"].close, [data-bs-dismiss="modal"].btn-close, .pc-close, .md-close, .close-modal').remove();
  $m.find('.modal-title, [class*="title"]').empty();
}
$(document).on('shown.bs.modal', '.modal', () => setTimeout(stripModalChrome, 0));

/* ---------- Open modal (Add/Edit) ---------- */
$(document).on('click', '.openmodaldetails-modal', function () {
  const action = module + 'source';
  const fd = new FormData();
  fd.append('action', $(this).data('type'));
  fd.append('id', $(this).data('id') || '');

  const req = main.send_ajax(fd, action, 'POST', true);
  req.done(function (data) {
    const submitAction = component + data.action;
    // Pass empty title to kill bootstrap header; we’ll strip any leftovers too
    main.modalOpen('', data.html, data.button, submitAction, 'modal-l');
    stripModalChrome();
  });
});

/* ---------- Delete row ---------- */
$(document).on('click', '.delete', function () {
  const fd = new FormData();
  fd.append('id', $(this).data('id'));

  main.confirmMessage(
    'warning',
    'DELETE RECORD',
    'Are you sure you want to delete this record?',
    'deleteSubjectRecord',
    fd
  );
});

function deleteSubjectRecord(fd) {
  const req = main.send_ajax(fd, module + 'delete', 'POST', true);
  req.done(function (data) {
    if (data.status) {
      main.alertMessage('success', 'Successfully Deleted!', '');
      loadSubjects(); // refresh table without full page reload
    } else {
      main.alertMessage('danger', 'Failed to Delete!', '');
    }
  });
}

/* ---------- List rendering (React-free) ---------- */
let dt = null; // DataTable instance if available

function setTableData(rows) {
  const $tbody = $('#subjectsTbody');
  if (!Array.isArray(rows) || rows.length === 0) {
    $tbody.html('<tr><td colspan="4" class="text-center">No data available</td></tr>');
    if (dt && $.fn.dataTable.isDataTable('#mainTable')) dt.clear().draw();
    return;
  }

  // If DataTables is present and already initialized, update via API
  if (dt && $.fn.dataTable.isDataTable('#mainTable')) {
    dt.clear();
    rows.forEach((r, i) => {
      dt.row.add([
        i + 1,
        r.code || '',
        r.name || '',
        `
          <button class="btn waves-effect waves-light btn-grd-primary btn-sm openmodaldetails-modal" data-type="edit" data-id="${r.id}">
            <i class="fa fa-edit"></i>
          </button>
          |
          <button class="btn waves-effect waves-light btn-grd-danger btn-sm delete" data-id="${r.id}">
            <i class="fa fa-times"></i>
          </button>
        `
      ]);
    });
    dt.draw();
    return;
  }

  // Plain HTML render (no DataTables)
  const html = rows.map((r, i) => `
    <tr>
      <td>${i + 1}</td>
      <td>${r.code || ''}</td>
      <td>${r.name || ''}</td>
      <td>
        <button class="btn waves-effect waves-light btn-grd-primary btn-sm openmodaldetails-modal" data-type="edit" data-id="${r.id}">
          <i class="fa fa-edit"></i>
        </button>
        |
        <button class="btn waves-effect waves-light btn-grd-danger btn-sm delete" data-id="${r.id}">
          <i class="fa fa-times"></i>
        </button>
      </td>
    </tr>
  `).join('');
  $tbody.html(html);
}

function initDataTableIfAny() {
  if (typeof $.fn.DataTable === 'undefined') return;
  if ($.fn.dataTable.isDataTable('#mainTable')) {
    dt = $('#mainTable').DataTable();
    return;
  }
  dt = $('#mainTable').DataTable({
    order: [[2, 'asc']], // sort by Subject Name
    columnDefs: [
      { targets: 0, orderable: false },               // #
      { targets: 3, orderable: false }                // Action
    ]
  });
}

function loadSubjects() {
  const fd = new FormData();
  const req = main.send_ajax(fd, module + 'list', 'POST', true);
  req.done(function (data) {
    // Expecting array of {id, code, name}
    if (!Array.isArray(data)) {
      $('#subjectsTbody').html('<tr><td colspan="4" class="text-center text-danger">Failed to load data.</td></tr>');
      return;
    }
    setTableData(data);
  });
}

/* ---------- Boot ---------- */
$(function () {
  initDataTableIfAny();
  loadSubjects();

  // Optional: reload icon in card header
  $(document).on('click', '#subjectsReloadBtn', loadSubjects);
});
