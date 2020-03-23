@extends('layouts.admin.focused')

@section('title', tr('login'))

<style type="text/css">
body {
    background: #000 !important; 

}
</style>

@section('content')

    <div class="login-box-body" style="height:275px;">


        <form class="form-layout" role="form" method="POST" action="{{ url('/admin/login') }}">
            {{ csrf_field() }}

            <div class="login-logo">
               <!-- <a href="{{route('admin.login')}}"><b>{{Setting::get('site_name')}}</b></a> -->
               <input type="hidden" name="timezone" value="" id="userTimezone">
            </div>

            <p class="text-center mb30"></p>

            <div class="form-inputs">
                <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">

                    <input type="email" class="form-control input-lg" value="{{Setting::get('demo_admin_email')}}" name="email"  required placeholder="{{tr('email')}}">

                    @if ($errors->has('email'))
                        <span class="help-block">
                            <strong>{{ $errors->first('email') }}</strong>
                        </span>
                    @endif

                </div>

                <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">

                    <input type="password" class="form-control input-lg" value="{{Setting::get('demo_admin_password')}}" required name="password" placeholder="{{tr('password')}}" >

                    @if ($errors->has('password'))
                        <span class="help-block">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                    @endif

                </div>


            </div>

            <div class="col-md-6 col-md-offset-3">
                <button class="btn btn-success btn-block mb15" type="submit">
                    <h5><span><i class="fa fa-btn fa-sign-in"></i> {{tr('login')}}</span></h5>
                </button>
            </div>

            <div class="clearfix"></div>

        </form>

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

