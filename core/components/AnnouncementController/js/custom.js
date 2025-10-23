const MODULE_BASE = `${URL_BASED}component/announcement/`;
const NS = '.announcement';
let isOpeningModal = false;

$(document)
  .off('click' + NS, '.openmodaldetails-modal')
  .on('click' + NS, '.openmodaldetails-modal', function () {
    if (isOpeningModal) return;
    isOpeningModal = true;

    const fd = new FormData();
    fd.append('action', $(this).data('type') || 'add');
    fd.append('id', $(this).data('id') || '');

    const actionUrl = MODULE_BASE + 'source';

    let req;
    try {
      req = main.send_ajax(fd, actionUrl, 'POST', true);
    } catch (err) {
      req = $.ajax({ url: actionUrl, method: 'POST', data: fd, processData: false, contentType: false, xhrFields: { withCredentials: true } });
    }

    req.done(function (data) {
      const header = data.header || 'Announcement';
      const html   = data.html   || '<div class="p-3">No content</div>';
      const button = ''; // submit button is INSIDE the form now

      if (typeof main.modalOpen === 'function') {
        main.modalOpen(header, html, button, 'noop', 'modal-xl no-header');
      } else {
        $('#genericModal .modal-title').text(header);
        $('#genericModal .modal-body').html(html);
        $('#genericModal .modal-footer').html(button);
        $('#genericModal').modal('show');
      }

      $('.modal.no-header .modal-header').remove();
    }).fail(function (xhr) {
      console.error('Open modal failed:', xhr && xhr.responseText);
      if (typeof main.alertMessage === 'function') main.alertMessage('danger', 'Failed to open modal', '');
    }).always(function () {
      isOpeningModal = false;
    });
  });

// Delete stays the same as earlier (AJAX is fine for delete)
$(document)
  .off('click' + NS, '.delete')
  .on('click' + NS, '.delete', function () {
    const id = $(this).data('id');
    if (!id) return;

    const confirmDelete = () => {
      const fd = new FormData();
      fd.append('id', id);
      const action = MODULE_BASE + 'delete';
      let r;
      try {
        r = main.send_ajax(fd, action, 'POST', true);
      } catch (err) {
        r = $.ajax({ url: action, method: 'POST', data: fd, processData: false, contentType: false, xhrFields: { withCredentials: true } });
      }
      r.done(function (data) {
        if (data && data.status) {
          if (typeof main.alertMessage === 'function') main.alertMessage('success', 'Successfully Deleted!', '');
          setTimeout(() => location.reload(), 600);
        } else {
          if (typeof main.alertMessage === 'function') main.alertMessage('danger', (data && data.msg) || 'Delete failed', '');
        }
      });
    };

    if (typeof swal === 'function') {
      swal({ title: 'Delete Announcement', text: 'This action cannot be undone.', icon: 'warning', buttons: ['Cancel', 'Delete'], dangerMode: true })
        .then(willDelete => { if (willDelete) confirmDelete(); });
    } else {
      if (confirm('Delete this announcement? This action cannot be undone.')) confirmDelete();
    }
  });
