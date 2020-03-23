@extends('layouts.admin')

@section('title', tr('sub_profiles'))

@section('content-header', tr('sub_profiles'))

@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
    <li><a href="{{route('admin.users')}}"> <i class="fa fa-user"></i> {{tr('users')}}</a></li>
    <li class="active"><i class="fa fa-user"></i> {{tr('sub_profiles')}}</li>
@endsection

@section('content')

	@include('notification.notify')

	<div class="row">
        <div class="col-xs-12">
          	<div class="box box-primary">
          	
	          	<div class="box-header label-primary">

	          		<b style="font-size:18px;">
	          			{{tr('sub_profiles')}} - 
	          			<a style="color: white;text-decoration: underline;" href="{{route('admin.users.view' , $user_details->id)}}"> 
	          				{{$user_details ? $user_details->name : ""}}
	          			</a>
	          		</b>
	              
	            </div>
	            
	            <div class="box-body">

	            	@if(count($sub_profiles) > 0)

		              	<table id="datatable-withoutpagination" class="table table-bordered table-striped">

							<thead>
							    <tr>
							      <th>{{tr('id')}}</th>
							      <th>{{tr('sub_profile_name')}}</th>
							      <th>{{tr('image')}}</th>
							      <th>{{tr('created_at')}}</th>
							      <th>{{tr('updated_at')}}</th>
							      <th>{{tr('action')}}</th>
							    </tr>
							</thead>

							<tbody>
								@foreach($sub_profiles as $i => $sub_profile_details)

								    <tr>
								      	<td>{{showEntries($_GET, $i+1)}}</td>
								      	<td>{{$sub_profile_details->name}}</td>
								     	<td><img src="{{$sub_profile_details->picture}}" style="height: 30px;"/></td>
								     	<td>{{convertTimeToUSERzone($sub_profile_details->created_at, Auth::guard('admin')->user()->timezone, 'd-m-Y H:i a')}}</td>
								     	<td>{{convertTimeToUSERzone($sub_profile_details->updated_at, Auth::guard('admin')->user()->timezone, 'd-m-Y H:i a')}}</td>
								     	<td>
								     		

								     		<ul class="admin-action btn btn-default">
	            								<li class="dropup">
									                <a class="dropdown-toggle" data-toggle="dropdown" href="#">
									                  {{tr('action')}} <span class="caret"></span>
									                </a>
									                <ul class="dropdown-menu dropdown-menu-right">
									                  	<li role="presentation"><a role="menuitem" tabindex="-1" href="{{route('admin.user.history', $sub_profile_details->id)}}">{{tr('history')}}</a></li>

									                  	<li role="presentation"><a role="menuitem" tabindex="-1" href="{{route('admin.user.wishlist', $sub_profile_details->id)}}">{{tr('wishlist')}}</a></li>

									                </ul>

									             </li>
									         </ul>
								     	</td>
								    </tr>
								@endforeach
							</tbody>
						
						</table>

						<div align="right" id="paglink"><?php echo $sub_profiles->links(); ?></div>
					
					@else
						<h3 class="no-result">{{tr('no_result_found')}}</h3>
					@endif
	            </div>
          	</div>
        </div>
    </div>

@endsection
