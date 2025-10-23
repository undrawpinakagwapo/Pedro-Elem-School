const module = URL_BASED + (URL_BASED && !URL_BASED.endsWith('/') ? '/' : '') + 'component/faculty-management/';

// OPEN modal (custom overlay HTML returned by PHP)
$(document).on('click', '.openmodaldetails-modal', function () {
  const action = module + 'source';
  const dataObj = { action: $(this).data('type'), id: $(this).data('id') || '' };

  const fd = new FormData();
  Object.keys(dataObj).forEach(k => fd.append(k, dataObj[k]));

  const req = main.send_ajax(fd, action, 'POST', true);
  req.done(function (resp) {
    try { resp = (typeof resp === 'string') ? JSON.parse(resp) : resp; } catch(e){}
    if (!resp || !resp.html) { main.alertMessage('danger','Failed to open.',''); return; }

    // Inject the whole overlay markup returned by PHP
    $('body').append(resp.html);

    // Wire up handlers
    bindFacultyModalEvents();
  });
});

// DELETE
$(document).on('click', '.delete', function () {
  const fd = new FormData();
  fd.append('id', $(this).data('id'));
  main.confirmMessage('warning', 'DELETE RECORD', 'Are you sure you want to delete this record?', 'deleteRecord', fd);
});
function deleteRecord(formData){
  const req = main.send_ajax(formData, module + 'delete', 'POST', true);
  req.done(function (data) {
    if (data.status) main.confirmMessage('success','Successfully Deleted!','Reload this page?','reloadPage','');
    else main.alertMessage('danger','Failed to Delete!','');
  });
}

// Preview photo (delegated for dynamic modal)
$(document).on('change', '#imageInput', function (event) {
  const file = event.target.files && event.target.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = e => $('#preview').attr('src', e.target.result).show();
  reader.readAsDataURL(file);
});

function bindFacultyModalEvents(){
  const $overlay = $('#fm-overlay');
  if (!$overlay.length) return;

  // Submit form (normal POST to allow server redirect)
  $('#fm-submit').off('click').on('click', function(){
    const form = document.getElementById('facultyForm');
    if (form) form.submit();
  });

  // Cancel
  $('#fm-cancel').off('click').on('click', function(){ $overlay.remove(); });

  // Click outside card to close
  $overlay.off('click').on('click', function(e){
    if (e.target === this) $(this).remove();
  });

  // ESC to close
  $(document).on('keydown.fm', function(e){
    if (e.key === 'Escape') { $overlay.remove(); $(document).off('keydown.fm'); }
  });
}
