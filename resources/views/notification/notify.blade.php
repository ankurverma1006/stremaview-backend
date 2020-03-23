@if(Session::has('flash_error'))
    <div class="alert alert-danger">
        <button type="button" class="close" data-dismiss="alert">×</button>
        {{Session::get('flash_error')}}
    </div>
@endif


@if(Session::has('flash_success'))
    <div class="alert alert-success" >
        <button type="button" class="close" data-dismiss="alert">×</button>
        {{Session::get('flash_success')}}
    </div>
@endif
