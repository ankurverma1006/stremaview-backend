<form action="{{ Setting::get('admin_delete_control') == YES ? '#' : route('admin.categories.save') }}" method="POST" enctype="multipart/form-data" role="form">

    <div class="box-body">

        <input type="hidden" name="category_id" value="{{ $category_details->id }}">

        <div class="form-group col-md-5">

            <label for="name">* {{ tr('name') }}</label>
            
            <input type="text" required class="form-control" title="{{ tr('only_alphanumeric') }}" value="{{  old('name') ?: $category_details->name }}" id="name" name="name" placeholder="{{ tr('enter') }} {{ tr('category') }}">

        </div>

        <div class="checkbox col-md-7">

            <label for="picture"></label>

            <label>
                <input type="checkbox" name="is_series" value="1" class="minimal-red" @if($category_details->is_series) checked @endif> {{ tr('is_series') }}
            </label>

        </div>

        <div class="form-group col-md-12">

            <label for="picture">* {{tr('picture')}}</label>
            
            <br>

            @if($category_details->picture != '')

                <img id="image_preview" style="width: 100px;height: 100px;" src="{{ $category_details->picture }}">

            @else 

                <img id="image_preview" style="width: 100px;height: 100px;display: none;">

            @endif

            <br>

            <input type="file" accept="image/jpeg,image/png" id="picture" name="picture" placeholder="{{tr('picture')}}" onchange="loadFile(this,'image_preview')" @If(!$category_details->id) required @endif>

            <p class="help-block">{{tr('image_validate')}} {{tr('image_square')}}</p>

        </div>

        <div class="clearfix"></div>

    </div>

    <div class="box-footer">

        <button type="reset" class="btn btn-danger">{{ tr('cancel') }}</button>

        <button type="submit" class="btn btn-success pull-right" @if(Setting::get('admin_delete_control') == YES ) disabled @endif >{{ tr('submit') }}</button>
    </div>

</form>