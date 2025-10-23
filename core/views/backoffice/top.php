<nav class="navbar header-navbar pcoded-header">
  <div class="navbar-wrapper">
    <div class="navbar-logo">
      <a class="mobile-menu waves-effect waves-light" id="mobile-collapse" href="#!">
        <i class="ti-menu"></i>
      </a>
      <a href="/"></a>
      <a class="mobile-options waves-effect waves-light">
        <i class="ti-more"></i>
      </a>
    </div>

    <div class="navbar-container container-fluid">
      <ul class="nav-left">
        <li>
          <div class="sidebar_toggle">
            <a href="javascript:void(0)"><i class="ti-menu"></i></a>
          </div>
        </li>
        <li>
          <a href="#!" onclick="javascript:toggleFullScreen()" class="waves-effect waves-light">
            <i class="ti-fullscreen"></i>
          </a>
        </li>
      </ul>

      <ul class="nav-right">
        <!-- Username display (static, not clickable, no dropdown) -->
        <!-- <li class="username-display-static">
          <i class="ti-user"></i>
          <span><?=$_SESSION['username']?></span>
        </li> -->

        <!-- Separate Logout -->
        <li class="header-logout">
          <a href="<?=$_ENV['URL_HOST']?>userLogout" class="logout-link waves-effect waves-light">
            <i class="ti-power-off"></i>
            <!-- <span>Logout</span> -->
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<style>
/* Username: static display (not clickable) */
.username-display-static {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
  font-size: 16px;     /* same size as logout */
  color: #fff;         /* white text for dark navbar */
  padding: 8px 14px;
  cursor: default;     /* not clickable */
  user-select: none;   /* prevent text selection */
}
.username-display-static i {
  font-size: 18px;     /* match logout icon */
  color: #93c5fd;      /* light blue accent */
}

/* Logout link (no underline, no hover effects) */
.logout-link {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #fff !important;  /* white */
  font-weight: 600;
  font-size: 16px;
  text-decoration: none !important; /* FORCE no underline */
  padding: 8px 14px;
}
.logout-link i {
  font-size: 18px;
}
/* Remove underline on hover/focus/active */
.logout-link:hover,
.logout-link:focus,
.logout-link:active {
  color: #fff !important;          /* keep it white */
  text-decoration: none !important; /* no underline ever */
}
</style>

<script>
/* Defensive fix: ensure no dropdown popup shows */
document.addEventListener('DOMContentLoaded', function(){
  var u = document.querySelector('.username-display-static');
  if (u){
    u.addEventListener('click', function(e){
      e.preventDefault();
      e.stopPropagation();
    }, true);
  }
});
</script>
