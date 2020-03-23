@extends('layouts.admin')

@section('title', tr('genres'))

@section('content-header')

	<span style="color:#1d880c !important">{{$sub_category->name}} </span> - {{tr('genres') }}

@endsection

@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
     <li><a href="{{route('admin.categories')}}"><i class="fa fa-suitcase"></i>{{tr('categories')}}</a></li>
    <li><a href="{{route('admin.sub_categories', array('category' => $sub_category->category_id))}}"><i class="fa fa-suitcase"></i> {{tr('sub_categories')}}</a></li>
    <li class="active"><i class="fa fa-suitcase"></i> {{tr('genres')}}</li>
@endsection

@section('content')

@include('notification.notify')

	<div class="row">
        <div class="col-xs-12">
          	<div class="box box-primary">
	          	<div class="box-header label-primary">
	                <b style="font-size:18px;">{{tr('genres')}}</b>
	                <a href="{{route('admin.add.genre' , array('sub_category' => $sub_category->id))}}" class="btn btn-default pull-right">{{tr('add_genre')}}</a>
	            </div>
	            <div class="box-body">
	            	
	            	<div class="table table-responsive" style="padding: 35px 0px"> 

		            	@if(count($data) > 0)

			              	<table id="datatable-withoutpagination" class="table table-bordered table-striped">

								<thead>
								    <tr>
								      <th>{{tr('id')}}</th>
								      <th>{{tr('category')}}</th>
								      <th>{{tr('sub_category')}}</th>
								      <th>{{tr('genre')}}</th>
								      <th>{{tr('position')}}</th>
								      <th>{{tr('image')}}</th>
								      <th>{{tr('status')}}</th>
								      <th>{{tr('action')}}</th>
								    </tr>
								</thead>

								<tbody>

									@foreach($data as $i => $value)


									    <tr>
									      	<td>{{showEntries($_GET, $i+1)}}</td>
									      	<td>{{$value->category_name}}</td>
									      	<td>{{$value->sub_category_name}}</td>
									      	<td>{{$value->genre_name}}</td>
									      	<td>

									      	@if($value->position > 0)

									      	<span class="label label-success">{{$value->position}}</span>

									      	@else

									      	<span class="label label-danger">{{$value->position}}</span>

									      	@endif
									      	</td>
									      	<td>
			                                	<img style="height: 30px;" src="{{$value->image}}">
			                            	</td>
									      	<td>
									      		@if($value->is_approved)
									      			<span class="label label-success">{{tr('approved')}}</span>
									       		@else
									       			<span class="label label-warning">{{tr('pending')}}</span>
									       		@endif
									       </td>


										    <td>
		            							<ul class="admin-action btn btn-default">
		            								<li class="dropdown">
										                <a class="dropdown-toggle" data-toggle="dropdown" href="#">
										                  {{tr('action')}} <span class="caret"></span>
										                </a>
										                <ul class="dropdown-menu dropdown-menu-right">

										                	<li role="presentation">
										                		<a role="menuitem" tabindex="-1" href="{{route('admin.view.genre' , array('id' => $value->genre_id))}}">{{tr('view_genre')}}</a>
										                	</li>

										                	@if($value->is_approved)

										                	<li role="presentation">
										                		<a role="menuitem" tabindex="-1" role="menuitem" tabindex="-1" data-toggle="modal" data-target="#genre_{{$value->genre_id}}">{{tr('change_position')}}</a>
										                	</li>

										                	@endif

										                  	<li role="presentation">
																@if(Setting::get('admin_delete_control'))
		                                                            <a role="button" href="javascript:;" class="btn disabled" style="text-align: left">{{tr('edit')}}</a>
		                                                        @else
																	<a role="menuitem" tabindex="-1" href="{{route('admin.edit.edit_genre' , array('sub_category_id' => $value->sub_category_id,'genre_id' => $value->genre_id))}}">{{tr('edit')}}</a>
																@endif

															</li>

										                  	<li class="divider" role="presentation"></li>

										                  	@if($value->is_approved)
										                  		<li role="presentation"><a role="menuitem" tabindex="-1" href="{{route('admin.genre.approve' , array('id' => $value->genre_id , 'status' =>0))}}" onclick="return confirm(&quot;{{tr('genre_decline_confirmation' , $value->genre_name)}}&quot;);">{{tr('decline')}}</a></li>
										                  	@else
										                  		<li role="presentation"><a role="menuitem" tabindex="-1" href="{{route('admin.genre.approve' , array('id' => $value->genre_id , 'status' => 1))}}" onclick="return confirm(&quot;{{tr('genre_approve_confirmation' , $value->genre_name)}}&quot;);">{{tr('approve')}}</a></li>
										                  	@endif

										                  	<li role="presentation">

										                  		@if(Setting::get('admin_delete_control'))

											                  	 	<a role="button" href="javascript:;" class="btn disabled" style="text-align: left">{{tr('delete')}}</a>

											                  	 @else

										                  			<a role="menuitem" onclick="return confirm(&quot;{{tr('genre_delete_confirmation' , $value->genre_name)}}&quot;);" tabindex="-1" href="{{route('admin.delete.genre' , array('id' => $value->genre_id))}}">{{tr('delete')}}</a>
										                  		@endif

										                  	</li>

										                  	<li class="divider" role="presentation"></li>

									                  		<li role="presentation"><a role="menuitem" tabindex="-1" href="{{route('admin.videos' , array('genre_id' => $value->genre_id))}}">{{tr('videos')}}</a></li>
										              
										                </ul>
		              								</li>
		            							</ul>
										    </td>
									    </tr>
									    <div id="genre_{{$value->genre_id}}" class="modal fade" role="dialog">
										  <div class="modal-dialog">
										  <form action="{{route('admin.save.genre.position',['genre_id'=>$value->genre_id])}}" method="POST">
											    <!-- Modal content-->
											   	<div class="modal-content">
											      <div class="modal-header">
											        <button type="button" class="close" data-dismiss="modal">&times;</button>
											        <h4 class="modal-title">{{tr('change_position')}}</h4>
											      </div>

											      <div class="modal-body">
											        
										            <div class="row">
											        	<div class="col-lg-3">
											        		<label>{{tr('position')}}</label>
											        	</div>
										                <div class="col-lg-9">
										                       <input type="number" required value="{{$value->position}}" name="position" class="form-control" id="position" placeholder="{{tr('position')}}" pattern="[0-9]{1,}" title="Enter 0-9 numbers">
										                  <!-- /input-group -->
										                </div>
										            </div>
											      </div>
											      <div class="modal-footer">
											        <div class="pull-right">
												        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
												        <button type="submit" class="btn btn-primary">{{tr('submit')}}</button>
												    </div>
												    <div class="clearfix"></div>
											      </div>
											    </div>
											</form>
										  </div>
										</div>
									@endforeach
								</tbody>
							
							</table>

							<div align="right" id="paglink"><?php echo $data->links(); ?></div>
						@else
							<h3 class="no-result">{{tr('no_genre')}}</h3>
						@endif
					</div>
            	</div>
          	</div>
        </div>

    </div>

@endsection
