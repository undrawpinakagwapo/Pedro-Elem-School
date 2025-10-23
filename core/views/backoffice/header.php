<?php
// $manifestPath = __DIR__ . '/../../public/react-app/build/asset-manifest.json';

// if (!file_exists($manifestPath)) {
//     die("Manifest not found: $manifestPath");
// }
// $manifest = json_decode(file_get_contents($manifestPath), true);

// $mainJs = $manifest['files']['main.js'] ?? null;
// $mainCss = $manifest['files']['main.css'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <title><?=$_ENV['APP_NAME']?></title>
    
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="description" content="Mega Able Bootstrap admin template made using Bootstrap 4 and it has huge amount of ready made feature, UI components, pages which completely fulfills any dashboard needs." />
        <meta name="keywords" content="bootstrap, bootstrap admin template, admin theme, admin dashboard, dashboard template, admin template, responsive" />
        <meta name="author" content="codedthemes" />
        <!-- Favicon icon -->
        <link rel="icon" href="<?=$_ENV['BASE_PATH']?>/src/images/logos/OIP-removebg-preview.png" type="image/x-icon">

        <!-- Google font-->     
        <link href="https://fonts.googleapis.com/css?family=Roboto:400,500" rel="stylesheet">

        <!-- waves.css -->
        <link rel="stylesheet" href="<?=$_ENV['BASE_PATH']?>/public/admin_template/assets/pages/waves/css/waves.min.css" type="text/css" media="all">
        <!-- Required Fremwork -->
        <link rel="stylesheet" type="text/css" href="<?=$_ENV['BASE_PATH']?>/public/admin_template/assets/css/bootstrap/css/bootstrap.min.css">
        <!-- waves.css -->
        <link rel="stylesheet" href="<?=$_ENV['BASE_PATH']?>/public/admin_template/assets/pages/waves/css/waves.min.css" type="text/css" media="all">
        <!-- themify icon -->
        <link rel="stylesheet" type="text/css" href="<?=$_ENV['BASE_PATH']?>/public/admin_template/assets/icon/themify-icons/themify-icons.css">
        <!-- Font Awesome -->
        <link rel="stylesheet" type="text/css" href="<?=$_ENV['BASE_PATH']?>/public/admin_template/assets/icon/font-awesome/css/font-awesome.min.css">
        <!-- scrollbar.css -->
        <link rel="stylesheet" type="text/css" href="<?=$_ENV['BASE_PATH']?>/public/admin_template/assets/css/jquery.mCustomScrollbar.css">
        <!-- am chart export.css -->
        <link rel="stylesheet" href="https://www.amcharts.com/lib/3/plugins/export/export.css" type="text/css" media="all" />
        <!-- Style.css -->
        <link rel="stylesheet" type="text/css" href="<?=$_ENV['BASE_PATH']?>/public/admin_template/assets/css/style.css">
     
        <!-- ico font -->
        <link rel="stylesheet" type="text/css" href="<?=$_ENV['BASE_PATH']?>/public/admin_template/assets/icon/icofont/css/icofont.css">

        <!-- Icon Font Stylesheet -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
        
        <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

        <link href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">

        <!-- 🔗 Load built React CSS -->
        <!-- <link rel="stylesheet" href="../../public/react-app/build<?=$mainCss?>">  -->
         <link rel="stylesheet" href="<?=$_ENV['BASE_PATH']?>/views/fonts/fonts.css">

        





        <style>
        /* ---------------- Alerts ---------------- */
        .custome-warning{
            padding:20px;
            margin: 20px;
            background-color: #FF4B2B;
            color: white;
            width: 100%;
            animation: fadeOut 2s forwards 2s;
        }
        .custome-success{
            padding:20px;
            margin: 20px;
            background-color: #0fb90d;
            color: white;
            width: 100%;
            animation: fadeOut 2s forwards 2s;
        }
        @keyframes fadeOut { to { opacity: 0; } }

        /* ---------------- Base Tweaks ---------------- */
        .card-block{ padding: 20px!important; }
        .dataTables_length .form-select-sm{ border-color: beige; }

        /* ---------------- Modern Blue Theme Tokens ---------------- */
        :root{
            --blue-50:  #eff6ff;
            --blue-100: #dbeafe;
            --blue-200: #bfdbfe;
            --blue-300: #93c5fd;
            --blue-400: #60a5fa;
            --blue-500: #3b82f6;
            --blue-600: #2563eb; /* Primary */
            --blue-700: #1d4ed8; /* Hover/Pressed */
            --blue-800: #1e40af;
        }

        a { color: var(--blue-600); text-decoration: none; }
        a:hover { color: var(--blue-700); text-decoration: underline; }

        .btn-primary,
        .btn.btn-primary {
            background-color: var(--blue-600) !important;
            border-color: var(--blue-600) !important;
        }
        .btn-primary:hover,
        .btn.btn-primary:hover {
            background-color: var(--blue-700) !important;
            border-color: var(--blue-700) !important;
        }

        .badge.bg-primary { background-color: var(--blue-600) !important; }
        .badge.bg-primary[href]:hover { background-color: var(--blue-700) !important; }

        .form-control:focus, .form-select:focus {
            border-color: var(--blue-500) !important;
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.15) !important;
        }

        #mainTable thead tr {
            background-color: var(--blue-600);
            color: #ffffff;
        }
        .table thead th { border-color: var(--blue-600) !important; }
        .table-striped tbody tr:nth-of-type(odd) { background-color: var(--blue-50); }

        /* ---------------- Remove blue block behind the sidebar image ---------------- */
        .pcoded-navbar .main-menu,
        .pcoded-navbar .pcoded-inner-navbar.main-menu {
            background: #ffffff !important;
            background-image: none !important;
        }
        .pcoded-navbar .main-menu .main-menu-header {
            background: transparent !important;
            background-image: none !important;
            box-shadow: none !important;
        }
        .pcoded-navbar .main-menu .main-menu-header:before,
        .pcoded-navbar .main-menu .main-menu-header:after {
            display: none !important;
        }
        .pcoded-navbar .main-menu .sidebar_toggle {
            background: transparent !important;
            background-image: none !important;
            box-shadow: none !important;
        }
        .pcoded-navbar .main-menu-header img {
            background: transparent !important;
            border: 0 !important;
            box-shadow: none !important;
        }
        .pcoded-navbar .pcoded-inner-navbar {
            background: #ffffff !important;
            background-image: none !important;
        }
        .pcoded-navbar .main-menu-header .user-details *,
        .pcoded-navbar .main-menu-header .user-details a {
            color: #0f172a !important;
        }

        /* ---------------- Gradient Top Bar ---------------- */
        .navbar,
        .pcoded-header {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 60%, #3b82f6 100%) !important;
            color: #ffffff !important;
        }

        /* ---------------- Hide sidebar scrollbar completely ---------------- */
        .pcoded-navbar .pcoded-inner-navbar {
            overflow-y: auto !important;   /* still scrollable */
            scrollbar-width: none;         /* Firefox */
            -ms-overflow-style: none;      /* IE/Edge */
        }
        .pcoded-navbar .pcoded-inner-navbar::-webkit-scrollbar {
            display: none !important;      /* Chrome, Safari */
            width: 0 !important;
            height: 0 !important;
        }
      </style>
    </head>

  <body class="URL_HOST" data-url="<?=$_ENV['BASE_PATH']?>">
