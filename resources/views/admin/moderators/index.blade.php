@extends('layouts.admin')

@section('title', tr('moderators'))

@section('content-header', tr('moderators'))

@section('breadcrumb')
	<li><a href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i>{{ tr('home') }}</a></li>
    
    <li class="active"><i class="fa fa-users"></i> {{ tr('moderators') }}</li>

@endsection

@section('content')

	<div class="row">

        <div class="col-xs-12">

          	<div class="box box-primary">
          	
	          	<div class="box-header label-primary">
	                <b style="font-size:18px;">{{ tr('moderators') }}</b>
	                <a href="{{ route('admin.moderators.create') }}" class="btn btn-default pull-right">{{ tr('add_moderator') }}</a>

	                <!-- EXPORT OPTION START -->

					@if(count($moderators) > 0 )
	                
		                <ul class="admin-action btn btn-default pull-right" style="margin-right: 20px">
		                 	
							<li class="dropdown">
				                <a class="dropdown-toggle" data-toggle="dropdown" href="#">
				                  {{ tr('export') }} <span class="caret"></span>
				                </a>
				                <ul class="dropdown-menu">
				                  	<li role="presentation">
				                  		<a role="menuitem" tabindex="-1" href="{{ route('admin.moderators.export' , ['format' => 'XLSX']) }}">
				                  			<span class="text-red"><b>{{ tr('excel_sheet') }}</b></span>
				                  		</a>
				                  	</li>

				                  	<li role="presentation">
				                  		<a role="menuitem" tabindex="-1" href="{{ route('admin.moderators.export' , ['format' => 'csv']) }}">
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

	            	@if(count($moderators) > 0)

		              	<table id="datatable-withoutpagination" class="table table-bordered table-striped">

							<thead>
							    <tr>
							      <th>{{ tr('id') }}</th>
							      <th>{{ tr('username') }}</th>
							      <th>{{ tr('email') }}</th>
							      <th>{{ tr('mobile') }}</th>
							      <th>{{ tr('address') }}</th>
							      <th>{{ tr('total_videos') }}</th>
							      <th>{{ tr('total') }}</th>
							      <th>{{ tr('status') }}</th>
							      <th>{{ tr('action') }}</th>
							    </tr>
							</thead>

							<tbody>

								@foreach($moderators as $i => $moderator_details)

								    <tr>
								      <td>{{ showEntries($_GET, $i+1) }}</td>
								      <td><a href="{{ route('admin.moderators.view',['moderator_id' => $moderator_details->id] ) }}">{{ $moderator_details->name }}</a></td>
								      <td>{{ $moderator_details->email }}</td>
								      <td>{{ $moderator_details->mobile }}</td>
								      <td>{{ $moderator_details->address }}</td>
								      <td><a href="{{ route('admin.moderators.videos.index',['moderator_id' => $moderator_details->id] ) }}" >{{ $moderator_details->moderatorVideos ? $moderator_details->moderatorVideos->count() : "0.00" }}</a></td>
								      <td>{{ Setting::get('currency') }} {{ $moderator_details->total }}</td>

								      <td>
								      		@if($moderator_details->is_activated)
								      			<span class="label label-success">{{ tr('approved') }}</span>
								       		@else
								       			<span class="label label-warning">{{ tr('pending') }}</span>
								       		@endif
								      </td>
								      
								      <td>
	            							<ul class="admin-action btn btn-default">
	            								<li class="dropup">

									                <a class="dropdown-toggle" data-toggle="dropdown" href="#">
									                  {{ tr('action') }} <span class="caret"></span>
									                </a>

									                <ul class="dropdown-menu dropdown-menu-right">

									                  	<li role="presentation"><a role="menuitem" tabindex="-1" href="{{ route('admin.moderators.view',['moderator_id' => $moderator_details->id] ) }}">{{ tr('view') }}</a></li>

									                	@if(Setting::get('admin_delete_control'))
                                                           	
                                                           	<li role="presentation"><a role="button" href="javascript:;" class="btn disabled" style="text-align: left">{{ tr('edit') }}</a></li>

                                                        	<li><a role="button" href="javascript:;" class="btn disabled" style="text-align: left">{{ tr('delete') }}</a></li>
                                                        @else
                                                        	<li role="presentation"><a role="menuitem" tabindex="-1" href="{{ route('admin.moderators.edit',['moderator_id' => $moderator_details->id] ) }}">{{ tr('edit') }}</a></li>

									                  		<li role="presentation">
									                  			<a role="menuitem" tabindex="-1" onclick="return confirm(&quot;{{ tr('admin_moderator_delete_confirmation' , $moderator_details->name) }}&quot;);" href="{{ route('admin.moderators.delete', ['moderator_id' => $moderator_details->id] ) }}">{{ tr('delete') }}</a>
									                  		</li>

                                                        @endif
									                  
									                  	<li role="presentation" class="divider"></li>
									                  	@if($moderator_details->is_activated)
									                		<li role="presentation"><a role="menuitem" tabindex="-1" onclick="return confirm(&quot;{{ tr('moderator_decline_confirmation' , $moderator_details->name) }}&quot;);" href="{{ route('admin.moderators.status.change', ['moderator_id' => $moderator_details->id] ) }}">{{ tr('decline') }}</a></li>
									                	@else
									                  		<li role="presentation"><a role="menuitem" tabindex="-1" onclick="return confirm(&quot;{{ tr('moderator_approve_confirmation' , $moderator_details->name) }}&quot;);"  href="{{ route('admin.moderators.status.change', ['moderator_id' => $moderator_details->id] ) }}">{{ tr('approve') }}</a></li>
									                  	@endif
									                  	
									                  	<li role="presentation" class="divider"></li>

									                  	<li role="presentation"><a role="menuitem" tabindex="-1" href="{{ route('admin.moderators.redeems',['moderator_id' => $moderator_details->id] ) }}">{{ tr('redeems') }}</a></li>

									                  	<li>

									                  	<li role="presentation">
									                  		<a role="menuitem" tabindex="-1" href="{{ route('admin.videos' ,['moderator_id' => $moderator_details->id] ) }}">{{ tr('videos') }}</a>
									                  	</li>

									                  	
									                </ul>

	              								</li>

	            							</ul>
								      </td>
								      
								    </tr>
								@endforeach
							</tbody>
						
						</table>

						<div align="right" id="paglink"><?php echo $moderators->links(); ?></div>
					@else
						<h3 class="no-result">{{ tr('no_result_found') }}</h3>
					@endif
	            
	            </div>
          	
          	</div>

        </div>
    </div>

@endsection
