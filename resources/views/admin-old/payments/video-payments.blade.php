@extends('layouts.admin')

@section('title', tr('video_payments'))

@section('content-header',tr('video_payments') . ' ( '.Setting::get("currency").' '. total_video_revenue() . ' ) ' ) 

@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
    <li class="active"><i class="fa fa-credit-card"></i> {{tr('video_payments')}}</li>
@endsection

@section('content')

@include('notification.notify')

	<div class="row">
        <div class="col-xs-12">
          <div class="box box-primary">
          	<div class="box-header label-primary">
                  
                <b style="font-size:18px;">{{tr('video_payments')}}</b>
                <!-- EXPORT OPTION START -->

				@if(count($data) > 0 )
                
	                <ul class="admin-action btn btn-default pull-right" style="margin-right: 60px">
	                 	
						<li class="dropdown">
			                <a class="dropdown-toggle" data-toggle="dropdown" href="#">
			                  {{tr('export')}} <span class="caret"></span>
			                </a>
			                <ul class="dropdown-menu">
			                  	<li role="presentation">
			                  		<a role="menuitem" tabindex="-1" href="{{route('admin.payperview.export' , ['format' => 'xls'])}}">
			                  			<span class="text-red"><b>{{tr('excel_sheet')}}</b></span>
			                  		</a>
			                  	</li>

			                  	<li role="presentation">
			                  		<a role="menuitem" tabindex="-1" href="{{route('admin.payperview.export' , ['format' => 'csv'])}}">
			                  			<span class="text-blue"><b>{{tr('csv')}}</b></span>
			                  		</a>
			                  	</li>
			                </ul>
						</li>
					</ul>

				@endif

                <!-- EXPORT OPTION END -->

            </div>
            <div class="box-body table-responsive">

            	@if(count($data) > 0)

	              	<table id="datatable-withoutpagination" class="table table-bordered table-striped"> 

						<thead>
						    <tr>
						      <th>{{tr('id')}}</th>
						      <th>{{tr('video')}}</th>
						      <th>{{tr('username')}}</th>
						      <th>{{tr('payment_id')}}</th>
						      <th>{{tr('payment_mode')}}</th>
						      <th>{{tr('ppv_amount')}}</th>
						      <th>{{tr('coupon_amount')}}</th>

						      <th>{{tr('total')}}</th>
                  			  <th>{{tr('admin')}} {{Setting::get('currency')}}</th>
                  			  <th>{{tr('moderator')}} {{Setting::get('currency')}}</th>
						      <!-- <th>{{tr('expiry_date')}}</th> -->
						      <th>{{tr('is_coupon_applied')}}</th>
						      <th>{{tr('coupon_reason')}}</th>
						      <th>{{tr('status')}}</th>
						    </tr>
						</thead>

						<tbody>

							@foreach($data as $i => $payment)

							    <tr>
							      	<td>{{showEntries($_GET, $i+1)}}</td>
							      	<td>

							      		@if($payment->adminVideo)

							      		<a href="{{route('admin.view.video' , array('id' => $payment->adminVideo->id))}}">{{$payment->adminVideo->title}}</a>

							      		@else 

							      		- 

							      		@endif

							      	</td>

							      	<td>
							      		<a href="{{route('admin.users.view' , $payment->user_id)}}"> {{$payment->userVideos ? $payment->userVideos->name : ""}} </a>
							      	</td>

							      	<td>{{$payment->payment_id}}</td>

							      	<td>{{$payment->payment_mode ? $payment->payment_mode : "-"}}</td>

							      	<td>{{Setting::get('currency')}} {{$payment->ppv_amount ? $payment->ppv_amount  : "0.00"}}</td>

							      	<td>{{Setting::get('currency')}} {{$payment->coupon_amount ? 
							      		$payment->coupon_amount : "0.00"}} @if($payment->coupon_code) ({{$payment->coupon_code}}) @endif</td>

							      	<td>{{Setting::get('currency')}} {{$payment->amount ? 
							      		$payment->amount : "0.00"}}</td>

							      	<td>{{Setting::get('currency')}} {{$payment->admin_amount ?
							      	 $payment->admin_amount : "0.00"}}</td>

							      	<td>{{Setting::get('currency')}} {{$payment->moderator_amount ? $payment->moderator_amount : "0.00"}}</td>

							      	<!-- <td>{{date('d M Y',strtotime($payment->expiry_date))}}</td> -->

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
							      		@if($payment->amount <= 0)

							      			@if($payment->coupon_amount <= 0)

							      				<label class="label label-danger">{{tr('not_paid')}}</label>

							      			@else 

							      				<label class="label label-success">{{tr('paid')}}</label>

							      			@endif

							      		@else
							      			<label class="label label-success">{{tr('paid')}}</label>

							      		@endif 
							      	</td>
							    </tr>					

							@endforeach
						</tbody>
					
					</table>

					<div align="right" id="paglink"><?php echo $data->links(); ?></div>

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
        "bLengthChange": false,
        "serverSide": true,
        "ajax": "{{route('admin.ajax.video-payments')}}",
        "deferLoading": "{{$payment_count > 0 ? $payment_count : 0}}"
    } );
} );
</script>

@endsection





