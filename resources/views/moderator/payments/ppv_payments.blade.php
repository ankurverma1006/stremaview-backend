@extends('layouts.moderator')

@section('title', tr('ppv_payments'))

@section('content-header') 

{{tr('ppv_payments') }} {{$total_moderator_video_revenue}} 

<a href="#" id="help-popover" class="btn btn-danger" style="font-size: 14px;font-weight: 600;border-radius:50%" title="Any Help ?"><i class="fa fa-question"></i></a>

<div id="help-content" style="display: none">

	<h5>{{tr('ppv_usage_moderator')}}</h5>

    <ul class="popover-list">
        <li><b>{{tr('amount')}} - </b> {{tr('total_amount_paid_user')}}</li>
        <li><b>{{tr('admin_amount')}} - </b> {{tr('admin_commission_amount')}}</li>
        <li><b>{{tr('my_commission')}} - </b> {{tr('your_earnings_payments')}} </li>
        <li><b>{{tr('reason')}} - </b> {{tr('payment_failure')}} </li>
        <li><b>{{tr('status')}} - </b> {{tr('payment_status_paid_unpaid')}} </li>
    </ul>
    
</div>

@endsection

@section('breadcrumb')

    <li class="active"><i class="fa fa-credit-card	"></i> {{tr('ppv_payments')}}</li>

@endsection

@section('content')

@include('notification.notify')

	<div class="row">
        <div class="col-xs-12">
          	<div class="box">
	            <div class="box-body">

	            	@if(count($ppv_payments) > 0)

		              	<table id="example1" class="table table-bordered table-striped">

							<thead>
							    <tr>
							      <th>{{tr('id')}}</th>
							      <th>{{tr('video')}}</th>
							      <th>{{tr('username')}}</th>
							      <th>{{tr('payment_id')}}</th>
							      <th>{{tr('amount')}}</th>
							      <th>{{tr('admin_amount')}}</th>
							      <th>{{tr('my_commission')}}</th>
							      <th>{{tr('reason')}}</th>
							      <th>{{tr('status')}}</th>
							    </tr>
							</thead>

							<tbody>

								@foreach($ppv_payments as $i => $ppv_payment_details)

								    <tr>
								      	<td>{{$i+1}}</td>

								      	<td>

								      		@if($ppv_payment_details->adminVideo)

								      		<a href="{{route('moderator.videos.view' , ['id' => $ppv_payment_details->video_id])}}">
								      			{{$ppv_payment_details->adminVideo->title ?: ""}}

								      		</a>

								      		@else
								      			-

								      		@endif
								      	</td>

								      	<td> {{$ppv_payment_details->userVideos ? $ppv_payment_details->userVideos->name : ""}}</td>
								      	
								      	<td>{{$ppv_payment_details->payment_id}}</td>

								      	<td>{{Setting::get('currency')}} {{$ppv_payment_details->amount}}</td>

								      	<td>{{Setting::get('currency')}} {{$ppv_payment_details->admin_amount}}</td>

								      	<td>{{Setting::get('currency')}} {{$ppv_payment_details->moderator_amount}}</td>
								      	
								      	<td>{{$ppv_payment_details->reason}}</td>
								      	
								      	<td>
								      		
								      		@if($ppv_payment_details->amount != 0 )

								      			<span class="text-green"><b>{{tr('paid')}}</b></span>

								      		@elseif($ppv_payment_details->amount == 0)
								      			
								      			<span class="text-red"><b>{{tr('unpaid')}}</b></span>

								      		@else

								      			-

								      		@endif

								      	</td>
								    </tr>					

								@endforeach
							</tbody>
						
						</table>

					@else
						<h3 class="no-result">{{tr('no_result_found')}}</h3>
					@endif
					
	            </div>
          	</div>
        </div>
    </div>

@endsection


