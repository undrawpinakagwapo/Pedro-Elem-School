const module = `${URL_BASED}component/curriculum-management/`;
const component = `component/curriculum-management/`;
var tblAvailable;
var tblSelected;

$(document).on('click', '.openmodaldetails-modal', function () {
  var action = module + 'source';

  var dataObj = {
    action: $(this).data('type'),
    id: $(this).data('id')
  };

  // Convert the data object into FormData
  var formData = new FormData();
  for (const key in dataObj) {
    if (dataObj.hasOwnProperty(key)) {
      formData.append(key, dataObj[key]);
    }
  }

  var request = main.send_ajax(formData, action, 'POST', true);
  request.done(function (data) {
    action = component + data.action;

    // ⛔ Remove the header title by passing an empty string
    main.modalOpen('', data.html, data.button, action, 'modal-xl');

    // ⛔ Remove the "×" close button (handles Bootstrap 4 & 5)
    // Also ensure any title text is cleared if a template injected it.
    const $modal = $('.modal.show, .modal'); // pick active modal
    $modal.find('.btn-close, .close').remove();             // remove close buttons
    $modal.find('.modal-title').empty();                    // clear any leftover title text

    // Initialize tables
    tblAvailable = $('#subjectlist').DataTable();
    tblSelected = $('#mysubjectlist').DataTable({
      paging: false // disables pagination completely
    });
  });
});

// Delete logic
$(document).on('click', '.delete', function () {
  var dataObj = {
    id: $(this).data('id')
  };

  var formData = new FormData();
  for (const key in dataObj) {
    if (dataObj.hasOwnProperty(key)) {
      formData.append(key, dataObj[key]);
    }
  }

  main.confirmMessage('warning', 'DELETE RECORD', 'Are you sure you want to delete this record? ', 'deleteRecord', formData);
});

function deleteRecord(formData) {
  var action = module + 'delete';

  var request = main.send_ajax(formData, action, 'POST', true);
  request.done(function (data) {
    if (data.status) {
      main.confirmMessage('success', 'Successfully Deleted!', 'Are you sure you want to reload this page? ', 'reloadPage', '');
    } else {
      main.alertMessage('danger', 'Failed to Delete!', '');
    }
  });
}

// Add subject (move from available -> selected)
$(document).on('click', '.add_subject', function () {
  var id = $(this).data('id');
  var code = $(this).data('code');
  var name = $(this).data('name');

  // Append to selected table
  tblSelected.row.add([
    `<button type="button" class="btn btn-sm btn-danger remove_subject" 
        data-id="${id}" data-code="${code}" data-name="${name}">
        <i class="fa fa-minus"></i>
     </button>
     <input type="hidden" value="${id}" name="itemlist[data][new${id}][subject_id]">`,
    tblSelected.rows().count() + 1,
    code,
    name
  ]).draw(false);

  // Remove from available table
  tblAvailable.row($(this).closest('tr')).remove().draw(false);
});

// Remove subject (move from selected -> available)
$(document).on('click', '.remove_subject', function () {
  var id = $(this).data('id');
  var code = $(this).data('code');
  var name = $(this).data('name');

  // Append back to available table
  tblAvailable.row.add([
    `<button type="button" class="btn btn-sm btn-primary add_subject" 
        data-id="${id}" data-code="${code}" data-name="${name}">
        <i class="fa fa-plus"></i>
     </button>`,
    code,
    name
  ]).draw(false);

  // Remove from selected table
  tblSelected.row($(this).closest('tr')).remove().draw(false);
});

// Mark row deleted in edit mode
$(document).on('click', '.remove_edit', function () {
  var tr = $(this).parent().parent();
  tr.find('.deleted').val(1);
  tr.addClass('rowhide');
});
