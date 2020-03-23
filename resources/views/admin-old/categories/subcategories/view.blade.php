@extends('layouts.admin')

@section('title', tr('view_sub_category'))

@section('content-header', tr('view_sub_category'))

@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
    <li><a href="{{route('admin.categories')}}"><i class="fa fa-key"></i> {{tr('categories')}}</a></li>
    <li><a href="{{route('admin.sub_categories' , array('category' => $sub_category_details->id))}}">{{tr('sub_categories')}} </a></li>
    <li class="active"><i class="fa fa-eye"></i>&nbsp;{{tr('view_sub_category')}}</li>
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

			<div class="box ">

				<div class="box-header btn btn-primary with-border">

					<div class="pull-left">
						<h3 class="box-title" style="color: white"><b>{{tr('sub_category')}}</b></h3>
					</div>

					<div class="pull-right">
		      			
						@if(Setting::get('admin_delete_control') == YES )
                            <a role="button" href="javascript:;" class="btn btn-sm btn-warning btn disabled" style="text-align: left">{{tr('edit')}}</a>

                            @if($sub_category_details->is_approved)
		                  		<a  class="btn btn-sm btn-warning" onclick="return confirm(&quot;{{tr('sub_category_decline_confirmation' ,$sub_category_details->name )}}&quot;);" href="javascript:;">{{tr('decline')}}</a>
		                  	@else
		                  		<a class="btn btn-sm btn-warning" onclick="return confirm(&quot;{{tr('sub_category_approve_confirmation' ,$sub_category_details->name )}}&quot;);" href="javascript:;" >{{tr('approve')}}</a>
		                  	@endif

		                  	<a class="btn btn-sm btn-warning" onclick="return confirm(&quot;{{tr('subcategory_delete_confirmation' , $sub_category_details->name)}}&quot;);" href="javascript:;">{{tr('delete')}}</a>
                           
                        @else
                            <a class="btn btn-sm btn-warning" role="menuitem" tabindex="-1" href="{{route('admin.edit.sub_category' , ['category_id' => $sub_category_details->id,'sub_category_id' => $sub_category_details->id] )}}">{{tr('edit')}}</a>


                            @if($sub_category_details->is_approved  == YES)
		                  		<a class="btn btn-sm btn-warning" role="menuitem" onclick="return confirm(&quot;{{tr('sub_category_decline_confirmation' , $sub_category_details->name)}}&quot;);" href="{{route('admin.sub_category.approve' , ['id' => $sub_category_details->id , 'status' => DECLINED] )}}">{{tr('decline')}}</a>
		                  	@else
		                  		<a class="btn btn-sm btn-warning" role="menuitem" onclick="return confirm(&quot;{{tr('sub_category_approve_confirmation' , $sub_category_details->name)}}&quot;);" href="{{route('admin.sub_category.approve' , ['id' => $sub_category_details->id , 'status' => APPROVED ] )}}">{{tr('approve')}}</a>
		                  	@endif

              				<a class="btn btn-sm btn-warning" onclick="return confirm(&quot;{{tr('subcategory_delete_confirmation' , $sub_category_details->name)}}&quot;);" tabindex="-1" href="{{route('admin.delete.sub_category' , array('sub_category_id' => $sub_category_details->id))}}">{{tr('delete')}}</a>

                        @endif
					</div>

					<div class="clearfix"></div>

				</div>

				<div class="box-body">

					<div class="col-md-6">
						<strong><i class="fa fa-book margin-r-5"></i> {{tr('name')}}</strong>

						<p class="text-muted">{{$sub_category_details->name}}</p>

						<hr>

						<strong><i class="fa fa-book margin-r-5"></i> {{tr('description')}}</strong>

						<p class="text-muted">{{$sub_category_details->description}}</p>

						<hr>

						<strong><i class="fa fa-book margin-r-5"></i> {{tr('status')}}</strong>
						<br>
						<br>
						<p class="text-muted">

			      			@if($sub_category_details->status)
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
					</div>

					<div class="col-md-6">
						<strong><i class="fa fa-calendar margin-r-5"></i> {{tr('created_at')}}</strong>

						<p class="text-muted">{{convertTimeToUSERzone($sub_category_details->created_at, Auth::guard('admin')->user()->timezone, 'd-m-Y H:i a')}}</p>

						<hr>
						<strong><i class="fa fa-calendar margin-r-5"></i> {{tr('updated_at')}}</strong>

						<p class="text-muted">{{convertTimeToUSERzone($sub_category_details->updated_at, Auth::guard('admin')->user()->timezone, 'd-m-Y H:i a')}}</p>

						<hr>
					</div>

				</div>

			</div>
			<!-- /.box -->
		</div>

    </div>

@endsection


