<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Alysei | Dashboard</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link href="{{asset('admin/css/progress-wizard.min.css')}}" rel="stylesheet">
  <link href="//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{asset('admin/plugins/fontawesome-free/css/all.min.css')}}">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet" href="{{asset('admin/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css')}}">
  <!-- iCheck -->
  <link rel="stylesheet" href="{{asset('admin/plugins/icheck-bootstrap/icheck-bootstrap.min.css')}}">
  <!-- JQVMap -->
  <link rel="stylesheet" href="{{asset('admin/plugins/jqvmap/jqvmap.min.css')}}">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{asset('admin/dist/css/adminlte.min.css')}}">
  <link rel="stylesheet" href="{{asset('admin/dist/css/custom.css')}}">
  <link rel="stylesheet" href="{{asset('admin/css/custom.css')}}">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="{{asset('admin/plugins/overlayScrollbars/css/OverlayScrollbars.min.css')}}">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="{{asset('admin/plugins/daterangepicker/daterangepicker.css')}}">
  <!-- summernote -->
  <link rel="stylesheet" href="{{asset('admin/plugins/summernote/summernote-bs4.min.css')}}">

  <!-- Sweet Alert -->
  <link rel="stylesheet" href="{{asset('admin/plugins/sweetalert2/sweetalert2.css')}}">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <script src="{{asset('admin/plugins/jquery/jquery.min.js')}}"></script>
  <script src="{{asset('admin/dist/js/chart.js')}}"></script> 
  <script src="{{asset('admin/admin-js/cropper-custom.js')}}"></script>
  <script src="https://cdn.jsdelivr.net/npm/@jsuites/cropper/cropper.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@jsuites/cropper/cropper.min.css" type="text/css" />
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <style type="text/css">
    .form-group.hidden {
        display: none;
    }
    .form-check.hidden {
        display: none;
    }
  </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- Preloader -->
  <!-- <div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__shake" src="{{asset('admin/dist/img/AdminLTELogo.png')}}" alt="AdminLTELogo" height="60" width="60">
  </div> -->

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="index3.html" class="nav-link">Home</a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="#" class="nav-link">Contact</a>
      </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <!-- Navbar Search -->
      <!-- <li class="nav-item">
        <a class="nav-link" data-widget="navbar-search" href="#" role="button">
          <i class="fas fa-search"></i>
        </a>
        <div class="navbar-search-block">
          <form class="form-inline">
            <div class="input-group input-group-sm">
              <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
              <div class="input-group-append">
                <button class="btn btn-navbar" type="submit">
                  <i class="fas fa-search"></i>
                </button>
                <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>
          </form>
        </div>
      </li> -->

      <!-- Messages Dropdown Menu -->
      <!-- 
      <li class="nav-item">
        <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
          <i class="fas fa-th-large"></i>
        </a>
      </li> -->
    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{url('dashboard')}}" class="brand-link">
      <img src="{{asset('images/logo.png')}}" alt="AdminLTE Logo" style="opacity: .8">
      <span class="brand-text font-weight-light"></span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="{{asset('images/user-unnamed.png')}}" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="{{url('dashboard')}}" class="d-block">Admin</a>
        </div>
      </div>
      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
          <li class="nav-item">
            <a href="{{url('/dashboard')}}" class="nav-link">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>
                Dashboard
              </p>
            </a>
          </li>
          
          
          
          <li class="nav-item {{ (Request::is('dashboard/users') || Request::is('dashboard/user/*') || Request::is('dashboard/users/*') | Request::is('dashboard/registration/*')) ? 'menu-open active' : '' }}">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Users
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item {{ (Request::is('dashboard/users') || Request::is('dashboard/user/*')) ? 'active' : '' }}">
                <a href="{{url('dashboard/users')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Users</p>
                </a>
              </li>
              <li class="nav-item {{ (Request::is('dashboard/users/user-report') || Request::is('dashboard/users/user-report/*')) ? 'active' : '' }}">
                <a href="{{url('dashboard/users/user-report')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>User Report</p>
                </a>
              </li>
              <li class="nav-item {{ (Request::is('dashboard/users/countries') || Request::is('dashboard//users/countries/*')) ? 'active' : '' }}">
                <a href="{{url('dashboard/users/countries')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Countries</p>
                </a>
              </li>
              <!-- <li class="nav-item {{ (Request::is('dashboard/registration/fields') || Request::is('dashboard/registration/field/*')) ? 'active' : '' }}">
                <a href="{{url('dashboard/registration/fields')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Registration fields</p>
                </a>
              </li>
              <li class="nav-item {{ (Request::is('dashboard/registration/field/options') || Request::is('dashboard/registration/field/option/*')) ? 'active' : '' }}">
                <a href="{{url('dashboard/registration/field/options')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Registration field options</p>
                </a>
              </li> -->
              
              <li class="nav-item {{ (Request::is('dashboard/users/hubs') || Request::is('dashboard/user/hub/*')) ? 'active' : '' }}">
                <a href="{{url('dashboard/users/hubs')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Hubs</p>
                </a>
              </li>
              <li class="nav-item {{ (Request::is('dashboard/users/roles') || Request::is('dashboard/user/role/*')) ? 'active' : '' }}">
                <a href="{{url('dashboard/users/roles')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Roles</p>
                </a>
              </li>

              <li class="nav-item {{ (Request::is('dashboard/users/property-types') || Request::is('dashboard/user/property-types/*')) ? 'active' : '' }}">
                <a href="{{url('dashboard/users/property-types')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Product Types</p>
                </a>
              </li>
              
              <li class="nav-item {{ (Request::is('dashboard/users/restaurant-types') || Request::is('dashboard/user/restaurant-types/*')) ? 'active' : '' }}">
                <a href="{{url('dashboard/users/restaurant-types')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Restaurant Types</p>
                </a>
              </li>

              <li class="nav-item {{ (Request::is('dashboard/users/expert-titles') || Request::is('dashboard/user/expert-titles/*')) ? 'active' : '' }}">
                <a href="{{url('dashboard/users/expert-titles')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Voice Of Expert Titles</p>
                </a>
              </li>

              <li class="nav-item {{ (Request::is('dashboard/users/speciality-trips') || Request::is('dashboard/user/speciality-trips/*')) ? 'active' : '' }}">
                <a href="{{url('dashboard/users/speciality-trips')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Speciality Trips</p>
                </a>
              </li>

            </ul>
          </li>

          <li class="nav-item {{ (Request::is('dashboard/discover-alysei') || Request::is('dashboard/discover-alysei/*')) ? 'menu-open active' : '' }}">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Discovery alysei
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item {{ (Request::is('dashboard/discover-alysei/news') || Request::is('dashboard/discover-alysei/news/*')) ? 'active' : '' }}">
                <a href="{{url('dashboard/discover-alysei/news')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Alysei news</p>
                </a>
              </li>
              
              <li class="nav-item {{ (Request::is('dashboard/discover-alysei/discovery-circle') || Request::is('dashboard/discover-alysei/discovery-circle/*')) ? 'active' : '' }}">
                <a href="{{url('dashboard/discover-alysei/discovery-circle')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Discovery circle</p>
                </a>
              </li>
              <li class="nav-item {{ (Request::is('dashboard/discover-alysei/discovery-posts') || Request::is('dashboard/discover-alysei/discovery-posts/*')) ? 'active' : '' }}">
                <a href="{{url('dashboard/discover-alysei/discovery-posts')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Discovery Posts</p>
                </a>
              </li>
            </ul>
          </li>
          
          <li class="nav-item {{ Request::is('dashboard/recipe/*') ? 'menu-open active' : '' }}">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Recipe Manager
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item {{ (Request::is('dashboard/recipe/ingredients') || Request::is('dashboard/recipe/ingredient/*')) ? 'active' : '' }}">
                <a href="{{url('dashboard/recipe/ingredients')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Ingredients</p>
                </a>
              </li>
               <li class="nav-item {{ (Request::is('dashboard/recipe/preference') || Request::is('dashboard/recipe/preference/*')) ? 'active' : '' }}">
                <a href="{{url('dashboard/recipe/preferences')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Preferences</p>
                </a>
              </li>
              <li class="nav-item {{ ( Request::is('dashboard/recipe/meals') || Request::is('dashboard/recipe/meal/*')) ? 'active' : '' }}">
                <a href="{{url('dashboard/recipe/meals')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Meals</p>
                </a>
              </li>
              <li class="nav-item {{ ( Request::is('dashboard/recipe/tools') || Request::is('dashboard/recipe/tool/*') ) ? 'active' : '' }}">
                <a href="{{url('dashboard/recipe/tools')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Tools</p>
                </a>
              </li>
              <li class="nav-item {{ ( Request::is('dashboard/recipe/diets') || Request::is('dashboard/recipe/diet/*') ) ? 'active' : '' }}">
                <a href="{{url('dashboard/recipe/diets')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Diets</p>
                </a>
              </li>
              <li class="nav-item {{ ( Request::is('dashboard/recipe/courses') || Request::is('dashboard/recipe/course/*') ) ? 'active' : '' }}">
                <a href="{{url('dashboard/recipe/courses')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Courses</p>
                </a>
              </li>
              <li class="nav-item {{ ( Request::is('dashboard/recipe/regions') || Request::is('dashboard/recipe/regions/*') ) ? 'active' : '' }}">
                <a href="{{url('dashboard/recipe/regions')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Regions</p>
                </a>
              </li>
              <li class="nav-item {{ ( Request::is('dashboard/recipe/unit-quantity') || Request::is('dashboard/recipe/unit-quantity/*') ) ? 'active' : '' }}">
                <a href="{{url('dashboard/recipe/unit-quantity')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Unit Quantity</p>
                </a>
              </li>
            </ul>
          </li>

          <li class="nav-item {{ Request::is('dashboard/marketplace/*') ? 'menu-open active' : '' }}">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Marketplace
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item {{ ( Request::is('dashboard/marketplace/stores') || Request::is('dashboard/marketplace/store/*') ) ? 'active' : '' }}">
                <a href="{{url('dashboard/marketplace/stores')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Stores ({{ $new_stores_count }})</p>
                </a>
              </li>
              <li class="nav-item {{ ( Request::is('dashboard/marketplace/banners') || Request::is('dashboard/marketplace/banner/*') ) ? 'active' : '' }}">
                <a href="{{url('dashboard/marketplace/banners')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Banners</p>
                </a>
              </li>
              <li class="nav-item {{ ( Request::is('dashboard/marketplace/inco-terms') || Request::is('dashboard/marketplace/inco-terms/*') ) ? 'active' : '' }}">
                <a href="{{url('dashboard/marketplace/inco-terms')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Inco-Terms</p>
                </a>
              </li>
              <li class="nav-item {{ ( Request::is('dashboard/marketplace/products') || Request::is('dashboard/marketplace/product/*') ) ? 'active' : '' }}">
                <a href="{{url('dashboard/marketplace/products')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Products</p>
                </a>
              </li>
              <li class="nav-item {{ ( Request::is('dashboard/marketplace/regions') || Request::is('dashboard/marketplace/region/*') ) ? 'active' : '' }}">
                <a href="{{url('dashboard/marketplace/regions')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Regions</p>
                </a>
              </li>
              <li class="nav-item {{ ( Request::is('dashboard/marketplace/orders') || Request::is('dashboard/marketplace/orders/*') ) ? 'active' : '' }}">
                <a href="{{url('dashboard/marketplace/orders')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Orders</p>
                </a>
              </li>
              <li class="nav-item {{ ( Request::is('dashboard/marketplace/transactions') || Request::is('dashboard/marketplace/transactions/*') ) ? 'active' : '' }}">
                <a href="{{url('dashboard/marketplace/transactions')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Transaction History</p>
                </a>
              </li>
            </ul>
          </li>

          <li class="nav-item {{ Request::is('dashboard/walkthrough/*') ? 'menu-open active' : '' }}">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Walkthrough
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item {{ ( Request::is('dashboard/walkthrough') || Request::is('dashboard/walkthrough/*') ) ? 'active' : '' }}">
                <a href="{{url('dashboard/walkthrough')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Alysei Walkthrough</p>
                </a>
              </li>

              <li class="nav-item {{ ( Request::is('dashboard/market-place/walkthrough') || Request::is('dashboard/market-place/walkthrough/*') ) ? 'active' : '' }}">
                <a href="{{url('dashboard/market-place/walkthrough')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Marketplace Walkthrough</p>
                </a>
              </li>
            </ul>
          </li>

          <li class="nav-item {{ Request::is('dashboard/feed*') ? 'menu-open active' : '' }}">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Activity
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item {{ ( Request::is('dashboard/feed') ) ? 'active' : '' }}">
                <a href="{{url('dashboard/feed')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Activity Feeds</p>
                </a>
              </li>
              <li class="nav-item {{ ( Request::is('dashboard/feed/spams') ) ? 'active' : '' }}">
                <a href="{{url('dashboard/feed/spams')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Activity Feeds Spam ({{ $new_spams_count }})</p>
                </a>
              </li>
            </ul>
          </li>

          <li class="nav-item {{ Request::is('dashboard/hub-infoicon') ? 'menu-open active' : '' }}">
            <a href="{{url('dashboard/hub-infoicon')}}" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Hub Info Icon
              </p>
            </a>
          </li>

          <li class="nav-item {{ Request::is('dashboard/faq') ? 'menu-open active' : '' }}">
            <a href="{{url('dashboard/faq')}}" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Faq
              </p>
            </a>
          </li>

          <li class="nav-item {{ Request::is('dashboard/award-medals') ? 'menu-open active' : '' }}">
            <a href="{{url('dashboard/award-medals')}}" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Award Medals
              </p>
            </a>
          </li>

          <li class="nav-item {{ Request::is('dashboard/localization') ? 'menu-open active' : '' }}">
            <a href="{{url('dashboard/localization')}}" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Site Localization
              </p>
            </a>
          </li>

          <li class="nav-item {{ Request::is('dashboard/version-manager') ? 'menu-open active' : '' }}">
            <a href="{{url('dashboard/version-manager')}}" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                App Versions
              </p>
            </a>
          </li>

         
          <li class="nav-item {{ ( Request::is('dashboard/push-notifications')) || ( Request::is('dashboard/push-notification/create')) ? 'active' : '' }}">
            <a href="{{url('dashboard/push-notifications')}}" class="nav-link">
              <i class="far fa-circle nav-icon"></i>
              <p>Push Notifications</p>
            </a>
          </li>

         

          <li class="nav-item">
          <a href="{{url('logout')}}" onclick="event.preventDefault(); document.getElementById('frm-logout').submit();" class="nav-link">
          <i class="nav-icon fas fa-sign-out-alt"></i>
              <p>
                Logout
              </p>
          </a>    
          <form id="frm-logout" action="{{url('logout')}}" method="get" style="display: none;">
              {{ csrf_field() }}
          </form>
            
          </li>

        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    

    @yield('content')
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  <footer class="main-footer">
    <strong>Copyright &copy; 2014-2021 <a href="https://alysei.com">Alysei.com</a>.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> 3.1.0
    </div>
  </footer>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- jQuery -->

