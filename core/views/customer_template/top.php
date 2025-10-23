  <!-- Header Type 6 -->
  <header id="header" class="header sticky_disabled header_sticky-bg_dark w-100 theme-bg-color">
   
    <div class="header-desk_type_6 style2">
      <div class="header-middle border-0 position-relative py-4">
        <div class="container d-flex align-items-center">
          <div class="logo">
            <a href="<?=$_ENV['URL_HOST']?>customer/customer/index">
              <img src="<?=$_ENV['URL_HOST']?>src/images/logos/blogo.ico" width="60" alt="Uomo" class="logo__image">
              <span style="font-weight: bold; font-family:'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif; font-size:16px; ">Powerhouse Project AutoCare</span>
            </a>
          </div><!-- /.logo -->

          <nav class="navigation flex-grow-1 fs-15 fw-semi-bold">
            <ul class="navigation__list list-unstyled d-flex">

              <li class="navigation__item">
                <a href="<?=$_ENV['URL_HOST']?>customer/customer/index" class="navigation__link">Home</a>
              </li>


              <li class="navigation__item">
                <a href="<?=$_ENV['URL_HOST']?>customer/customer/index?page=shop" class="navigation__link">Shop</a>
              </li>


              <li class="navigation__item">
                <a href="<?=$_ENV['URL_HOST']?>customer/customer/index?page=about" class="navigation__link">About</a>
              </li>

              <li class="navigation__item">
                <a href="<?=$_ENV['URL_HOST']?>customer/customer/index?page=faq" class="navigation__link">FAQs</a>
              </li>
             
              <li class="navigation__item">
                <a href="<?=$_ENV['URL_HOST']?>customer/customer/index?page=myorders&view=Orders" class="navigation__link">My Profile</a>
              </li>
             
            </ul><!-- /.navigation__list -->
          </nav><!-- /.navigation -->

          <div class="header-tools d-flex align-items-center me-0">
            <div class="header-tools__item text-white d-none d-xxl-block">
              <span class="fs-15 "><?=isset($_SESSION["email"]) ?$_SESSION["email"]:''?></span>
            </div>


            <a class="header-tools__item" href="<?=$_ENV['URL_HOST']?>customer/customer/index?page=myorders&view=notification">
              <i class="fas fa-bell" style="font-size: 24px;"></i>
            </a>

            <div class="header-tools__item hover-container">
              <a class="header-tools__item js-open-aside" href="#" data-aside="customerForms">
                <svg class="d-block" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><use href="#icon_user" /></svg>
              </a>
            </div>
            
    
    
        
          </div><!-- /.header__tools -->
        </div>
      </div><!-- /.header-middle -->

      <div class="header-bottom pb-4 mb-2">
        <div class="container d-flex align-items-center">

          <?php 
           $sub = 'shop';
          ?>

          <form action="<?=$_ENV['URL_HOST']?>customer/customer/index" method="GET" class="header-search search-field me-0 border-radius-10">
            <input type="hidden" name="page" value="<?=$sub?>">
            <button class="btn header-search__btn" type="submit">
              <svg class="d-block" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><use href="#icon_search" /></svg>
            </button>
            <input class="header-search__input w-100" type="text" name="searchkeyword" placeholder="Search products...">
            <div class="hover-container position-relative">
              <div class="js-hover__open">
                <input class="header-search__category search-field__actor border-0 bg-white w-100 fw-semi-bold" type="text" name="searchcategory" placeholder="ALL CATEGORY" readonly>
              </div>
              <div class="header-search__category-list js-hidden-content mt-2">
                <ul class="search-suggestion list-unstyled">
                  <li class="search-suggestion__item js-search-select">All Category</li>
                  <li class="search-suggestion__item js-search-select">Brand</li>
                </ul>
              </div>
            </div>
          </form><!-- /.header-search -->
        </div>
      </div><!-- /.header-bottom -->
    </div><!-- /.header-desk header-desk_type_6 -->
  </header><!-- End Header Type 6 -->
