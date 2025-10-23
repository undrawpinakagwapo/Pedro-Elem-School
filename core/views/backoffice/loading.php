<!-- Pre-loader start -->
<div class="theme-loader">
  <svg class="spinner" viewBox="0 0 44 44" aria-hidden="true">
    <defs>
      <!-- Blue → light-blue gradient that fades toward the tail -->
      <linearGradient id="arcGradient" x1="0" y1="0" x2="44" y2="44" gradientUnits="userSpaceOnUse">
        <stop offset="0%"  stop-color="#1e40af"/>   <!-- indigo-800 -->
        <stop offset="45%" stop-color="#2563eb"/>   <!-- blue-600 -->
        <stop offset="75%" stop-color="#60a5fa"/>   <!-- blue-400 -->
        <stop offset="100%" stop-color="#93c5fd" stop-opacity="0.7"/> <!-- blue-300, slight fade -->
      </linearGradient>
    </defs>

    <!-- Optional faint full track (very light) -->
    <circle class="track" cx="22" cy="22" r="18" fill="none" stroke="#e5efff" stroke-width="6"/>

    <!-- The visible arc segment (rounded ends) -->
    <circle class="arc" cx="22" cy="22" r="18" fill="none"
            stroke="url(#arcGradient)" stroke-width="6" stroke-linecap="round"/>
  </svg>

  <div class="loader-text">Loading...</div>
</div>

<style>
/* Fullscreen container */
.theme-loader{
  position: fixed; inset: 0;
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  background: #f8fafc;           /* light background; change to #fff/transparent if needed */
  z-index: 9999;
}

/* Spinner sizing + rotation */
.spinner{
  width: 88px; height: 88px;      /* adjust size here */
  animation: rotate 1.1s linear infinite;
}

/* Arc styling: a fixed-length dash to create the “gap” */
.arc{
  /* Circumference of r=18 is ~113; we want about 75% arc, 25% gap */
  stroke-dasharray: 85 120;       /* arc length then gap length */
  stroke-dashoffset: 0;           /* start position of the arc */
}

/* Optional faint track (already set via SVG) */
.track{ opacity: .45; }

/* Smooth rotation */
@keyframes rotate{ to{ transform: rotate(360deg); } }

/* Label */
.loader-text{
  margin-top: 14px;
  font-size: 14px;
  color: #475569;                 /* slate-600 */
  font-weight: 500;
  letter-spacing: .2px;
}

/* Respect reduced-motion */
@media (prefers-reduced-motion: reduce){
  .spinner{ animation: none; }
}
</style>

<script>
  // Optional: auto fade-out when the page has loaded
  // Uncomment to enable.
  // window.addEventListener('load', function(){
  //   const root = document.querySelector('.theme-loader');
  //   if (!root) return;
  //   root.style.transition = 'opacity .25s ease';
  //   root.style.opacity = '0';
  //   setTimeout(() => root.remove(), 260);
  // });
</script>
<!-- Pre-loader end -->
