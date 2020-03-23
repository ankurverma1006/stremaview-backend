@extends('layouts.admin')

@section('title', tr('view_user'))

@section('content-header', tr('view_user'))

@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
    <li><a href="{{route('admin.users')}}"><i class="fa fa-user"></i> {{tr('users')}}</a></li>
    <li class="active"><i class="fa fa-user"></i> {{tr('view_user')}}</li>
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

    		<div class="box box-widget widget-user-2">

            	<div class="widget-user-header bg-gray">
            		<div class="pull-left">
	              		<div class="widget-user-image">

	                		<img class="img-circle" src=" @if($user->picture) {{$user->picture}} @else {{asset('admin-css/dist/img/avatar.png')}} @endif" alt="User Avatar">
	              		</div>

	              		<h3 class="widget-user-username">{{$user->name}} </h3>
	      				<h5 class="widget-user-desc">{{tr('user')}}</h5>
      				</div>
      				<div class="pull-right">
      					<a href="{{route('admin.users.edit' , array('id' => $user->id))}}" class="btn btn-sm btn-warning">{{tr('edit')}}</a>
      				</div>
      				<div class="clearfix"></div>
            	</div>	
            	
            	<div class="box-footer no-padding">
            		<div class="col-md-6">
              		<ul class="nav nav-stacked">

		                <li><a>{{tr('username')}} <span class="pull-right">{{$user->name}}</span></a></li>
		                <li><a>{{tr('email')}} <span class="pull-right">{{$user->email}}</span></a></li>
		                <li><a>{{tr('mobile')}} <span class="pull-right">{{$user->mobile}}</span></a></li>
		                
		                <li>
		                	<a>{{tr('validity_days')}} 
		                		<span class="pull-right"> 
		            				@if($user->user_type)
		                                <p style="color:#cc181e">
		                                	{{tr('no_of_days_expiry')}} 
		                                	<b>{{get_expiry_days($user->id)}} days</b>
		                            	</p>
		                            @endif
		                        </span>
		                    </a>
		                </li>


		                <li style="border-left: 20px solid #fff;">
	                		{{tr('is_moderator')}}

	                		<span class="pull-right" style="margin-right: 20px;">

	                			@if($user->is_moderator) 
					      			<a href="{{route('admin.moderator.view',$user->moderator->id)}}"><span class="label label-success">{{tr('yes')}}</span></a>
					       		@else 
					       			<span class="label label-warning">{{tr('no')}}</span>
					       		@endif
	                		</span>
		                </li>
		                
		                <li>
		                	<a>{{tr('status')}} 
		                		<span class="pull-right">
		                			@if($user->is_activated) 
						      			<span class="label label-success">{{tr('approved')}}</span>
						       		@else 
						       			<span class="label label-warning">{{tr('pending')}}</span>
						       		@endif
		                		</span>
		                	</a>
		                </li>

		                <li>
		                	<a>

		                		{{tr('user_type')}}

		                		<span class="pull-right">

		                			@if($user->user_type) 
						      			<span class="label label-success">{{tr('paid_user')}}</span>
						       		@else 
						       			<span class="label label-warning">{{tr('normal_user')}}</span>
						       		@endif
		                		</span>
		                	</a>
		                </li>
		               <li><a>{{tr('amount_paid')}} <span class="pull-right">{{$user->amount_paid ? $user->amount_paid : '0.00'}}</span></a></li>

		                <li><a>{{tr('email_notification')}} <span class="pull-right">
				                @if($user->email_notification_status)
				                 	<span class="label label-success">
				                 	{{tr('yes')}}</span>
				                @else
				                 <span class="label label-warning">
				                 {{tr('no')}}</span>
				                @endif </span>
		             		</a>
		             	</li>
              		</ul>
            	</div>
            	<div class="col-md-6">
            		<ul class="nav nav-stacked">
            			
		             	<li><a>{{tr('no_of_account')}} <span class="pull-right">{{$user->no_of_account}}</span></a></li>
            			

		                <?php

		                /* We are not  using Paypal email user paypal mode check payments

		                <li><a>{{tr('paypal_email')}} <span class="pull-right">{{$user->paypal_email ? $user->paypal_email : "-"}}</span></a></li>

		                <li><a>{{tr('payment_mode')}} <span class="pull-right">
		                	{{ $user->payment_mode ? $user->payment_mode : "-" }}
		                </span></a></li>
		                /*
							We are not receiving moderator address,description field for anywhere
	
		
		                 /* <li><a>{{tr('address')}} <span class="pull-right">{{$user->address ? $user->address : "-"}}</span></a></li> -->

		                	<li><a>{{tr('description')}} <span class="pull-right">{{$user->description ? $user->description : "-"}}</span></a></li> */
		                 ?>

		                <li><a>{{tr('device_type')}} <span class="pull-right">{{$user->device_type}}</span></a></li>

		                <li><a>{{tr('login_by')}} <span class="pull-right">{{$user->login_by}}</span></a></li>

		                <li><a>{{tr('social_unique_id')}} <span class="pull-right">{{$user->social_unique_id ? $user->social_unique_id : "-"}}</span></a></li>

		                <li><a>{{tr('timezone')}} <span class="pull-right">{{$user->timezone ? $user->timezone : "-"}}</span></a></li>

		                <li><a>{{tr('created_at')}} <span class="pull-right">{{convertTimeToUSERzone($user->created_at, Auth::guard('admin')->user()->timezone, 'd-m-Y H:i a')}}</span></a></li>

		                <li><a>{{tr('updated_at')}} <span class="pull-right">{{convertTimeToUSERzone($user->updated_at, Auth::guard('admin')->user()->timezone, 'd-m-Y H:i a')}}</span></a></li>


              		</ul>
            	</div>
          	</div>
          	</div>

		</div>

    </div>

@endsection


