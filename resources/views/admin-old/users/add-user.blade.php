@extends('layouts.admin')

@section('title', tr('add_user'))

@section('content-header', tr('add_user'))

@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
    <li><a href="{{route('admin.users')}}"><i class="fa fa-user"></i> {{tr('users')}}</a></li>
    <li class="active"><i class="fa fa-user-plus"></i> {{tr('add_user')}}</li>
@endsection

@section('content')

@include('notification.notify')

    <div class="row">

        <div class="col-md-10">

            <div class="box box-primary">

                <div class="box-header label-primary">
                    <b style="font-size:18px;">{{tr('add_user')}}</b>
                    <a href="{{route('admin.users')}}" class="btn btn-default pull-right">{{tr('view_users')}}</a>
                </div>

                <form class="form-horizontal" action="{{route('admin.save.user')}}" method="POST" enctype="multipart/form-data" role="form">

                    <div class="box-body">

                        <div class="form-group">
                            <label for="username" class="col-sm-2 control-label">* {{tr('username')}}</label>

                            <div class="col-sm-10">
                                <input type="text" required name="name" 
                                pattern = "[a-zA-Z,0-9\s\-\.]{2,100}" title="{{tr('only_alphanumeric')}}" class="form-control" id="username" placeholder="{{tr('name')}}" value="{{old('name')}}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email" class="col-sm-2 control-label">* {{tr('email')}}</label>
                            <div class="col-sm-10">
                                <input type="email" maxlength="255" required class="form-control" id="email" name="email" pattern="[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,10}$" placeholder="{{tr('email')}}" value="{{old('email')}}">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="mobile" class="col-sm-2 control-label">* {{tr('mobile')}}</label>

                            <div class="col-sm-10">
                                <input type="text" required name="mobile" class="form-control" id="mobile" placeholder="{{tr('mobile')}}" minlength="4" maxlength="16" pattern="[0-9]{4,16}" value="{{old('mobile')}}">
                                <br>
                                 <small style="color:brown">{{tr('mobile_note')}}</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password" class="col-sm-2 control-label">* {{tr('password')}}</label>

                            <div class="col-sm-10">
                                <input type="password" required  name="password" pattern=".{6,}" title="{{tr('password_notes')}}" class="form-control" id="password" placeholder="{{tr('password')}}" value="{{old('password')}}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="username" class="col-sm-2  control-label">* {{tr('password_confirmation')}}</label>

                            <div class="col-sm-10">
                                <input type="password" required pattern=".{6,}"  title="{{tr('password_notes')}}"  name="password_confirmation" class="form-control" id="username" placeholder="{{tr('password_confirmation')}}"  value="{{old('password_confirmation')}}">
                            </div>
                        </div>

                    </div>

                    <div class="box-footer">
                        <button type="reset" class="btn btn-danger">{{tr('cancel')}}</button>
                        @if(Setting::get('admin_delete_control'))
                            <a href="#" class="btn btn-success pull-right" disabled>{{tr('submit')}}</a>
                        @else
                            <button type="submit" class="btn btn-success pull-right">{{tr('submit')}}</button>
                        @endif
                    </div>
                    <input type="hidden" name="timezone" value="" id="userTimezone">
                </form>
            
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