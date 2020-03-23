@extends('layouts.admin')

@section('title', tr('view_subscription'))

@section('content-header', tr('view_subscription'))

@section('breadcrumb')
    <li><a href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i>{{ tr('home') }}</a></li>
    <li><a href="{{ route('admin.subscriptions.index') }}"><i class="fa fa-key"></i> {{ tr('subscriptions') }}</a></li>
    <li class="active"><i class="fa fa-eye"></i>&nbsp;{{ tr('view_subscription') }}</li>
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

				<div class="box-header btn btn-primary with-border">
					<div class="pull-left">
						<h3 class="box-title" style="color: white"><b>{{ tr('subscription') }}</b></h3>
					</div>
                    <a href="{{route('admin.subscriptions.index')}}" style="float:right" class="btn btn-default">{{tr('view_subscriptions')}}</a>

					<div class="clearfix"></div>

				</div>

				<div class="box-body">
					<div class="col-md-6">
						<strong><i class="fa fa-book margin-r-5"></i> {{ tr('title') }}</strong>
						<p class="text-muted pull-right">{{ $subscription_details->title }}</p>
						<hr>

						<strong><i class="fa fa-calendar margin-r-5"></i> {{ tr('no_of_months') }}</strong>
						<p class="pull-right">
						<span class="label label-success" style="padding: 5px 10px;margin: 5px;font-size: 18px"><b>{{ $subscription_details->plan }}</b></span>					
						</p>
						<hr>	

						<strong><i class="fa fa-money margin-r-5"></i> {{ tr('amount') }}</strong>
						<p class="pull-right">
						<span class="label label-danger" style="padding: 5px 10px;margin: 5px;font-size: 18px"><b>{{ Setting::get('currency' , "$") }} {{ $subscription_details->amount }}</b></span>					
						</p>
						<hr>
						
						<strong><i class="fa fa-users margin-r-5"></i> {{ tr('no_of_account') }}</strong>
						<p class="pull-right">
						<span class="label label-danger" style="padding: 5px 10px;margin: 5px;font-size: 18px"><b>{{ $subscription_details->no_of_account }}</b></span>	
						</p>
						<hr>

						<strong><i class="fa fa-book margin-r-5"></i> {{ tr('popular_status') }}</strong>
						<p class="text-muted pull-right">
			      			@if($subscription_details->popular_status)
			      				<a href="{{ route('admin.subscriptions.popular.status' , ['subscription_id' => $subscription_details->id] ) }}" class="btn  btn-xs btn-danger">
		              				{{ tr('remove_popular') }}
		              			</a>
				      		@else
				      			<a href="{{ route('admin.subscriptions.popular.status' , ['subscription_id' => $subscription_details->id] ) }}" class="btn  btn-xs btn-success">
		              				{{ tr('mark_popular') }}
		              			</a>
				      		@endif
				      	</p>

					</div>

					<div class="col-md-6">

						<strong><i class="fa fa-users margin-r-5"></i> {{ tr('total_subscribers') }}</strong>						
						<p class="text-muted pull-right">{{ $total_subscribers }}</p>
						<hr>

						<strong><i class="fa fa-money margin-r-5"></i> {{ tr('total_earnings') }}</strong>
						<p class="text-muted pull-right ">{{ $earnings }}</p>
						<hr>

						<strong><i class="fa fa-calendar margin-r-5"></i> {{ tr('created_at') }}</strong>
						<p class="text-muted pull-right">{{ convertTimeToUSERzone($subscription_details->created_at, Auth::guard('admin')->user()->timezone, 'd-m-Y H:i a') }}</p>
						<hr>

						<strong><i class="fa fa-calendar margin-r-5"></i> {{ tr('updated_at') }}</strong> 
						<p class="text-muted pull-right">{{ convertTimeToUSERzone($subscription_details->updated_at, Auth::guard('admin')->user()->timezone, 'd-m-Y H:i a') }}</p>
						<hr>

					</div>
					<div class="col-md-12">					
						<hr>
						<strong><i class="fa fa-book margin-r-5"></i> {{ tr('description') }}</strong>
						<p class="text-muted" style="word-wrap: break-word;">{{ $subscription_details->description }}</p>
						<hr>
					</div>
				</div>

				<div class="box-footer">

					<center>

              			@if(Setting::get('admin_delete_control') == YES )
							
			              	<a class="btn btn-sm btn-warning" href="javascript:;" ><i class="fa fa-edit"></i>&nbsp;{{ tr('edit') }}</a>

							<a class="btn btn-sm btn-danger" href="javascript:;" class="btn disabled" style="text-align: left"><i class="fa fa-trash"></i>&nbsp;{{ tr('delete') }}</a>

						@else
							
			              	<a class="btn btn-sm btn-warning" href="{{ route('admin.subscriptions.edit' , ['subscription_id' => $subscription_details->id] ) }}"><i class="fa fa-edit"></i>&nbsp;{{ tr('edit') }}</a>

							<a class="btn btn-sm btn-danger" onclick="return confirm(&quot;{{ tr('subscription_delete_confirmation' , $subscription_details->title) }}&quot;)"  href="{{ route('admin.subscriptions.delete', ['subscription_id' => $subscription_details->id] ) }}"><i class="fa fa-trash"></i>&nbsp;{{ tr('delete') }}</a>
			              	
						@endif
		              	
		              	@if($subscription_details->status == APPROVED )

	              			<a class="btn btn-sm btn-warning" onclick="return confirm(&quot;{{ tr('subscription_decline_confirmation' , $subscription_details->title) }}&quot;)" href="{{ route('admin.subscriptions.status.change' , ['subscription_id' => $subscription_details->id] ) }}">
	              				<span class="text-white"><b><i class="fa fa-close"></i>&nbsp;{{ tr('decline') }}</b></span>
	              			</a>

		              	@else

		              		<a class="btn btn-sm btn-success" onclick="return confirm(&quot;{{ tr('subscription_approve_confirmation' , $subscription_details->title) }}&quot;)"  href="{{ route('admin.subscriptions.status.change' , ['subscription_id' => $subscription_details->id] ) }}">
	              				<span class="text-white"><b><i class="fa fa-check"></i>&nbsp;{{ tr('approve') }}</b></span>
	              			</a> 	

		              	@endif	

              			<a class="btn btn-sm btn-success" href="{{ route('admin.subscriptions.users' , ['subscription_id' => $subscription_details->id] ) }}">
              				<span class="text-white"><b><i class="fa fa-user"></i>&nbsp;{{ tr('subscribers') }}</b></span>
              			</a>
              		</center>
				
				</div>

			</div>
			<!-- /.box -->
		</div>

    </div>

@endsection


