@extends('layouts.admin')

@section('title', tr('languages'))


@section('content-header', tr('languages'))


@section('breadcrumb')
    <li><a href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i>{{ tr('home') }}</a></li>
    <li class="active"><i class="fa fa-globe"></i> {{ tr('languages') }}</li>

@endsection

@section('content')

	@include('notification.notify')

    <div class="row">

        <div class="col-xs-12">

            <div class="box box-primary">

                <div class="box-header label-primary">
                    <b>{{ tr('languages') }}</b>
                    <a href="{{ route('admin.languages.create') }}" style="float:right" class="btn btn-default">{{ tr('create_language') }}</a>
                </div>
                
                <div class="box-body">

              	    <table id="datatable-withoutpagination" class="table table-bordered table-striped">

                        <thead>
                            <tr>
                                <th>{{ tr('id') }}</th>
                                <th>{{ tr('language')  }}</th>
                                <th>{{ tr('short_name') }}</th>
                                <th>{{ tr('file') }}</th>
                                <th>{{ tr('status') }}</th>
                                <th>{{ tr('action') }}</th>
                            </tr>
                        
                        </thead>

                        <tbody>
                           
                            @foreach($languages as $index => $language_details)
                                <tr>
                                    <td>{{ showEntries($_GET, $index+1) }}</td>
                                    <td>{{ $language_details->language }}</td>
                                    <td>{{ $language_details->folder_name }}</td>
                                    <td>
                                        <a href="{{ route('admin.languages.download', $language_details->folder_name) }}" target="_blank">
                                            {{ tr('download_here') }}
                                        </a>

                                    </td>
                                    <td>
                                         @if($language_details->status)
                                            <span class="label label-success">{{ tr('active') }}</span>
                                        @else
                                            <span class="label label-warning">{{ tr('inactive') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                       <div class="dropdown">
                                        
                                            <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                {{ tr('action') }}
                                                <span class="caret"></span>
                                            </button>

                                            <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu">
                                                @if($index != 0)
                                                <li>
                                                    @if(Setting::get('admin_delete_control'))
                                                        <a href="javascript:;" class="btn disabled" style="text-align: left"><b><i class="fa fa-edit"></i>&nbsp;{{ tr('edit') }}</b></a>
                                                    @else
                                                        <a href="{{ route('admin.languages.edit', $language_details->id) }}"><b><i class="fa fa-edit"></i>&nbsp;{{ tr('edit') }}</b></a>
                                                    @endif
                                                </li>

                                                @endif
                                                <li>

                                                    <a href="{{ route('admin.languages.status', $language_details->id) }}"><b>
                                                    @if($language_details->status)
                                                        <i class="fa fa-close"></i>&nbsp;{{ tr('inactivate') }}
                                                    @else
                                                        <i class="fa fa-check"></i>&nbsp;{{ tr('activate') }}
                                                    @endif
                                                    </b>
                                                    </a>
                                                </li>
                                                
                                                @if($language_details->folder_name != Setting::get('default_lang'))
                                                <li>

                                                    <a href="{{ route('admin.languages.set_default_language', $language_details->folder_name) }}"><b>
                                                        <i class="fa fa-globe"></i>&nbsp;{{ tr('set_default_language') }}
                                                        </b>
                                                    </a>
                                                </li>

                                                @endif
                                                

                                                @if($index != 0)

                                                <li>
                                                    @if(Setting::get('admin_delete_control'))
                                                        <a href="javascript:;" class="btn disabled" style="text-align: left"><b><i class="fa fa-trash"></i>&nbsp;{{ tr('delete') }}</b></a>

                                                    @else
                                                        <a onclick="return confirm('Are you sure?')" href="{{ route('admin.languages.delete',$language_details->id) }}"><b><i class="fa fa-trash"></i>&nbsp;{{ tr('delete') }}</b></a>

                                                    @endif

                                                </li>   

                                                @endif                                 

                                            </ul>

                                        </div>
                                    </td>
                                </tr>
                            
                            @endforeach

    					</tbody>

					</table>

                    @if(count($languages) > 0)<div align="right" id="paglink"><?php echo $languages->links(); ?> @endif</div>
                
                </div>

            </div>
        
        </div>
    
    </div>

@endsection


@if(Session::has('flash_language'))

@section('scripts')

<script type="text/javascript" src="{{ asset('common/js/bootbox.min.js') }}"></script>
<script type="text/javascript">

bootbox.confirm("Do you want to reload the page to view default language ?", function(result){ 
    if (result == true) {
        window.location.reload(true);
    }
    console.log('This was logged in the callback: ' + result); 
});

</script>
@endsection

@endif