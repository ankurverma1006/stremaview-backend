@extends('layouts.admin')

@section('title', tr('settings'))

@section('content-header') 

{{tr('settings')}} 

<a href="#" id="help-popover" class="btn btn-danger" style="font-size: 14px;font-weight: 600" title="">{{tr('help_ques_mark')}}</a>

<div id="help-content" style="display: none">

    <ul class="popover-list">
        <li><b>{{tr('paypal')}}- </b>{{tr('minimum_accepted_amount_01')}}</li>
        <li><b>{{tr('stripe')}}- </b>{{tr('minimum_accepted_amount')}}<br> <a target="_blank" href="https://stripe.com/docs/currencies">{{tr('check_references')}}</a></li>
    </ul>
    
</div>

@endsection

@section('styles')

<style>
    
/*  streamview tab */
div.streamview-tab-container{
  z-index: 10;
  background-color: #ffffff;
  padding: 0 !important;
  border-radius: 4px;
  -moz-border-radius: 4px;
  border:1px solid #ddd;
  margin-top: 20px;
  margin-left: 50px;
  -webkit-box-shadow: 0 6px 12px rgba(0,0,0,.175);
  box-shadow: 0 6px 12px rgba(0,0,0,.175);
  -moz-box-shadow: 0 6px 12px rgba(0,0,0,.175);
  background-clip: padding-box;
  opacity: 0.97;
  filter: alpha(opacity=97);
}
div.streamview-tab-menu{
  padding-right: 0;
  padding-left: 0;
  padding-bottom: 0;
}
div.streamview-tab-menu div.list-group{
  margin-bottom: 0;
}
div.streamview-tab-menu div.list-group>a{
  margin-bottom: 0;
}
div.streamview-tab-menu div.list-group>a .glyphicon,
div.streamview-tab-menu div.list-group>a .fa {
  color: #1e5780;
}
div.streamview-tab-menu div.list-group>a:first-child{
  border-top-right-radius: 0;
  -moz-border-top-right-radius: 0;
}
div.streamview-tab-menu div.list-group>a:last-child{
  border-bottom-right-radius: 0;
  -moz-border-bottom-right-radius: 0;
}
div.streamview-tab-menu div.list-group>a.active,
div.streamview-tab-menu div.list-group>a.active .glyphicon,
div.streamview-tab-menu div.list-group>a.active .fa{
  background-color: #1e5780;
  background-image: #1e5780;
  color: #ffffff;
}
div.streamview-tab-menu div.list-group>a.active:after{
  content: '';
  position: absolute;
  left: 100%;
  top: 50%;
  margin-top: -13px;
  border-left: 0;
  border-bottom: 13px solid transparent;
  border-top: 13px solid transparent;
  border-left: 10px solid #1e5780;
}

div.streamview-tab-content{
  background-color: #ffffff;
  /* border: 1px solid #eeeeee; */
  padding-left: 20px;
  padding-top: 10px;
}

.box-body {
    padding: 0px;
}

div.streamview-tab div.streamview-tab-content:not(.active){
  display: none;
}

.sub-title {
    width: fit-content;
    color: #2c648c;
    font-size: 18px;
    /*border-bottom: 2px dashed #285a86;*/
    padding-bottom: 5px;
}

hr {
    margin-top: 15px;
    margin-bottom: 15px;
}
</style>
@endsection

@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
    <li class="active"><i class="fa fa-gears"></i> {{tr('settings')}}</li>
@endsection

@section('content')

