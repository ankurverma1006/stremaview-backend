
@extends('layouts.admin')

@section('title', tr('add_user'))

@section('content-header', tr('add_user'))

@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
    <li><a href="{{route('admin.users.index')}}"><i class="fa fa-user"></i> {{tr('users')}}</a></li>
    <li class="active"><i class="fa fa-user-plus"></i> {{tr('add_user')}}</li>
@endsection

@section('content')

    <div class="row">

        <div class="col-md-10">
            
            <div class="box box-primary">

                <div class="box-header label-primary">
                    <b style="font-size:18px;">{{tr('add_user')}}</b>
                    <a href="{{route('admin.users.index')}}" class="btn btn-default pull-right">{{tr('view_users')}}</a>
                </div>
                
                @include('admin.users._form')

            </div>

        </div>

    </div>

@endsection

@section('scripts')

<script src="{{asset('assets/js/jstz.min.js')}}"></script>
<script>
    
    $(document).ready(function() {

        var dMin = new Date().getTimezoneOffset();
        var dtz = -(dMin/60);
        // alert(dtz);
        $("#userTimezone").val(jstz.determine().name());
    });

</script>

@endsection