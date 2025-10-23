<?php
$const_display_component_SEGMENT = isset(getSegment()[2]) ? getSegment()[2] : 'dashboard';

function sidebar($value, $parent = false, $const_display_component_SEGMENT)  {
    $sidebar = ($parent) ? '<ul class="pcoded-item pcoded-left-item">' : '<ul class="pcoded-submenu">';
    foreach ($value as $key => $val) {
        $active = ($key == $const_display_component_SEGMENT) ? 'active' : '';

        if (isset($val['child']) && count($val['child']) > 0) {
            $sidebar .= '<li class="pcoded-hasmenu '.$active.'">';
            $sidebar .=   '<a href="javascript:void(0)" class="waves-effect waves-dark">';
            $sidebar .=     '<span class="pcoded-micon"><i class="ti-'.$val['icon'].'"></i></span>';
            $sidebar .=     '<span class="pcoded-mtext" data-i18n="nav.basic-components.main">'.$val['Title'].'</span>';
            $sidebar .=     '<span class="pcoded-mcaret"></span>';
            $sidebar .=   '</a>';
            $sidebar .=   sidebar($val['child'], false, $const_display_component_SEGMENT);
            $sidebar .= '</li>';
        } else {
            $basePath = $_ENV['BASE_PATH'] ?? '';
            $sidebar .= '<li class="'.$active.'">';
            $sidebar .=   '<a href="'.$basePath.'/component/'.$key.'/index" class="waves-effect waves-dark" style="text-decoration:none">';
            $sidebar .=     '<span class="pcoded-micon"><i class="fa fa-'.$val['icon'].'"></i><b>D</b></span>';
            $sidebar .=     '<span class="pcoded-mtext" data-i18n="nav.dash.main">'.$val['Title'].'</span>';
            $sidebar .=     '<span class="pcoded-mcaret"></span>';
            $sidebar .=   '</a>';
            $sidebar .= '</li>';
        }
    }
    $sidebar .= '</ul>';
    return $sidebar;
}

$componentPages = sideBarDetails();
$sidebar = '';
foreach ($componentPages as $key => $val) {
    $sidebar .= '<div class="pcoded-navigation-label" data-i18n="nav.category.navigation">'.$key.'</div>';
    $sidebar .= sidebar($val, true, $const_display_component_SEGMENT);
}
?>

<!-- Override styles to remove the faint underline/divider under the navbar -->
<style>
  /* Remove bottom borders/shadows/after-lines often used by Pcoded skins */
  .pcoded-navbar,
  .pcoded-navbar .pcoded-inner-navbar,
  .pcoded-navbar .main-menu {
    border-bottom: 0 !important;
    box-shadow: none !important;
    background-image: none !important;
  }

  /* Some themes draw a thin rule with pseudo-elements */
  .pcoded-navbar::before,
  .pcoded-navbar::after,
  .pcoded-navbar .pcoded-inner-navbar::before,
  .pcoded-navbar .pcoded-inner-navbar::after {
    content: none !important;
    display: none !important;
  }

  /* Kill per-item separators that appear as a thin line */
  .pcoded-navbar .pcoded-item.pcoded-left-item::after,
  .pcoded-navbar .pcoded-item > li::after,
  .pcoded-navbar .pcoded-submenu > li::after {
    content: none !important;
    display: none !important;
    border: 0 !important;
  }

  /* Ensure list items themselves have no bottom border */
  .pcoded-navbar .pcoded-item.pcoded-left-item,
  .pcoded-navbar .pcoded-item > li,
  .pcoded-navbar .pcoded-submenu > li,
  .pcoded-navbar .pcoded-navigation-label {
    border-bottom: 0 !important;
    box-shadow: none !important;
  }

  /* And links never show underlines */
  .pcoded-navbar a { text-decoration: none !important; }
</style>

<nav class="pcoded-navbar">
  <div class="sidebar_toggle"><a href="#"><i class="icon-close icons"></i></a></div>
  <div class="pcoded-inner-navbar main-menu">
    <div class="">
      <div class="main-menu-header">
        <!-- <img class="img-80 img-radius" src="<?=$_ENV['BASE_PATH']?>/public/admin_template/assets/images/avatar-blank.jpg" alt="User-Profile-Image"> -->
        <img src="<?=$_ENV['BASE_PATH']?>/src/images/logos/OIP-removebg-preview.png" alt="User-Profile-Image">
        <div class="user-details">
          <span id="more-details"><?=$_SESSION['username']?></span>
        </div>
      </div>

      <!--
      <div class="main-menu-content">
        <ul>
          <li class="more-details">
            <a href="user-profile.html"><i class="ti-user"></i>View Profile</a>
            <a href="#!"><i class="ti-settings"></i>Settings</a>
            <a href="<?=$_ENV['BASE_PATH']?>/userLogout"><i class="ti-layout-sidebar-left"></i>Logout</a>
          </li>
        </ul>
      </div>
      -->
    </div>

    <?php echo $sidebar; ?>

  </div>
</nav>
