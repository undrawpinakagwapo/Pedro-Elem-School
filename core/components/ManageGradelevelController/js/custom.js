// components/ManageGradelevelController/js/custom.js
(function () {
  const module = URL_BASED + (URL_BASED && !URL_BASED.endsWith('/') ? '/' : '') + 'component/manage-gradelevel/';
  const component = 'component/manage-gradelevel/';

  /* ---------- Modal polish: hide any Bootstrap/theme close buttons & header ---------- */
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
    const $m = $('.modal:visible').last();
    if (!$m.length) return;
    $m.find('.modal-header, .card-header, .header, .md-header').remove();
    $m.find(
      '.btn-close, button.close, a.close,' +
      '.modal-content > .btn-close, .modal-content > .close,' +
      '[data-dismiss="modal"].close, [data-bs-dismiss="modal"].btn-close,' +
      '.pc-close, .md-close, .close-modal'
    ).remove();
    $m.find('.modal-title, [class*="title"]').empty();
  }
  function stripModalChromeWithRetries() { [0,50,200,500].forEach(d => setTimeout(stripModalChrome, d)); }
  $(document).on('shown.bs.modal', '.modal', stripModalChromeWithRetries);

  /* ---------- Modal open + delete handlers (work for dynamic rows) ---------- */
  $(document).on('click', '.openmodaldetails-modal', function () {
    const action = module + 'source';
    const dataObj = { action: $(this).data('type'), id: $(this).data('id') };
    const formData = new FormData();
    Object.keys(dataObj).forEach(k => formData.append(k, dataObj[k]));

    const req = main.send_ajax(formData, action, 'POST', true);
    req.done(function (data) {
      const submitAction = component + data.action;
      // Open with empty header so we can fully remove it
      main.modalOpen('', data.html, data.button, submitAction, 'modal-l');
      stripModalChromeWithRetries();
    });
  });

  $(document).on('click', '.delete', function () {
    const fd = new FormData();
    fd.append('id', $(this).data('id'));
    main.confirmMessage(
      'warning',
      'DELETE RECORD',
      'Are you sure you want to delete this record?',
      'deleteRecord',
      fd
    );
  });

  window.deleteRecord = function (formData) {
    const action = module + 'delete';
    const req = main.send_ajax(formData, action, 'POST', true);
    req.done(function (data) {
      if (data.status) {
        main.confirmMessage('success', 'Successfully Deleted!', 'Reload this page?', 'reloadPage', '');
      } else {
        main.alertMessage('danger', 'Failed to Delete!', '');
      }
    });
  };

  /* ---------- <gradelevel-widget> (vanilla, no React) ---------- */
  class GradeLevelWidget extends HTMLElement {
    connectedCallback() {
      this.renderSkeleton();
      this.loadData();
      this.bindLocalEvents();
    }

    renderSkeleton() {
      this.innerHTML = `
        <button class="btn waves-effect waves-light btn-primary openmodaldetails-modal" data-type="add">
          <i class="fa fa-plus"></i>&nbsp;Add New
        </button>

        <div class="card table-card mt-2">
          <div class="card-header">
            <h5 class="mb-0">Grade Level</h5>
            <div class="card-header-right">
              <ul class="list-unstyled card-option">
                <li><i class="fa fa fa-wrench open-card-option"></i></li>
                <li><i class="fa fa-window-maximize full-card"></i></li>
                <li><i class="fa fa-minus minimize-card"></i></li>
                <li><i class="fa fa-refresh reload-card"></i></li>
                <li><i class="fa fa-trash close-card"></i></li>
              </ul>
            </div>
          </div>
          <div class="card-block">
            <div class="table-responsive">
              <table id="mainTable" class="table table-hover">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Grade Level</th>
                    <th>Code</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody id="gl-rows">
                  <tr><td colspan="4" class="text-center text-muted">Loading…</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>`;
    }

    loadData() {
      fetch(module + 'list', { method: 'POST' })
        .then(r => r.json())
        .then(rows => this.renderRows(Array.isArray(rows) ? rows : []))
        .catch(() => this.renderRows([]));
    }

    renderRows(rows) {
      const tbody = this.querySelector('#gl-rows');
      if (!tbody) return;

      if (!rows.length) {
        tbody.innerHTML = `<tr><td colspan="4" class="text-center">No data available</td></tr>`;
        return;
      }

      tbody.innerHTML = rows.map((g, idx) => `
        <tr>
          <td>${idx + 1}</td>
          <td>${this.esc(g.name)}</td>
          <td>${this.esc(g.code)}</td>
          <td>
            <button class="btn waves-effect waves-light btn-grd-primary btn-sm openmodaldetails-modal"
                    data-type="edit" data-id="${g.id}"><i class="fa fa-edit"></i></button>
            |
            <button class="btn waves-effect waves-light btn-grd-danger btn-sm delete"
                    data-id="${g.id}"><i class="fa fa-times"></i></button>
          </td>
        </tr>
      `).join('');
    }

    bindLocalEvents() {
      // Nothing special needed—buttons bubble to document handlers above.
    }

    esc(s) {
      return String(s ?? '').replace(/[&<>"']/g, m => ({
        '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
      })[m]);
    }
  }

  // Register once
  if (!customElements.get('gradelevel-widget')) {
    customElements.define('gradelevel-widget', GradeLevelWidget);
  }
})();
