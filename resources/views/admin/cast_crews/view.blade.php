@extends('layouts.admin')

@section('title', tr('view_cast_crew'))

@section('content-header', tr('view_cast_crew'))

@section('breadcrumb')
    <li><a href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i>{{ tr('home') }}</a></li>
    <li><a href="{{ route('admin.cast_crews.index') }}"><i class="fa fa-users"></i> {{ tr('cast_crews') }}</a></li>
    <li class="active"><i class="fa fa-eye"></i>&nbsp;{{ tr('view_cast_crew') }}</li>
@endsection

@section('content')

	<div class="row">

        <div class="col-xs-12">
            
            <div class="box">
                
                <div class="box-header label-primary ">
                    <h3 class="box-title pull-left" style="color: white"><b> {{ $cast_crew_details->name }} </b></h3> 
                    <a href="{{route('admin.cast_crews.index')}}" class="btn btn-default pull-right">{{tr('cast_crews')}}</a>
                </div>

                <div class="panel-body">

                    <div class="row margin-bottom">  

                        <div class="col-sm-12">

                            <div class="row">

                                <div class="col-sm-6">

                                    <div class="header">

                                        <h4><b>{{ tr('name') }}</b></h4>

                                        <p>{{ $cast_crew_details->name }}</p>
                                        <hr>

                                    </div>

                                    <h3><b>{{ tr('content') }}</b></h3>

                                    <p><?= $cast_crew_details->description ?></p>

                                </div>

                                <div class="col-sm-6">

                                    <div class="header">

                                        <h4><b>{{ tr('picture') }}</b></h4>

                                        <img src="{{ $cast_crew_details->image }}" style="width: 100%;max-height:300px;">

                                    </div>

                                </div>
                        	
                        	</div>
                    
                   		</div>

                	</div>

                    <div class="box-footer">
                    
                        <center>
                            @if(Setting::get('admin_delete_control') == YES )

                                <a class="btn btn-warning btn-sm"  href="javascript:;" class="btn disabled" style="text-align: left"><b> <i class="fa fa-edit"></i>&nbsp;</b>{{ tr('edit') }}</b></a>

                                <a class="btn btn-danger btn-sm"  href="javascript:;" class="btn disabled" style="text-align: left"><b> <i class="fa fa-trash"></i>&nbsp;</b>{{ tr('delete') }}</b></a>

                            @else

                                <a class="btn btn-warning btn-sm" href="{{ route('admin.cast_crews.edit' , ['cast_crew_id' => $cast_crew_details->id] ) }}"><b> <i class="fa fa-edit"></i>&nbsp;</b>{{ tr('edit') }}</b></a>

                                <a class="btn btn-danger btn-sm" onclick="return confirm(&quot;{{ tr('cast_crew_delete_confirmation' , $cast_crew_details->name) }}&quot;);" href="{{ route('admin.cast_crews.delete' , ['cast_crew_id' => $cast_crew_details->id] ) }}"> <i class="fa fa-trash"></i>&nbsp; {{ tr('delete') }} </b> </a>

                            @endif  

                            @if( $cast_crew_details->status == DECLINED )
                                <a class="btn btn-success btn-sm" href="{{ route('admin.cast_crews.status.change',['cast_crew_id' => $cast_crew_details->id] ) }}" onclick="return confirm(&quot;{{ tr('cast_crew_approve_confirmation' , $cast_crew_details->name) }}&quot;);"> <b> <i class="fa fa-check"></i>&nbsp;{{ tr('approve') }}</b> </a>
                            @else
                                <a class="btn btn-danger btn-sm" href="{{ route('admin.cast_crews.status.change',['cast_crew_id' => $cast_crew_details->id] ) }}" onclick="return confirm(&quot;{{ tr('cast_crew_decline_confirmation' , $cast_crew_details->name) }}&quot;);"><b> <i class="fa fa-close"></i>&nbsp;{{ tr('decline') }}</b></a>
                            @endif
                                                        
                        </center> 

                    </div>
            

        	   </div>

        	</div>

    	</div>

    </div>

@endsection






