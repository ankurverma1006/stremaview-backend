
<form class="form-horizontal" action="{{ Setting::get('admin_delete_control') == YES ? '#' : route('admin.users.save')  }}" method="POST" enctype="multipart/form-data" role="form">

    <div class="box-body">
        <input type="hidden" name="user_id" value="{{ $user_details->id  }}">

        <div class="form-group">
            <label for="username" class="col-sm-2 control-label">*{{ tr('username')  }}</label>

            <div class="col-sm-10">
                <input type="text" required title="{{ tr('only_alphanumeric')  }}" name="name" value="{{ old('name') ?: $user_details->name  }}" class="form-control" id="username" placeholder="{{ tr('name')  }}">
            </div>
        </div>

        <div class="form-group">
            <label for="email" class="col-sm-2 control-label">*{{ tr('email')  }}</label>
            <div class="col-sm-10">
                <input type="email" required class="form-control" value="{{ old('email') ?: $user_details->email  }}" id="email" name="email" placeholder="{{ tr('email')  }}">
            </div>
        </div>

        <div class="form-group">
            <label for="mobile" class="col-sm-2 control-label">*{{ tr('mobile')  }}</label>

            <div class="col-sm-10">
                <input type="text" required  minlength="4" maxlength="16" name="mobile" value="{{ old('mobile') ?: $user_details->mobile  }}" class="form-control" id="mobile" placeholder="{{ tr('mobile')  }}">
            </div>
        </div>

        <div class="form-group">
            <label for="picture" class="col-sm-2 control-label">*{{ tr('picture') }}</label>
            <div class="col-sm-10">
                <input type="file" accept="image/png, image/jpeg" id="picture" name="picture" placeholder="{{ tr('picture') }}"  value="{{ old('picture') ?: $user_details->picture  }}">
            </div>
        </div>

        @if($user_details->id == '')

            <div class="form-group">
                <label for="password" class="col-sm-2 control-label">* {{ tr('password') }}</label>

                <div class="col-sm-10">
                    <input type="password" required  name="password" pattern=".{6,}" title="{{ tr('password_notes') }}" class="form-control" id="password" placeholder="{{ tr('password') }}" value="{{ old('password') }}">
                </div>
            </div>

            <div class="form-group">
                <label for="username" class="col-sm-2  control-label">* {{ tr('password_confirmation') }}</label>

                <div class="col-sm-10">
                    <input type="password" required pattern=".{6,}"  title="{{ tr('password_notes') }}"  name="password_confirmation" class="form-control" id="username" placeholder="{{ tr('password_confirmation') }}" value="{{ old('password_confirmation') }}">
                </div>
            </div>

        @endif

    </div>

    <input type="hidden" name="timezone" value="" id="userTimezone">

    <div class="box-footer">
        <button type="reset" class="btn btn-danger">{{ tr('cancel')  }}</button>
        
        <button type="submit" class="btn btn-success pull-right" @if(Setting::get('admin_delete_control') == YES) disabled @endif) >{{ tr('submit')  }}</button>        
    </div>


</form>
