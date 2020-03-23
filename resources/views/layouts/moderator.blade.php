<!Doctype html>
<html class="no-js" lang="">
<head>
    <meta charset="utf-8">
    <title>@yield('title')</title>
    
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

   <meta name="description" content="{{Setting::get('meta_description')}}">

    <meta name="keywords" content="{{Setting::get('meta_keywords')}}">
    
    <meta name="author" content="{{Setting::get('meta_author')}}">

    <link rel="shortcut icon" href=" @if(Setting::get('site_icon')) {{ Setting::get('site_icon') }} @else {{asset('favicon.png') }} @endif">

    <link rel="stylesheet" href="{{ asset('admin-css/bootstrap/css/bootstrap.min.css')}}">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">

    <!-- Ionicons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">

    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('admin-css/plugins/datatables/dataTables.bootstrap.css')}}">

    @yield('mid-styles')

    <link rel="stylesheet" href="{{ asset('admin-css/plugins/select2/select2.min.css')}}">

      <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('admin-css/dist/css/AdminLTE.min.css') }}">

    <link rel="stylesheet" href="{{ asset('admin-css/dist/css/skins/_all-skins.min.css')}}">

    <link rel="stylesheet" href="{{ asset('admin-css/dist/css/custom.css')}}">

    @yield('styles')

    <style>
    
        /*.skin-blue .main-header .navbar {
            background: linear-gradient(to bottom right, rgb(86, 202, 193), #0e5c73);
        }
        .skin-blue .main-header .logo {
            background: linear-gradient(to bottom right, rgb(86, 202, 193), #0e5c73);
        }
        .skin-blue .main-sidebar{
            background: linear-gradient(to bottom right, rgb(42, 49, 53), #39a1bf);
        }*/

        .popover {
            max-width: 500px;
        }

        .popover-content {
            padding: 10px 5px;

        }

        .popover-list li{

            margin-bottom: 5px;

        }

        #help-popover {
            -webkit-animation-name: example; /* Safari 4.0 - 8.0 */
            -webkit-animation-duration: 4s; /* Safari 4.0 - 8.0 */
            animation-name: example;
            animation-duration: 7s;
            border: transparent;
        }

        /* Standard syntax */
        @keyframes example {
            from {background-color: #db2a1a;}
            to {background-color: #12c3e2;}
        }
        .popover-h5 {
            line-height:1.5;
        }

        .column {

            position: fixed;top:0;left: 0;bottom: 0;right: 0;display: flex; justify-content: center; align-items: center;background: #fff !important;opacity: 0.6;
        }

        .loader-form {
            position: relative;width: 100%;height: 100%;z-index: 9999;;
        }
    </style>


    <?php echo Setting::get('header_scripts'); ?>

</head>


<body class="hold-transition skin-red fixed sidebar-mini">

    <div class="loader-form" style="display: none;">
        <div class="column">
            <div class="loader-container animation-5">
              <div class="shape shape1"></div>
              <div class="shape shape2"></div>
              <div class="shape shape3"></div>
              <div class="shape shape4"></div>
            </div>
        </div>

    </div>

    <div class="wrapper">

        @include('layouts.moderator.header')

        @include('layouts.moderator.nav')

        <div class="content-wrapper">

            <section class="content-header">
                <h1>@yield('content-header')<small>@yield('content-sub-header')</small></h1>
                <ol class="breadcrumb">

                    <li><a href="{{route('moderator.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>

                    @yield('breadcrumb')
                </ol>
            </section>

            <!-- Main content -->
            <section class="content">
                @yield('content')
            </section>

        </div>

        <!-- include('layouts.admin.footer') -->

        <!-- include('layouts.admin.left-side-bar') -->

    </div>


    <!-- jQuery 2.2.0 -->
    <script src="{{asset('admin-css/plugins/jQuery/jQuery-2.2.0.min.js')}}"></script>
    <!-- Bootstrap 3.3.6 -->
    <script src="{{asset('admin-css/bootstrap/js/bootstrap.min.js')}}"></script>

    <script src="{{asset('admin-css/plugins/datatables/jquery.dataTables.min.js')}}"></script>

    <script src="{{asset('admin-css/plugins/datatables/dataTables.bootstrap.min.js')}}"></script>

    <!-- Select2 -->
    <script src="{{asset('admin-css/plugins/select2/select2.full.min.js')}}"></script>
    <!-- InputMask -->
    <script src="{{asset('admin-css/plugins/input-mask/jquery.inputmask.js')}}"></script>
    <script src="{{asset('admin-css/plugins/input-mask/jquery.inputmask.date.extensions.js')}}"></script>
    <script src="{{asset('admin-css/plugins/input-mask/jquery.inputmask.extensions.js')}}"></script>

    <!-- SlimScroll -->
    <script src="{{asset('admin-css/plugins/slimScroll/jquery.slimscroll.min.js')}}"></script>
    <!-- FastClick -->
    <script src="{{asset('admin-css/plugins/fastclick/fastclick.js')}}"></script>
    <!-- AdminLTE App -->
    <script src="{{asset('admin-css/dist/js/app.min.js')}}"></script>
      
    <script src="{{asset('admin-css/dist/js/demo.js')}}"></script>

    <!-- page script -->
    <script>
        $(function () {
            $("#example1").DataTable();
            $('#example2').DataTable({
                "paging": true,
                "lengthChange": false,
                "searching": false,
                "ordering": true,
                "info": true,
                "autoWidth": false
            });
        });
    </script>

    @yield('scripts')

    <script type="text/javascript">

        $("#{{$page}}").addClass("active");

        @if($sub_page) $("#{{$sub_page}}").addClass("active"); @endif

    </script>

    <script type="text/javascript">

        /****************************************************
         *
         * used to display help section with use of popover in bootstrap
         *
         *
         ****************************************************/

        $(document).ready(function(){
            $('#help-popover').popover({
                html : true, 
                content: function() {
                    return $('#help-content').html();
                } 
            });  
        });
        
        $(function () {

            //Initialize Select2 Elements
            $(".select2").select2();

            //Datemask dd/mm/yyyy
            $("#datemask").inputmask("dd:mm:yyyy", {"placeholder": "hh:mm:ss"});
            //Datemask2 mm/dd/yyyy
            // $("#datemask2").inputmask("hh:mm:ss", {"placeholder": "hh:mm:ss"});
            //Money Euro
            $("[data-mask]").inputmask();

              //iCheck for checkbox and radio inputs
            $('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
              checkboxClass: 'icheckbox_minimal-blue',
              radioClass: 'iradio_minimal-blue'
            });
            //Red color scheme for iCheck
            $('input[type="checkbox"].minimal-red, input[type="radio"].minimal-red').iCheck({
              checkboxClass: 'icheckbox_minimal-red',
              radioClass: 'iradio_minimal-red'
            });
            //Flat red color scheme for iCheck
            $('input[type="checkbox"].flat-red, input[type="radio"].flat-red').iCheck({
              checkboxClass: 'icheckbox_flat-green',
              radioClass: 'iradio_flat-green'
            });
        });
    </script>

    <?php echo Setting::get('body_scripts'); ?>


</body>

</html>
