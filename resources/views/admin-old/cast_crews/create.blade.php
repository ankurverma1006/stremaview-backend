@extends('layouts.admin')

@section('title', tr('add_cast_crew'))

@section('content-header', tr('add_cast_crew'))

@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
    <li><a href="{{route('admin.cast_crews.index')}}"><i class="fa fa-users"></i> {{tr('cast_crews')}}</a></li>
    <li class="active">{{tr('add_cast_crew')}}</li>
@endsection


@section('content')

@include('notification.notify')

@include('admin.cast_crews._form')

@endsection

@section('scripts')
    <script type="text/javascript">

        function loadFile(event,id){

            $('#'+id).show();

            var reader = new FileReader();

            reader.onload = function(){

                var output = document.getElementById(id);

                output.src = reader.result;
            };

            reader.readAsDataURL(event.files[0]);
        }

    </script>

    <script src="https://cdn.ckeditor.com/4.5.5/standard/ckeditor.js"></script>
    <script>
        CKEDITOR.replace( 'ckeditor' );
    </script>
@endsection
