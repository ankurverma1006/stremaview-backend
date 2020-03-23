@extends('layouts.admin')

@section('title',tr('coupons'))

@section('content-header',tr('coupons'))

@section('breadcrumb')

	<li><a href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i>{{ tr('home') }}</a></li>
	<li class="active">{{ tr('coupons') }}</li>

@endsection

@section('content')

	<div class="row">

		<div class="col-xs-12">

			<div class="box box-primary">

				<div class="box-header label-primary">
					<b style="font-size: 18px;">{{ tr('coupons') }}</b>
					<a href="{{ route('admin.coupons.create') }}" class="btn btn-default pull-right">{{ tr('add_coupon') }}</a>
				</div>

				<div class="box-body">

					@if(count($coupons) > 0)
						
						<table id="datatable-withoutpagination" class="table table-bordered table-striped">
							<thead>
								<tr>
									<th>{{ tr('id') }}</th>
									<th>{{ tr('title') }}</th>
									<th>{{ tr('coupon_code') }}</th>
									<th>{{ tr('amount_type') }}</th>
									<th>{{ tr('amount') }}</th>
									<th>{{ tr('expiry_date') }}</th>
									<th>{{ tr('status') }}</th>
									<th>{{ tr('action') }}</th>
								</tr>
							</thead>
							<tbody>
								@foreach($coupons as $i => $coupon_details)
								<tr>
									<td>{{ showEntries($_GET, $i+1) }}</td>

									<td><a href="{{ route('admin.coupons.view', ['coupon_id'=>$coupon_details->id ] ) }}">{{ $coupon_details->title }}</a></td>

									<td>{{ $coupon_details->coupon_code }}</td>

									<td>
										@if($coupon_details->amount_type == 0)
										<span class="label label-primary">{{ tr('percentage') }}</span>
										@else
										<span class="label label-primary">{{ tr('absoulte') }}</span>
										@endif
									</td>

									<td>
										@if($coupon_details->amount_type == 0)
										{{ $coupon_details->amount }} %
										@else
										{{ Setting::get('currency') }} {{ $coupon_details->amount }} 
										@endif
									</td>

									<td>
										{{ date('d M y', strtotime($coupon_details->expiry_date)) }}	
									</td>

									<td>
										@if($coupon_details->status ==0)
										<span class="label label-warning">{{ tr('declined') }}</span>
										@else
										<span class="label label-success">{{ tr('approved') }}</span>
										@endif
									</td>

									<td>
										<ul class="admin-action btn btn-default">
										
											<li class="dropdown">
												<a class="dropdown-toggle" data-toggle="dropdown" href="#">
	                                                  {{ tr('action') }} <span class="caret"></span>
	                                            </a>
											
												<ul class="dropdown-menu dropdown-menu-right">

													<li role="presentation">
														<a class="menuitem" tabindex="-1" href="{{ route('admin.coupons.view',['coupon_id' => $coupon_details->id ] ) }}">{{ tr('view') }}</a>
													</li>

												    @if(Setting::get('admin_delete_control') == YES )

												    	<li role="presentation">
															<a class = "menuitem"  tabindex= "-1" href="javascript:;">{{ tr('edit') }}</a>
														</li>

														<li role="presentation">
															<a class="menuitem" tabindex="-1" href="javascript:;" onclick="return confirm(&quot;{{tr('coupon_delete_confirmation' , $coupon_details->title)}}&quot;);">{{ tr('delete') }}</a>
														</li>

												    @else

														<li role="presentation">
															<a class = "menuitem"  tabindex= "-1" href="{{ route('admin.coupons.edit',['coupon_id' => $coupon_details->id ] ) }}">{{ tr('edit') }}</a>
														</li>

														<li role="presentation">
															<a class="menuitem" tabindex="-1" href="{{ route('admin.coupons.delete', ['coupon_id'=>$coupon_details->id ] ) }}" onclick="return confirm(&quot;{{tr('coupon_delete_confirmation' , $coupon_details->title)}}&quot;);"> {{ tr('delete') }}</a>
														</li>

													@endif

													<li role="presentation" class="divider"></li>

													<li role="presentation">
														@if($coupon_details->status == APPROVED )
														
														<a class="menuitem" tabindex="-1" href="{{ route('admin.coupons.status',['coupon_id'=>$coupon_details->id ] ) }}" onclick="return confirm(&quot;{{ tr('coupon_decline_confirmation' , $coupon_details->title) }}&quot;);" >{{ tr('decline') }}</a>
														@else
														<a class="menuitem" tabindex="-1" href="{{ route('admin.coupons.status', ['coupon_id'=>$coupon_details->id ] ) }}" >{{ tr('approve') }} </a>
														@endif
													</li>
												</ul>

											</li>

										</ul>

									</td>

								</tr>

								@endforeach

							</tbody>
						
						</table>
						
						<div align="right" id="paglink"><?php echo $coupons->links(); ?></div>

					@else
						<h3 class="no-result">{{ tr('coupon_result_not_found_error') }}</h3>
					@endif
				</div>
			</div>
		</div>
	</div>


@endsection

