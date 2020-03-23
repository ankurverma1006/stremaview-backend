@extends('layouts.admin')

@section('title', tr('view_category'))

@section('content-header', tr('view_category'))

@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
    <li><a href="{{route('admin.categories.index')}}"><i class="fa fa-suitcase"></i> {{tr('categories')}}</a></li>
    <li class="active"><i class="fa fa-eye"></i>&nbsp;{{tr('view_categories')}}</li>
@endsection

@section('content')

	<style type="text/css">
		.timeline::before {
		    content: '';
		    position: absolute;
		    top: 0;
		    bottom: 0;
		    width: 0;
		    background: #fff;
		    left: 0px;
		    margin: 0;
		    border-radius: 0px;
		}
	</style>

	
	<div class="row">

		<div class="col-md-10 col-md-offset-1">
			
			<div class="box">

				<div class="box-header label-primary ">
					<h3 class="box-title pull-left" style="color: white"><b>{{tr('category')}}</b></h3> 
					<a href="{{route('admin.categories.index')}}" class="btn btn-default pull-right">{{tr('categories')}}</a>
            	</div>

				<div class="box-body">

					<div class="col-md-6">

						<strong><i class="fa fa-book margin-r-5"></i> {{tr('name')}}</strong>
						<span class="pull-right">{{$category_details->name}}</span>
						<hr>

						<strong><i class="fa fa-book margin-r-5"></i> {{tr('status')}}</strong>
						
			      			@if($category_details->is_approved == CATEGORY_APPROVED )
				      			<a href="#" class="btn  btn-xs btn-success pull-right">{{tr('approved')}}</a>
				      		@else
			      				<a href="#" class="btn  btn-xs btn-danger pull-right">{{tr('declined')}}</a>
				      		@endif
						<hr>

				      	<strong><i class="fa fa-book margin-r-5"></i> {{tr('is_series')}}</strong>	
			      			@if($category_details->is_series)
			      				<a href="#" class="btn btn-xs btn-success pull-right" >{{tr('yes')}}</a>
				      		@else
				      			<a href="#" class="btn btn-xs btn-danger pull-right"> {{tr('no')}}</a>
				      		@endif
						<hr>

						<strong><i class="fa fa-calendar margin-r-5"></i> {{tr('sub_categories')}}</strong>
						<a href="{{route('admin.sub_categories.index' , ['category_id' => $category_details->id] )}}" class="pull-right">
		                {{count($category_details->subCategory)}}</a>
		                <hr>

						<strong><i class="fa fa-calendar margin-r-5"></i> {{tr('created_at')}}</strong>						
						<p class="text-muted pull-right">{{convertTimeToUSERzone($category_details->created_at, Auth::guard('admin')->user()->timezone, 'd-m-Y H:i a')}}</p>
						<hr>

						<strong><i class="fa fa-calendar margin-r-5"></i> {{tr('updated_at')}}</strong>
						<p class="text-muted pull-right">{{convertTimeToUSERzone($category_details->updated_at, Auth::guard('admin')->user()->timezone, 'd-m-Y H:i a')}}</p>

					</div>

					<div class="col-md-6">

                        <div class="header">

                            <h4><b>{{tr('picture')}}</b></h4>

                            <img src="{{$category_details->picture}}" style="width: 100%;max-height:300px;">

                        </div>					

					</div>

				</div>

				<div class="box-footer">

	            	<center>
		      			
						@if(Setting::get('admin_delete_control') == YES)

                            <a role="button" href="javascript:;" class="btn btn-sm btn-warning btn disabled" style="text-align: left"><i class="fa fa-edit"></i>{{tr('edit')}}
                            </a>

                            @if($category_details->is_approved == CATEGORY_APPROVED)
		                  		<a  class="btn btn-sm btn-warning" onclick="return confirm(&quot;{{tr('category_decline_confirmation' , $category_details->name)}}&quot;);" href="javascript:;"><i class="fa fa-close"></i>{{tr('decline')}}</a>
		                  	@else
		                  		<a class="btn btn-sm btn-success" onclick="return confirm(&quot;{{tr('category_approve_confirmation' , $category_details->name)}}&quot;);" href="javascript:;" ><i class="fa fa-check"></i>{{tr('approve')}}</a>
		                  	@endif

		                  	<a class="btn btn-sm btn-danger" href="javascript:;"  onclick="return confirm(&quot;{{tr('category_delete_confirmation' , $category_details->name)}}&quot;);"><i class="fa fa-trash"></i>&nbsp;	{{tr('delete')}}
		                  	</a>
                        @else
                            <a class="btn btn-sm btn-warning" role="menuitem" tabindex="-1" href="{{route('admin.categories.edit' , ['category_id' => $category_details->id] )}}"><b> <i class="fa fa-edit"></i>&nbsp;{{tr('edit')}}</b></a>

                            @if($category_details->is_approved == CATEGORY_APPROVED)
		                  		<a  class="btn btn-sm btn-warning" onclick="return confirm(&quot;{{tr('category_decline_confirmation' , $category_details->name)}}&quot;);" href="{{route('admin.categories.status.change' , ['category_id' => $category_details->id] )}}"><b> <i class="fa fa-close"></i>&nbsp;</b>{{tr('decline')}}</a>
		                  	@else
		                  		<a class="btn btn-sm btn-success" onclick="return confirm(&quot;{{tr('category_approve_confirmation' , $category_details->name)}}&quot;);" href="{{route('admin.categories.status.change' , ['category_id' => $category_details->id ])}}"><b> <i class="fa fa-check"></i>&nbsp;</b>{{tr('approve')}}</a>
		                  	@endif

		                  	<a class="btn btn-sm btn-danger" onclick="return confirm(&quot;{{tr('category_delete_confirmation' , $category_details->name)}}&quot;);" href="{{route('admin.categories.delete' , ['category_id' => $category_details->id] )}}"><b> <i class="fa fa-trash"></i>&nbsp;</b>{{tr('delete')}}</a>
		                  	</a>

                        @endif

                    	<a class="btn btn-sm btn-info" href="{{route('admin.sub_categories.create', ['category_id' => $category_details->id] )}}">{{tr('add_sub_category')}}</a>

	                  	<a class="btn btn-sm btn-info" href="{{route('admin.sub_categories.index', ['category_id' => $category_details->id] )}}">{{tr('view_sub_categories')}}</a>

	                  	<a class="btn btn-sm btn-success" href="{{route('admin.videos', ['category_id' => $category_details->id] )}}"><b> <i class="fa fa-video-camera"></i>&nbsp;</b>{{tr('videos')}}</a></a>
	                </center>
				</div>

			</div>
			<!-- /.box -->
		</div>

    </div>

@endsection


