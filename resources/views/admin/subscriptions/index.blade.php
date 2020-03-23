@extends('layouts.admin')

@section('title', tr('subscriptions'))

@section('content-header', tr('subscriptions'))

@section('breadcrumb')
    <li><a href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i>{{ tr('home') }}</a></li>
    <li class="active"><i class="fa fa-key"></i> {{ tr('subscriptions') }}</li>
@endsection

@section('content')

	<div class="row">

        <div class="col-xs-12">
          	
          	<div class="box box-primary">

	          	<div class="box-header label-primary">
	                <b>{{ tr('subscriptions') }}</b>
	                <a href="{{ route('admin.subscriptions.create') }}" style="float:right" class="btn btn-default">{{ tr('add_subscription') }}</a>
	            </div>
	            
	            <div class="box-body">

	              	<table id="datatable-withoutpagination" class="table table-bordered table-striped">

						<thead>
						    <tr>
						      	<th>{{ tr('id') }}</th>
						      	<th>{{ tr('title') }}</th>
						      	<th>{{ tr('no_of_months') }}</th>
						      	<th>{{ tr('amount') }}</th>
						      	<th>{{ tr('status') }}</th>
						      	<th>{{ tr('popular') }}</th>
						      	<th>{{ tr('no_of_account') }}</th>
						      	<th>{{ tr('subscribers') }}</th>
						      	<th>{{ tr('action') }}</th>
						    </tr>
						</thead>

						<tbody>
						
							@foreach($subscriptions as $i => $subscription_details)

							    <tr>
							      	<td>{{ showEntries($_GET, $i+1) }}</td>

							      	<td><a href="{{ route('admin.subscriptions.view' , ['subscription_id' => $subscription_details->id] ) }}">{{ $subscription_details->title }}</a></td>

							      	<td>{{ $subscription_details->plan }}</td>

							      	<td>{{ Setting::get('currency' , "$") }} {{ $subscription_details->amount }}</td>

							      	<td class="text-center">

						      			@if($subscription_details->status)
							      			<span class="label label-success">{{ tr('approved') }}</span>
							      		@else
							      			<span class="label label-warning">{{ tr('pending') }}</span>
							      		@endif
							      	</td>

							      	<td class="text-center">
						      			@if($subscription_details->popular_status)

						      				<a href="{{ route('admin.subscriptions.popular.status' , ['subscription_id' => $subscription_details->id] ) }}" class="btn  btn-xs btn-danger">
					              				{{ tr('remove_popular') }}
					              			</a>
							      		@else

							      			<a href="{{ route('admin.subscriptions.popular.status' , ['subscription_id' => $subscription_details->id] ) }}" class="btn  btn-xs btn-success">
					              				{{ tr('mark_popular') }}
					              			</a>
							      		@endif							      		
							      	</td>

							      	<td>{{ $subscription_details->no_of_account }}</td>

							      	<td>
							      		<a href="{{ route('admin.subscriptions.users' , ['subscription_id' => $subscription_details->id] ) }}"> {{ $subscription_details->userSubscription()->where('subscription_id',$subscription_details->id)->where('status' , 1)->count() }}</a>
							      	</td>
							      
									<td>

										<ul class="admin-action btn btn-default">

											<li class="dropdown">

									            <a class="dropdown-toggle" data-toggle="dropdown" href="#">
									              {{ tr('action') }} <span class="caret"></span>
									            </a>

									            <ul class="dropdown-menu dropdown-menu-right">

									              	<li role="presentation">
									              		<a role="menuitem" tabindex="-1" href="{{ route('admin.subscriptions.view' , ['subscription_id' => $subscription_details->id] ) }}"><span class="text-blue"><b><i class="fa fa-eye"></i>&nbsp;{{ tr('view') }}</b></span>
									              		</a>
									              	</li>			            


													@if(Setting::get('admin_delete_control') == YES )

														<li role="presentation"><a role="button" href="javascript:;" class="btn disabled" style="text-align: left"><i class="fa fa-trash"></i>&nbsp;{{ tr('delete') }}</a></li>

														<li role="presentation">
										              		<a role="menuitem" tabindex="-1" href="javascript:;" ><i class="fa fa-edit"></i>&nbsp;{{ tr('edit') }}
										              		</a>
										              	</li>

													@else

														<li role="presentation"><a role="menuitem" tabindex="-1" onclick="return confirm(&quot;{{ tr('subscription_delete_confirmation' , $subscription_details->title) }}&quot;)"  href="{{ route('admin.subscriptions.delete', ['subscription_id' => $subscription_details->id] ) }}"><i class="fa fa-trash"></i>&nbsp;{{ tr('delete') }}</a></li>

														<li role="presentation">
										              		<a role="menuitem" tabindex="-1" href="{{ route('admin.subscriptions.edit' , ['subscription_id' => $subscription_details->id] ) }}"><i class="fa fa-edit"></i>&nbsp;{{ tr('edit') }}
										              		</a>
										              	</li>

													@endif		  	
									    
									              	<li role="presentation" class="divider"></li>

								              		<li role="presentation">
								              			<a role="menuitem" tabindex="-1" href="{{ route('admin.subscriptions.users' , ['subscription_id' => $subscription_details->id] ) }}">
								              			<span class="text-green"><b><i class="fa fa-user"></i>&nbsp;{{ tr('subscribers') }}</b></span>
								              			</a>
								              		</li>
									              	
									              	<li role="presentation" class="divider"></li>

									              	@if($subscription_details->status == APPROVED )

									              		<li role="presentation">
									              			<a role="menuitem" tabindex="-1" onclick="return confirm(&quot;{{ tr('subscription_decline_confirmation' , $subscription_details->title) }}&quot;)" href="{{ route('admin.subscriptions.status.change' , ['subscription_id' => $subscription_details->id] ) }}">
									              				<span class="text-red"><b><i class="fa fa-close"></i>&nbsp;{{ tr('decline') }}</b></span>
									              			</a>
									              		</li>

									              	@else

														<li role="presentation">
									              			<a role="menuitem" tabindex="-1"  onclick="return confirm(&quot;{{ tr('subscription_approve_confirmation' , $subscription_details->title) }}&quot;)"  href="{{ route('admin.subscriptions.status.change' ,  ['subscription_id' => $subscription_details->id] ) }}">
									              			<span class="text-green"><b><i class="fa fa-check"></i>&nbsp;{{ tr('approve') }}</b></span>
									              			</a>
									              		</li>      	

									              	@endif		              									        
									              	<li role="presentation" class="divider"></li>

									            </ul>
											
											</li>

										</ul>

									</td>
							    </tr>
							
							@endforeach

						</tbody>
					
					</table>

					@if(count($subscriptions) > 0) <div align="right" id="paglink"><?php echo $subscriptions->links(); ?></div> @endif
				
	            </div>
          	
          	</div>

        </div>

    </div>

@endsection