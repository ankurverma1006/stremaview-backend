@extends('layouts.admin')

@section('title', tr('subscriptions'))

@section('content-header', tr('subscriptions'))

@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
    <li class="active"><i class="fa fa-key"></i> {{tr('subscriptions')}}</li>
@endsection

@section('content')

	@include('notification.notify')

	<div class="row">
        <div class="col-xs-12">
          	
          	<div class="box box-primary">

	          	<div class="box-header label-primary">
	                <b>{{tr('subscriptions')}}</b>
	                <a href="{{route('admin.subscriptions.create')}}" style="float:right" class="btn btn-default">{{tr('add_subscription')}}</a>
	            </div>
	            
	            <div class="box-body">

	              	<table id="datatable-withoutpagination" class="table table-bordered table-striped">

						<thead>
						    <tr>
						      	<th>{{tr('id')}}</th>
						      	<th>{{tr('title')}}</th>
						      	<th>{{tr('no_of_months')}}</th>
						      	<th>{{tr('amount')}}</th>
						      	<th>{{tr('status')}}</th>
						      	<th>{{tr('popular')}}</th>
						      	<th>{{tr('no_of_account')}}</th>
						      	<th>{{tr('subscribers')}}</th>
						      	<th>{{tr('action')}}</th>
						    </tr>
						</thead>

						<tbody>
						
							@foreach($data as $i => $value)

							    <tr>
							      	<td>{{showEntries($_GET, $i+1)}}</td>
							      	<td><a href="{{route('admin.subscriptions.view' , $value->unique_id)}}">{{$value->title}}</a></td>
							      	<td>{{$value->plan}}</td>
							      	<td>{{Setting::get('currency' , "$")}} {{$value->amount}}</td>

							      	<td class="text-center">

						      			@if($value->status)
							      			<span class="label label-success">{{tr('approved')}}</span>
							      		@else
							      			<span class="label label-warning">{{tr('pending')}}</span>
							      		@endif
							      	</td>

							      	<td class="text-center">

						      			@if($value->popular_status)

						      				<a href="{{route('admin.subscriptions.popular.status' , $value->unique_id)}}" class="btn  btn-xs btn-danger">
					              				{{tr('remove_popular')}}
					              			</a>

							      		@else

							      			<a href="{{route('admin.subscriptions.popular.status' , $value->unique_id)}}" class="btn  btn-xs btn-success">

					              				{{tr('mark_popular')}}

					              			</a>

							      		@endif
							      		
							      	</td>

							      	<td>{{$value->no_of_account}}</td>

							      	<td><a href="{{route('admin.subscriptions.users' , $value->id)}}"> {{$value->userSubscription()->where('subscription_id',$value->id)->where('status' , 1)->count()}}</a></td>
							      
									<td>
										<ul class="admin-action btn btn-default">

											<li class="dropdown">

									            <a class="dropdown-toggle" data-toggle="dropdown" href="#">
									              {{tr('action')}} <span class="caret"></span>
									            </a>

									            <ul class="dropdown-menu dropdown-menu-right">

									              	<li role="presentation">
									              		<a role="menuitem" tabindex="-1" href="{{route('admin.subscriptions.edit' , $value->unique_id)}}"><i class="fa fa-edit"></i>&nbsp;{{tr('edit')}}
									              		</a>
									              	</li>

									              	<li role="presentation">
									              		<a role="menuitem" tabindex="-1" href="{{route('admin.subscriptions.view' , $value->unique_id)}}"><span class="text-blue"><b><i class="fa fa-eye"></i>&nbsp;{{tr('view')}}</b></span>
									              		</a>
									              	</li>

									    
									              	<li role="presentation" class="divider"></li>

									              	<li role="presentation">
									              			<a role="menuitem" tabindex="-1" href="{{route('admin.subscriptions.users' , $value->id)}}">
									              				<span class="text-green"><b><i class="fa fa-user"></i>&nbsp;{{tr('subscribers')}}</b></span>
									              			</a>
									              		</li>
									              	
									              	<li role="presentation" class="divider"></li>

									              	@if($value->status)

									              		<li role="presentation">
									              			<a role="menuitem" tabindex="-1" onclick="return confirm(&quot;{{tr('subscription_decline_confirmation' , $value->title)}}&quot;)" href="{{route('admin.subscriptions.status' , $value->unique_id)}}">
									              				<span class="text-red"><b><i class="fa fa-close"></i>&nbsp;{{tr('decline')}}</b></span>
									              			</a>
									              		</li>

									              	@else

														<li role="presentation">
									              			<a role="menuitem" tabindex="-1"  onclick="return confirm(&quot;{{tr('subscription_approve_confirmation' , $value->title)}}&quot;)"  href="{{route('admin.subscriptions.status' , $value->unique_id)}}">
									              				<span class="text-green"><b><i class="fa fa-check"></i>&nbsp;{{tr('approve')}}</b></span>
									              			</a>
									              		</li>								              	

									              	@endif								       								              									        
									              	<li role="presentation" class="divider"></li>								            

									              	<li role="presentation">

														@if(Setting::get('admin_delete_control'))
															<a role="button" href="javascript:;" class="btn disabled" style="text-align: left"><i class="fa fa-trash"></i>&nbsp;{{tr('delete')}}</a>
														@else
															<a role="menuitem" tabindex="-1"onclick="return confirm(&quot;{{tr('subscription_delete_confirmation' , $value->title)}}&quot;)"  href="{{route('admin.subscriptions.delete', array('id' => $value->id))}}"><i class="fa fa-trash"></i>&nbsp;{{tr('delete')}}</a>
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

					@if(count($data) > 0) <div align="right" id="paglink"><?php echo $data->links(); ?></div> @endif
				
	            </div>
          	
          	</div>
        </div>
    </div>

@endsection

@section('scripts')

@endsection