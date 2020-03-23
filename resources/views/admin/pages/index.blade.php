@extends('layouts.admin')

@section('title', tr('pages'))

@section('content-header', tr('pages'))

@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
    <li class="active"><i class="fa fa-book"></i> {{tr('pages')}}</li>
@endsection

@section('content')

    <div class="row">
    
        <div class="col-xs-12">

            <div class="box box-primary">

                <div class="box-header label-primary">
                    
                     <h2 class="box-title" style="color: white"><b>{{tr('pages')}}</b>s</h2>
                    <a href="{{route('admin.pages.create')}}" style="float:right" class="btn btn-default">{{tr('add_page')}}</a>
                </div>

                <div class="box-body table-responsive">

                    <table id="datatable-withoutpagination" class="table table-bordered table-striped">

                        <thead>
                            <tr>
                              <th>{{tr('id')}}</th>
                              <th>{{tr('heading')}}</th>
                              <th>{{tr('page_type')}}</th>
                              <th>{{tr('action')}}</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach($pages as $i => $page_details)
                    
                                <tr>
                                    <td>{{showEntries($_GET, $i+1)}}</td>
                                    <td><a href="{{route('admin.pages.view', ['page_id' => $page_details->id] )}}">{{$page_details->heading}}</a></td>
                                   
                                    <td>{{$page_details->type}}</td>
                                    
                                    <td>

                                        <div class="dropdown">
                                            
                                            <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                {{tr('action')}}
                                                <span class="caret"></span>
                                            </button>

                                            <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu">

                                                <li>    
                                                    <a href="{{route('admin.pages.view', ['page_id' => $page_details->id] )}}"><b>{{tr('view')}}</b></a>
                                                </li>

                                                @if(Setting::get('admin_delete_control') == YES )

                                                    <li><a href="javascript:;" class="btn disabled" style="text-align: left"><b>{{tr('edit')}}</b></a></li>

                                                    <li><a href="javascript:;" class="btn disabled" style="text-align: left"><b>{{tr('delete')}}</b></a></li>

                                                @else

                                                    <li><a href="{{route('admin.pages.edit', ['page_id' => $page_details->id] )}}"><b>{{tr('edit')}}</b></a></li>

                                                    <li><a onclick="return confirm(&quot;{{tr('page_delete_confirmation' , $page_details->heading)}}&quot;);" href="{{ route('admin.pages.delete',['page_id' => $page_details->id] ) }}"><b>{{tr('delete')}}</b></a></li>
                                                @endif

                                            </ul>

                                        </div>
                                        
                                    </td>
                                
                                </tr>

                            @endforeach

                        </tbody>
                    
                    </table>

                    @if(count($pages) > 0) <div align="right" id="paglink"><?php echo $pages->links(); ?></div> @endif

                </div>

            </div>

        </div>

    </div>

@endsection