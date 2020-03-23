@extends('layouts.admin')

@section('title', tr('view_subscription'))

@section('content-header', tr('view_subscription'))

@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
    <li><a href="{{route('admin.subscriptions.index')}}"><i class="fa fa-key"></i> {{tr('subscriptions')}}</a></li>
    <li class="active"><i class="fa fa-eye"></i>&nbsp;{{tr('view_subscriptions')}}</li>
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

			<div class="box box-primary">

				<div class="box-header btn btn-primary with-border">
					<div class="pull-left">
						<h3 class="box-title" style="color: white"><b>{{tr('subscription')}}</b></h3>
					</div>
					<div class="pull-right">
		      			<a href="{{route('admin.subscriptions.status' , $data->unique_id)}}" class="btn btn-sm {{$data->status ? 'btn-warning' : 'btn-success'}}">
		      				@if($data->status) 
      							<i class="fa fa-close"></i>&nbsp;&nbsp;{{tr('decline')}}
      						@else 
      							<i class="fa fa-check"></i>&nbsp;&nbsp;{{tr('approve')}}
      						@endif
      					</a>
						<a href="{{route('admin.subscriptions.edit',$data->unique_id)}}" class="btn btn-sm btn-warning"><i class="fa fa-pencil"></i> {{tr('edit')}}</a>
					</div>
					<div class="clearfix"></div>
				</div>

				<div class="box-body">
					<div class="col-md-6">
						<strong><i class="fa fa-book margin-r-5"></i> {{tr('title')}}</strong>

						<p class="text-muted">{{$data->title}}</p>

						<hr>

						<strong><i class="fa fa-book margin-r-5"></i> {{tr('description')}}</strong>

						<p class="text-muted">{{$data->description}}</p>

						<hr>

						<strong><i class="fa fa-calendar margin-r-5"></i> {{tr('no_of_months')}}</strong>
						<br>
						<br>

						<p>
						<span class="label label-success" style="padding: 5px 10px;margin: 5px;font-size: 18px"><b>{{$data->plan}}</b></span>
						
						</p>

						<hr>

						<strong><i class="fa fa-money margin-r-5"></i> {{tr('amount')}}</strong>

						<br>
						<br>

						<p><span class="label label-danger" style="padding: 5px 10px;margin: 5px;font-size: 18px"><b>{{Setting::get('currency' , "$")}} {{$data->amount}}</b></span>
						</p>

						<hr>

						<strong><i class="fa fa-users margin-r-5"></i> {{tr('no_of_account')}}</strong>

						<br>
						<br>

						<p><span class="label label-danger" style="padding: 5px 10px;margin: 5px;font-size: 18px"><b>{{$data->no_of_account}}</b></span>
						</p>
						<hr>

						<strong><i class="fa fa-book margin-r-5"></i> {{tr('popular_status')}}</strong>
						<br>
						<br>
						<p class="text-muted">
			      			@if($data->popular_status)

			      				<a href="{{route('admin.subscriptions.popular.status' , $data->unique_id)}}" class="btn  btn-xs btn-danger">
		              				{{tr('remove_popular')}}
		              			</a>

				      		@else

				      			<a href="{{route('admin.subscriptions.popular.status' , $data->unique_id)}}" class="btn  btn-xs btn-success">

		              				{{tr('mark_popular')}}

		              			</a>
				      		@endif
				      	</p>

					</div>

					<div class="col-md-6">

						<strong><i class="fa fa-users margin-r-5"></i> {{tr('total_subscribers')}}</strong>
						
						<p class="text-muted">{{$total_subscribers}}</p>

						<?php /*<hr>
						<strong><i class="fa fa-users margin-r-5"></i> {{tr('active_subscribers')}}</strong>

						<p class="text-muted"></p>

						<hr>
						<strong><i class="fa fa-users margin-r-5"></i> {{tr('expired_subscribers')}}</strong>

						<p class="text-muted"></p>

						<hr> 
						<strong><i class="fa fa-users margin-r-5"></i> {{tr('cancelled_subscribers')}}</strong>

						<p class="text-muted"></p> */ ?>
 
						<hr>
						<strong><i class="fa fa-money margin-r-5"></i> {{tr('total_earnings')}}</strong>

						<p class="text-muted">{{$earnings}}</p>

						<hr>
						<strong><i class="fa fa-calendar margin-r-5"></i> {{tr('created_at')}}</strong>

						<p class="text-muted">{{convertTimeToUSERzone($data->created_at, Auth::guard('admin')->user()->timezone, 'd-m-Y H:i a')}}</p>

						<hr>
						<strong><i class="fa fa-calendar margin-r-5"></i> {{tr('updated_at')}}</strong>

						<p class="text-muted">{{convertTimeToUSERzone($data->updated_at, Auth::guard('admin')->user()->timezone, 'd-m-Y H:i a')}}</p>

						<hr>

					</div>
				</div>

			</div>
			<!-- /.box -->
		</div>

    </div>

@endsection


