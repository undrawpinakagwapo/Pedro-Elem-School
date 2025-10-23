(function(){
  document.addEventListener('DOMContentLoaded', function(){
    // Keyboard accessibility for tiles (Enter / Space)
    document.querySelectorAll('.kpi-tile').forEach(function(tile){
      tile.setAttribute('tabindex','0');
      tile.addEventListener('keydown', function(e){
        if (e.key === 'Enter' || e.key === ' ') {
          var a = tile.closest('a.kpi-link');
          if (a) a.click();
          e.preventDefault();
        }
      });
    });
  });
})();
