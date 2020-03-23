@extends('layouts.admin')

@section('title', tr('subscription_payments'))

@section('content-header',tr('subscription_payments'))

@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
    <li class="active"><i class="fa fa-credit-card"></i> {{tr('subscription_payments')}}</li>
@endsection

@section('content')

@include('notification.notify')

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
			                  		<a role="menuitem" tabindex="-1" href="{{route('admin.subscription.export' , ['format' => 'xls'])}}">
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

							@foreach($payments as $i => $payment)
								
							    <tr>
							      	<td>{{showEntries($_GET, $i+1)}}</td>
							      	<td><a href="{{route('admin.users.view' , $payment->user_id)}}"> {{($payment->user) ? $payment->user->name : ''}} </a></td>
							      	<td>
							      		@if($payment->subscription)
							      			<a href="{{route('admin.subscriptions.view' , $payment->subscription->unique_id)}}" target="_blank">{{$payment->subscription ? $payment->subscription->title : ''}}</a>
							      		@else
							      			-
							      		@endif
							      	</td>
							      	<td class="text-capitalize">{{$payment->payment_mode ? $payment->payment_mode : 'free-plan'}}</td>

							      	<td>{{$payment->payment_id}}</td>

							      	<td>{{$payment->coupon_code}}</td>

							      	<td>{{Setting::get('currency')}} {{$payment->coupon_amount? $payment->coupon_amount : "0.00"}}</td>

							      	<td>{{Setting::get('currency')}} {{$payment->subscription ? $payment->subscription_amount : "0.00"}}</td>

							      	<td>{{Setting::get('currency')}} {{$payment->amount ? $payment->amount : "0.00" }}</td>
							      	
							      	<td>{{date('d M Y',strtotime($payment->expiry_date))}}</td>
							      	<td>
							      		@if($payment->is_coupon_applied)
										<span class="label label-success">{{tr('yes')}}</span>
										@else
										<span class="label label-danger">{{tr('no')}}</span>
										@endif
							      	</td>
							      	<td>
							      		{{$payment->coupon_reason ? $payment->coupon_reason : '-'}}
							      	</td>
							      	<td>
							      		@if($payment->status)
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