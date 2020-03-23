@extends('layouts.admin')

@section('title', tr('view_moderator'))

@section('content-header', tr('view_moderator'))

@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
    <li><a href="{{route('admin.moderators')}}"><i class="fa fa-users"></i> {{tr('moderators')}}</a></li>
    <li class="active"><i class="fa fa-user"></i> {{tr('view_moderators')}}</li>
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
	                		<img class="img-circle" src="@if($moderator->picture) {{$moderator->picture}} @else {{asset('admin-css/dist/img/avatar.png')}} @endif" alt="User Avatar">
	              		</div>

	              		<h3 class="widget-user-username">{{$moderator->name}} </h3>
	      				<h5 class="widget-user-desc">{{tr('moderator')}}</h5>
	      			</div>
	      			<div class='pull-right'>
	      			<a href="{{route('admin.edit.moderator',$moderator->id)}}" class="btn btn-sm btn-warning">{{tr('edit')}}</a>
	      			</div>
	      			<div class="clearfix"></div>
            	</div>
            	
            	<div class="box-footer no-padding">
              		<ul class="nav nav-stacked">
		                <li><a href="#">{{tr('username')}} <span class="pull-right">{{$moderator->name}}</span></a></li>
		                <li><a href="#">{{tr('email')}} <span class="pull-right">{{$moderator->email}}</span></a></li>
		                <li><a href="#">{{tr('mobile')}} <span class="pull-right">{{$moderator->mobile}}</span></a></li>

		                <li><a href="#">{{tr('address')}} <span class="pull-right">{{$moderator->address}}</span></a></li>
		                
		                <?php 

		                /*
		                   We are not receiving moderator dob field for anywhere
		                */

		                /*
		                <li><a href="#">{{tr('dob')}} <span class="pull-right">@if($moderator->dob && $moderator->dob != 0000-00-00) {{date('d M, Y',strtotime($seeker->dob))}} @else - @endif</span></a></li> */

		                 ?>

		                <li>
		                	<a href="#">{{tr('status')}} 
		                		<span class="pull-right">
		                			@if($moderator->is_activated) 
						      			<span class="label label-success">{{tr('approved')}}</span>
						       		@else 
						       			<span class="label label-warning">{{tr('pending')}}</span>
						       		@endif
		                		</span>
		                	</a>
		                </li>

		                <li style="border-left: 20px solid #fff;">
		                	{{tr('is_user')}}
	                		<span class="pull-right" style="margin-right: 20px;">
	                			@if($moderator->is_user) 
					      			@if ($moderator->user)

						      			<a href="{{route('admin.users.view',$moderator->user->id)}}"><span class="label label-success" ">{{tr('yes')}}</span>
						      			</a>

					      			@else

					      				<span class="label label-success" ">{{tr('yes')}}</span>

					      			@endif
					       		@else 
					       			<a href="#"><span class="label label-warning">{{tr('no')}}</span></a>
					       		@endif
	                		</span>
		                </li>

		                <?php /*<li><a href="#">{{tr('paypal_email')}} <span class="pull-right">{{$moderator->paypal_email ? $moderator->paypal_email : "-"}}</span></a></li> */ ?>
		                <li><a href="#">{{tr('timezone')}} <span class="pull-right">{{$moderator->timezone}}</span></a></li>
		                <li><a href="#">{{tr('created_at')}} <span class="pull-right">{{convertTimeToUSERzone($moderator->created_at, Auth::guard('admin')->user()->timezone, 'd-m-Y H:i a')}}</span></a></li>
		                <li><a href="#">{{tr('updated_at')}} <span class="pull-right">{{convertTimeToUSERzone($moderator->updated_at, Auth::guard('admin')->user()->timezone, 'd-m-Y H:i a')}}</span></a></li>
              		</ul>
            	</div>
          	
          	</div>

		</div>

		<div class="col-md-6">

    		<div class="box box-widget widget-user-2">

            	<div class="widget-user-header bg-gray">
            		{{tr('revenue_details')}}
            	</div>

            	<div class="box-footer no-padding">

              		<ul class="nav nav-stacked">

              			 <li><a href="{{route('admin.videos')}}">{{tr('total_videos')}} <span class="pull-right">{{$moderator->moderatorVideos ? $moderator->moderatorVideos->count() : "0.00"}}</span></a></li>

		                <li><a href="#">{{tr('total_earnings')}} <span class="pull-right">{{Setting::get('currency')}} {{$moderator->moderatorRedeem ? $moderator->moderatorRedeem->total_moderator_amount : "0.00"}}</span></a></li>

		                <li><a href="#">{{tr('total_ppv_amount')}} <span class="pull-right">{{Setting::get('currency')}} <?php echo total_moderator_video_revenue($moderator->id) ?> </span></a></li>

		                <li><a href="#">{{tr('total_viewer_count_amount')}} <span class="pull-right">{{Setting::get('currency')}} <?php echo redeem_amount($moderator->id) ?></span></a></li>

		                <li><a href="#">{{tr('total_admin_commission')}} <span class="pull-right">{{Setting::get('currency')}} {{$moderator->moderatorRedeem ? $moderator->moderatorRedeem->total_admin_amount : "0.00"}}</span></a></li>

		                <li><a href="#">{{tr('total_moderator_commission')}} <span class="pull-right">{{Setting::get('currency')}}{{$moderator->moderatorRedeem ? $moderator->moderatorRedeem->total_moderator_amount : "0.00"}}</span></a></li>

		                <li><a href="#">{{tr('wallet')}} <span class="pull-right">{{Setting::get('currency')}} {{$moderator->moderatorRedeem ? $moderator->moderatorRedeem->remaining : "0.00"}}</span></a></li>

		                <li><a href="#">{{tr('admin_paid_amount')}} <span class="pull-right">{{Setting::get('currency')}} {{$moderator->paid_amount}}</span></a></li>

		               
              		</ul>
            	</div>
          	
          	</div>

		</div>

    </div>

@endsection


