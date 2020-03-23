@extends('layouts.admin')

@section('title', tr('view_moderator'))

@section('content-header', tr('view_moderator'))

@section('breadcrumb')
    <li><a href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i>{{ tr('home') }}</a></li>
    <li><a href="{{ route('admin.moderators.index') }}"><i class="fa fa-users"></i> {{ tr('moderators') }}</a></li>
    <li class="active"><i class="fa fa-user"></i> {{ tr('view_moderators') }}</li>
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

		<div class="col-md-6">

    		<div class="box box-widget widget-user-2">

            	<div class="widget-user-header bg-gray">
            		<div class="pull-left">
	              		<div class="widget-user-image">
	                		<img class="img-circle" src="@if($moderator_details->picture) {{ $moderator_details->picture }} @else {{ asset('admin-css/dist/img/avatar.png') }} @endif" alt="User Avatar">
	              		</div>

	              		<h3 class="widget-user-username">{{ $moderator_details->name }} </h3>
	      				<h5 class="widget-user-desc">{{ tr('moderator') }}</h5>
	      			</div>

	      			<div class="clearfix"></div>

            	</div>
            	
            	<div class="box-footer no-padding">
              		<ul class="nav nav-stacked">
		                <li><a href="#">{{ tr('username') }} <span class="pull-right">{{ $moderator_details->name }}</span></a></li>
		                <li><a href="#">{{ tr('email') }} <span class="pull-right">{{ $moderator_details->email }}</span></a></li>
		                <li><a href="#">{{ tr('mobile') }} <span class="pull-right">{{ $moderator_details->mobile }}</span></a></li>

		                <li><a href="#">{{ tr('address') }} <span class="pull-right">{{ $moderator_details->address }}</span></a></li>
		               
		                <li>
		                	<a href="#">{{ tr('status') }} 
		                		<span class="pull-right">
		                			@if($moderator_details->is_activated) 
						      			<span class="label label-success">{{ tr('approved') }}</span>
						       		@else 
						       			<span class="label label-warning">{{ tr('pending') }}</span>
						       		@endif
		                		</span>
		                	</a>
		                </li>

		                <li>

		                	<a href="#"> {{ tr('is_user') }}

	                			@if($moderator_details->is_user) 
	                			
					      			@if ($moderator_details->user)

						      			<a href="{{ route('admin.users.view',['user_id' => $moderator_details->user->id] ) }}">
						      			<span class="label label-success pull-right">{{ tr('yes') }}</span>
						      			</a>

					      			@else

					      				<span class="label label-success pull-right">{{ tr('yes') }}</span>

					      			@endif
					       		@else 
					       			<span class="label label-warning pull-right">{{ tr('no') }}</span>
					       		@endif
					       		</a>
		                </li>

		                <li><a href="#">{{ tr('timezone') }} <span class="pull-right">{{ $moderator_details->timezone }}</span></a></li>

		                <li><a href="#">{{ tr('created_at') }} <span class="pull-right">{{ convertTimeToUSERzone($moderator_details->created_at, Auth::guard('admin')->user()->timezone, 'd-m-Y H:i a') }}</span></a></li>

		                <li><a href="#">{{ tr('updated_at') }} <span class="pull-right">{{ convertTimeToUSERzone($moderator_details->updated_at, Auth::guard('admin')->user()->timezone, 'd-m-Y H:i a') }}</span></a></li>

              		</ul>
            	</div>

            	<div class="box-footer">

	            	<center>

	            		<div>

		      			  	@if(Setting::get('admin_delete_control'))
		                                                       	
		                       	<a href="javascript:;" class="btn btn-sm btn-warning" style="text-align: left">{{ tr('edit') }}</a>

		                    	<a href="javascript:;" class="btn btn-sm btn-danger" style="text-align: left">{{ tr('delete') }}</a>
		                    @else
		                    	<a href="{{ route('admin.moderators.edit',['moderator_id' => $moderator_details->id] ) }}" class="btn btn-sm btn-warning">{{ tr('edit') }}</a>
		                  		
		                  		<a onclick="return confirm(&quot;{{ tr('admin_moderator_delete_confirmation' , $moderator_details->name) }}&quot;);" href="{{ route('admin.moderators.delete', ['moderator_id' => $moderator_details->id] ) }}" class="btn btn-sm btn-danger">{{ tr('delete') }}</a>   

		                    @endif
		                  
		                  	@if($moderator_details->is_activated)
		                		<a class="btn btn-sm btn-warning" onclick="return confirm(&quot;{{ tr('moderator_decline_confirmation' , $moderator_details->name) }}&quot;);" href="{{ route('admin.moderators.status.change', ['moderator_id' => $moderator_details->id] ) }}">{{ tr('decline') }}</a>
		                	@else
		                  		<a class="btn btn-sm btn-success" onclick="return confirm(&quot;{{ tr('moderator_approve_confirmation' , $moderator_details->name) }}&quot;);"  href="{{ route('admin.moderators.status.change', ['moderator_id' => $moderator_details->id] ) }}">{{ tr('approve') }}</a>
		                  	@endif

		                  	<a class="btn btn-sm btn-success" href="{{ route('admin.moderators.redeems',['moderator_id' => $moderator_details->id] ) }}">{{ tr('redeems') }}</a>

		                  	<a class="btn btn-sm btn-success" href="{{ route('admin.videos' , ['moderator_id' => $moderator_details->id] ) }}">{{ tr('videos') }}</a>

		      			</div>

					</center>

            	</div>

            	<div class="clearfix"></div>

          	</div>

		</div>

		<div class="col-md-6">

    		<div class="box box-widget widget-user-2">

            	<div class="widget-user-header bg-gray">
            		{{ tr('revenue_details') }}
            	</div>

            	<div class="box-footer no-padding">

              		<ul class="nav nav-stacked">

              			 <li><a href="{{ route('admin.videos') }}">{{ tr('total_videos') }} <span class="pull-right">{{ $moderator_details->moderatorVideos ? $moderator_details->moderatorVideos->count() : "0.00" }}</span></a></li>

		                <li><a href="#">{{ tr('total_earnings') }} <span class="pull-right">{{ Setting::get('currency') }} {{ $moderator_details->moderatorRedeem ? $moderator_details->moderatorRedeem->total_moderator_amount : "0.00" }}</span></a></li>

		                <li><a href="#">{{ tr('total_ppv_amount') }} <span class="pull-right">{{ Setting::get('currency') }} <?php echo total_moderator_video_revenue($moderator_details->id) ?> </span></a></li>

		                <li><a href="#">{{ tr('total_viewer_count_amount') }} <span class="pull-right">{{ Setting::get('currency') }} <?php echo redeem_amount($moderator_details->id) ?></span></a></li>

		                <li><a href="#">{{ tr('total_admin_commission') }} <span class="pull-right">{{ Setting::get('currency') }} {{ $moderator_details->moderatorRedeem ? $moderator_details->moderatorRedeem->total_admin_amount : "0.00" }}</span></a></li>

		                <li><a href="#">{{ tr('total_moderator_commission') }} <span class="pull-right">{{ Setting::get('currency') }}{{ $moderator_details->moderatorRedeem ? $moderator_details->moderatorRedeem->total_moderator_amount : "0.00" }}</span></a></li>

		                <li><a href="#">{{ tr('wallet') }} <span class="pull-right">{{ Setting::get('currency') }} {{ $moderator_details->moderatorRedeem ? $moderator_details->moderatorRedeem->remaining : "0.00" }}</span></a></li>

		                <li><a href="#">{{ tr('admin_paid_amount') }} <span class="pull-right">{{ Setting::get('currency') }} {{ $moderator_details->paid_amount }}</span></a></li>
		               
              		</ul>
            	</div>
          	
          	</div>

		</div>

    </div>

@endsection


