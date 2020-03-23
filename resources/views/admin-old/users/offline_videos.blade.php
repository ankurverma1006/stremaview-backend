@extends('layouts.admin')

@section('title', tr('offline_videos'))

@section('content-header',tr('offline_videos'))

@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
    <li><a href="{{route('admin.users')}}"><i class="fa fa-user"></i> {{tr('users')}}</a></li>
        <li><a href="{{route('admin.users.subprofiles', $user->id)}}"> <i class="fa fa-user"></i> {{tr('sub_profiles')}}</a></li>

    <li><a href="{{route('admin.users.view', $user->id)}}"> <i class="fa fa-user"></i> {{tr('view_user')}}</a></li>
    <li class="active"> {{tr('offline_videos')}}</li>
@endsection

@section('content')

@include('notification.notify')

	<div class="row">
        <div class="col-xs-12">
          	<div class="box">
	            <div class="box-body">


		              	<table id="datatable-withoutpagination" class="table table-bordered table-striped">

							<thead>
							    <tr>
							      <th>{{tr('id')}}</th>
							      <th>{{tr('video')}}</th>
							      <th>{{tr('date')}}</th>
							      <th>{{tr('status')}}</th>
							      <th>{{tr('action')}}</th>
							    </tr>
							</thead>

							<tbody>

								@foreach($videos as $i => $data)

								    <tr>
								      	<td>{{showEntries($_GET, $i+1)}}</td>
								      	<td>{{$data->title}}</td>
								      	<td>{{$data->created_at->diffForHumans()}}</td>
								      	<td>{{downloadStatus($data->download_status)}}</td>
									    <td>
	            							<ul class="admin-action btn btn-default">
	            								<li class="dropup">

									                <a class="dropdown-toggle" data-toggle="dropdown" href="#">
									                  {{tr('action')}} 
									                  <span class="caret"></span>
									                </a>

									                <ul class="dropdown-menu dropdown-menu-right">
									                  	<li role="presentation"><a role="menuitem" tabindex="-1" onclick="return confirm('Are you sure?');" href="{{route('admin.videos.remove-video' , [

									                  		'admin_video_id'=>$data->admin_video_id, 'id'=>$data->user_id

									                  		])}}">{{tr('remove')}}</a></li>
									                </ul>

	              								</li>
	            							</ul>
									    </td>
								    </tr>					

								@endforeach
							</tbody>
						
						</table>

						@if(count($videos) > 0)

						<div align="right" id="paglink"><?php echo $videos->links(); ?></div>

						@endif

				
	            </div>
          	</div>
        </div>
    </div>

@endsection


