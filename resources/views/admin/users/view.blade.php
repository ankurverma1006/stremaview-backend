@extends('layouts.admin')

@section('title', tr('view_user'))

@section('content-header', tr('view_user'))

@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
    <li><a href="{{route('admin.users.index')}}"><i class="fa fa-user"></i> {{tr('users')}}</a></li>
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
	                		<img class="img-circle" src=" @if($user_details->picture) {{$user_details->picture}} @else {{asset('admin-css/dist/img/avatar.png')}} @endif" alt="User Avatar">
	              		</div>

	              		<h3 class="widget-user-username">{{$user_details->name}} </h3>
	      				<h5 class="widget-user-desc">{{tr('user')}}</h5>
      				</div>

      				<div class="clearfix"></div>
            	</div>	
            	
            	<div class="box-footer no-padding">
            		<div class="col-md-6">
              		<ul class="nav nav-stacked">

		                <li><a>{{tr('username')}} <span class="pull-right">{{$user_details->name}}</span></a></li>
		                <li><a>{{tr('email')}} <span class="pull-right">{{$user_details->email}}</span></a></li>
		                <li><a>{{tr('mobile')}} <span class="pull-right">{{$user_details->mobile}}</span></a></li>
		                
		                <li>
		                	<a>{{tr('validity_days')}} 
		                		<span class="pull-right"> 
		            				@if($user_details->user_type)
		                                <p style="color:#cc181e">
		                                	{{tr('no_of_days_expiry')}} 
		                                	<b>{{get_expiry_days($user_details->id)}} days</b>
		                            	</p>
		                            @endif
		                        </span>
		                    </a>
		                </li>

		                <li>
		                	<a>{{tr('is_moderator')}} 
		                		<span class="pull-right">
		                			@if($user_details->is_moderator) 
						      			<a href="{{route('admin.moderators.view',['moderator_id' => $user_details->moderator->id ] )}}"><span class="label label-success">{{tr('yes')}}</span></a>
						       		@else 
						       			<span class="label label-warning">{{tr('no')}}</span>
						       		@endif
		                		</span>
		                	</a>
		                </li>
		                
		                <li>
		                	<a>{{tr('status')}} 
		                		<span class="pull-right">
		                			@if($user_details->is_activated) 
						      			<span class="label label-success">{{tr('approved')}}</span>
						       		@else 
						       			<span class="label label-warning">{{tr('pending')}}</span>
						       		@endif
		                		</span>
		                	</a>
		                </li>

		                <li>
		                	<a>{{tr('is_verified')}} 
		                		<span class="pull-right text-uppercase">
		                			@if($user_details->is_verified) 
						      			<span class="label label-success">{{tr('yes')}}</span>
						       		@else 
						       			<span class="label label-danger">{{tr('no')}}</span>
						       		@endif
		                		</span>
		                	</a>
		                </li>

		                <li>
		                	<a>

		                		{{tr('user_type')}}

		                		<span class="pull-right">

		                			@if($user_details->user_type) 
						      			<span class="label label-success">{{tr('paid_user')}}</span>
						       		@else 
						       			<span class="label label-warning">{{tr('normal_user')}}</span>
						       		@endif
		                		</span>
		                	</a>
		                </li>
		               <li><a>{{tr('amount_paid')}} <span class="pull-right">{{$user_details->amount_paid ? $user_details->amount_paid : '0.00'}}</span></a></li>

		                <li><a>{{tr('email_notification')}} <span class="pull-right">
				                @if($user_details->email_notification)
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
            			
		             	<li><a>{{tr('no_of_account')}} <span class="pull-right">{{$user_details->no_of_account}}</span></a></li>
            			
		                <li><a>{{tr('device_type')}} <span class="pull-right">{{$user_details->device_type}}</span></a></li>

		                <li><a>{{tr('login_by')}} <span class="pull-right">{{$user_details->login_by}}</span></a></li>

		                <li><a>{{tr('social_unique_id')}} <span class="pull-right">{{$user_details->social_unique_id ? $user_details->social_unique_id : "-"}}</span></a></li>

		                <li><a>{{tr('timezone')}} <span class="pull-right">{{$user_details->timezone ? $user_details->timezone : "-"}}</span></a></li>

		                <li><a>{{tr('created_at')}} <span class="pull-right">{{convertTimeToUSERzone($user_details->created_at, Auth::guard('admin')->user()->timezone, 'd-m-Y H:i a')}}</span></a></li>

		                <li><a>{{tr('updated_at')}} <span class="pull-right">{{convertTimeToUSERzone($user_details->updated_at, Auth::guard('admin')->user()->timezone, 'd-m-Y H:i a')}}</span></a></li>


              		</ul>
            	</div>
          	</div>

            	<div class="box-footer">
            	<center>
						@if(Setting::get('admin_delete_control') == YES )

							<a href="javascript:;" class="btn btn-sm btn-warning"><b>{{tr('edit')}}</b></a>

							@if(get_expiry_days($user_details->id) > 0)

	                  	 		<a onclick="return confirm(&quot;{{tr('admin_user_delete_with_expiry_days_confirmation' , get_expiry_days($user_details->id ) )}}&quot;);" href="javascript:;" class="btn btn-sm btn-danger"><b>{{tr('delete')}}</b></a>

	                  		@else 
	                  			<a onclick="return confirm(&quot;{{tr('admin_user_delete_confirmation' , $user_details->name)}}&quot;);" href="javascript:;" class="btn btn-sm btn-danger"><b>{{tr('delete')}}</b></a>
	                  	 	@endif

						@else    

      						<a href="{{route('admin.users.edit' , ['user_id' => $user_details->id] )}}" class="btn btn-sm btn-warning"><b><i class="fa fa-edit"></i>&nbsp;{{tr('edit')}}</b></a>

      						@if(get_expiry_days($user_details->id) > 0)

	                  	 		<a onclick="return confirm(&quot;{{tr('admin_user_delete_with_expiry_days_confirmation' , get_expiry_days($user_details->id ) )}}&quot;);"  href="{{route('admin.users.delete', ['user_id' => $user_details->id ])}}" class="btn btn-sm btn-danger"><b><i class="fa fa-trash"></i>&nbsp;{{tr('delete')}}</b></a>

	                  		@else 
	                  			<a onclick="return confirm(&quot;{{tr('admin_user_delete_confirmation' , $user_details->name)}}&quot;);" href="{{route('admin.users.delete', ['user_id' => $user_details->id ] )}}" class="btn btn-sm btn-danger"><b><i class="fa fa-trash"></i>&nbsp;{{tr('delete')}}</b></a>
	                  	 	@endif

	                 	@endif

                  	 		@if($user_details->is_activated)
		                  		<a onclick="return confirm(&quot;{{ $user_details->name }} - {{tr('user_decline_confirmation')}}&quot;);" href="{{route('admin.users.status.change' , ['user_id' => $user_details->id ] )}}" class="btn btn-sm btn-danger"><b><i class="fa fa-close"></i>&nbsp; {{tr('decline')}} </b></a>
		                  	@else 
		                  	 	<a onclick="return confirm(&quot;{{$user_details->name}} - {{tr('user_approve_confirmation')}}&quot;);" href="{{route('admin.users.status.change' , ['user_id' => $user_details->id ] )}}" class="btn btn-sm btn-success"> <b><i class="fa fa-check"></i>&nbsp;{{tr('approve')}} </b> </a>
		                  	@endif


      						<a class="btn btn-sm btn-info" href="{{route('admin.users.subprofiles',  ['user_id' => $user_details->id ] )}}"><b><i class="fa fa-users"></i>&nbsp;{{tr('sub_profiles')}}</b></a>

      						<a class="btn btn-sm btn-success" href="{{route('admin.subscriptions.plans' ,['user_id' => $user_details->id] ) }}">		
      							<b><i class="fa fa-eye"></i>&nbsp;{{tr('subscription_plans')}}</b>
							</a>

							@if(!$user_details->is_verified)

								<a class="btn btn-sm bg-maroon text-uppercase" href="{{ route('admin.users.verify' , ['user_id' => $user_details->id ] ) }}">		
	      							<b><i class="fa fa-check"></i>&nbsp;{{tr('verify')}}</b>
								</a>
											                  	
				      		@endif
				</center>
      			</div>
          	</div>

		</div>

    </div>

@endsection


