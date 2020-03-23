@extends('layouts.admin')

@section('title', tr('view_page'))

@section('content-header', tr('view_page'))

@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
    <li><a href="{{route('admin.pages.index')}}"><i class="fa fa-book"></i> {{tr('pages')}}</a></li>
    <li class="active"> {{tr('view_page')}}</li>
@endsection

@section('content')
    
    <div class="row">

        <div class="col-md-12">

            <div class="box ">

                <div class="box-header label-primary">

                    <div class="pull-left">
                        <h2 class="box-title" style="color: white"><b>{{$page_details->heading}}</b></h2>
                    </div>

                    <div class="pull-right">
                       
                        @if(Setting::get('admin_delete_control') == YES )

                            <a href="javascript:;" class="btn btn-sm btn-warning" style="text-align: left"><b><i class="fa fa-edit"></i>&nbsp;{{tr('edit')}}</b></a>

                            <a href="javascript:;" class="btn btn-sm btn-danger" style="text-align: left"><b><i class="fa fa-trash"></i>&nbsp;{{tr('delete')}}</b></a>
                                
                        @else

                            <a href="{{route('admin.pages.edit', ['page_id' => $page_details->id] )}}" class="btn btn-sm btn-warning"><b><i class="fa fa-edit"></i>&nbsp;{{tr('edit')}}</b></a>

                            <a onclick="return confirm(&quot;{{tr('page_delete_confirmation' , $page_details->heading)}}&quot;);"  href="{{ route('admin.pages.delete',['page_id' => $page_details->id] ) }}" class="btn btn-sm btn-danger"><b><i class="fa fa-trash"></i>&nbsp;{{tr('delete')}}</b></a>
                            
                        @endif
                    </div>
                    <div class="clearfix"></div>
                </div>

                <div class="box-body">

                    <strong><i class="fa fa-book margin-r-5"></i> {{tr('title')}}</strong>
                    <p class="text-muted">{{$page_details->heading}}</p>
                    <hr>

                    <strong><i class="fa fa-book margin-r-5"></i> {{tr('description')}}</strong>
                    <p class="text-muted"><?= $page_details->description?></p>
                    <hr>

                </div>

            </div>
            <!-- /.box -->
        </div>

    </div>
@endsection


