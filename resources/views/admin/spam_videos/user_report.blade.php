@extends('layouts.admin')

@section('title')

	{{tr('user_reports') }}

@endsection

@section('content-header')

	{{tr('user_reports') }} 

	@if($video_details) 
		<a href="{{route('admin.view.video', array('id' => $video_details->id))}}"> - {{$video_details->title}} </a> 
	@endif

@endsection

@section('breadcrumb')

    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>

    <li><a href="{{route('admin.spam-videos')}}"><i class="fa fa-flag"></i>{{tr('spam_videos')}}</a></li>

    <li class="active"><i class="fa fa-suitcase"></i> {{tr('user_reports')}}</li>

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
							      <th>{{tr('id')}}</th>
							      <th>{{tr('username')}}</th>
							      <th>{{tr('reason')}}</th>
							    </tr>
							</thead>

							<tbody>
								@foreach($spam_videos as $i => $spam_video_details)
								    <tr>
								      	<td>{{showEntries($_GET, $i+1)}}</td>
								      	<td>{{$spam_video_details->userVideos->name}}</td>
								      	<td>{{$spam_video_details->reason}}</td>
								    </tr>
								@endforeach
							</tbody>
						
						</table>

						<div align="right" id="paglink"><?php echo $spam_videos->links(); ?></div>

					@else

						<h3 class="no-result">{{tr('no_result_found')}}</h3>

					@endif
	            
	            </div>

          	</div>

        </div>

    </div>

@endsection
