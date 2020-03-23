@extends('layouts.admin')

@section('title',tr('view_coupon'))

@section('content-header',tr('view_coupon'))

@section('breadcrumb')

	<li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>

	<li><a href="{{route('admin.coupons.index')}}"><i class="fa fa-gift"></i>{{tr('coupons')}}</a></li>

	<li class="active">{{tr('view_coupon')}}</li>

@endsection

@section('content')
	
	<div class="row">

		<div class="col-md-12">

			<div class="box box-primary">

				<div class="box-header label-primary ">
					<div class="pull-left">
						<h3 class="box-title" style="color: white"><b>{{ tr('coupons') }}</b></h3>
					</div>
                    <a href="{{route('admin.coupons.index')}}" style="float:right" class="btn btn-default">{{tr('coupons')}}</a>

					<div class="clearfix"></div>

				</div>

				<div class="box-body">

					<div class="col-md-6">

						<strong>{{tr('title')}}</strong>
						<h5 class="pull-right">{{$coupon_details->title}}</h5><hr>

						<strong>{{tr('coupon_code')}}</strong>
						<h4 class="pull-right text-green"><b>{{$coupon_details->coupon_code}}</b></h4><hr>

						<strong>{{tr('amount_type')}}</strong>
							@if($coupon_details->amount_type == 0)
							<span class="label label-primary pull-right">{{tr('percentage')}}</span>
							@else
							<span class="label label-primary pull-right">{{tr('absoulte')}}</span>
							@endif
						<hr>

						<strong>{{tr('amount')}}</strong>
							@if($coupon_details->amount_type == 0)
							<span class="label label-primary pull-right">{{$coupon_details->amount}} % </span>
							@else
							<span class="label label-primary pull-right">{{Setting::get('currency')}}{{$coupon_details->amount}}</span>
							@endif
						<hr>

						<strong>{{tr('no_of_users_limit')}}</strong>
							<h5 class="pull-right">							
								 {{$coupon_details->no_of_users_limit}} 
							</h5>
						<hr>

						<strong>{{tr('per_users_limit')}}</strong>
							<h5 class="pull-right">							
								{{$coupon_details->per_users_limit}}
							</h5>
						<hr>

						<strong>{{tr('no_of_used_coupon')}}</strong>
							<h5 class="pull-right">							
								 {{$user_coupon}} 							
							</h5>
					</div>

					<div class="col-md-6">
						
						<strong>{{tr('status')}}</strong>
							@if($coupon_details->status == 0)
							<span class="label label-warning pull-right">{{tr('declined')}}</span>
							@else
							<span class="label label-success pull-right">{{tr('approved')}}</span>
							@endif					
						<hr>

						<strong>{{tr('expiry_date')}}</strong>
							<h5 class="pull-right">							
								 {{date('d M y', strtotime($coupon_details->expiry_date))}} 
							</h5>
						<hr>
				        <strong>{{tr('created_at')}}</strong>
				           <h5 class="pull-right"> {{convertTimeToUSERzone($coupon_details->created_at, Auth::guard('admin')->user()->timezone, 'd-m-Y H:i a')}} </h5>
				        <hr>

				        <strong>{{tr('updated_at')}}</strong>
				          	<h5 class="pull-right">{{convertTimeToUSERzone($coupon_details->updated_at, Auth::guard('admin')->user()->timezone, 'd-m-Y H:i a')}}</h5>

							@if($coupon_details->description == '')

							@else
						<hr>

						<strong>{{tr('description')}}</strong><br>
							<?php echo $coupon_details->description ?>
						@endif
					</div>
				</div>
				<div class="box-footer">
					<center>
					@if(Setting::get('admin_delete_control') == YES )		

						<a class="btn btn-sm btn-warning pull-right" href="javascript:;">{{ tr('edit') }}</a>

						<a class="btn btn-sm btn-danger pull-right" href="javascript:;" onclick="return confirm(&quot;{{tr('coupon_delete_confirmation' , $coupon_details->title)}}&quot;);" >{{ tr('delete') }}</a>
				    @else

						<a class="btn btn-sm btn-warning" href="{{ route('admin.coupons.edit' , ['coupon_id' => $coupon_details->id] ) }}"><i class="fa fa-edit"></i>&nbsp;{{ tr('edit') }}</a>

						<a class="btn btn-sm btn-danger" href="{{ route('admin.coupons.delete', ['coupon_id' => $coupon_details->id ] ) }}" onclick="return confirm(&quot;{{tr('coupon_delete_confirmation' , $coupon_details->title)}}&quot;);" ><i class="fa fa-trash"></i>&nbsp;{{ tr('delete') }}</a>

					@endif
				
					@if($coupon_details->status == APPROVED )

						<a class="btn btn-sm btn-warning " href="{{ route('admin.coupons.status',['coupon_id'=>$coupon_details->id ] ) }}" onclick="return confirm(&quot;{{ tr('coupon_decline_confirmation' , $coupon_details->title) }}&quot;);" ><i class="fa fa-close"></i>{{ tr('decline') }}</a>

					@else
					
						<a class="btn btn-sm btn-success " href="{{ route('admin.coupons.status',['coupon_id'=>$coupon_details->id ] ) }}"><i class="fa fa-check"></i>{{ tr('approve') }} </a>

					@endif
					</center>
				</div>

			</div>

		</div>

	</div>


@endsection