<!-- jQuery UI 1.11.4 -->
<script src="{{asset('admin/plugins/jquery-ui/jquery-ui.min.js')}}"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="{{asset('admin/plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
<!-- ChartJS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
<!-- Sparkline -->
<script src="{{asset('admin/plugins/sparklines/sparkline.js')}}"></script>
<!-- JQVMap -->
<script src="{{asset('admin/plugins/jqvmap/jquery.vmap.min.js')}}"></script>
<script src="{{asset('admin/plugins/jqvmap/maps/jquery.vmap.usa.js')}}"></script>
<!-- jQuery Knob Chart -->
<script src="{{asset('admin/plugins/jquery-knob/jquery.knob.min.js')}}"></script>
<!-- daterangepicker -->
<script src="{{asset('admin/plugins/moment/moment.min.js')}}"></script>
<script src="{{asset('admin/plugins/daterangepicker/daterangepicker.js')}}"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="{{asset('admin/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js')}}"></script>
<!-- Summernote -->
<script src="{{asset('admin/plugins/summernote/summernote-bs4.min.js')}}"></script>
<!-- overlayScrollbars -->
<script src="{{asset('admin/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js')}}"></script>
<!-- AdminLTE App -->
<script src="{{asset('admin/dist/js/adminlte.js')}}"></script>
<!-- Sweet alert -->
<script src="{{asset('admin/plugins/sweetalert2/sweetalert2.js')}}"></script>
<!-- AdminLTE for demo purposes -->
<script src="{{asset('admin/dist/js/demo.js')}}"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<script src="{{asset('admin/dist/js/pages/dashboard.js')}}"></script>
<script type="text/javascript">
    $(document).ready(function() {
      $('.summernote').summernote({
        height: 400
      });
    });
