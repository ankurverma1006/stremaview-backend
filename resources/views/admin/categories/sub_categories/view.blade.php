@extends('layouts.admin')

@section('title', tr('view_sub_category'))

@section('content-header', tr('view_sub_category'))

@section('breadcrumb')
    <li><a href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i>{{ tr('home') }}</a></li>
    <li><a href="{{ route('admin.categories.index') }}"><i class="fa fa-suitcase"></i> {{ tr('categories') }}</a></li>
    <li><a href="{{ route('admin.sub_categories.index' , ['category_id' => $sub_category_details->category_id] ) }}"><i class="fa fa-list"></i>&nbsp;</i>{{ tr('sub_categories') }} </a></li>
    <li class="active"><i class="fa fa-eye"></i>&nbsp;{{ tr('view_sub_category') }}</li>
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
					<h3 class="box-title pull-left" style="color: white"><b>{{tr('sub_category')}}</b></h3> 
					<a href="{{  route('admin.sub_categories.index' , [ 'category_id' => $sub_category_details->category_id  , 'sub_category_id' => $sub_category_details->id] ) }}" class="btn btn-default pull-right">{{ tr('sub_categories') }}</a>
            	</div>

				<div class="box-body">

					<div class="col-md-6">
						<strong><i class="fa fa-book margin-r-5 "></i> {{ tr('name') }}</strong>
						<p class="text-muted pull-right">{{ $sub_category_details->name }}</p>
						<hr>

						<strong><i class="fa fa-book margin-r-5"></i> {{ tr('status') }}</strong>						
			      			@if($sub_category_details->status)
			      				<a href="#" class="btn btn-xs btn-danger pull-right">
		              				{{ tr('declined') }}
		              			</a>
				      		@else
				      			<a href="#" class="btn btn-xs btn-success pull-right">
		              				{{ tr('approved') }}
		              			</a>
				      		@endif
						<hr>

						<strong><i class="fa fa-book margin-r-5"></i> {{ tr('description') }}</strong>
						<p class="text-muted" >{{ $sub_category_details->description }}</p>
						<hr>

						<strong><i class="fa fa-calendar margin-r-5"></i> {{ tr('created_at') }}</strong>
						<p class="text-muted pull-right ">{{ convertTimeToUSERzone($sub_category_details->created_at, Auth::guard('admin')->user()->timezone, 'd-m-Y H:i a') }}</p>
						<hr>

						<strong><i class="fa fa-calendar margin-r-5"></i> {{ tr('updated_at') }}</strong>
						<p class="text-muted pull-right">{{ convertTimeToUSERzone($sub_category_details->updated_at, Auth::guard('admin')->user()->timezone, 'd-m-Y H:i a') }}</p>
					</div>

					<div class="col-md-6">

                        <div class="header">

                            <h4><b>{{tr('picture')}}</b></h4>

                            <img src="{{$sub_category_details->picture}}" style="width: 100%;max-height:300px;">

                        </div>					

					</div>

				</div>

				<div class="box-footer">		      			
					<center>
					@if(Setting::get('admin_delete_control') == YES )
                        <a role="button" href="javascript:;" class="btn btn-sm btn-warning btn disabled" style="text-align: left"><i class="fa fa-edit"></i>&nbsp;{{ tr('edit') }}</a>

                        @if($sub_category_details->is_approved)
	                  		<a  class="btn btn-sm btn-warning" onclick="return confirm(&quot;{{ tr('sub_category_decline_confirmation' ,$sub_category_details->name ) }}&quot;);" href="javascript:;"><i class="fa fa-close"></i>&nbsp;{{ tr('decline') }}</a>
	                  	@else
	                  		<a class="btn btn-sm btn-warning" onclick="return confirm(&quot;{{ tr('sub_category_approve_confirmation' ,$sub_category_details->name ) }}&quot;);" href="javascript:;" ><i class="fa fa-check"></i>&nbsp;{{ tr('approve') }}</a>
	                  	@endif

	                  	<a class="btn btn-sm btn-danger" onclick="return confirm(&quot;{{ tr('subcategory_delete_confirmation' , $sub_category_details->name) }}&quot;);" href="javascript:;"><i class="fa fa-trash"></i>&nbsp;{{ tr('delete') }}</a>
                       
                    @else
                    
                        <a class="btn btn-sm btn-warning" role="menuitem" tabindex="-1" href="{{ route('admin.sub_categories.edit' , ['category_id' => $sub_category_details->category_id, 'sub_category_id' => $sub_category_details->sub_category_id] ) }}"><i class="fa fa-edit"></i>&nbsp; {{ tr('edit') }}</a>

                        @if($sub_category_details->is_approved  == YES)
	                  		<a class="btn btn-sm btn-warning" role="menuitem" onclick="return confirm(&quot;{{ tr('sub_category_decline_confirmation' , $sub_category_details->name) }}&quot;);" href="{{ route('admin.sub_categories.status.change' , ['sub_category_id' => $sub_category_details->sub_category_id ] ) }}"><i class="fa fa-close"></i>&nbsp;{{ tr('decline') }}</a>
	                  	@else
	                  		<a class="btn btn-sm btn-success" role="menuitem" onclick="return confirm(&quot;{{ tr('sub_category_approve_confirmation' , $sub_category_details->name) }}&quot;);" href="{{ route('admin.sub_categories.status.change' , ['sub_category_id' => $sub_category_details->sub_category_id ] ) }}"><i class="fa fa-check"></i>&nbsp;{{ tr('approve') }}</a>
	                  	@endif

          				<a class="btn btn-sm btn-danger" onclick="return confirm(&quot;{{ tr('subcategory_delete_confirmation' , $sub_category_details->name) }}&quot;);" tabindex="-1" href="{{ route('admin.sub_categories.delete' , ['sub_category_id' => $sub_category_details->sub_category_id] ) }}"><i class="fa fa-trash"></i>&nbsp;{{ tr('delete') }}</a>

                    @endif  	

					</center>
				</div>

			</div>
			<!-- /.box -->
		</div>

    </div>

@endsection


