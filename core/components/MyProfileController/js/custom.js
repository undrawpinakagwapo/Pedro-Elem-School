const module = `${URL_BASED}component/user-management/`;
const component = `component/user-management/`;

/* ---------------- Modal helpers ---------------- */
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

$(document).on('click', '.js-user-modal-close, [data-dismiss="modal"], [data-bs-dismiss="modal"]', function (e) {
  e.preventDefault();
  closeClosestModal(this);
});

/* ---------------- Open modal details ---------------- */
$(document).on('click', '.openmodaldetails-modal', function () {
  var action = module + 'source';
  var dataObj = { action: $(this).data('type'), id: $(this).data('id') };
  var formData = new FormData();
  for (const key in dataObj) if (Object.prototype.hasOwnProperty.call(dataObj, key)) formData.append(key, dataObj[key]);

  var request = main.send_ajax(formData, action, 'POST', true);
  request.done(function (data) {
    action = component + data.action;
    main.modalOpen(data.header, data.html, data.button, action, 'modal-xl');

    const $imgInput = $('#imageInput');
    if ($imgInput.length) {
      $imgInput.off('change.userimg').on('change.userimg', function (event) {
        const file = event.target.files && event.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function (e) { $('#preview').attr('src', e.target.result).show(); };
        reader.readAsDataURL(file);
      });
    }
  });
});

/* ---------------- Delete (confirm → ajax) ---------------- */
$(document).on('click', '.delete', function () {
  var dataObj = { id: $(this).data('id') };
  var formData = new FormData();
  for (const key in dataObj) if (Object.prototype.hasOwnProperty.call(dataObj, key)) formData.append(key, dataObj[key]);

  main.confirmMessage('warning','DELETE RECORD','Are you sure you want to delete this record? ','deleteRecord',formData);
});

function deleteRecord(formData) {
  var action = module + 'delete';
  var request = main.send_ajax(formData, action, 'POST', true);
  request.done(function (data) {
    if (data.status) {
      main.confirmMessage('success','Successfully Deleted!','Are you sure you want to reload this page? ','reloadPage','');
    } else {
      main.alertMessage('danger', 'Failed to Delete!', '');
    }
  });
}
