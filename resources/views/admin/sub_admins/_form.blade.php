
<div class="row">

    <div class="col-md-10">
        
        <div class="box box-primary">

            <div class="box-header label-primary">
                <b style="font-size:18px;">@yield('title')</b>
                <a href="{{ route('admin.sub_admins.index') }}" class="btn btn-default pull-right">{{ tr('sub_admin_view') }}</a>
            </div>

            <form class="form-horizontal" action="{{ route('admin.sub_admins.save') }}" method="POST" enctype="multipart/form-data" role="form">

                <div class="box-body">

                    <input type="hidden" name="timezone" value="{{ $sub_admin_details->timezone }}" id="userTimezone">

                    <input type="hidden" name="sub_admin_id" value="{{ $sub_admin_details->id }}">

                    <div class="form-group">
                        <label for="username" class="col-sm-2 control-label">* {{ tr('username') }}</label>

                        <div class="col-sm-10">
                            <input type="text" required name="name" title="{{ tr('only_alphanumeric') }}" class="form-control" id="username" placeholder="{{ tr('name') }}" value="{{ old('name') ?: $sub_admin_details->name }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email" class="col-sm-2 control-label">* {{ tr('email') }}</label>
                        <div class="col-sm-10">
                            <input type="email" maxlength="255" required class="form-control" id="email" name="email"placeholder="{{ tr('email') }}" value="{{ old('email') ?: $sub_admin_details->email  }}">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="mobile" class="col-sm-2 control-label">* {{ tr('mobile') }}</label>

                        <div class="col-sm-10">
                            <input type="number" required name="mobile" class="form-control" id="mobile" placeholder="{{ tr('mobile') }}" minlength="4" maxlength="16"  value="{{ old('mobile') ?: $sub_admin_details->mobile }}">
                            <br>
                             <small style="color:brown">{{ tr('mobile_note') }}</small>
                        </div>
                    </div>

                    @if(!$sub_admin_details->id)

                    <div class="form-group">
                        <label for="password" class="col-sm-2 control-label">* {{ tr('password') }}</label>

                        <div class="col-sm-10">
                            <input type="password" required  name="password"  title="{{ tr('password_notes') }}" class="form-control" id="password" placeholder="{{ tr('password') }}" value="">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation" class="col-sm-2  control-label">* {{ tr('password_confirmation') }}</label>

                        <div class="col-sm-10">
                            <input type="password" required title="{{ tr('password_notes') }}"  name="password_confirmation" class="form-control" id="password_confirmation" placeholder="{{ tr('password_confirmation') }}" value="">
                        </div>
                    </div>

                    @endif

                    <div class="form-group">
                        <label for="description" class="col-sm-2 control-label">{{ tr('description') }}</label>
                        <div class="col-sm-10">
                           <textarea class="form-control" name="description" placeholder="{{ tr('description') }}">{{ old('description') ?: $sub_admin_details->description}}</textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="picture" class="col-sm-2 control-label">{{ tr('picture')  }}</label>

                        <div class="col-sm-10">
                            <input type="file" name="picture" value="{{ old('picture') ?: $sub_admin_details->picture  }}" class="form-control" id="picture" placeholder="{{ tr('picture') }}">
                        </div>
                    </div>

                </div>

                <div class="box-footer">
                    
                    <button type="reset" class="btn btn-danger">{{ tr('cancel') }}</button>
                    
                    <button type="submit" class="btn btn-success pull-right"  @if(Setting::get('admin_delete_control') == YES) disabled @endif>{{ tr('submit') }}</button>                    
                </div>
                
            </form>
        
        </div>

    </div>

</div>
