@extends('layouts.admin')

@section('title', tr('spam_videos'))

@section('content-header', tr('spam_videos'))

@section('breadcrumb')
    <li><a href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i>{{ tr('home') }}</a></li>
    <li class="active"><i class="fa fa-flag"></i> {{ tr('spam_videos') }}</li>
@endsection

@section('content')

	<div class="row">
        
        <div class="col-xs-12">
         	
         	<div class="box">
            
	            <div class="box-body">

	            	@if(count($spam_videos) > 0)

		              	<table id="datatable-withoutpagination" class="table table-bordered table-striped">

							<thead>
							    <tr>
							      <th>{{ tr('id') }}</th>
							      <th>{{ tr('category') }}</th>
							      <th>{{ tr('sub_category') }}</th>
							      <th>{{ tr('genre') }}</th>
							      <th>{{ tr('title') }}</th>
							      <th>{{ tr('user_count') }}</th>
							      <th>{{ tr('status') }}</th>
							      <th>{{ tr('action') }}</th>
							    </tr>
							</thead>

							<tbody>
								
								@foreach($spam_videos as $i => $spam_video_details)

								    <tr>
								      	<td>{{ showEntries($_GET, $i+1) }}</td>
								      	
								      	<td>{{ $spam_video_details->adminVideo->category->name }}</td>
								      	
								      	<td>{{ $spam_video_details->adminVideo->subCategory->name }}</td>
								      	
								      	<td>{{ ($spam_video_details->adminVideo->genreName) ? $spam_video_details->adminVideo->genreName->name : '-' }}</td>
								      	
								      	<td>{{ substr($spam_video_details->adminVideo->title , 0,25) }}...</td>
								      	
								      	<td>{{ count($spam_video_details->adminVideo->userFlags) }}</td>
								      	
								      	<td>
								      		@if($spam_video_details->adminVideo->is_approved)
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
									                  	<li role="presentation">
									                  		@if(Setting::get('admin_delete_control') == YES)

										                  	 	<a role="button" href="javascript:;" class="btn disabled" style="text-align: left">{{ tr('delete') }}</a>

										                  	@else
									                  			<a role="menuitem" tabindex="-1" onclick="return confirm(&quot;{{  substr($spam_video_details->adminVideo->title , 0,25) }} - {{ tr('admin_video_delete_confirmation') }}&quot;);"  href="{{ route('admin.delete.video' , ['admin_video_id' => $spam_video_details->video_id] ) }}">{{ tr('delete') }}</a>
									                  		@endif
									                  	</li>

														<li class="divider" role="presentation"></li>

									                  	@if($spam_video_details->adminVideo->is_approved == DEFAULT_TRUE)
									                		<li role="presentation"><a role="menuitem" tabindex="-1" href="{{ route('admin.videos.decline',['admin_video_id' => $spam_video_details->video_id]) }}">{{ tr('decline') }}</a></li>
									                	@else
									                  		<li role="presentation"><a role="menuitem" tabindex="-1" href="{{ route('admin.videos.approve', ['admin_video_id' => $spam_video_details->video_id] ) }}">{{ tr('approve') }}</a></li>
									                  	@endif

									                  	<li class="divider" role="presentation"></li>

									                  	<li role="presentation"><a role="menuitem" tabindex="-1" href="{{ route('admin.spam-videos.user-reports' , ['admin_video_id' => $spam_video_details->video_id]) }}">{{ tr('user_reports') }}</a></li>

									                </ul>
	              								</li>

	            							</ul>
	            							
								        </td>

								    </tr>

								@endforeach

							</tbody>
						
						</table>

						<div align="right" id="paglink"><?php echo $spam_videos->links(); ?></div>

					@else
						<h3 class="no-result">{{ tr('no_result_found') }}</h3>
					@endif
	            
	            </div>

          	</div>
          	
        </div>

    </div>

@endsection
