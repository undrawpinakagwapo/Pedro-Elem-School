// components/StudentProfileController/js/custom.js

/* ==========================
 * Image preview (avatar)
 * ========================== */
$(document).on('change', '#imageInput', function (event) {
  const file = event.target.files && event.target.files[0];
  if (!file) return;

  if (!/^image\//.test(file.type)) {
    alert("Please select an image file.");
    this.value = "";
    return;
  }

  const reader = new FileReader();
  reader.onload = function (e) {
    $('#preview').attr('src', e.target.result).show();
  };
  reader.readAsDataURL(file);
});

/* ==========================
 * QR Modal Controls
 * Works with the markup:
 * - Button: #btnShowQr  (data-qrsrc, data-lrn)
 * - Backdrop/Modal: #qrBackdrop
 * - Image: #qrImg
 * - Close btn: #qrClose
 * - Hint: #qrHint
 * ========================== */
(function initQrModal(){
  const $btn   = $('#btnShowQr');
  const $back  = $('#qrBackdrop');
  const $img   = $('#qrImg');
  const $hint  = $('#qrHint');
  const $close = $('#qrClose');

  if ($back.length === 0) return; // no modal on this page

  function openQR(){
    if (!$btn.length || $btn.is(':disabled')) return;

    const src = $btn.attr('data-qrsrc');
    const lrn = $btn.attr('data-lrn') || '';

    if (!src) {
      alert('QR is not available yet. Make sure an LRN is set and saved.');
      return;
    }

    // Reset and set src
    $img.off('error'); // clear previous handlers
    $img.attr('src', src);

    // Set hint text
    if (lrn) {
      $hint.text('LRN: ' + lrn + ' • Right-click the image to download or print.');
    } else {
      $hint.text('Right-click the image to download or print.');
    }

    // Handle missing image
    $img.on('error', function(){
      $hint.text('QR image not found at ' + src + '. Save the profile or re-import to generate the QR.');
    });

    // Show modal
    $back.css('display', 'flex');
  }

  function closeQR(){
    $back.css('display', 'none');
  }

  // Button click / Enter key
  $(document).on('click', '#btnShowQr', function(e){
    e.preventDefault();
    openQR();
  });

  $(document).on('keydown', function(e){
    if (e.key === 'Enter' && $(document.activeElement).is('#btnShowQr')) {
      e.preventDefault();
      openQR();
    }
  });

  // Close actions
  $(document).on('click', '#qrClose', function(e){
    e.preventDefault();
    closeQR();
  });

  // Close when clicking backdrop (but not when clicking inside modal content)
  $back.on('click', function(e){
    if (e.target === this) closeQR();
  });

  // Esc to close
  $(document).on('keydown', function(e){
    if (e.key === 'Escape') closeQR();
  });
})();
