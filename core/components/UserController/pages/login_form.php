<!DOCTYPE html>
<html lang="en">

<head>
    <title><?=$_ENV['APP_NAME']?> | Login</title>
 
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="<?=$_ENV['URL_HOST']?>" />
    <meta name="keywords" content="<?=$_ENV['URL_HOST']?>" />
    <meta name="author" content="<?=$_ENV['URL_HOST']?>" />

    <!-- ✅ New favicon set -->
    <link rel="icon" href="<?=$_ENV['URL_HOST']?>src\images\logos\OIP-removebg-preview.png" type="image/x-icon">

    <!-- Google font-->     
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500" rel="stylesheet">
    <!-- Required Framework -->
    <link rel="stylesheet" type="text/css" href="<?=$_ENV['URL_HOST']?>public/admin_template/assets/css/bootstrap/css/bootstrap.min.css">
    <!-- waves.css -->
    <link rel="stylesheet" href="<?=$_ENV['URL_HOST']?>public/admin_template/assets/pages/waves/css/waves.min.css" type="text/css" media="all">
    <!-- themify-icons line icon -->
    <link rel="stylesheet" type="text/css" href="<?=$_ENV['URL_HOST']?>public/admin_template/assets/icon/themify-icons/themify-icons.css">
    <!-- ico font -->
    <link rel="stylesheet" type="text/css" href="<?=$_ENV['URL_HOST']?>public/admin_template/assets/icon/icofont/css/icofont.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" type="text/css" href="<?=$_ENV['URL_HOST']?>public/admin_template/assets/icon/font-awesome/css/font-awesome.min.css">
    <!-- Style.css -->
    <link rel="stylesheet" type="text/css" href="<?=$_ENV['URL_HOST']?>public/admin_template/assets/css/style.css">

    <!-- Custom overrides: keep this AFTER all CSS links -->
    <style>
        body {
            background: url('<?=$_ENV['URL_HOST']?>src/images/logos/pedro_logo.jpg') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;            /* full screen */
            margin: 0;
            display: flex;
            justify-content: flex-start; /* form to the left */
            align-items: center;        /* vertically centered */
            overflow: hidden;           /* prevent scrolling */
        }

        .warning {
            padding: 20px;
            margin: 20px;
            border-radius: 3%;
            background-color: #FF4B2B;
            color: white;
        }

        .success {
            padding: 20px;
            margin: 20px;
            border-radius: 3%;
            background-color: #0fb90d;
            color: white;
        }

        /* Make login form fixed size */
        .login-block {
            max-width: 420px;
            margin-left: 50px;  /* push form from left edge */
            width: 100%;
        }

        .login-block .auth-box {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            padding: 30px;
            width: 100%;
            min-height: 380px;  /* control length */
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* Rounded inputs */
        .login-block .auth-box .form-group.form-primary .form-control {
            width: 100%;
            padding: 12px 16px !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 30px !important;
            background-color: #fff !important;
            outline: none !important;
            font-size: 14px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08) !important;
            transition: box-shadow 0.25s ease, border-color 0.25s ease, transform 0.05s ease;
        }
        .login-block .auth-box .form-group.form-primary .form-bar { display: none !important; }
        .login-block .auth-box .form-group.form-primary .form-control:hover {
            border-color: #4a90e2 !important;
            box-shadow: 0 6px 14px rgba(74,144,226,0.25) !important;
        }
        .login-block .auth-box .form-group.form-primary .form-control:focus {
            border-color: #4a90e2 !important;
            box-shadow: 0 8px 18px rgba(74,144,226,0.35) !important;
            transform: translateY(-1px);
        }
        .login-block .auth-box .form-group.form-primary .form-control::placeholder { color: #94a3b8; }

        /* Button */
        .login-block .auth-box button[type="submit"] {
            border-radius: 30px !important;
            padding: 12px 16px !important;
            font-size: 15px;
            font-weight: 500;
            border: none;
            background: linear-gradient(135deg, #4a90e2, #357ABD);
            color: #fff;
            box-shadow: 0 3px 8px rgba(74,144,226,0.35);
            transition: all 0.3s ease;
        }
        .login-block .auth-box button[type="submit"]:hover {
            background: linear-gradient(135deg, #357ABD, #2d5ea8);
            box-shadow: 0 6px 14px rgba(74,144,226,0.45);
            transform: translateY(-2px);
        }
        .login-block .auth-box button[type="submit"]:active {
            transform: translateY(0);
            box-shadow: 0 2px 6px rgba(74,144,226,0.3);
        }
    </style>
</head>

<body themebg-pattern="theme1">
      
  <!-- Pre-loader start -->
  <div class="theme-loader">
      <div class="loader-track">
          <div class="preloader-wrapper">
              <div class="spinner-layer spinner-blue">
                  <div class="circle-clipper left">
                      <div class="circle"></div>
                  </div>
                  <div class="gap-patch">
                      <div class="circle"></div>
                  </div>
                  <div class="circle-clipper right">
                      <div class="circle"></div>
                  </div>
              </div>
              <div class="spinner-layer spinner-red">
                  <div class="circle-clipper left">
                      <div class="circle"></div>
                  </div>
                  <div class="gap-patch">
                      <div class="circle"></div>
                  </div>
                  <div class="circle-clipper right">
                      <div class="circle"></div>
                  </div>
              </div>
              <div class="spinner-layer spinner-yellow">
                  <div class="circle-clipper left">
                      <div class="circle"></div>
                  </div>
                  <div class="gap-patch">
                      <div class="circle"></div>
                  </div>
                  <div class="circle-clipper right">
                      <div class="circle"></div>
                  </div>
              </div>
              <div class="spinner-layer spinner-green">
                  <div class="circle-clipper left">
                      <div class="circle"></div>
                  </div>
                  <div class="gap-patch">
                      <div class="circle"></div>
                  </div>
                  <div class="circle-clipper right">
                      <div class="circle"></div>
                  </div>
              </div>
          </div>
      </div>
  <!-- Pre-loader end -->
  </div>

  <section class="login-block ">
        <!-- Container-fluid starts -->
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <!-- Authentication card start -->
                    <form action="<?=$_ENV['URL_HOST'].'auth' ?>" method="POST" class="md-float-material form-material">
                        <div class="auth-box card">
                            <div class="card-block">
                                <div class="row m-b-20">
                                    <div class="col-md-12">
                                        <img src="<?=$_ENV['URL_HOST']?>src/images/logos/OIP-removebg-preview.png" width="100%" alt="logo.png">
                                        <h3 class="text-center">Sign In</h3>
                                    </div>
                                </div>

                                <div class="form-group form-primary">
                                    <input type="text" name="email" class="form-control" required placeholder="Your Email Address">
                                    <span class="form-bar"></span>
                                </div>

                                <div class="form-group form-primary">
                                    <input type="password" name="password" class="form-control" required placeholder="Password">
                                    <span class="form-bar"></span>
                                </div>

                                <div class="row m-t-25 text-left">
                                    <div class="col-12">
                                        <div class="checkbox-fade fade-in-primary d-">
                                            <!-- <label>
                                                <input type="checkbox" value="">
                                                <span class="cr"><i class="cr-icon icofont icofont-ui-check txt-primary"></i></span>
                                                <span class="text-inverse">Remember me</span>
                                            </label> -->
                                        </div>
                                        <!-- <div class="forgot-phone text-right f-right">
                                            <a href="<?=$_ENV['URL_HOST'].'forgot_password' ?>" class="text-right f-w-600"> Forgot Password?</a>
                                        </div> -->
                                    </div>
                                </div>

                                <div class="row m-t-30">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary btn-md btn-block waves-effect waves-light text-center m-b-20">Sign in</button>
                                    </div>
                                </div>

                                <div class="boxings <?=isset($_GET["type"])?$_GET["type"]:''?>">
                                    <?=isset($_GET["message"])?$_GET["message"]:''?>
                                </div>
                            </div>
                        </div>
                    </form>
                    <!-- end of form -->
                </div>
                <!-- end of col-sm-12 -->
            </div>
            <!-- end of row -->
        </div>
        <!-- end of container-fluid -->
    </section>
  
    <script type="text/javascript" src="<?=$_ENV['URL_HOST']?>public/admin_template/assets/js/jquery/jquery.min.js"></script>
    <script type="text/javascript" src="<?=$_ENV['URL_HOST']?>public/admin_template/assets/js/jquery-ui/jquery-ui.min.js "></script>
    <script type="text/javascript" src="<?=$_ENV['URL_HOST']?>public/admin_template/assets/js/popper.js/popper.min.js"></script>
    <script type="text/javascript" src="<?=$_ENV['URL_HOST']?>public/admin_template/assets/js/bootstrap/js/bootstrap.min.js "></script>
    <script src="<?=$_ENV['URL_HOST']?>public/admin_template/assets/pages/waves/js/waves.min.js"></script>
    <script type="text/javascript" src="<?=$_ENV['URL_HOST']?>public/admin_template/assets/js/jquery-slimscroll/jquery.slimscroll.js "></script>
    <script type="text/javascript" src="<?=$_ENV['URL_HOST']?>public/admin_template/assets/js/SmoothScroll.js"></script>
    <script src="<?=$_ENV['URL_HOST']?>public/admin_template/assets/js/jquery.mCustomScrollbar.concat.min.js "></script>
    <!-- <script type="text/javascript" src="bower_components/i18next/js/i18next.min.js"></script>
    <script type="text/javascript" src="bower_components/i18next-xhr-backend/js/i18nextXHRBackend.min.js"></script>
    <script type="text/javascript" src="bower_components/i18next-browser-languagedetector/js/i18nextBrowserLanguageDetector.min.js"></script>
    <script type="text/javascript" src="bower_components/jquery-i18next/js/jquery-i18next.min.js"></script> -->
    <script type="text/javascript" src="<?=$_ENV['URL_HOST']?>public/admin_template/assets/js/common-pages.js"></script>
</body>
</html>
