@extends('layouts.admin')

@section('title', tr('subscription_payments'))

@section('content-header',tr('subscription_payments'))

@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
    <li class="active"><i class="fa fa-credit-card"></i> {{tr('subscription_payments')}}</li>
@endsection

@section('content')

	<div class="row">

        <div class="col-xs-12">

          	<div class="box box-primary">

	          	<div class="box-header label-primary">
	                  
	                <b style="font-size:18px;">{{tr('subscription_payments')}}</b>
	                
	                <!-- EXPORT OPTION START -->

					@if(count($payments) > 0 )
	                
		                <ul class="admin-action btn btn-default pull-right" style="margin-right: 60px">
		                 	
							<li class="dropdown">
				                <a class="dropdown-toggle" data-toggle="dropdown" href="#">
				                  {{tr('export')}} <span class="caret"></span>
				                </a>
				                <ul class="dropdown-menu">
				                  	<li role="presentation">
				                  		<a role="menuitem" tabindex="-1" href="{{route('admin.subscription.export' , ['format' => 'xlsx'])}}">
				                  			<span class="text-red"><b>{{tr('excel_sheet')}}</b></span>
				                  		</a>
				                  	</li>

				                  	<li role="presentation">
				                  		<a role="menuitem" tabindex="-1" href="{{route('admin.subscription.export' , ['format' => 'csv'])}}">
				                  			<span class="text-blue"><b>{{tr('csv')}}</b></span>
				                  		</a>
				                  	</li>
				                </ul>
							</li>
						</ul>

					@endif

	                <!-- EXPORT OPTION END -->

	            </div>

	            <div class="box-body  table-responsive">
	          
	            	@if(count($payments) > 0)

		              	<table id="datatable-withoutpagination" class="table table-bordered table-striped">

							<thead>
							    <tr>
							      <th>{{tr('id')}}</th>
							      <th>{{tr('username')}}</th>
							      <th>{{tr('subscriptions')}}</th>
							      <th>{{tr('payment_mode')}}</th>
							      <th>{{tr('payment_id')}}</th>
							      <th>{{tr('coupon_code')}}</th>
							      <th>{{tr('coupon_amount')}}</th>
							      <th>{{tr('plan_amount')}}</th>
							      <th>{{tr('final_amount')}}</th>
							      <th>{{tr('expiry_date')}}</th>
							      <th>{{tr('is_coupon_applied')}}</th>
							      <th>{{tr('coupon_reason')}}</th>
							      <th>{{tr('status')}}</th>
							    </tr>
							</thead>

							<tbody>

								@foreach($payments as $i => $payment_details)
									
								    <tr>
								      	<td>{{showEntries($_GET, $i+1)}}</td>
								      	<td><a href="{{route('admin.users.view' , ['user_id' => $payment_details->user_id] )}}"> {{($payment_details->user) ? $payment_details->user->name : ''}} </a></td>
								      	<td>
								      		@if($payment_details->subscription)
								      			<a href="{{route('admin.subscriptions.view' , ['subscription_id' => $payment_details->subscription->id] )}}" target="_blank">{{$payment_details->subscription ? $payment_details->subscription->title : ''}}</a>
								      		@else
								      			-
								      		@endif
								      	</td>
								      	<td class="text-capitalize">{{$payment_details->payment_mode ? $payment_details->payment_mode : 'free-plan'}}</td>

								      	<td>{{$payment_details->payment_id}}</td>

								      	<td>{{$payment_details->coupon_code}}</td>

								      	<td>{{Setting::get('currency')}} {{$payment_details->coupon_amount? $payment_details->coupon_amount : "0.00"}}</td>

								      	<td>{{Setting::get('currency')}} {{$payment_details->subscription ? $payment_details->subscription_amount : "0.00"}}</td>

								      	<td>{{Setting::get('currency')}} {{$payment_details->amount ? $payment_details->amount : "0.00" }}</td>
								      	
								      	<td>{{date('d M Y',strtotime($payment_details->expiry_date))}}</td>
								      	<td>
								      		@if($payment_details->is_coupon_applied)
											<span class="label label-success">{{tr('yes')}}</span>
											@else
											<span class="label label-danger">{{tr('no')}}</span>
											@endif
								      	</td>
								      	<td>
								      		{{$payment_details->coupon_reason ? $payment_details->coupon_reason : '-'}}
								      	</td>
								      	<td>
								      		@if($payment_details->status)
											<span class="label label-success">{{tr('paid')}}</span>
											@else
											<span class="label label-danger">{{tr('not_paid')}}</span>
											@endif
								      	</td>
								    </tr>					
								@endforeach

							</tbody>
						
						</table>

						<div align="right" id="paglink"><?php echo $payments->links(); ?></div>

					@else

						<h3 class="no-result">{{tr('no_result_found')}}</h3>

					@endif
	            </div>

          	</div>

      	</div>

    </div>

@endsection


@section('scripts')

<script type="text/javascript">


$(document).ready(function() {
    $('#example3').DataTable( {
        "processing": true,
        "serverSide": true,
	    "bLengthChange": false,
        "ajax": "{{route('admin.ajax.user-payments')}}",
        "deferLoading": "{{$payment_count > 0 ? $payment_count : 0}}"
    } );
} );
</script>

@endsection