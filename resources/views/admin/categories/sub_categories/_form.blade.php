<form class="form-horizontal" action="{{ Setting::get('admin_delete_control') == YES ? '#' : route('admin.sub_categories.save') }}" method="POST" enctype="multipart/form-data" role="form">

    <div class="box-body">

        <input type="hidden" name="category_id" value="{{ $category_details->id }}">
        
        <input type="hidden" name="sub_category_id" value="{{ $sub_category_details->id }}">

        <div class="form-group">
            <label for="name" class="col-sm-2 control-label">*{{ tr('name') }}</label>
            <div class="col-sm-10">
                <input type="text" required title="{{ tr('only_alphanumeric') }}" class="form-control" value="{{ old('name') ?: $sub_category_details->name }}" id="name" name="name" placeholder="{{ tr('name') }}">
            </div>
        </div>

        <div class="form-group">
            <label for="description" class="col-sm-2 control-label">*{{ tr('description') }}</label>
            <div class="col-sm-10">
                <input type="text" required class="form-control" value="{{  old('description') ?: $sub_category_details->description }}" id="description" name="description" placeholder="{{ tr('description') }}">
            </div>
        </div>

        @if($sub_category_details->id != '')

            @foreach($sub_category_images as $i => $sub_category_image_details)

                <div class="col-sm-12">
                    <label for="picture" class="col-sm-2 control-label"></label>
                    <img style="height: 90px;margin-bottom: 15px; border-radius:2em;" src="{{ $sub_category_image_details->picture }}" id="image_preview">
                </div>
            @endforeach

        @endif

        <div class="form-group col-md-12">

            <label for="picture" class="col-sm-2 control-label">*{{ tr('picture') }}</label>
            
            <div class="col-sm-10">

                @if($sub_category_details->picture != '')

                    <img id="image_preview" style="width: 100px;height: 100px;" src="{{ $sub_category_details->picture }}">

                @else 

                    <img id="image_preview" style="width: 100px;height: 100px;display: none;">

                @endif

                <br>

                <input type="file" accept="image/jpeg,image/png" id="picture" name="picture" placeholder="{{tr('picture')}}" onchange="loadFile(this,'image_preview')" @If(!$sub_category_details->id) required @endif>

                <p class="help-block">{{tr('image_validate')}} {{tr('image_square')}}</p>

            </div>

        </div>

    </div>

    <div class="box-footer">

        <button type="reset" class="btn btn-danger">{{ tr('cancel') }}</button>
        
        <button type="submit" class="btn btn-success pull-right" @if(Setting::get('admin_delete_control')) disabled  @endif>{{ tr('submit') }}</button>
       
    </div>

</form>