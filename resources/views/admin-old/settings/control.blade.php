@extends('layouts.admin')

@section('title', tr('settings'))

@section('content-header', tr('settings'))

@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
    <li class="active"><i class="fa fa-money"></i> {{tr('settings')}}</li>
@endsection

@section('content')

@include('notification.notify')

    <div class="row">

        <div class="col-md-6">
            <div class="box box-danger">
                <div class="box-header with-border">

                    <h3 class="box-title">{{tr('settings')}}</h3>

                </div>

                <form action="{{route('save_admin_control')}}" method="POST" role="form">

                    <div class="box-body">


                        <div class="form-group">
                            <label>{{ tr('admin_delete_control') }}</label>
                            <br>
                            <label>
                                <input required type="radio" name="admin_delete_control" value="1" class="flat-red" @if(Setting::get('admin_delete_control') == 1) checked @endif>
                                {{tr('yes')}}
                            </label>

                            <label>
                                <input required type="radio" name="admin_delete_control" class="flat-red"  value="0" @if(Setting::get('admin_delete_control') == 0) checked @endif>
                                {{tr('no')}}
                            </label>
                        </div>

                        <div class="form-group">
                            <label>{{ tr('ffmpeg_installed') }}</label>
                            <br>
                            <label>
                                <input required type="radio" name="ffmpeg_installed" value="1" class="flat-red" @if(Setting::get('ffmpeg_installed') == 1) checked @endif>
                                {{tr('yes')}}
                            </label>

                            <label>
                                <input required type="radio" name="ffmpeg_installed" class="flat-red"  value="0" @if(Setting::get('ffmpeg_installed') == 0) checked @endif>
                                {{tr('no')}}
                            </label>
                        </div>


                        <div class="form-group">
                            <label>{{tr('spam_video_enable')}}</label>
                            <br>
                            <label>
                                <input required type="radio" name="is_spam" value="1" class="flat-red" @if(Setting::get('is_spam')) checked @endif>
                                {{tr('yes')}}
                            </label>

                            <label>
                                <input required type="radio" name="is_spam" class="flat-red"  value="0" @if(!Setting::get('is_spam')) checked @endif>
                                {{tr('no')}}
                            </label>
                        </div>

                        <div class="form-group">
                            <label>{{tr('default_subscription')}}</label>
                            <br>
                            <label>
                                <input required type="radio" name="is_subscription" value="1" class="flat-red" @if(Setting::get('is_subscription')) checked @endif>
                                {{tr('yes')}}
                            </label>

                            <label>
                                <input required type="radio" name="is_subscription" class="flat-red"  value="0" @if(!Setting::get('is_subscription')) checked @endif>
                                {{tr('no')}}
                            </label>
                        </div>

                        <div class="form-group">

                            <label>{{tr('pay_per_view_display_hide')}}</label>

                            <br>
                            
                            <label>
                                <input required type="radio" name="is_payper_view" value="1" class="flat-red" @if(Setting::get('is_payper_view')) checked @endif>
                                {{tr('yes')}}
                            </label>

                            <label>
                                <input required type="radio" name="is_payper_view" class="flat-red"  value="0" @if(!Setting::get('is_payper_view')) checked @endif>
                                {{tr('no')}}
                            </label>
                        </div>

                        <div class="form-group">

                            <label>{{tr('email_verify_control')}}</label>

                            <br>
                            
                            <label>
                                <input required type="radio" name="email_verify_control" value="1" class="flat-red" @if(Setting::get('email_verify_control')) checked @endif>
                                {{tr('yes')}}
                            </label>

                            <label>
                                <input required type="radio" name="email_verify_control" class="flat-red"  value="0" @if(!Setting::get('email_verify_control')) checked @endif>
                                {{tr('no')}}
                            </label>
                        </div>

                         <div class="form-group">
                            <label>{{ tr('no_of_static_pages') }}</label>
                            <br>
                            <input type="text" name="no_of_static_pages" class="form-control" value="{{Setting::get('no_of_static_pages')}}" pattern="[0-9]{1,}" title="Enter digits between 7 to 15" required>
                        </div>

                         <hr>
                        <div class="form-group">
                           
                                <label for="post_max_size">{{tr('post_max_size_label')}}</label>
                                <br> 
                                <p class="example-note">{{tr('post_max_size_label_note')}}</p>
                                <input type="text" class="form-control" name="post_max_size" value="{{ Setting::get('post_max_size')  }}" id="post_max_size" placeholder="{{tr('post_max_size_label')}}">
                            
                        </div>

                        <div class="form-group">
                            <label for="upload_max_size">{{tr('max_upload_size_label')}}</label>
                            <br>

                            <p class="example-note">{{tr('max_upload_size_label_note')}}</p> 
                            <input type="text" class="form-control" name="upload_max_size" value="{{Setting::get('upload_max_size')  }}" id="upload_max_size" placeholder="{{tr('max_upload_size_label')}}">
                        </div>
                        <hr>
        
                        <div class="form-group">
                            <label for="token_expiry_hour">{{tr('token_expiry_hour')}}</label>

                            <input type="number" class="form-control" name="token_expiry_hour" value="{{Setting::get('token_expiry_hour')  }}" id="token_expiry_hour" placeholder="{{tr('token_expiry_hour')}}" pattern="[0-9]{1,}" maxlength="2">
                        </div>
                
                        <div class="form-group">
                            <label for="custom_users_count">{{tr('custom_user')}}</label>
                            <input type="text" class="form-control" value="{{ Setting::get('custom_users_count')}}" name=" custom_users_count" id="custom_users_count" placeholder="{{tr('custom_user')}}">
                        </div>


                  </div>
                  <!-- /.box-body -->

                  <div class="box-footer">
                    <button type="submit" class="btn btn-primary">{{tr('submit')}}</button>
                  </div>
                </form>

            </div>
        </div>

    </div>


@endsection