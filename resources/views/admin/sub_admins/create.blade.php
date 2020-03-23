@extends('layouts.admin')

@section('title', tr('sub_admin_create'))

@section('content-header', tr('sub_admin_create'))

@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
    <li><a href="{{route('admin.sub_admins.index')}}"><i class="fa fa-support"></i> {{tr('sub_admins')}}</a></li>
    <li class="active"><i class="fa fa-support"></i> {{tr('sub_admin_create')}}</li>
@endsection

@section('content')

@include('admin.sub_admins._form')

@endsection

@section('scripts')

<script src="{{asset('assets/js/jstz.min.js')}}"></script>

<script>
    
    $(document).ready(function() {

        var dMin = new Date().getTimezoneOffset();
        
        var dtz = -(dMin/60);
        
        $("#userTimezone").val(jstz.determine().name());

    });

</script>

<script src="{{asset('admin-css/plugins/iCheck/icheck.min.js')}}"></script>

@endsection