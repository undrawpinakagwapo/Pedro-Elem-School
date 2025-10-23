




  <!-- Footer Type 1 -->
  <footer class="footer footer_type_1 dark theme-bg-color">
    <div class="footer-bottom container">
      <div style=" text-align: center; ">
        <span>©2024 Powerhouse Project AutoCare</span>
        
      </div><!-- /.d-flex -->
    </div><!-- /.footer-bottom container -->
  </footer><!-- /.footer footer_type_1 -->
  <!-- End Footer Type 1 -->

 

  <!-- Customer Login Form -->
  <div class="aside aside_right overflow-hidden customer-forms" id="customerForms">
      <div class="customer__login">
        <div class="aside-header d-flex align-items-center">
          <h3 class="text-uppercase fs-6 mb-0"><?=isset($_SESSION["user_id"]) ? 'My Profile' :'Logins' ?></h3>
          <button class="btn-close-lg js-close-aside ms-auto"></button>
        </div><!-- /.aside-header -->


        <?php 
          if(isset($_SESSION["user_id"])) {
            ?>
              <button class="btn btn-primary w-100 text-uppercase" type="button"><a href="<?=$_ENV['URL_HOST'].'customer/customer/index?page=myorders&view=Account'?>" style="color:white">Profile</a></button>
              <hr>
              <button class="btn btn-primary w-100 text-uppercase" type="button"><a href="<?=$_ENV['URL_HOST'].'userLogout'?>" style="color:white">Logout</a></button>
            
            <?php
          } else {
            ?>
            <form action="<?=$_ENV['URL_HOST'].'auth' ?>" method="POST" class="aside-content">
              <input type="hidden" name="login_type" value="customer">
              <div class="form-floating mb-3">
                <input name="email" type="text" class="form-control form-control_gray" >
                <label for="customerNameEmailInput">Username or email address *</label>
              </div>
    
              <div class="pb-3"></div>
    
              <div class="form-label-fixed mb-3">
                <label for="customerPasswordInput" class="form-label">Password *</label>
                <input name="password" id="customerPasswordInput" class="form-control form-control_gray" type="password" placeholder="********">
              </div>
    
              <div class="d-flex align-items-center mb-3 pb-2">
                <div class="form-check mb-0">
                  <input name="remember" class="form-check-input form-check-input_fill" type="checkbox" value="" id="flexCheckDefault">
                  <label class="form-check-label text-secondary" for="flexCheckDefault">Remember me</label>
                </div>
                <a href="<?=$_ENV['URL_HOST'].'forgot_password' ?>" class="btn-text ms-auto">Lost password?</a>
              </div>
    
              <button class="btn btn-primary w-100 text-uppercase" type="submit">Log In</button>
    
              <div class="customer-option mt-4 text-center">
                <span class="text-secondary">No account yet?</span>
                <a href="<?=$_ENV['URL_HOST'].'customer/customer/index?page=register' ?>" class="btn-text js-show-register">Create Account</a>
              </div>
            </form>

            <?php
          }
        ?>
        
      </div><!-- /.customer__login -->

  </div><!-- /.aside aside_right -->