<div class="row">

    <div class="col-md-12">

        @include('notification.notify')

    </div>

    <div class="col-lg-11 col-md-11 col-sm-11 col-xs-11 streamview-tab-container">

        <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 streamview-tab-menu">
            
            <div class="list-group">
                <a href="#" class="list-group-item active text-left">
                    <!-- <h4 class="fa fa-globe"></h4><br/> -->

                    {{tr('site_settings')}}
                </a>
                <a href="#" class="list-group-item text-left">
                    <!-- <h4 class="glyphicon glyphicon-road"></h4><br/> -->
                    {{tr('video_settings')}}
                </a>
                <a href="#" class="list-group-item text-left">
                    <!-- <h4 class="glyphicon glyphicon-home"></h4><br/> -->

                    {{tr('revenue_settings')}}
                </a>
                <a href="#" class="list-group-item text-left">
                    <!-- <h4 class="glyphicon glyphicon-cutlery"></h4><br/> -->

                    {{tr('social_settings')}}
                </a>
                <a href="#" class="list-group-item text-left">
                    <!-- <h4 class="glyphicon glyphicon-credit-card"></h4><br/> -->

                    {{tr('payment_settings')}}
                </a>

                <a href="#" class="list-group-item text-left">
                    <!-- <h4 class="glyphicon glyphicon-credit-card"></h4><br/> -->

                    {{tr('email_settings')}}
                </a>

                <a href="#" class="list-group-item text-left">
                    <!-- <h4 class="glyphicon glyphicon-credit-card"></h4><br/> -->

                    {{tr('site_url_settings')}}
                </a>

                <a href="#" class="list-group-item text-left">
                    <!-- <h4 class="glyphicon glyphicon-credit-card"></h4><br/> -->

                    {{tr('mobile_settings')}}
                </a>

                <a href="#" class="list-group-item text-left">
                    <!-- <h4 class="glyphicon glyphicon-credit-card"></h4><br/> -->

                    {{tr('seo_settings')}}
                </a>

                <a href="#" class="list-group-item text-left">
                    <!-- <h4 class="glyphicon glyphicon-credit-card"></h4><br/> -->

                    {{tr('other_settings')}}
                </a>

            </div>

        </div>

        <div class="col-lg-9 col-md-9 col-sm-9 col-xs-9 streamview-tab">
            
            <!-- flight section -->
            
            <div class="streamview-tab-content active">

                <form action="{{(Setting::get('admin_delete_control') == 1) ? '' : route('admin.save.settings')}}" method="POST" enctype="multipart/form-data" role="form">

                    <div class="box-body">

                        <div class="row">

                            <div class="col-md-12">

                                <h3 class="settings-sub-header text-uppercase"><b>{{tr('site_settings')}}</b></h3>

                                <hr>

                            </div>

                            <div class="col-md-6">

                                <div class="form-group">
                                    
                                    <label for="sitename">{{tr('site_name')}}</label>
                                    
                                    <input type="text" class="form-control" name="site_name" value="{{ Setting::get('site_name') }}" id="sitename" placeholder="Enter sitename">

                                </div>

                                <div class="form-group">
                                   
                                    <label for="site_logo">{{tr('site_logo')}}</label>

                                    <br>

                                    @if(Setting::get('site_logo'))
                                        <img class="settings-img-preview " src="{{Setting::get('site_logo')}}" title="{{Setting::get('sitename')}}">
                                    @endif

                                    <input type="file" id="site_logo" name="site_logo" accept="image/png, image/jpeg">
                                    <p class="help-block">{{tr('image_note_help')}}</p>
                                </div>

                            </div>

                            <div class="col-lg-6">

                                <div class="form-group">

                                    <label for="streaming_url">{{tr('ANGULAR_SITE_URL')}}</label>


                                    <input type="text" value="{{ Setting::get('ANGULAR_SITE_URL')}}" class="form-control" name="ANGULAR_SITE_URL" id="ANGULAR_SITE_URL" placeholder="Enter App URL">
                                </div> 


                                <div class="form-group">

                                    <label for="site_icon">{{tr('site_icon')}}</label>

                                    <br>

                                    @if(Setting::get('site_icon'))
                                        <img class="settings-img-preview " src="{{Setting::get('site_icon')}}" title="{{Setting::get('sitename')}}">
                                    @endif
                                    <input type="file" id="site_icon" name="site_icon" accept="image/png, image/jpeg">
                                    <p class="help-block">{{tr('image_note_help')}}</p>
                                </div>

                            </div>

                        </div>

                    </div>

                    <!-- /.box-body -->

                    <div class="box-footer">

                        <button type="reset" class="btn btn-warning">{{tr('reset')}}</button>

                        @if(Setting::get('admin_delete_control') == 1)
                            <button type="submit" class="btn btn-primary pull-right" disabled>{{tr('submit')}}</button>
                        @else
                            <button type="submit" class="btn bg-blue pull-right">{{tr('submit')}}</button>
                        @endif
                    </div>
                
                </form>

            </div>

            <!-- train section -->
            <div class="streamview-tab-content">
                
                <form action="{{ (Setting::get('admin_delete_control') == 1) ? '' : route('admin.save.common-settings')}}" method="POST" enctype="multipart/form-data" role="form">

                    <div class="box-body">

                        <div class="row">

                            <div class="col-md-12">

                                <h3 class="settings-sub-header text-uppercase"><b>{{tr('video_settings')}}</b></h3>

                                <hr>

                            </div>

                            <div class="col-md-12">

                                <h5 class="sub-title" >{{tr('player_configuration')}}</h5>

                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">

                                    <label for="JWPLAYER_KEY">{{tr('jwplayer_key')}}</label>

                                    <input type="text" value="{{ Setting::get('JWPLAYER_KEY')}}" class="form-control" name="JWPLAYER_KEY" id="JWPLAYER_KEY" placeholder="{{tr('jwplayer_key')}}">
                                </div> 
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">

                                    <label for="socket_url">{{tr('socket_url')}}</label>

                                    <input type="text" value="{{ Setting::get('socket_url')}}" class="form-control" name="socket_url" id="socket_url" placeholder="{{tr('socket_url')}}">
                                </div> 
                            </div>

                            <div class="col-md-12">

                                <h5 class="sub-title" >{{tr('streaming_configuration')}}</h5>

                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">

                                    <label for="streaming_url">{{tr('streaming_url')}}</label>

                                    <br>

                                    <p class="example-note">{{tr('rtmp_settings_note')}}</p>

                                    <input type="text" value="{{ Setting::get('streaming_url')}}" class="form-control" name="streaming_url" id="streaming_url" placeholder="{{tr('enter_streaming_url')}}">
                                </div> 
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="HLS_STREAMING_URL">{{tr('HLS_STREAMING_URL')}}</label>
                                    
                                    <br>

                                    <p class="example-note">{{tr('hls_settings_note')}}</p>

                                    <input type="text" value="{{ Setting::get('HLS_STREAMING_URL')}}" class="form-control" name="HLS_STREAMING_URL" id="HLS_STREAMING_URL" placeholder="{{tr('enter_streaming_url')}}">
                                </div> 
                            </div>

                            <div class="col-md-12">

                                <h5 class="sub-title" >{{tr('s3_settings')}}</h5>

                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="s3_key">{{tr('S3_KEY')}}</label>
                                    <input type="text" class="form-control" name="S3_KEY" id="s3_key" placeholder="{{tr('S3_KEY')}}" value="{{$result['S3_KEY']}}">
                                </div>
                            </div>
                            
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="s3_secret">{{tr('S3_SECRET')}}</label>    
                                    <input type="text" class="form-control" name="S3_SECRET" id="s3_secret" placeholder="{{tr('S3_SECRET')}}" value="{{$result['S3_SECRET']}}">
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="s3_region">{{tr('S3_REGION')}}</label>    
                                    <input type="text" class="form-control" name="S3_REGION" id="s3_region" placeholder="{{tr('S3_REGION')}}" value="{{$result['S3_REGION']}}">
                                </div>
                            </div>
                            
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="s3_bucket">{{tr('S3_BUCKET')}}</label>    
                                    <input type="text" class="form-control" name="S3_BUCKET" id="s3_bucket" placeholder="{{tr('S3_BUCKET')}}" value="{{$result['S3_BUCKET']}}">
                                </div>
                            </div>
                            
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="s3_ses_region">{{tr('S3_SES_REGION')}}</label>    
                                    <input type="text" class="form-control" name="S3_SES_REGION" id="s3_ses_region" placeholder="{{tr('S3_SES_REGION')}}" value="{{$result['S3_SES_REGION']}}">
                                </div>
                            </div>


                        </div>

                    </div>

                    <div class="box-footer">

                        <button type="reset" class="btn btn-warning">{{tr('reset')}}</button>

                        @if(Setting::get('admin_delete_control') == 1)
                            <button type="submit" class="btn btn-primary pull-right" disabled>{{tr('submit')}}</button>
                        @else
                            <button type="submit" class="btn bg-blue pull-right">{{tr('submit')}}</button>
                        @endif
                    </div>
                
                </form>

            </div>

            <!-- Revenue settings -->

            <div class="streamview-tab-content">

                <form action="{{ (Setting::get('admin_delete_control') == 1) ? '' : route('admin.save.common-settings')}}" method="POST" enctype="multipart/form-data" role="form">
                    
                    <div class="box-body">

                        <div class="row">

                            <div class="col-md-12">

                                <h3 class="settings-sub-header text-uppercase"><b>{{tr('revenue_settings')}}</b></h3>

                                <hr>

                            </div>
                            <div class="col-md-12">
                                <div class="form-group">

                                    <label for="video_viewer_count">{{tr('video_viewer_count_size_label')}}</label>

                                    <br>

                                    <p class="example-note">{{tr('video_viewer_count_size_label_note')}}</p>

                                    <input type="text" class="form-control" name="video_viewer_count" value="{{Setting::get('video_viewer_count')  }}" id="video_viewer_count" placeholder="{{tr('video_viewer_count_size_label')}}">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="upload_max_size">{{tr('amount_per_video')}}</label>
                                    
                                    <br>
                                    
                                    <p class="example-note">{{tr('amount_per_video_note')}}</p>

                                    <input type="text" class="form-control" name="amount_per_video" value="{{Setting::get('amount_per_video')  }}" min="0.5" id="amount_per_video" placeholder="{{tr('amount_per_video')}}">

                                </div>
                            </div>

                             <div class="col-md-6">
                                <div class="form-group">

                                    <label for="admin_commission">{{tr('admin_commission')}}</label>

                                    <input type="text" class="form-control" name="admin_commission" value="{{Setting::get('admin_commission')}}" id="admin_commission" placeholder="{{tr('admin_commission')}}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="user_commission">{{tr('moderator_commission')}}</label>
                                    <input type="text" class="form-control" name="user_commission" value="{{Setting::get('user_commission')  }}" id="user_commission" placeholder="{{tr('moderator_commission')}}">
                                </div>
                            </div>
                            <div class="clearfix"></div>

                        </div>
                        
                    </div>

                    <div class="box-footer">

                        <button type="reset" class="btn btn-warning">{{tr('reset')}}</button>

                        @if(Setting::get('admin_delete_control') == 1)
                            <button type="submit" class="btn btn-primary pull-right" disabled>{{tr('submit')}}</button>
                        @else
                            <button type="submit" class="btn bg-blue pull-right">{{tr('submit')}}</button>
                        @endif
                    </div>

                </form>
            
            </div>


            <!-- Social settings -->

            <div class="streamview-tab-content">
                
                <form action="{{ (Setting::get('admin_delete_control') == 1) ? '' : route('admin.save.common-settings')}}" method="POST" enctype="multipart/form-data" role="form">
                    <div class="box-body">

                        <div class="row">

                            <div class="col-md-12">

                                <h3 class="settings-sub-header text-uppercase"><b>{{tr('social_settings')}}</b></h3>

                                <hr>

                            </div>

                            <div class="col-md-12">

                                <h5 class="sub-title" >{{tr('fb_settings')}}</h5>

                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="fb_client_id">{{tr('FB_CLIENT_ID')}}</label>
                                    <input type="text" class="form-control" name="FB_CLIENT_ID" id="fb_client_id" placeholder="{{tr('FB_CLIENT_ID')}}" value="{{$result['FB_CLIENT_ID']}}">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="fb_client_secret">{{tr('FB_CLIENT_SECRET')}}</label>    
                                    <input type="text" class="form-control" name="FB_CLIENT_SECRET" id="fb_client_secret" placeholder="{{tr('FB_CLIENT_SECRET')}}" value="{{$result['FB_CLIENT_SECRET']}}">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="fb_call_back">{{tr('FB_CALL_BACK')}}</label>    
                                    <input type="text" class="form-control" name="FB_CALL_BACK" id="fb_call_back" placeholder="{{tr('FB_CALL_BACK')}}" value="{{$result['FB_CALL_BACK']}}">
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <!-- <div class="col-md-12">

                                <h5 class="sub-title" >{{tr('twitter_settings')}}</h5>

                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="twitter_client_id">{{tr('TWITTER_CLIENT_ID')}}</label>
                                    <input type="text" class="form-control" name="TWITTER_CLIENT_ID" id="twitter_client_id" placeholder="{{tr('TWITTER_CLIENT_ID')}}" value="{{$result['TWITTER_CLIENT_ID']}}">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="twitter_client_secret">{{tr('TWITTER_CLIENT_SECRET')}}</label>    
                                    <input type="text" class="form-control" name="TWITTER_CLIENT_SECRET" id="twitter_client_secret" placeholder="{{tr('TWITTER_CLIENT_SECRET')}}" value="{{$result['TWITTER_CLIENT_SECRET']}}">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="twitter_call_back">{{tr('TWITTER_CALL_BACK')}}</label>    
                                    <input type="text" class="form-control" name="TWITTER_CALL_BACK" id="twitter_call_back" placeholder="{{tr('TWITTER_CALL_BACK')}}" value="{{$result['TWITTER_CALL_BACK']}}">
                                </div>
                            </div>
                            <div class="clearfix"></div> -->
                            <div class="col-md-12">

                                <h5 class="sub-title" >{{tr('google_settings')}}</h5>

                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="google_client_id">{{tr('GOOGLE_CLIENT_ID')}}</label>
                                    <input type="text" class="form-control" name="GOOGLE_CLIENT_ID" id="google_client_id" placeholder="{{tr('GOOGLE_CLIENT_ID')}}" value="{{$result['GOOGLE_CLIENT_ID']}}">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="google_client_secret">{{tr('GOOGLE_CLIENT_SECRET')}}</label>    
                                    <input type="text" class="form-control" name="GOOGLE_CLIENT_SECRET" id="google_client_secret" placeholder="{{tr('GOOGLE_CLIENT_SECRET')}}" value="{{$result['GOOGLE_CLIENT_SECRET']}}">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="google_call_back">{{tr('GOOGLE_CALL_BACK')}}</label>    
                                    <input type="text" class="form-control" name="GOOGLE_CALL_BACK" id="google_call_back" placeholder="{{tr('GOOGLE_CALL_BACK')}}" value="{{$result['GOOGLE_CALL_BACK']}}">
                                </div>
                            </div>
                            <div class='clearfix'></div>

                        </div>
                    
                    </div>
                   
                    <div class="box-footer">

                        <button type="reset" class="btn btn-warning">{{tr('reset')}}</button>

                        @if(Setting::get('admin_delete_control') == 1)
                            <button type="submit" class="btn btn-primary pull-right" disabled>{{tr('submit')}}</button>
                        @else
                            <button type="submit" class="btn bg-blue pull-right">{{tr('submit')}}</button>
                        @endif
                    </div>

                </form>
            </div>

            <!-- Payment settings -->

            <div class="streamview-tab-content">
                
                <form action="{{ (Setting::get('admin_delete_control') == 1) ? '' : route('admin.save.common-settings')}}" method="POST" enctype="multipart/form-data" role="form">
                   
                    <div class="box-body">

                        <div class="row">

                            <div class="col-md-12">

                                <h3 class="settings-sub-header text-uppercase"><b>{{tr('payment_settings')}}</b></h3>

                                <hr>

                            </div>

                            <div class="col-md-12">

                                <h5 class="sub-title" >{{tr('paypal_settings')}}</h5>

                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="paypal_id">{{tr('PAYPAL_ID')}}</label>
                                    <input type="text" class="form-control" name="PAYPAL_ID" id="paypal_id" placeholder="{{tr('PAYPAL_ID')}}" value="{{$result['PAYPAL_ID']}}">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="paypal_secret">{{tr('PAYPAL_SECRET')}}</label>    
                                    <input type="text" class="form-control" name="PAYPAL_SECRET" id="paypal_secret" placeholder="{{tr('PAYPAL_SECRET')}}" value="{{$result['PAYPAL_SECRET']}}">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="paypal_mode">{{tr('PAYPAL_MODE')}}</label>    
                                    <input type="text" class="form-control" name="PAYPAL_MODE" id="paypal_mode" placeholder="{{tr('PAYPAL_MODE')}}" value="{{$result['PAYPAL_MODE']}}">
                                </div>
                            </div>

                            <div class="clearfix"></div>

                            <div class="col-md-12">

                                <h5 class="sub-title" >{{tr('stripe_settings')}}</h5>

                            </div>

                             <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="stripe_publishable_key">{{tr('stripe_publishable_key')}}</label>
                                    <input type="text" class="form-control" name="stripe_publishable_key" id="stripe_publishable_key" placeholder="{{tr('stripe_publishable_key')}}" value="{{Setting::get('stripe_publishable_key')}}">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="stripe_secret_key">{{tr('stripe_secret_key')}}</label>
                                    <input type="text" class="form-control" name="stripe_secret_key" id="stripe_secret_key" placeholder="{{tr('stripe_secret_key')}}" value="{{Setting::get('stripe_secret_key')}}">
                                </div>
                            </div>

                        </div>

                    </div>

                    <div class="box-footer">

                        <button type="reset" class="btn btn-warning">{{tr('reset')}}</button>

                        @if(Setting::get('admin_delete_control') == 1)
                            <button type="submit" class="btn btn-primary pull-right" disabled>{{tr('submit')}}</button>
                        @else
                            <button type="submit" class="btn bg-blue pull-right">{{tr('submit')}}</button>
                        @endif
                    </div>
                </form>
            
            </div>

            <!-- Email settings -->

            <div class="streamview-tab-content">
                <form action="{{route('admin.email.settings.save')}}" method="POST" enctype="multipart/form-data" role="form">
                            
                    <div class="box-body">

                        <div class="row">

                            <div class="col-md-12">

                                <h3 class="settings-sub-header text-uppercase"><b>{{tr('email_settings')}}</b></h3>

                                <hr>

                            </div>


                            <div class="col-md-6">

                                <div class="form-group">
                                    <label for="MAIL_DRIVER">{{tr('MAIL_DRIVER')}}</label>
                                    <input type="text" value="{{ $result['MAIL_DRIVER']}}" class="form-control" name="MAIL_DRIVER" id="MAIL_DRIVER" placeholder="Enter {{tr('MAIL_DRIVER')}}">
                                </div>

                                <div class="form-group">
                                    <label for="MAIL_HOST">{{tr('MAIL_HOST')}}</label>
                                    <input type="text" class="form-control" value="{{$result['MAIL_HOST']}}" name="MAIL_HOST" id="MAIL_HOST" placeholder="{{tr('MAIL_HOST')}}">
                                </div>

                                <div class="form-group">
                                    <label for="MAIL_PORT">{{tr('MAIL_PORT')}}</label>
                                    <input type="text" class="form-control" value="{{$result['MAIL_PORT']}}" name="MAIL_PORT" id="MAIL_PORT" placeholder="{{tr('MAIL_PORT')}}">
                                </div>

                            </div>

                            <div class="col-md-6">

                                <div class="form-group">
                                    <label for="MAIL_USERNAME">{{tr('MAIL_USERNAME')}}</label>
                                    <input type="text" class="form-control" value="{{$result['MAIL_USERNAME'] }}" name="MAIL_USERNAME" id="MAIL_USERNAME" placeholder="{{tr('MAIL_USERNAME')}}">
                                </div>

                                <div class="form-group">
                                    <label for="MAIL_PASSWORD">{{tr('MAIL_PASSWORD')}}</label>
                                    <input type="password" class="form-control" name="MAIL_PASSWORD" id="MAIL_PASSWORD" placeholder="{{tr('MAIL_PASSWORD')}}" value="{{$result['MAIL_PASSWORD']}}">
                                </div>

                                <div class="form-group">
                                    <label for="MAIL_ENCRYPTION">{{tr('MAIL_ENCRYPTION')}}</label>
                                    <input type="text" class="form-control" value="{{$result['MAIL_ENCRYPTION'] }}" name="MAIL_ENCRYPTION" id="MAIL_ENCRYPTION" placeholder="{{tr('MAIL_ENCRYPTION')}}">
                                </div>

                            </div>

                            <div class="clearfix"></div>

                            @if($result['MAIL_DRIVER'] == 'mailgun')

                            <div class="col-md-12">

                                <div class="form-group">
                                    <label for="MAILGUN_DOMAIN">{{tr('MAILGUN_DOMAIN')}}</label>
                                    <input type="text" class="form-control" value="{{$result['MAILGUN_DOMAIN'] }}" name="MAILGUN_DOMAIN" id="MAILGUN_DOMAIN" placeholder="{{tr('MAILGUN_DOMAIN')}}">
                                </div>

                                <div class="form-group">
                                    <label for="MAILGUN_SECRET">{{tr('MAILGUN_SECRET')}}</label>
                                    <input type="text" class="form-control" name="MAILGUN_SECRET" id="MAILGUN_SECRET" placeholder="{{tr('MAILGUN_SECRET')}}" value="{{$result['MAILGUN_SECRET']}}">
                                </div>

                            </div>

                            @endif

                        </div>

                    </div>

                    <div class="box-footer">

                        <button type="reset" class="btn btn-warning">{{tr('reset')}}</button>

                        @if(Setting::get('admin_delete_control') == 1)
                            <button type="submit" class="btn btn-primary pull-right" disabled>{{tr('submit')}}</button>
                        @else
                            <button type="submit" class="btn bg-blue pull-right">{{tr('submit')}}</button>
                        @endif

                    </div>

                </form>
            </div>

            <!-- Company Settings  -->

            <div class="streamview-tab-content">
               
                <form action="{{ (Setting::get('admin_delete_control') == 1) ? '' : route('admin.save.common-settings')}}" method="POST" enctype="multipart/form-data" role="form">
                    
                    <div class="box-body">
                        <div class="row">

                            <div class="col-md-12">

                                <h3 class="settings-sub-header text-uppercase"><b>{{tr('site_url_settings')}}</b></h3>

                                <hr>

                            </div>

                            <div class="col-md-6">
                                <div class="form-group">

                                    <label for="facebook_link">{{tr('facebook_link')}}</label>

                                    <input type="url" class="form-control" name="facebook_link" id="facebook_link"
                                    value="{{Setting::get('facebook_link')}}" placeholder="{{tr('facebook_link')}}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="linkedin_link">{{tr('linkedin_link')}}</label>

                                    <input type="url" class="form-control" name="linkedin_link" value="{{Setting::get('linkedin_link')  }}" id="linkedin_link" placeholder="{{tr('linkedin_link')}}">

                                </div>
                            </div>

                             <div class="col-md-6">
                                <div class="form-group">

                                    <label for="twitter_link">{{tr('twitter_link')}}</label>

                                    <input type="url" class="form-control" name="twitter_link" value="{{Setting::get('twitter_link')  }}" id="twitter_link" placeholder="{{tr('twitter_link')}}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="google_plus_link">{{tr('google_plus_link')}}</label>
                                    <input type="url" class="form-control" name="google_plus_link" value="{{Setting::get('google_plus_link')  }}" id="google_plus_link" placeholder="{{tr('google_plus_link')}}">
                                </div>
                            </div>


                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pinterest_link">{{tr('pinterest_link')}}</label>
                                    <input type="url" class="form-control" name="pinterest_link" value="{{Setting::get('pinterest_link')  }}" id="pinterest_link" placeholder="{{tr('pinterest_link')}}">
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            
                        </div>
                    
                    </div>
                    
                    <div class="box-footer">

                        <button type="reset" class="btn btn-warning">{{tr('reset')}}</button>

                        @if(Setting::get('admin_delete_control') == 1)
                            <button type="submit" class="btn btn-primary pull-right" disabled>{{tr('submit')}}</button>
                        @else
                            <button type="submit" class="btn bg-blue pull-right">{{tr('submit')}}</button>
                        @endif
                    </div>
                </form>
            </div>

            <!-- APP Url Settings -->

            <div class="streamview-tab-content">
                <form action="{{ (Setting::get('admin_delete_control') == 1) ? '' : route('admin.mobile.settings.save')}}" method="POST" enctype="multipart/form-data" role="form">
                    <div class="box-body">
                            
                        <div class="row">

                            <div class="col-md-12">

                                <h3 class="settings-sub-header text-uppercase"><b>{{tr('mobile_settings')}}</b></h3>

                                <hr>

                            </div>

                            <div class="col-md-12">

                                <h5 class="sub-title" >{{tr('fcm_settings')}}</h5>

                            </div>

                            <div class="col-md-6">
                                <div class="form-group">

                                    <label for="FCM_SERVER_KEY">{{tr('FCM_SERVER_KEY')}}</label>

                                    <input type="text" class="form-control" name="FCM_SERVER_KEY" id="FCM_SERVER_KEY"
                                    value="{{envfile('FCM_SERVER_KEY')}}" placeholder="{{tr('FCM_SERVER_KEY')}}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">

                                    <label for="FCM_SENDER_ID">{{tr('FCM_SENDER_ID')}}</label>

                                    <input type="text" class="form-control" name="FCM_SENDER_ID" id="FCM_SENDER_ID"
                                    value="{{envfile('FCM_SENDER_ID')}}" placeholder="{{tr('FCM_SENDER_ID')}}">
                                </div>
                            </div>


                            <div class="col-md-12">

                                <h5 class="sub-title" >{{tr('app_url_settings')}}</h5>

                            </div>

                            <div class="col-md-6">

                                <div class="form-group">

                                    <label for="appstore">{{tr('appstore')}}</label>

                                    <input type="url" class="form-control" name="appstore" id="appstore"
                                    value="{{Setting::get('appstore')}}" placeholder="{{tr('appstore')}}">

                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="playstore">{{tr('playstore')}}</label>

                                    <input type="url" class="form-control" name="playstore" value="{{Setting::get('playstore')  }}" id="playstore" placeholder="{{tr('playstore')}}">

                                </div>
                            </div>
                            
                        </div>

                    </div>

                    <div class="box-footer">

                        <button type="reset" class="btn btn-warning">{{tr('reset')}}</button>

                        @if(Setting::get('admin_delete_control') == 1)
                            <button type="submit" class="btn btn-primary pull-right" disabled>{{tr('submit')}}</button>
                        @else
                            <button type="submit" class="btn bg-blue pull-right">{{tr('submit')}}</button>
                        @endif
                    </div>
                </form>
            </div>

            <!-- SEO Settings -->

            <div class="streamview-tab-content">

                <form action="{{ (Setting::get('admin_delete_control') == 1) ? '' : route('admin.save.settings')}}" method="POST" enctype="multipart/form-data" role="form">

                    <div class="box-body">

                        <div class="row">

                            <div class="col-md-12">

                                <h3 class="settings-sub-header text-uppercase"><b>{{tr('seo_settings')}}</b></h3>

                                <hr>

                            </div>

                            <div class="col-md-5">

                                <div class="form-group">
                                    <label>{{ tr('meta_title') }}</label>
                                     <input type="text" name="meta_title" value="{{ Setting::get('meta_title', '')  }}" required class="form-control" placeholder="{{tr('meta_title')}}">
                                </div>
                               
                            </div>

                            <div class="col-md-5 col-md-offset-1">

                                <div class="form-group">
                                    <label for="meta_author">{{tr('meta_author')}}</label>
                                    <input type="text" class="form-control" value="{{Setting::get('meta_author')  }}" name="meta_author" id="meta_author" placeholder="{{tr('meta_author')}}">
                                </div> 
                            
                            </div>

                            <div class="clearfix"></div>

                            <div class="col-md-12">

                                <div class="form-group">
                                    <label for="meta_keywords">{{tr('meta_keywords')}}</label>
                                    <textarea class="form-control" id="meta_keywords" name="meta_keywords">{{Setting::get('meta_keywords')}}</textarea>
                                </div>

                                <div class="form-group">
                                    <label for="meta_description">{{tr('meta_description')}}</label>
                                    <textarea class="form-control" id="meta_description" name="meta_description">{{Setting::get('meta_description')}}</textarea>
                                </div>  

                            </div>

                            <div class="clearfix"></div>


                        </div>
                    
                    </div>

                    <div class="box-footer">

                        <button type="reset" class="btn btn-warning">{{tr('reset')}}</button>

                        @if(Setting::get('admin_delete_control') == 1)
                            <button type="submit" class="btn btn-primary pull-right" disabled>{{tr('submit')}}</button>
                        @else
                            <button type="submit" class="btn bg-blue pull-right">{{tr('submit')}}</button>
                        @endif
                    </div>

                </form>
            </div>

            <!-- OTHER Settings -->

            <div class="streamview-tab-content">
                <form action="{{(Setting::get('admin_delete_control') == 1) ? '' : route('admin.other.settings.save')}}" method="POST" enctype="multipart/form-data" r  ole="form">
                            
                    <div class="box-body"> 
                        <div class="row"> 

                            <div class="col-md-12">

                                <h3 class="settings-sub-header text-uppercase"><b>{{tr('other_settings')}}</b></h3>

                                <hr>

                            </div>

                            <div class="col-lg-12">

                                <div class="form-group">
                                    <label for="google_analytics">{{tr('google_analytics')}}</label>
                                    <textarea class="form-control" id="google_analytics" name="google_analytics">{{Setting::get('google_analytics')}}</textarea>
                                </div>

                            </div> 

                            <div class="col-lg-12">

                                <div class="form-group">
                                    <label for="header_scripts">{{tr('header_scripts')}}</label>
                                    <textarea class="form-control" id="header_scripts" name="header_scripts">{{Setting::get('header_scripts')}}</textarea>
                                </div>

                            </div> 

                            <div class="col-lg-12">

                                <div class="form-group">
                                    <label for="body_scripts">{{tr('body_scripts')}}</label>
                                    <textarea class="form-control" id="body_scripts" name="body_scripts">{{Setting::get('body_scripts')}}</textarea>
                                </div>
                            </div> 

                            <div class="col-lg-12">

                                <div class="form-group">

                                    <label for="email_notification">{{tr('email_notification')}}</label>
                                    <div class="clearfix"></div>

                                    <input type="checkbox" name="email_notification" value="1" id="email_notification" @if(Setting::get('email_notification')) checked @endif>{{tr('enable_email_notification_to_user')}}
                                </div>

                            </div>

                            @if(Setting::get('admin_language_control') == 0)

                            <div class="col-lg-4">

                                <div class="form-group">
                                    <label for="default_lang">{{tr('default_lang')}}</label>

                                    <select class="form-control select2" name="default_lang" id="default_lang" required>

                                        <option value="">{{tr('language')}}</option>
                                            @foreach($languages as $h => $language)
                                                <option value="{{$language->folder_name}}" {{(Setting::get('default_lang') == $language->folder_name) ? 'selected' : Setting::get('default_lang')}}>{{$language->language}}({{$language->folder_name}})</option>
                                            @endforeach
                                        
                                        </select>
                                </div> 

                            </div>  

                            @endif

                        </div>
                    </div>
                          <!-- /.box-body -->

                    <div class="box-footer">

                        <button type="reset" class="btn btn-warning">{{tr('reset')}}</button>

                        @if(Setting::get('admin_delete_control') == 1)
                            <button type="submit" class="btn btn-primary pull-right" disabled>{{tr('submit')}}</button>
                        @else
                            <button type="submit" class="btn bg-blue pull-right">{{tr('submit')}}</button>
                        @endif
                    </div>
                </form>
            </div>

        </div>
    
    </div>
    
    <div class="clearfix"></div>

</div>


@endsection


@section('scripts')

<script type="text/javascript">
    
    $(document).ready(function() {
        $("div.streamview-tab-menu>div.list-group>a").click(function(e) {
            e.preventDefault();
            $(this).siblings('a.active').removeClass("active");
            $(this).addClass("active");
            var index = $(this).index();
            $("div.streamview-tab>div.streamview-tab-content").removeClass("active");
            $("div.streamview-tab>div.streamview-tab-content").eq(index).addClass("active");
        });
    });
</script>
@endsection