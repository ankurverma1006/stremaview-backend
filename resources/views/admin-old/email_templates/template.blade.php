@extends('layouts.admin')

@section('title', tr('edit_template'))

@section('content-header', tr('edit_template'))

@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
    <li><a href="{{route('admin.templates.index')}}"><i class="fa fa-book"></i>{{tr('templates')}}</a></li>
    <li class="active"><i class="fa fa-book"></i> {{tr('edit_template')}}</li>
@endsection

@section('content')

    @include('notification.notify')

  	<div class="row">

	    <div class="col-md-10">

	        <div class="box box-primary">

	            <div class="box-header label-primary">
                    <b style="font-size:18px;">{{tr('edit_template')}}</b>
                    <a href="{{route('admin.templates')}}" class="btn btn-default pull-right">{{tr('templates')}}</a>
                </div>

	            <form  action="{{route('admin.save.template')}}" method="POST" enctype="multipart/form-data" role="form">

	                <div class="box-body">

	                	<input type="hidden" name="id" value="{{$template->id}}">

	                     <div class="form-group floating-label">
	                     	<label for="select2">{{tr('template_type')}}</label>
                            <input type="hidden" required class="form-control" name="template_type" id="template_type" placeholder="{{tr('enter')}} {{tr('template_type')}}"  value="{{$template->template_type}}">

                             <input type="text" required class="form-control" id="template_type" readonly value="{{getTemplateName($template->template_type)}}">

                        </div>

	                    <div class="form-group">
	                        <label for="heading">{{tr('heading')}}</label>
	                        <input type="text" required class="form-control" name="subject" id="heading" placeholder="{{tr('enter')}} {{tr('heading')}}" value="{{$template->subject}}">
	                    </div>

	                    <div class="form-group">
	                        <label for="description">{{tr('description')}}</label>

	                        <textarea id="ckeditor" required name="description" class="form-control" placeholder="{{tr('enter')}} {{tr('description')}}">{{$template->description}}</textarea>
	                        
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
    <script src="https://cdn.ckeditor.com/4.5.5/standard/ckeditor.js"></script>
    <script>
        CKEDITOR.replace( 'ckeditor' );
    </script>
@endsection

