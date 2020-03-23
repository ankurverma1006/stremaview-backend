@extends('layouts.admin')

@section('title', tr('view_template'))

@section('content-header', tr('view_template'))

@section('breadcrumb')
    <li><a href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i>{{ tr('home') }}</a></li>
    <li><a href="{{ route('admin.templates.index') }}"><i class="fa fa-envelope"></i> {{ tr('email_templates') }}</a></li>
    <li class="active"><i class="fa fa-eye"></i>&nbsp;{{ tr('view_template') }}</li>
@endsection

@section('content')

    <div class="row">

        <div class="col-xs-12">

            <div class="box box-primary">

                <div class="box-header label-primary">
                    <b style="font-size: 18px;">{{ getTemplateName($template_details->template_type) }}</b>
                    <div class="pull-right">
                        <a href="{{ route('admin.templates.edit', [ 'template_id' => $template_details->id] ) }}" class="btn btn-warning btn-sm"><b> <i class="fa fa-pencil"></i> {{ tr('edit') }}</b></a>
                    </div>
                </div>

                <div class="box-body">

                    <div class="row margin-bottom">  

                        <div class="col-sm-12">

                            <div class="row">

                                <div class="col-sm-12">
                                    <h4><b>{{ tr('template_type') }}</b></h4>
                                    <p>{{ getTemplateName($template_details->template_type) }} </p>
                                </div>

                                <div class="col-sm-12">
                                    <h4><b>{{ tr('subject') }}</b></h4>
                                    <p>{{ $template_details->subject }}</p>
                                </div>

                                <div class="col-sm-12">
                                    <h3><b>{{ tr('content') }}</b></h3>
                                    <p><?= $template_details->description ?></p>
                                </div>
                        	
                        	</div>
                    
                   		</div>

                	</div>
            
            	</div>

        	</div>

    	</div>

	</div>

@endsection

