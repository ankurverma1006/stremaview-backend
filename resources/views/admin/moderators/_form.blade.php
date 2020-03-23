

<form class="form-horizontal" action="{{  Setting::get('admin_delete_control') == YES ? '#' : route('admin.moderators.save') }}" method="POST" enctype="multipart/form-data" role="form">

    <div class="box-body">

        <input type="hidden" name="moderator_id" value="{{ $moderator_details->id }}">

        <div class="form-group">
            <label for="name" class="col-sm-2 control-label">*{{ tr('username') }}</label>

            <div class="col-sm-10">
                <input type="text" required  title="{{ tr('only_alphanumeric') }}"  name="name" value="{{old('name') ?: $moderator_details->name}}" class="form-control" id="name" placeholder="{{ tr('name') }}">
            </div>
        </div>

        <div class="form-group">
            <label for="email" class="col-sm-2 control-label">*{{ tr('email') }}</label>
            <div class="col-sm-10">
                <input type="email" maxlength="255" required class="form-control" value="{{ old('email') ?: $moderator_details->email}}" id="email" name="email" placeholder="{{ tr('email') }}">
            </div>
        </div>

        <div class="form-group">
            <label for="mobile" class="col-sm-2 control-label">*{{ tr('mobile') }}</label>

            <div class="col-sm-10">
                <input type="text" required name="mobile" value="{{ old('mobile') ?: $moderator_details->mobile}}" class="form-control" id="mobile" minlength="4"  maxlength="16" placeholder="{{ tr('mobile') }}">
            </div>
        </div>

        @if($moderator_details->id == '')
            <div class="form-group">
                <label for="password" class="col-sm-2 control-label">*{{ tr('password') }}</label>

                <div class="col-sm-10">
                    <input type="password" required name="password" class="form-control"  minlength="6" id="password" placeholder="{{ tr('password') }}">
                </div>
            </div>

            <div class="form-group">
                <label for="username" class="col-sm-2  control-label">*{{ tr('password_confirmation') }}</label>

                <div class="col-sm-10">
                    <input type="password"  minlength="6" required name="password_confirmation" class="form-control" id="username" placeholder="{{ tr('password_confirmation') }}">
                </div>
            </div>
        @endif

    </div>


    <div class="box-footer">
        
        <button type="reset" class="btn btn-danger">{{ tr('cancel') }}</button>
            
        <button type="submit" class="btn btn-success pull-right" @if(Setting::get('admin_delete_control') == YES ) disabled @endif>{{ tr('submit') }}</button>
    
    </div>

</form>