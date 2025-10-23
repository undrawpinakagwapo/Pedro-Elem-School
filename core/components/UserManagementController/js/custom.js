const module = URL_BASED + (URL_BASED && !URL_BASED.endsWith('/') ? '/' : '') + 'component/user-management/';
const component = 'component/user-management/';

/** Inject a one-time style to hide any stray close buttons */
(function injectModalStyleOnce(){
  if (document.getElementById('hide-modal-x-style')) return;
  const css = `
    /* Hide all common close buttons in modals (Bootstrap 4/5 themes) */
    .modal .modal-header .btn-close,
    .modal .modal-header .close,
    .modal .modal-content > .btn-close,
    .modal .modal-content > .close {
      display: none !important;
    }
  `;
  const style = document.createElement('style');
  style.id = 'hide-modal-x-style';
  style.textContent = css;
  document.head.appendChild(style);
})();

/** Remove X and trim empty header (robust: runs after animations, multiple selectors) */
function polishModalHeader(expectedHeaderText) {
  const attempts = [0, 50, 200]; // re-run to catch post-animation DOM changes
  attempts.forEach(delay => {
    setTimeout(function () {
      const $modal = $('.modal:visible').last();
      if (!$modal.length) return;

      // Remove close buttons injected by theme/Bootstrap variants
      $modal.find('.btn-close, button.close, a.close').remove();

      const $header = $modal.find('.modal-header');
      if (!$header.length) return;

      const titleText = $.trim($header.find('.modal-title').text() || '');
      const shouldRemoveHeader =
        (typeof expectedHeaderText !== 'undefined' && $.trim(expectedHeaderText) === '') ||
        titleText === '';

      if (shouldRemoveHeader) {
        $header.remove();
      }
    }, delay);
  });
}

/** Also hook Bootstrap's 'shown' so we run after transitions */
$(document).on('shown.bs.modal', '.modal', function () {
  polishModalHeader();
});

$(document).on('click', '.openmodaldetails-modal', function() {
  var action = module + 'source';
  var dataObj  = { action : $(this).data('type'), id : $(this).data('id') };

  var formData = new FormData();
  for (const key in dataObj) {
    if (Object.prototype.hasOwnProperty.call(dataObj, key)) {
      formData.append(key, dataObj[key]);
    }   
  }

  var request = main.send_ajax(formData, action, 'POST', true);
  request.done(function (data) {
    action = component + data.action;
    main.modalOpen(data.header, data.html, data.button, action, 'modal-xl');

    // remove the "X" and strip the header if title is empty
    polishModalHeader(data.header);
  });
});

$(document).on('click', '.delete', function() {
  var dataObj  = { id : $(this).data('id') };
  var formData = new FormData();
  for (const key in dataObj) {
    if (Object.prototype.hasOwnProperty.call(dataObj, key)) {
      formData.append(key, dataObj[key]);
    }   
  }
  main.confirmMessage('warning', 'DELETE RECORD', 'Are you sure you want to delete this record? ', 'deleteRecord', formData)
});

function deleteRecord(formData) {
  var action = module + 'delete';
  var request = main.send_ajax(formData, action, 'POST', true);
  request.done(function (data) {
    if (data.status) {
      main.confirmMessage('success', 'Successfully Deleted!', 'Are you sure you want to reload this page? ', 'reloadPage', '')
    } else {
      main.alertMessage('danger', 'Failed to Delete!', '');
    }
  });
}

/* Keep your image preview */
$(document).on('change', '#imageInput', function(event) {
  const file = event.target.files && event.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      $('#preview').attr('src', e.target.result).show();
    }
    reader.readAsDataURL(file);
  }
});
