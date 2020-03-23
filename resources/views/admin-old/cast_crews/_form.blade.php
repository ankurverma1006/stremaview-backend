<div class="row">

    <div class="col-md-10">

        <div class="box box-primary">

            <div class="box-header label-primary">
                <b style="font-size:18px;">@yield('title')</b>
                <a href="{{route('admin.cast_crews.index')}}" class="btn btn-default pull-right">{{tr('cast_crews')}}</a>
            </div>

            <form class="form-horizontal" action="{{route('admin.cast_crews.save')}}" method="POST" enctype="multipart/form-data" role="form">

                <div class="box-body">

                    <div class="form-group">
                        <label for="name" class="col-sm-1 control-label">*{{tr('name')}}</label>
                        <div class="col-sm-10">
                            <input type="text" required class="form-control" 
                              pattern = "[a-zA-Z0-9\s\-\.]{2,100}" title="{{tr('only_alphanumeric')}}" id="name" name="name" placeholder="{{tr('name')}}" value="{{$model->name}}">
                        </div>
                    </div>

                    <input type="hidden" name="id" value="{{$model->id}}">

                    <div class="form-group">
                        <label for="picture" class="col-sm-1 control-label">*{{tr('picture')}}</label>
                        <div class="col-sm-10"> 

                             <input type="file" @if(!$model->id) required @endif accept="image/jpeg,image/png" id="picture" name="image" placeholder="{{tr('picture')}}" onchange="loadFile(this,'image_preview')">
                            <p class="help-block">{{tr('image_validate')}} {{tr('image_square')}}</p>

                            @if ($model->id)

                                <img id="image_preview" style="width: 100px;height: 100px;" src="{{$model->image}}">

                            @else

                            <img id="image_preview" style="width: 100px;height: 100px;display: none;">

                            @endif


                           
                        </div>
                    </div>

                    <div class="form-group">

                        <label for="description" class="col-sm-1 control-label">*{{tr('description')}}</label>

                        <br>

                        <div class="col-sm-12">

                        <textarea id="ckeditor" required name="description" class="form-control" placeholder="{{tr('enter')}} {{tr('description')}}">{{$model->description}}</textarea>

                        </div>
                        
                    </div>

                </div>

                <div class="box-footer">
                    <a href="" class="btn btn-danger">{{tr('cancel')}}</a>
                    @if(Setting::get('admin_delete_control'))
                        <a href="#" class="btn btn-success pull-right" disabled>{{tr('submit')}}</a>
                    @else
                        <button type="submit" class="btn btn-success pull-right">{{tr('submit')}}</button>
                    @endif
                </div>
            </form>
        
        </div>

    </div>

</div>

