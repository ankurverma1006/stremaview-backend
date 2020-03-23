@extends('layouts.moderator')

@section('title', tr('revenues'))

@section('content-header') 

{{tr('revenues') }} {{Setting::get('currency')}} {{$redeem_details ? $redeem_details->total_moderator_amount : "0.00"}} 

<a href="#" id="help-popover" class="btn btn-danger" style="font-size: 14px;font-weight: 600;border-radius:50%" title="{{tr('any_help')}}"><i class="fa fa-question"></i></a>

<div id="help-content" style="display: none">

	<h5 class="popover-h5">{{tr('useage_moderator_ppv_help')}}</h5>

    <ul class="popover-list">
        <li><b>{{tr('ppv')}} - </b>{{tr('pay_per_view')}}</li>
        <li><b>{{tr('watch_count_revenue')}} - </b> {{tr('revenue_details_view_count')}}</li>
        <li><b>{{tr('ppv_revenue')}} - </b> {{tr('revenue_based_ppv')}} </li>
    </ul>
    
</div>

@endsection

@section('breadcrumb')
    <li class="active"><i class="fa fa-credit-card	"></i> {{tr('revenues')}}</li>
@endsection

@section('content')

@include('notification.notify')

	<div class="row">

		<div class="col-lg-3 col-xs-6">

		    <div class="small-box bg-maroon">
		        <div class="inner">
		            <h3>{{Setting::get('currency')}} {{$total_revenue}}</h3>
		            <p>{{tr('total_revenue')}}</p>
		        </div>

		        <div class="icon"><i class="fa fa-money"></i></div>

		        <!-- <a class="small-box-footer">
		        	{{tr('more_info')}}
		        	<i class="fa fa-arrow-circle-right"></i>
		        </a> -->
		    </div>

		</div>

		<div class="col-lg-3 col-xs-6">

		    <div class="small-box bg-blue">
		        <div class="inner">
		            <h3>{{Setting::get('currency')}} {{$watch_count_revenue}}</h3>
		            <p>{{tr('watch_count_revenue')}}</p>
		        </div>

		        <div class="icon"><i class="fa fa-eye"></i></div>

		        <!-- <a class="small-box-footer">
		        	{{tr('more_info')}}
		        	<i class="fa fa-arrow-circle-right"></i>
		        </a> -->
		    </div>

		</div>

		<div class="col-lg-3 col-xs-6">

		    <div class="small-box bg-yellow">
		        <div class="inner">
		            <h3>{{Setting::get('currency')}} {{$ppv_revenue}}</h3>
		            <p>{{tr('ppv_revenue')}}</p>
		        </div>

		        <div class="icon"><i class="fa fa-video-camera"></i></div>

		        <!-- <a class="small-box-footer">
		        	{{tr('more_info')}}
		        	<i class="fa fa-arrow-circle-right"></i>
		        </a> -->
		    </div>

		</div>

        <div class="col-xs-12">
          	<div class="box">

          		<div class="box-header">
	                <b style="font-size:18px;">{{tr('revenues')}}</b>
	            </div>


	            <div class="box-body">

	            	@if(count($payments) > 0)

		              	<table id="example1" class="table table-bordered table-striped">

							<thead>
							    <tr>
							      <th>{{tr('id')}}</th>
							      <th>{{tr('video')}}</th>
							      <th>{{tr('viewers_cnt')}}</th>
							      <th>{{tr('watch_count_revenue')}}</th>
							      <th>{{tr('ppv_revenue')}}</th>
							      <th>{{tr('total')}}</th>
							    </tr>
							</thead>

							<tbody>
								
								@foreach($payments as $i => $payment)

								    <tr>
								      	<td>{{$i+1}}</td>
								      	
								      	<td>
								      		<a href="{{route('moderator.videos.view' , ['id' => $payment->id])}}">
								      			{{$payment->title}}
								      		</a>
								      	</td>

								      	<td> {{$payment->watch_count}}</td>

								      	<td>{{Setting::get('currency')}} {{$payment->redeem_amount}}</td>

								      	<td>{{Setting::get('currency')}} {{$payment->user_amount}}</td>
								      	
								      	<td>{{Setting::get('currency')}} {{$payment->user_amount+$payment->redeem_amount}}</td>
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


