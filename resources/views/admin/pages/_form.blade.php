<form  action="{{ Setting::get('admin_delete_control') == YES ? '#' : route('admin.pages.save') }}" method="POST" enctype="multipart/form-data" role="form">

    <div class="box-body">

        <input type="hidden" name="page_id" value="{{ $page_details->id }}">

        @if($page_details->id != '')

        <div class="form-group">
            <label for="title">*{{ tr('page_type') }}</label>
            <input type="text" class="form-control" name="type" id="title" value="{{  $page_details->type }}" placeholder="{{ tr('enter_type') }}" disabled="true">
        </div>

        @else

        <div class="form-group floating-label">
            <label for="select2">*{{tr('page_type')}}</label>

            <select id="select2" name="type" class="form-control" required>

                <option value=""  selected="true">{{tr('choose')}} {{tr('page_type')}}</option>
                <option value="about">{{tr('about')}}</option>
                <option value="terms">{{tr('terms')}}</option>
                <option value="privacy">{{tr('privacy')}}</option>
                <option value="contact">{{tr('contact')}}</option>
                <option value="help">{{tr('help')}}</option>
                <option value="faq">{{tr('faq')}}</option>
                <option value="others">{{tr('others')}}</option>

            </select>            
        </div>

        @endif

        <div class="form-group">
            <label for="heading">*{{ tr('heading') }}</label>
            <input type="text" class="form-control" name="heading" required value="{{  $page_details->heading   }}" id="heading" placeholder="{{ tr('enter_heading') }}">
        </div>

        <div class="form-group">
            <label for="description">*{{ tr('description') }}</label>

            <textarea id="ckeditor" name="description" class="form-control" required placeholder="{{ tr('enter_text') }}">{{ $page_details->description }}</textarea>
            
        </div>

    </div>

    <div class="box-footer">
            <button type="reset" class="btn btn-danger">{{ tr('cancel') }}</button>
            
            <button type="submit" class="btn btn-success pull-right" @if(Setting::get('admin_delete_control') == YES) disabled @endif) >{{ tr('submit') }}</button> 
    </div>

</form>