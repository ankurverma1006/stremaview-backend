@extends('layouts.admin')

@section('title', tr('sub_categories'))

@section('content-header')

	<span style="color:#1d880c !important">{{$category->name}} </span> - {{tr('sub_categories') }}

@endsection

@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
    <li><a href="{{route('admin.categories')}}"><i class="fa fa-suitcase"></i> {{tr('categories')}}</a></li>
    <li class="active"><i class="fa fa-suitcase"></i> {{tr('sub_categories')}}</li>
@endsection

@section('content')

@include('notification.notify')

	<div class="row">
        <div class="col-xs-12">
          	<div class="box box-primary">
	          	<div class="box-header label-primary">
	                <b style="font-size:18px;">{{tr('sub_categories')}}</b>
	                <a href="{{route('admin.add.sub_category' , array('category' => $category->id))}}" class="btn btn-default pull-right">{{tr('add_sub_category')}}</a>
	            </div>
	            <div class="box-body">

	            	@if(count($data) > 0)

		              	<table id="datatable-withoutpagination" class="table table-bordered table-striped">

							<thead>
							    <tr>
							      <th>{{tr('id')}}</th>
							      <th>{{tr('sub_category')}}</th>
							      <th>{{tr('description')}}</th>
							      <th>{{tr('status')}}</th>
							     
							      <th>{{tr('image')}}</th>
							      <th>{{tr('action')}}</th>
							    </tr>
							</thead>

							<tbody>

								@foreach($data as $i => $sub_category)

									<?php $images = ($sub_category->subCategoryImage != null && !empty($sub_category->subCategoryImage)) ? $sub_category->subCategoryImage : []; ?>

								    <tr>
								      	<td>{{showEntries($_GET, $i+1)}}</td>

								      	<td>
								      		<a href="{{route('admin.sub_categories.view',['category_id' => $category->id ,'sub_category_id' => $sub_category->id ]) }}">{{$sub_category->sub_category_name}}</a>
								      	</td>

								      	<td>{{$sub_category->description}}</td>

								      	<td>
								      		@if($sub_category->is_approved)
								      			<span class="label label-success">{{tr('approved')}}</span>
								       		@else
								       			<span class="label label-warning">{{tr('pending')}}</span>
								       		@endif
								       </td>

								       	<?php /*@if($category->is_series)

									       <td>
									      		<button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#genres{{$i}}">
													{{tr('view_genres')}}
												</button>
									      	</td>

								      	@endif */?>

								      	<td>
								      		@if(count($images) > 0)

								                	@if($images[0])
									                    
									                        <img class="img-responsive" src="{{$images[0]->picture}}" alt="SubCategory" style="width: 50px;height: 50px;">
									                    
								                    @endif

							                @endif
								      	</td>

									    <td>
	            							<ul class="admin-action btn btn-default">
	            								<li class="dropup">
									                <a class="dropdown-toggle" data-toggle="dropdown" href="#">
									                  {{tr('action')}} <span class="caret"></span>
									                </a>
									                <ul class="dropdown-menu dropdown-menu-right">
									                	<li>
									                		<a href="{{route('admin.sub_categories.view',['category_id' => $category->id ,'sub_category_id' => $sub_category->id ]) }}">{{tr('view')}}</a>
									                	</li>
									                  	<li role="presentation">
															@if(Setting::get('admin_delete_control'))
	                                                            <a role="button" href="javascript:;" class="btn disabled" style="text-align: left">{{tr('edit')}}</a>
	                                                        @else
																<a role="menuitem" tabindex="-1" href="{{route('admin.edit.sub_category' , array('category_id' => $category->id,'sub_category_id' => $sub_category->id))}}">{{tr('edit')}}</a></li>
															@endif

									                  	<!-- <li role="presentation"><a role="menuitem" tabindex="-1" href="{{route('admin.view.sub_category' , array('sub_category_id' => $sub_category->id))}}">{{tr('view_sub_category')}}</a></li> -->


									                  	<li class="divider" role="presentation"></li>
									                  	
									                  	@if($sub_category->is_approved)
									                  		<li role="presentation"><a role="menuitem" tabindex="-1" onclick="return confirm(&quot;{{tr('sub_category_decline_confirmation' , $sub_category->sub_category_name)}}&quot;);" href="{{route('admin.sub_category.approve' , array('id' => $sub_category->id , 'status' =>0))}}">{{tr('decline')}}</a></li>
									                  	@else
									                  		<li role="presentation"><a role="menuitem" tabindex="-1" onclick="return confirm(&quot;{{tr('sub_category_approve_confirmation' , $sub_category->sub_category_name)}}&quot;);" href="{{route('admin.sub_category.approve' , array('id' => $sub_category->id , 'status' => 1))}}">{{tr('approve')}}</a></li>
									                  	@endif


									                  	<li role="presentation">

									                  		@if(Setting::get('admin_delete_control'))

										                  	 	<a role="button" href="javascript:;" class="btn disabled" style="text-align: left">{{tr('delete')}}</a>

										                  	 @else

									                  			<a role="menuitem" onclick="return confirm(&quot;{{tr('subcategory_delete_confirmation' , $sub_category->sub_category_name)}}&quot;);" tabindex="-1" href="{{route('admin.delete.sub_category' , array('sub_category_id' => $sub_category->id))}}">{{tr('delete')}}</a>
									                  		@endif

									                  	</li>

									                  	@if($category->is_series)

									                  	
										                  	<li class="divider" role="presentation"></li>

										                  	<li role="presentation"><a role="menuitem" tabindex="-1" href="{{route('admin.add.genre' , array('sub_category' => $sub_category->id))}}">{{tr('add_genre')}}</a></li>
										                  	<li role="presentation"><a role="menuitem" tabindex="-1" href="{{route('admin.genres' , array('sub_category' => $sub_category->id))}}">{{tr('view_genres')}}</a></li>

									                  	@endif

									                  	<li class="divider" role="presentation"></li>

									                  	<li role="presentation"><a role="menuitem" tabindex="-1" href="{{route('admin.videos' , array('sub_category_id' => $sub_category->id))}}">{{tr('videos')}}</a></li>
									              
									                </ul>
	              								</li>
	            							</ul>
									    </td>
								    </tr>



									<!-- Modalfor sub category images -->
									@if($category->is_series)

										<div class="modal fade" id="genres{{$i}}" role="dialog">

										    <div class="modal-dialog">
										    	<!-- Modal content-->
										    	<div class="modal-content">

										        	<div class="modal-header">
										          		<button type="button" class="close" data-dismiss="modal">&times;</button>
										          		<h4 class="modal-title">{{$sub_category->sub_category_name}}</h4>
										        	</div>

										        	<div class="modal-body">

										        		@if(count($sub_category->genres) > 0)

											                <div class="row">

											                	@foreach($sub_category->genres as $genre)
											                		<div class="col-lg-12">
												                		<div class="box">
												                			<div class="box-header ui-sortable-handle" style="cursor: move;">

																             	<h3 class="box-title">{{$genre->name}}</h3>
																              	<!-- tools box -->
																              	<div class="pull-right box-tools">
																              		<!--@if($genre->is_approved)
																	              		<a title="Decline" href="{{route('admin.genre.approve' , array('id' => $genre->id , 'status' => 0))}}" class="btn btn-warning btn-sm">
																	                  		<i class="fa fa-times"></i>
																	                  	</a>
																		       		@else
																		       			<a title="Approve" href="{{route('admin.genre.approve' , array('id' => $genre->id , 'status' => 1))}}" class="btn btn-success btn-sm">
																	                  		<i class="fa fa-check"></i>
																	                  	</a>
																		       		@endif -->

																                	<a title="Delete" href="{{route('admin.delete.genre' , $genre->id)}}" class="btn btn-danger btn-sm">
																                  		<i class="fa fa-trash"></i>
																                  	</a>
																              	</div>
																              	<!-- /. tools -->
																	        </div>
												                		</div>
												                  	</div>
											                    @endforeach

											                </div>

											            @else
											            	<p style="padding: 5px">{{tr('no_genre')}}</p>
										                @endif

										        	</div>

										        	<div class="modal-footer">
										          		<button type="button" class="btn btn-default" data-dismiss="modal">{{tr('close')}}</button>
										        	</div>
										    	</div>

											</div>

										</div>

									@endif

								    <script type="text/javascript">
									    $(function () {
									    	$('#image{{$i}}').on('shown.bs.modal', function () {
												  $('#myInput').focus()
											});
										});
								    </script>

								@endforeach
							</tbody>
						
						</table>

						<div align="right" id="paglink"><?php echo $data->links(); ?></div>
					@else
						<h3 class="no-result">{{tr('no_sub_category_found')}}</h3>
					@endif
	            </div>
          	</div>
        </div>

    </div>

@endsection
