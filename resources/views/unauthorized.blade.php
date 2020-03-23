@extends('layouts.admin')

@section('title', tr('profile'))

@section('content-header', tr('profile'))

@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
    <li class="active"><i class="fa fa-diamond"></i> {{tr('account')}}</li>
@endsection

@section('content')

@include('notification.notify')

<!-- Main content -->
<section class="content">

    <div class="text-center">

        <h1 class="headline text-yellow"> 401</h1>

        <!-- <div class="error-content"> -->

            <h3><i class="fa fa-warning text-yellow"></i> Unauthorized Error.</h3>

            <p>
                The Requested resource access denied.
            </p>

            <a href="{{route('admin.dashboard')}}" class="btn btn-success btn-lg text-uppercase">Go {{tr('home')}}</a>

            
        <!-- </div> -->
        <!-- /.error-content -->
    </div>
    <!-- /.error-page -->
</section>
<!-- /.content -->

@endsection