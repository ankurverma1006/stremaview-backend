@extends('layouts.admin')

@section('title', tr('redeems'))

@section('content-header')

 	{{tr('redeems')}} 

 	@if($moderator_details)
 		- <a  href="{{ route('admin.moderators.view' , ['moderator_id' => $moderator_details->id] ) }}"> {{ $moderator_details->name }} </a>
 	@endif

@endsection

@section('breadcrumb')

    <li><a href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i>{{ tr('home') }}</a></li>

    <li><a href="{{ route('admin.moderators.index') }}"><i class="fa fa-users"></i> {{ tr('moderators') }}</a></li>

    @if($moderator_details)
    	<li><a href="{{ route('admin.moderators.view', ['moderator_id' => $moderator_details->id] ) }}"><i class="fa fa-users"></i> {{ tr('view_moderator') }}</a></li>
    @endif
    
    <li class="active"><i class="fa fa-trophy"></i> {{ tr('redeems') }}</li>
@endsection

@section('content')

	<div class="row">

        <div class="col-xs-12">

          	<div class="box box-primary">

	          	<div class="box-header label-primary">

	                <b style="font-size:18px;">{{ tr('redeems') }}</b>

	                <a href="{{ route('admin.moderators.index') }}" class="btn btn-default pull-right">{{ tr('view_moderators') }}</a>

	            </div>
            	
            	<div class="box-body">

					<table id="example1" class="table table-bordered table-striped">

						<thead>
						    <tr>
						      <th>{{ tr('id') }}</th>
						      <th>{{ tr('moderator') }}</th>
						      <th>{{ tr('redeem_amount') }}</th>
						      <th>{{ tr('paid_amount') }}</th>
						      <th>{{ tr('payment_mode') }}</th>
						      <th>{{ tr('sent_date') }}</th>
						      <th>{{ tr('status') }}</th>
						      <th>{{ tr('action') }}</th>
						    </tr>						
						</thead>

						<tbody>

							@foreach($redeem_requests as $i => $redeem_request_details)

							    <tr>

							      	<td>{{ $i+1 }}</td>

							      	<td>
							      		<a href="{{ route('admin.moderators.view' , ['moderator_id' => $redeem_request_details->moderator_id] ) }}">
							      			{{ $redeem_request_details->moderator ? $redeem_request_details->moderator->name : "" }}
							      		</a>
							      	</td>

							      	<td><b>{{ Setting::get('currency') }} {{ $redeem_request_details->request_amount }}</b></td>

							      	<td><b>{{ Setting::get('currency') }} {{ $redeem_request_details->paid_amount }}</b></td>

							      	<td class="text-uppercase"><b>{{ $redeem_request_details->payment_mode }}</b></td>

							      	<td>{{ $redeem_request_details->created_at ? $redeem_request_details->created_at->diffForHumans() : "" }}</td>

							      	<td><b>{{ redeem_request_status($redeem_request_details->status) }}</b></td>
							 
							      	<td>

							      		@if(in_array($redeem_request_details->status ,[REDEEM_REQUEST_SENT , REDEEM_REQUEST_PROCESSING]))

								      		<form action="{{ route('admin.moderators.payout.invoice') }}" method="get">

								      			<input type="hidden" name="redeem_request_id" value="{{ $redeem_request_details->id }}">

								      			<input type="hidden" name="paid_amount" value="{{ $redeem_request_details->request_amount }}">

								      			<input type="hidden" name="moderator_id" value="{{ $redeem_request_details->moderator_id }}">

								      			<?php $confirm_message = tr('are_you_sure'); ?>

								      			<button type="submit" class="btn btn-success btn-sm">{{ tr('paynow') }}</button>
								      		</form>

								      	@else
								      		<span>-</span>
							      		@endif

							      	</td>

							    </tr>
							    
							@endforeach
						
						</tbody>

					</table>

				</div>

			</div>

		</div>

	</div>

@endsection