<!DOCTYPE html>
<html lang="en">

    <head>
        <title><?=$_ENV['APP_NAME']?></title>
    
        <meta http-equiv="content-type" content="text/html; charset=utf-8">
        <meta name="author" content="flexkit">


        <!-- Favicon icon -->
        <link rel="icon" href="<?=$_ENV['BASE_PATH']?>/src/images/logos/blogo.ico" type="image/x-icon">

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        <!-- Google font-->
        <link href="https://fonts.googleapis.com/css?family=Roboto:400,500" rel="stylesheet">

        
        <!-- Icon Font Stylesheet -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
        
        <link href="https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Allura&display=swap" rel="stylesheet">
        
        <!-- Stylesheets -->
        <link rel="stylesheet" href="<?=$_ENV['BASE_PATH']?>/public/customer_template/Demo18/css/plugins/swiper.min.css" type="text/css">
        <link rel="stylesheet" href="<?=$_ENV['BASE_PATH']?>/public/customer_template/Demo18/css/style.css" type="text/css">
        <link rel="stylesheet" href="<?=$_ENV['BASE_PATH']?>/public/customer_template/Demo18/css/plugins/jquery.fancybox.css" type="text/css">

        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- SWEET ALERT -->
        <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

        <style>
            .custome-warning{
                padding:20px;
                margin: 20px;
                background-color: #FF4B2B;
                color: white;
                width: 100%;
                animation: fadeOut 2s forwards 2s; /* Fade out after 3 seconds */
            }

            .custome-success{
                padding:20px;
                margin: 20px;
                background-color: #0fb90d;
                color: white;
                width: 100%;
                animation: fadeOut 2s forwards 2s; /* Fade out after 3 seconds */
            }
            /* Fade-out animation */
            @keyframes fadeOut {
                to {
                    opacity: 0;
                }
            }
      </style>
    </head>

  <body class="URL_HOST" data-url="<?=$_ENV['BASE_PATH']?>">