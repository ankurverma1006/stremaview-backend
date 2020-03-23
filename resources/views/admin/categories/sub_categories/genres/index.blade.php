@extends('layouts.admin')

@section('title', tr('genres'))

@section('content-header')

	<span style="color:#1d880c !important">{{ $sub_category_details->name }} </span> - {{ tr('genres')  }}

@endsection

@section('breadcrumb')
    <li><a href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i>{{ tr('home') }}</a></li>
     <li><a href="{{ route('admin.categories.index') }}"><i class="fa fa-suitcase"></i>{{ tr('categories') }}</a></li>
    <li><a href="{{ route('admin.sub_categories.index', ['category_id' => $sub_category_details->category_id] ) }}"><i class="fa fa-suitcase"></i> {{ tr('sub_categories') }}</a></li>
    <li class="active"><i class="fa fa-suitcase"></i> {{ tr('genres') }}</li>
@endsection

@section('content')

	<div class="row">
        <div class="col-xs-12">
          	<div class="box box-primary">
	          	<div class="box-header label-primary">
	                <b style="font-size:18px;">{{ tr('genres') }}</b>
	                <a href="{{ route('admin.genres.create' , ['sub_category_id' => $sub_category_details->id] ) }}" class="btn btn-default pull-right">{{ tr('add_genre') }}</a>
	            </div>
	            <div class="box-body">
	            	
	            	<div class="table table-responsive" style="padding: 35px 0px"> 

		            	@if(count($genres) > 0)

			              	<table id="datatable-withoutpagination" class="table table-bordered table-striped">

								<thead>
								    <tr>
								      <th>{{ tr('id') }}</th>
								      <th>{{ tr('category') }}</th>
								      <th>{{ tr('sub_category') }}</th>
								      <th>{{ tr('genre') }}</th>
								      <th>{{ tr('position') }}</th>
								      <th>{{ tr('image') }}</th>
								      <th>{{ tr('status') }}</th>
								      <th>{{ tr('action') }}</th>
								    </tr>
								</thead>

								<tbody>

									@foreach($genres as $i => $genre_details)

									    <tr>
									      	<td>{{ showEntries($_GET, $i+1) }}</td>

									      	<td>{{ $genre_details->category_name }}</td>

									      	<td>{{ $genre_details->sub_category_name }}</td>

									      	<td>{{ $genre_details->genre_name }}</td>

									      	<td>

										      	@if($genre_details->position > 0)

										      		<span class="label label-success">{{ $genre_details->position }}</span>

										      	@else

										      		<span class="label label-danger">{{ $genre_details->position }}</span>

										      	@endif
									      	</td>

									      	<td>
			                                	<img style="height: 30px;" src="{{ $genre_details->image }}">
			                            	</td>

									      	<td>
									      		@if($genre_details->is_approved == YES)
									      			<span class="label label-success">{{ tr('approved') }}</span>
									       		@else
									       			<span class="label label-warning">{{ tr('pending') }}</span>
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
										                		<a role="menuitem" tabindex="-1" href="{{ route('admin.genres.view' , ['genre_id' => $genre_details->genre_id] ) }}">{{ tr('view_genre') }}</a>
										                	</li>

										                	@if($genre_details->is_approved == YES)

										                	<li role="presentation">
										                		<a role="menuitem" tabindex="-1" role="menuitem" tabindex="-1" data-toggle="modal" data-target="#genre_{{ $genre_details->genre_id }}">{{ tr('change_position') }}</a>
										                	</li>

										                	@endif

															@if(Setting::get('admin_delete_control') == YES)

	                                                            <li role="presentation"> <a role="button" href="javascript:;" class="btn disabled" style="text-align: left">{{ tr('edit') }}</a></li>

	                                                            <li role="presentation"> <a role="button" href="javascript:;" class="btn disabled" style="text-align: left">{{ tr('delete') }}</a></li>

	                                                        @else

																<li role="presentation"><a role="menuitem" tabindex="-1" href="{{ route('admin.genres.edit' , ['sub_category_id' => $genre_details->sub_category_id,'genre_id' => $genre_details->genre_id] ) }}">{{ tr('edit') }}</a></li>

																<li role="presentation"><a role="menuitem" onclick="return confirm(&quot;{{ tr('genre_delete_confirmation' , $genre_details->genre_name) }}&quot;);" tabindex="-1" href="{{ route('admin.genres.delete' , ['genre_id' => $genre_details->genre_id] ) }}">{{ tr('delete') }}</a></li>

															@endif

										                  	<li class="divider" role="presentation"></li>

										                  	@if($genre_details->is_approved == YES )
										                  		<li role="presentation"><a role="menuitem" tabindex="-1" href="{{ route('admin.genres.status.change' , ['genre_id' => $genre_details->genre_id ] ) }}" onclick="return confirm(&quot;{{ tr('genre_decline_confirmation' , $genre_details->genre_name) }}&quot;);">{{ tr('decline') }}</a></li>
										                  	@else
										                  		<li role="presentation"><a role="menuitem" tabindex="-1" href="{{ route('admin.genres.status.change' , ['genre_id' => $genre_details->genre_id ] ) }}" onclick="return confirm(&quot;{{ tr('genre_approve_confirmation' , $genre_details->genre_name) }}&quot;);">{{ tr('approve') }}</a></li>
										                  	@endif

										                  	<li class="divider" role="presentation"></li>

									                  		<li role="presentation"><a role="menuitem" tabindex="-1" href="{{ route('admin.videos' , ['genre_id' => $genre_details->genre_id] ) }}">{{ tr('videos') }}</a></li>
										              
										                </ul>

		              								</li>

		            							</ul>

										    </td>

									    </tr>

									    <div id="genre_{{ $genre_details->genre_id }}" class="modal fade" role="dialog">
										  <div class="modal-dialog">
										  
										  <form action="{{ route('admin.genres.position.change',['genre_id' => $genre_details->genre_id]) }}" method="POST">
											    <!-- Modal content-->
											   	<div class="modal-content">
											      <div class="modal-header">
											        <button type="button" class="close" data-dismiss="modal">&times;</button>
											        <h4 class="modal-title">{{ tr('change_position') }}</h4>
											      </div>

											      <div class="modal-body">
											        
										            <div class="row">
											        	<div class="col-lg-3">
											        		<label>{{ tr('position') }}</label>
											        	</div>
										                <div class="col-lg-9">
										                       <input type="number" required value="{{ $genre_details->position }}" name="position" class="form-control" id="position" placeholder="{{ tr('position') }}" pattern="[0-9]{1,}" title="Enter 0-9 numbers">
										                  <!-- /input-group -->
										                </div>
										            </div>
											      </div>
											      <div class="modal-footer">
											        <div class="pull-right">
												        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
												        <button type="submit" class="btn btn-primary">{{ tr('submit') }}</button>
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

							<div align="right" id="paglink"><?php echo $genres->links(); ?></div>
						@else
							<h3 class="no-result">{{ tr('no_genre') }}</h3>
						@endif
					</div>
            	</div>
          	</div>
        </div>

    </div>

@endsection
