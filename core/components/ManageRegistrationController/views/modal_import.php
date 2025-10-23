  <div class="card">
    <div class="card-header bg-primary text-white">Upload Excel</div>
    <div class="card-body">
      <form id="excelForm">
        <div class="mb-3">
          <label for="excel_file" class="form-label">Excel File (.xls, .xlsx)</label>
          <input type="file" class="form-control" id="excel_file" name="excel_file" required accept=".xls,.xlsx">
        </div>
        <button type="submit" class="btn btn-success">Upload</button>
      </form>
    </div>
  </div>

  <div class="mt-4" id="table_result"></div>


  <script>
  $('#excelForm').on('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);

    $.ajax({
      url: 'upload_excel',
      type: 'POST',
      data: formData,
      contentType: false,
      processData: false,
      success: function(response) {
        $('#table_result').html(response);
      },
      error: function(xhr) {
        alert('Upload failed!');
      }
    });
  });
</script>