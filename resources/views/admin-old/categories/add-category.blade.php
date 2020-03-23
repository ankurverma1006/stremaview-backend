@extends('layouts.admin')

@section('title', tr('add_category'))

@section('content-header', tr('add_category'))

@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
    <li><a href="{{route('admin.categories')}}"><i class="fa fa-suitcase"></i> {{tr('categories')}}</a></li>
    <li class="active">{{tr('add_category')}}</li>
@endsection

@section('styles')

    <link rel="stylesheet" href="{{asset('admin-css/plugins/iCheck/all.css')}}">

@endsection

@section('content')

@include('notification.notify')

    <div class="row">

        <div class="col-md-10">

            <div class="box box-primary">

                <div class="box-header label-primary">
                    <b style="font-size:18px;">{{tr('add_category')}}</b>
                    <a href="{{route('admin.categories')}}" class="btn btn-default pull-right">{{tr('categories')}}</a>
                </div>

                <form class="form-horizontal" action="{{route('admin.save.category')}}" method="POST" enctype="multipart/form-data" role="form">

                    <div class="box-body">

                        <div class="form-group">
                            <label for="name" class="col-sm-1 control-label">*{{tr('name')}}</label>
                            <div class="col-sm-10">
                                <input type="text" required class="form-control" 
                                  pattern = "[a-zA-Z0-9\s\-\.]{2,100}" title="{{tr('only_alphanumeric')}}" id="name" name="name" placeholder="{{tr('category_name')}}">
                            </div>
                        </div>


                        <div class="form-group">
                            <label for="picture" class="col-sm-1 control-label">*{{tr('picture')}}</label>
                            <div class="col-sm-10">

                                <img id="image_preview" style="width: 100px;height: 100px;display: none;">


                                <input type="file" required accept="image/jpeg,image/png" id="picture" name="picture" placeholder="{{tr('picture')}}" onchange="loadFile(this,'image_preview')">
                                <p class="help-block">{{tr('image_validate')}} {{tr('image_square')}}</p>
                            </div>
                        </div>

                        <div class="checkbox">
                            <label for="picture" class="col-sm-1 control-label"></label>
                            <label>
                                <input type="checkbox" name="is_series" value="1" class="minimal-red"> {{tr('is_series')}}
                            </label>
                        </div>

                    </div>

                    <div class="box-footer">
                        <a href="" class="btn btn-danger">{{tr('cancel')}}</a>
                        @if(Setting::get('admin_delete_control'))
                            <a href="#" class="btn btn-success pull-right" disabled>{{tr('submit')}}</a>
                        @else
                            <button type="submit" class="btn btn-success pull-right">{{tr('submit')}}</button>
                        @endif
                    </div>
                </form>
            
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