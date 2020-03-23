@extends('layouts.admin')

@section('title', tr('add_category'))

@section('content-header', tr('add_category'))

@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
    <li><a href="{{route('admin.categories.index')}}"><i class="fa fa-suitcase"></i> {{tr('categories')}}</a></li>
    <li class="active">{{tr('add_category')}}</li>
@endsection

@section('styles')

    <link rel="stylesheet" href="{{asset('admin-css/plugins/iCheck/all.css')}}">

@endsection

@section('content')

    <div class="row">

        <div class="col-md-10">

            <div class="box box-primary">

                <div class="box-header label-primary">
                    <b style="font-size:18px;">{{tr('add_category')}}</b>
                    <a href="{{route('admin.categories.index')}}" class="btn btn-default pull-right">{{tr('categories')}}</a>
                </div>

                @include('admin.categories._form')

            </div>

        </div>

    </div>

@endsection

@section('scripts')

   <script src="{{asset('admin-css/plugins/iCheck/icheck.min.js')}}"></script>

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

@endsection