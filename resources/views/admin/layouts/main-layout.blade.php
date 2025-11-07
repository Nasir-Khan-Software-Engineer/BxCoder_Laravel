<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <!-- <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1"> -->

    <link rel="icon" type="image/png" href="{{ asset('images/favicon/favicon-96x96.png') }}" sizes="96x96">

    <link rel="shortcut icon" href="{{ asset('images/favicon/favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/favicon/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('images/favicon/site.webmanifest') }}">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>BxCoder - Admin Panel.</title>

    <!-- Custom styles for this template-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.css" integrity="sha512-57lHfvbE5GLWA20qDTCTRUpKntBnFlG0V+x8f3q3DqZK6XlHGVsbpCCI/kadtaFBMEYmq5IeVZnsWjWx2QoQtw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous"
        referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.3/css/dataTables.bootstrap5.min.css" />

    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400&display=swap" rel="stylesheet">
    <style>
    body {
        font-family: 'Rubik', sans-serif !important;
    }
    </style>

    <style>
    .sidebar .nav-item .collapse .collapse-inner .collapse-item.disabled,
    .sidebar .nav-item .collapsing .collapse-inner .collapse-item.disabled {
        color: #a9adb1ff;
        /* Bootstrap's $gray-600 */
        cursor: not-allowed;
        text-decoration: none;
    }
    </style>

    @vite(['resources/css/default.css'])
    @vite(['resources/css/custom-theme/theme-purple.css'])
    @vite(['resources/css/custom-style.css'])


    @yield('style')

    <!-- Scripts -->
    <script>
    var BxCoder = {};
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="">
                <div class="sidebar-brand-icon rotate-n-15">
                    <img width="50px" src="{{ asset('images/logo.png') }}" alt="Logo">
                </div>
                <div class="sidebar-brand-text mx-3">BxCoder</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item">
                <a class="nav-link" href="">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Pages Collapse Menu -->
            <li class="nav-item">
                <a class="nav-link" href="#" data-toggle="collapse" data-target="#projectDropdown" aria-expanded="true" aria-controls="projectDropdown">
                    <i class="fa-solid fa-spa"></i>
                    <span>Projects</span>
                </a>
                <div id="projectDropdown" class="collapse" aria-labelledby="headingProjectDropdown" data-parent="#accordionSidebar">

                    <div class="bg-white py-2 collapse-inner rounded">
                        <a class="collapse-item" href="{{ route('admin.projects.index') }}">
                            <i class="fa-solid fa-magic"></i> Project
                        </a>
                    </div>
                </div>
            </li>

            <hr class="sidebar-divider my-0">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline mt-2">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

            <div class="sidebar-footer">
                © 2025 BxCoder.com
            </div>

        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-2 static-top toolbar-border">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <ul class="navbar-nav ml-auto">

                        <li class="nav-item">
                            <a href="javascript:void(0);" class="nav-link pos-terminal text-gray-400 cursor-not-allowed no-access-link" data-message="You do not have permission to access the POS Terminal." title="POS Terminal">
                                <i class="fa-solid fa-desktop"></i>
                            </a>
                        </li>

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 user-name">Test</span>
                                <img class="img-profile rounded-circle" src="{{asset('images/default-profile.png')}}">
                            </a>

                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profile
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    {{ __('Logout') }}
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    </ul>
                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    @yield('content')

                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->

            <!-- End of Footer -->
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.3/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.3/js/dataTables.bootstrap5.min.js"></script>

    @vite([
    'resources/js/app.js',
    'resources/js/common.js',
    'resources/js/datatable_service.js',
    'resources/js/validator-script.js'
    ])


    <script>
    $(document).ready(function() {

        $("#sidebarToggle").click(function() {
            $(".sidebar").toggleClass("toggled");
            $(".collapse").removeClass("show");
        });

        $('[data-toggle="tooltip"]').tooltip({
            html: true
        });

        setTimeout(function() {
            $(".alert").alert('close');
        }, 3000); // close after 5 seconds
    });
  
    </script>

    @yield('script')
</body>

</html>