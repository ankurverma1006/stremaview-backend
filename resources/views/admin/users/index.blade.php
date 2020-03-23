@extends('layouts.admin')

@section('title', tr('users'))

@section('content-header') 

	{{ tr('users') }} 

	<a href="#" id="help-popover" class="btn btn-danger" style="font-size: 14px;font-weight: 600" title="{{ tr('any_help') }}">{{ tr('help_ques_mark') }}
	</a>

	<div id="help-content" style="display: none">

	    <ul class="popover-list">
	        <li><span class="text-green"><i class="fa fa-check-circle"></i></span> - {{ tr('paid_subscribed_users') }}</li>
	        <li><span class="text-red"><i class="fa fa-times"></i></span> -{{ tr('unpaid_unsubscribed_user') }}</li>
	        <li><b>{{ tr('validity_days') }} - </b>{{ tr('expiry_days_subscription_user') }}</li>
	        <li><b>{{ tr('upgrade') }} - </b>{{ tr('admin_moderator_upgrade_option') }}</li>
	    </ul>
	    
	</div>

@endsection

@section('breadcrumb')
    <li><a href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i>{{ tr('home') }}</a></li>
    <li class="active"><i class="fa fa-user"></i> {{ tr('users') }}</li>
@endsection

@section('content')

	<div class="row">

        <div class="col-xs-12">
          	
          	<div class="box box-primary">
          	
	          	<div class="box-header label-primary">
	                
	          		<b style="font-size:18px;">
	          			{{ tr('users') }} @if(isset($subscription))- 
	          			<a style="color: white;text-decoration: underline;" href="{{ route('admin.subscriptions.view' ,['subscription_id' => $subscription->id] ) }}"> 
	          				{{  $subscription->title  }}
	          			</a>@endif
	          		</b>
	              
	                <a href="{{ route('admin.users.create') }}" class="btn btn-default pull-right">{{ tr('add_user') }}</a>

	                <!-- EXPORT OPTION START -->

					@if(count($users) > 0 )
	                
		                <ul class="admin-action btn btn-default pull-right" style="margin-right: 20px">
		                 	
							<li class="dropdown">
				                <a class="dropdown-toggle" data-toggle="dropdown" href="#">
				                  {{ tr('export') }} <span class="caret"></span>
				                </a>
				                <ul class="dropdown-menu">
				                  	<li role="presentation">
				                  		<a role="menuitem" tabindex="-1" href="{{ route('admin.users.export' , ['format' => 'XLSX']) }}">
				                  			<span class="text-red"><b>{{ tr('excel_sheet') }}</b></span>
				                  		</a>
				                  	</li>

				                  	<li role="presentation">
				                  		<a role="menuitem" tabindex="-1" href="{{ route('admin.users.export' , ['format' => 'csv']) }}">
				                  			<span class="text-blue"><b>{{ tr('csv') }}</b></span>
				                  		</a>
				                  	</li>
				                </ul>
							</li>
						</ul>

					@endif

	                <!-- EXPORT OPTION END -->

	            </div>

            
	            <div class="box-body">

	            	<div class="table-responsive" style="padding: 35px 0px"> 
	            		
		            	@if(count($users) > 0)

		            		<div class="table table-responsive">
				              	
				              	<table id="datatable-withoutpagination" class="table table-bordered table-striped ">

									<thead>
									    <tr>
											<th>{{ tr('id') }}</th>
											<th>{{ tr('username') }}</th>
											<th>{{ tr('email') }}</th>
											<th>{{ tr('upgrade') }}</th>
											<th>{{ tr('active_plan') }}</th>
											<th>{{ tr('sub_profiles') }}</th>
											<th>{{ tr('clear_login') }}</th>
											<th>{{ tr('status') }}</th>
											<th>{{ tr('action') }}</th>
									    </tr>
									
									</thead>

									<tbody>
										@foreach($users as $i => $user_details)

										    <tr>
										      	<td>{{ showEntries($_GET, $i+1) }}</td>

										      	<td>
										      		<a href="{{ route('admin.users.view' , ['user_id' => $user_details->id] ) }}">{{ $user_details->name }}

										      			@if($user_details->user_type)

										      				<span class="text-green pull-right"><i class="fa fa-check-circle"></i></span>

										      			@else

										      				<span class="text-red pull-right"><i class="fa fa-times"></i></span>

										      			@endif
										      		</a>
										      	</td>

										      	<td>{{ $user_details->email }}</td>
										      	
										      	<td>
										      		@if($user_details->is_moderator)
										      			<a onclick="return confirm(&quot;{{ tr('disable_user_to_moderator',$user_details->name) }}&quot;);" href="{{ route('admin.users.upgrade.disable' , ['user_id' => $user_details->id, 'moderator_id' => $user_details->moderator_id] ) }}" class="label label-warning" title="{{  tr('admin_user_remove_moderator_role')  }}">{{ tr('disable') }}</a>
										      		@else
										      			<a onclick="return confirm(&quot;{{ tr('upgrade_user_to_moderator',$user_details->name) }}&quot;);" href="{{ route('admin.users.upgrade' , ['user_id' => $user_details->id ] ) }}" class="label label-danger" title="{{ tr('admin_user_change_to_moderator_role') }}">{{ tr('upgrade') }}</a>
										      		@endif

										      	</td>

										      	<td>
										      		<?php echo active_plan($user_details->id);?>

										      		@if($user_details->user_type)
				                                        <br>
				                                        ({{ get_expiry_days($user_details->id) }} days)
				                                    @endif
										      		
										      	</td>

										      	<td>
										      		<a role="menuitem" tabindex="-1" href="{{ route('admin.users.subprofiles',['user_id' => $user_details->id ] ) }}"><span class="label label-primary">
										      			{{ count($user_details->subProfile) }} {{ tr('sub_profiles') }}</span>
										      		</a>
										      	</td>
										      	<td class="text-center">
						      		
										      		<a href="{{ route('admin.users.clear-login',['user_id' => $user_details->id ] ) }}"><span class="label label-warning">{{ tr('clear') }}</span></a>

										      	</td>

										      	<td>
											      	@if($user_details->is_activated)

											      		<span class="label label-success">{{ tr('approved') }}</span>

											      	@else

											      		<span class="label label-warning">{{ tr('pending') }}</span>

											      	@endif

										     	</td>
										 
										      	<td>

			            							<ul class="admin-action btn btn-default">

			            								<li class="@if($i < 2) dropdown @else dropup @endif">
											                <a class="dropdown-toggle" data-toggle="dropdown" href="#">
											                  {{ tr('action') }} <span class="caret"></span>
											                </a>
											                <ul class="dropdown-menu dropdown-menu-right">

											                  	<li role="presentation"><a role="menuitem" tabindex="-1" href="{{ route('admin.users.view' , ['user_id' => $user_details->id ] ) }}">{{ tr('view') }}</a></li>

											                @if(Setting::get('admin_delete_control') == YES )
											                	<li role="presentation"><a role="menuitem" tabindex="-1" href="javascript:;">{{ tr('edit') }}</a></li>

											                	@if(get_expiry_days($user_details->id) > 0)

										                  	 		<li role="presentation"><a role="menuitem" tabindex="-1" 	  href="javascript:;">{{ tr('delete') }}
										                  			</a></li>

											                  	@else 

										                  			<li role="presentation"><a role="menuitem" tabindex="-1" onclick="return confirm(&quot;{{ tr('admin_user_delete_confirmation' , $user_details->name) }}&quot;);" href="javascript:;">{{ tr('delete') }}
										                  			</a></li>

											                  	@endif

												                <li role="presentation" class="divider"></li>	
											                @else    

											                  	<li role="presentation"><a role="menuitem" tabindex="-1" href="{{ route('admin.users.edit' , ['user_id' => $user_details->id ] ) }}">{{ tr('edit') }}</a></li>

											                  	<li>
											                  		@if(get_expiry_days($user_details->id) > 0)

											                  	 		<a role="menuitem" tabindex="-1" onclick="return confirm(&quot;{{ tr('admin_user_delete_with_expiry_days_confirmation' , get_expiry_days($user_details->id ) ) }}&quot;);"  href="{{ route('admin.users.delete', ['user_id' => $user_details->id ]) }}">{{ tr('delete') }}
											                  			</a>

											                  		@else 

											                  			<a role="menuitem" tabindex="-1" onclick="return confirm(&quot;{{ tr('admin_user_delete_confirmation' , $user_details->name) }}&quot;);" href="{{ route('admin.users.delete', ['user_id' => $user_details->id ] ) }}">{{ tr('delete') }}
											                  			</a>

											                  	 	@endif
											                  	</li>

												                <li role="presentation" class="divider"></li>	

											                @endif

											                  	@if($user_details->is_activated)
											                  		<li role="presentation"><a role="menuitem" onclick="return confirm(&quot;{{  $user_details->name  }} - {{ tr('user_decline_confirmation') }}&quot;);" tabindex="-1" href="{{ route('admin.users.status.change' , ['user_id' => $user_details->id ] ) }}"> {{ tr('decline') }}</a></li>
											                  	@else 
											                  	 	<li role="presentation"><a role="menuitem" onclick="return confirm(&quot;{{ $user_details->name }} - {{ tr('user_approve_confirmation') }}&quot;);" tabindex="-1" href="{{ route('admin.users.status.change' , ['user_id' => $user_details->id ] ) }}"> 
											                  		{{ tr('approve') }} </a></li>
											                  	@endif

											                  	@if(!$user_details->is_verified)
											                  	
												                  	<li role="presentation" class="divider"></li>								                  	
														      		<li role="presentation">
												                  		<a role="menuitem" tabindex="-1" href="{{ route('admin.users.verify' , ['user_id' => $user_details->id ] ) }}">{{ tr('verify') }}</a>
											                  		</li>
													      		@endif


											                  	<li role="presentation" class="divider"></li>

											                  	<li role="presentation">
											                  		<a role="menuitem" tabindex="-1" href="{{ route('admin.users.subprofiles',  ['user_id' => $user_details->id ] ) }}">{{ tr('sub_profiles') }}</a>
											                  	</li>

											                  	
											                  	<li role="presentation" class="divider"></li>
											                  	<li>
																	<a href="{{ route('admin.subscriptions.plans' , ['user_id' => $user_details->id] ) }}">		
																		<span class="text-green"><b><i class="fa fa-eye"></i>&nbsp;{{ tr('subscription_plans') }}</b></span>
																	</a>

																</li>               	

											                </ul>

			              								</li>

			            							</ul>
										      	
										      	</td>

										    </tr>

										@endforeach
									
									</tbody>
								
								</table>

								<div align="right" id="paglink"><?php echo $users->links(); ?></div>

							</div>
						
						@else
							<h3 class="no-result">{{ tr('no_user_found') }}</h3>
						@endif

					</div>

	            </div>

          	</div>

        </div>
    
    </div>

@endsection
