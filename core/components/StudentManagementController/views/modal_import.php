<div class="card">
  <div class="card-header bg-primary text-white">Upload Excel</div>
  <div class="card-body">
    <form id="excelForm">
      <!-- Excel File Upload -->
      <div class="mb-3">
        <label for="excel_file" class="form-label">Excel File (.xls, .xlsx)</label>
        <input 
          type="file" 
          class="form-control styled-input" 
          id="excel_file" 
          name="excel_file" 
          required 
          accept=".xls,.xlsx">
      </div>

      <!-- Submit -->
      <button type="submit" class="btn btn-success">Upload</button>
    </form>
  </div>
</div>

<div class="mt-4" id="table_result"></div>

<style>
.styled-input{appearance:none;-webkit-appearance:none;-moz-appearance:none;display:block;width:100%;height:calc(2.25rem + 2px);padding:.375rem .75rem;font-size:1rem;line-height:1.5;color:#495057;background:#fff;background-clip:padding-box;border:1px solid #ced4da;border-radius:.25rem;box-sizing:border-box;transition:border-color .15s ease-in-out,box-shadow .15s ease-in-out}
.styled-input:focus{border-color:#80bdff;outline:0;box-shadow:0 0 0 .2rem rgba(0,123,255,.25)}
</style>

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
    error: function() {
      alert('Upload failed!');
    }
  });
});
</script>