</script>
<script>
  $(function(){
      $('#hubs_select').select2();


      // $('#hubs_select').on("select2:select", function (e) { 
      //       var data = e.params.data.text;
      //       if(data=='all'){
      //       $("#hubs_select > option").prop("selected","selected");
      //       $("#hubs_select").trigger("change");
      //       }
      // });

      $('#selectAll').click( function() {
        if($(this).is(':checked')){
          $("#hubs_select > option").prop("selected","selected");
          $("#hubs_select").trigger("change");
        }
        else{
          $('#hubs_select').val(null).trigger('change');
        }
      });
      $("#parent").click(function() {
        if($(this).is(':checked')){
          $(".ingredient_list").css("display","none");
        }else{
          $(".ingredient_list").css("display","block");
        }
      });

      $(".alert-danger").fadeOut(5000);
      $(".alert-success").fadeOut(5000);

       $(document).on("change",'#image', function(){
            var fileName = document.getElementById('image').files[0].name;
            $('.custom-file-label').html(fileName);
            //console.log(fileName);
        });

        $(document).on("change",'.submitForm', function(){
            $('#searchFilter').submit();
            //console.log('sadfsafa');
        });

        $(document).on("change",'.mapRadius', function(){

          var address = $('#autocomplete').val();
          var lat = $('#latitude').val();
          var lang = $('#longitude').val();
          var miles = $(this).val();
          drawCircle(address, lat, lang, miles);
        });

        // // Summernote
        // $('.edit_summernote').summernote()
        // CodeMirror.fromTextArea(document.getElementById("codeMirrorDemo"), {
        //   mode: "htmlmixed",
        //   theme: "monokai",
        //   minHeight: 500,
        // });
        
        $(".remove_hub").click(function(){
        if (confirm("Are you sure you want to remove this hub?") == true) {

            var userId = $(this).data("user_id");
            var hubId = $(this).data("hub_id");

            $.ajax({
                url: "{{url('dashboard/users/remove/hub')}}",
                type: 'post',
                data: {
                    'user_id': userId,
                    'hub_id': hubId,
                    '_token': '{{ csrf_token() }}'
                },
                success: function(path) {
                    //location.reload();
                    $("#"+hubId).remove();
                }
            });
        } else {
            return false;
        }
      });
  });

  $(document).on('change','.store_status', function(){
    var $this = $(this);
    let status = $($this).val();
    let id = $($this).attr('data-id');
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to changed status",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, change it!'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajaxSetup({
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              }
          });
          $.ajax({
              type: 'POST', //THIS NEEDS TO BE GET
              url: 'stores/store-status',
              data: {status : status, id:id},
              dataType: 'json',

              success: function (response) {
                if(response.success){
                  Swal.fire({
                    position: 'top-center',
                    icon: 'success',
                    title: response.message,
                    showConfirmButton: false,
                    timer: 2000
                  })
                }
                else{
                  $($this).val('0').change();
                  Swal.fire({
                    position: 'top-center',
                    icon: 'error',
                    title: response.message,
                    showConfirmButton: true
                  })
                }
              },
              error: function() { 
                  console.log('cfgdfgsdf');
              }
          });
        }
      })
  });

  $(document).on('click','.adminTransferOrderAmount', function(){
    var $this = $(this);
    let status = $($this).val();
    let transaction_id = $($this).attr('data-id');
    let adminPayAmount = $($this).attr('admin-amount');
    let orderAmount = $($this).attr('order-amount');
    Swal.fire({
        title: 'Are you sure?',
        text: "The order amount is $"+orderAmount+". But you have to pay $"+adminPayAmount+" amount into the producer account. The payment status will change to paid. Thanks",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes!'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajaxSetup({
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              }
          });
          //alert("sdfsdafdsfdsafd");
          $.ajax({
              type: 'POST', //THIS NEEDS TO BE GET
              url: 'transaction/payment-status',
              data: {transaction_id:transaction_id},
              dataType: 'json',

              success: function (response) {
                if(response.success){
                  Swal.fire({
                    position: 'top-center',
                    icon: 'success',
                    title: response.message,
                    showConfirmButton: false,
                    timer: 2000
                  });

                  location.reload();
                }
                else{
                  $($this).val('0').change();
                  Swal.fire({
                    position: 'top-center',
                    icon: 'error',
                    title: response.message,
                    showConfirmButton: true
                  })
                }
              },
              error: function() { 
                  console.log('cfgdfgsdf');
              }
          });
        }
      })
  });

  $(document).on('change','.productTypeOption', function(){
      let fieldId = $(this).attr('data-id');
      //console.log(fieldId,"fieldId");
      if($("#"+fieldId).hasClass("is_hide")){
        $('#'+fieldId).removeClass('is_hide');
      }
      else{
        $('#'+fieldId).addClass('is_hide');
        $(".method_"+fieldId).prop('checked', false); 
      }
  })

  $(document).on('click','#submitSave', function(){
    var selectedId = [];
    var user_id = $('.userID').val();
    $(".consProperty:checkbox[name=productType]:checked").each(function(){
      selectedId.push($(this).val());
    });
    //console.log(selectedId,"selectedId");
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to change product type",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, change it!'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajaxSetup({
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              }
          });
          $.ajax({
              type: 'POST', //THIS NEEDS TO BE GET
              url: '/dashboard/admin/update/user/product-type',
              data: {selectedId : selectedId, id:user_id},
              dataType: 'json',

              success: function (response) {
                //console.log(response,"response");
                if(response.success){
                  Swal.fire({
                    position: 'top-center',
                    icon: 'success',
                    title: response.message,
                    showConfirmButton: false,
                    timer: 2000
                  })
                }
                else{
                  Swal.fire({
                    position: 'top-center',
                    icon: 'success',
                    title: response.message,
                    showConfirmButton: true
                  })
                }
              },
              error: function() { 
                  console.log('cfgdfgsdf');
              }
          });
        }
      })
  })

  $(document).on('change','.product_status', function(){
    let status = $(this).val();
    let id = $(this).attr('data-id');
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to changed status",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, change it!'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajaxSetup({
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              }
          });
          $.ajax({
              type: 'POST', //THIS NEEDS TO BE GET
              url: 'product/product-status',
              data: {status : status, id:id},
              dataType: 'json',

              success: function (response) {
                if(response.success){
                  Swal.fire({
                    position: 'top-center',
                    icon: 'success',
                    title: response.message,
                    showConfirmButton: false,
                    timer: 2000
                  })
                }
                else{
                  Swal.fire({
                    position: 'top-center',
                    icon: 'success',
                    title: response.message,
                    showConfirmButton: true
                  })
                }
              },
              error: function() { 
                  console.log('cfgdfgsdf');
              }
          });
        }
      })
  });

  $(document).ready(function() {
  $('#multiselect').multiselect({
    buttonWidth : '160px',
    includeSelectAllOption : true,
		nonSelectedText: 'Select an Role'
  });

  $('#multiselects').multiselect({
    enableFiltering: true,
    includeSelectAllOption : true,
		nonSelectedText: 'Select an User'
  });

});

$(document).on('change','#notificationType', function(){
  var type = $(this).val();
  if(type == 'role'){
    $('.users').hide();
    $('.roles').show();
  }
  else{
    $('.roles').hide();
    $('.users').show();
  }
});

function getSelectedValues() {
  var selectedVal = $("#multiselect").val();
	for(var i=0; i<selectedVal.length; i++){
		function innerFunc(i) {
			setTimeout(function() {
				location.href = selectedVal[i];
			}, i*2000);
		}
		innerFunc(i);
	}

  var selectedUserVal = $("#multiselects").val();
  for(var i=0; i<selectedUserVal.length; i++){
		function innerFunc(i) {
			setTimeout(function() {
				location.href = selectedUserVal[i];
			}, i*2000);
		}
		innerFunc(i);
	}


}
</script>



</body>
</html>
