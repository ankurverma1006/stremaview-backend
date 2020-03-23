@extends('layouts.admin')

@section('title', tr('add_moderator'))

@section('content-header', tr('add_moderator'))

@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
    <li><a href="{{route('admin.moderators.index')}}"><i class="fa fa-users"></i> {{tr('moderators')}}</a></li>
    <li class="active">{{tr('add_moderator')}}</li>
@endsection

@section('content')

    <div class="row">

        <div class="col-md-10">

            <div class="box box-primary">

                <div class="box-header label-primary">
                    <b style="font-size:18px;">{{tr('add_moderator')}}</b>
                    <a href="{{route('admin.moderators.index')}}" class="btn btn-default pull-right">{{tr('moderators')}}</a>
                </div>

                @include('admin.moderators._form')

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