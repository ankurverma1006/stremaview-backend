
@include('notification.notify')

    <div class="row">

        <div class="col-md-10">

            <div class="box box-primary">

                <div class="box-header label-primary">
                    <b style="font-size:18px;">@yield('title')</b>
                    <a href="{{route('admin.genres' , array('sub_category' => $subcategory->id))}}" class="btn btn-default pull-right">{{tr('genres')}}</a>
                </div>

                <form class="form-horizontal" action="{{route('admin.save.genre')}}" method="POST" enctype="multipart/form-data" role="form">

                    <div class="box-body">

                        <input type="hidden" name="sub_category_id" value="{{$subcategory->id}}">

                        <input type="hidden" name="category_id" value="{{$subcategory->category_id}}">

                        <input type="hidden" name="id" value="{{$genre->id}}">

                        <div class="form-group">

                            <label for="name" class="col-sm-2 control-label">{{tr('name')}}*</label>
                            <div class="col-sm-10">
                                <input type="text" required  pattern = "[a-zA-Z0-9\s\-\.]{2,100}" title="{{tr('only_alphanumeric')}}" class="form-control" id="name" name="name" placeholder="{{tr('name')}}" value="{{$genre->name}}">
                            </div>
                        </div>

                        <div class="form-group">


                            <label for="picture1" class="col-sm-2 control-label">{{tr('image')}} * </label>
                                <div class="col-sm-10">
                                    <input type="file" accept="image/png,image/jpeg" id="image" name="image" placeholder="{{tr('image')}}" onchange="loadFile(this,'image_preview')" @if(!$genre->id) required @endif>
                                     <p class="help-block">{{tr('image_validate')}} {{tr('rectangle_image')}}</p>

                                    @if(!$genre->id)
                                    <img id="image_preview" style="width:100px;height:100px; display: none;">
                                    @else
                                     <img id="image_preview" style="width:100px;height:100px;" src="{{$genre->image}}">
                                    @endif
                                   
                                </div>
                        </div>

                        <div class="form-group">

                            <label for="picture1" class="col-sm-2 control-label">{{tr('trailer_video')}} *</label>

                            <div class="col-sm-10">
                                <input type="file" accept="video/mp4" id="video" name="video" placeholder="{{tr('video')}}" @if(!$genre->id) required @endif>
                                 <p class="help-block">{{tr('video_validate')}}</p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="video" class="col-sm-2 control-label">{{tr('sub_title')}}</label>

                            <div class="col-sm-10">
                            <input type="file" id="subtitle" name="subtitle" accept="text/plain">
                            <p class="help-block">{{tr('subtitle_validate')}}</p>

                            </div>
                        </div>
        
                    </div>

                    <div class="box-footer">
                        <button type="reset" class="btn btn-danger">{{tr('cancel')}}</button>
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

@section('scripts')

    <script>
      
      function loadFile(event, id){

        $("#"+id).show();

          // alert(event.files[0]);
          var reader = new FileReader();
          reader.onload = function(){
          var output = document.getElementById(id);
          // alert(output);
            output.src = reader.result;
               //$("#imagePreview").css("background-image", "url("+this.result+")");
          };
          reader.readAsDataURL(event.files[0]);
        }
    </script>
@endsection
