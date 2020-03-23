@extends('layouts.admin')

@section('title', tr('view_category'))

@section('content-header', tr('view_category'))

@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
    <li><a href="{{route('admin.categories')}}"><i class="fa fa-key"></i> {{tr('categories')}}</a></li>
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

	@include('notification.notify')
	
	<div class="row">

		<div class="col-md-10 col-md-offset-1">

			<div class="box">

				<div class="box-header btn btn-primary with-border">

					<div class="pull-left">
						<h3 class="box-title" style="color: white"><b>{{tr('category')}}</b></h3>
					</div>

					<div class="pull-right">
		      			
						@if(Setting::get('admin_delete_control') == YES)

                            <a role="button" href="javascript:;" class="btn btn-sm btn-warning btn disabled" style="text-align: left">{{tr('edit')}}
                            </a>
                            @if($category_details->is_approved)
		                  		<a  class="btn btn-sm btn-warning" onclick="return confirm(&quot;{{tr('category_decline_confirmation' , $category_details->name)}}&quot;);" href="javascript:;"">{{tr('decline')}}</a>
		                  	@else
		                  		<a class="btn btn-sm btn-warning" onclick="return confirm(&quot;{{tr('category_approve_confirmation' , $category_details->name)}}&quot;);" href="javascript:;" >{{tr('approve')}}</a>
		                  	@endif
		                  	<a class="btn btn-sm btn-warning" href="javascript:;"  onclick="return confirm(&quot;{{tr('category_delete_confirmation' , $category_details->name)}}&quot;);">	{{tr('delete')}}
		                  	</a>
                        @else
                            <a class="btn btn-sm btn-warning" role="menuitem" tabindex="-1" href="{{route('admin.edit.category' , array('id' => $category_details->id))}}">{{tr('edit')}}</a>

                            @if($category_details->is_approved == YES)
		                  		<a  class="btn btn-sm btn-warning" onclick="return confirm(&quot;{{tr('category_decline_confirmation' , $category_details->name)}}&quot;);" href="{{route('admin.category.approve' , ['id' => $category_details->id , 'status' =>DECLINED ] )}}">{{tr('decline')}}</a>
		                  	@else
		                  		<a class="btn btn-sm btn-warning" onclick="return confirm(&quot;{{tr('category_approve_confirmation' , $category_details->name)}}&quot;);" href="{{route('admin.category.approve' , ['id' => $category_details->id , 'status' => APPROVED ])}}">{{tr('approve')}}</a>
		                  	@endif

		                  	<a class="btn btn-sm btn-warning" onclick="return confirm(&quot;{{tr('category_delete_confirmation' , $category_details->name)}}&quot;);" href="{{route('admin.delete.category' , ['category_id' => $category_details->id] )}}">{{tr('delete')}}
		                  	</a>

                        @endif
					</div>
					<div class="clearfix"></div>
				</div>

				<div class="box-body">

					<div class="col-md-6">

						<strong><i class="fa fa-book margin-r-5"></i> {{tr('name')}}</strong>
						<p class="text-muted">{{$category_details->name}}</p>
						<hr>

						<strong><i class="fa fa-book margin-r-5"></i> {{tr('status')}}</strong>
						<br>
						<br>
						<p class="text-muted">
			      			@if($category_details->status)
			      				<a href="#" class="btn  btn-xs btn-danger">
		              				{{tr('declined')}}
		              			</a>
				      		@else
				      			<a href="#" class="btn  btn-xs btn-success">
		              				{{tr('approved')}}
		              			</a>
				      		@endif
				      	</p>
						<hr>

				      	<strong><i class="fa fa-book margin-r-5"></i> {{tr('is_series')}}</strong>
						<br>
						<br>
						<p class="text-muted">
			      			@if($category_details->is_series)

			      				<a href="#" class="btn   btn-xs btn-success">
		              				{{tr('yes')}}
		              			</a>

				      		@else

				      			<a href="#" class="btn  btn-xs btn-danger">

		              				{{tr('no')}}

		              			</a>
				      		@endif
				      	</p>
						<hr>

						<strong><i class="fa fa-calendar margin-r-5"></i> {{tr('sub_categories')}}</strong>
						<br>
						<p class="text-muted">
						<a href="{{route('admin.sub_categories' , array('category' => $category_details->id))}}">
		                {{count($category_details->subCategory)}}</a>
		                </p>
		                <hr>

						<strong><i class="fa fa-calendar margin-r-5"></i> {{tr('created_at')}}</strong>						
						<p class="text-muted">{{convertTimeToUSERzone($category_details->created_at, Auth::guard('admin')->user()->timezone, 'd-m-Y H:i a')}}</p>
						<hr>

						<strong><i class="fa fa-calendar margin-r-5"></i> {{tr('updated_at')}}</strong>
						<p class="text-muted">{{convertTimeToUSERzone($category_details->updated_at, Auth::guard('admin')->user()->timezone, 'd-m-Y H:i a')}}</p>
						<hr>

					</div>

					<div class="col-md-6">

                        <div class="header">

                            <h4><b>{{tr('picture')}}</b></h4>

                            <img src="{{$category_details->picture}}" style="width: 100%;max-height:300px;">

                        </div>					

					</div>

				</div>

			</div>
			<!-- /.box -->
		</div>

    </div>

@endsection


