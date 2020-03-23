@extends('layouts.admin')

@section('title', tr('view_cast_crew'))

@section('content-header', tr('view_cast_crew'))

@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
    <li><a href="{{route('admin.cast_crews.index')}}"><i class="fa fa-users"></i> {{tr('cast_crews')}}</a></li>
    <li class="active"><i class="fa fa-eye"></i>&nbsp;{{tr('view_cast_crew')}}</li>
@endsection

@section('content')

	@include('notification.notify')

	<div class="row">

        <div class="col-xs-12">

            <div class="panel">
                
                <div class="panel-body">

                    <div class="post">
                        
                        <div class="user-block">

                            <div class="pull-left">
                                <span class="username" style="margin-left: 0px;">
                                    <a href="">{{$model->name}}</a> 

                                </span>
                            </div>
                            <div class="pull-right">
                                <a href="{{route('admin.cast_crews.edit', array('id'=>$model->unique_id))}}" class="btn btn-warning btn-sm"><i class="fa fa-pencil"></i> {{tr('edit')}}</a>
                            </div>
                        </div>

                        <hr>

                        <div class="row margin-bottom">  

                            <div class="col-sm-12">

                                <div class="row">

                                    <div class="col-sm-12">

                                        <div class="header">

                                            <h4><b>{{tr('name')}}</b></h4>

                                            <p>{{$model->name}}</p>

                                        </div>

                                    </div>


                                    <div class="col-sm-6">

                                        <div class="header">

                                            <h4><b>{{tr('picture')}}</b></h4>

                                            <img src="{{$model->image}}" style="width: 100%;max-height:300px;">

                                        </div>

                                    </div>
                                    
                                    <div class="col-sm-12">

                                        <h3><b>{{tr('content')}}</b></h3>

                                        <p><?= $model->description ?></p>

                                    </div>
                            	
                            	</div>
                        
                       		</div>

                    	</div>
                
                	</div>

            	</div>

        	</div>

    	</div>

    </div>

@endsection

