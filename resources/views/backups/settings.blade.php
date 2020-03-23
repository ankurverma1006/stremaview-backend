<?php /** <div class="col-md-12" style="display: none;">
    <div class="nav-tabs-custom">

            <ul class="nav nav-tabs">
                <li class="active"><a href="#site_setting" data-toggle="tab">{{tr('site_settings')}}</a></li>
                <li><a href="#video_settings" data-toggle="tab">{{tr('video_settings')}}</a></li>
                <li><a href="#revenue_settings" data-toggle="tab">{{tr('revenue_settings')}}</a></li>
                <li><a href="#social_settings" data-toggle="tab">{{tr('social_settings')}}</a></li>
                <li><a href="#payment_settings" data-toggle="tab">{{tr('payment_settings')}}</a></li>
                <li><a href="#email_settings" data-toggle="tab">{{tr('email_settings')}}</a></li>
                <li><a href="#site_url_settings" data-toggle="tab">{{tr('site_url_settings')}}</a></li>
                <li><a href="#mobile_settings" data-toggle="tab">{{tr('mobile_settings')}}</a></li>
                <li><a href="#other_settings" data-toggle="tab">{{tr('other_settings')}}</a></li>
                 <li><a href="#seo_settings" data-toggle="tab">{{tr('seo_settings')}}</a></li>
            </ul>
           
            <div class="tab-content">
               
                <div class="active tab-pane" id="site_setting">

                    <form action="{{(Setting::get('admin_delete_control') == 1) ? '' : route('admin.save.settings')}}" method="POST" enctype="multipart/form-data" role="form">

                        <div class="box-body">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sitename">{{tr('site_name')}}</label>
                                    <input type="text" class="form-control" name="site_name" value="{{ Setting::get('site_name') }}" id="sitename" placeholder="Enter sitename">
                                </div>
                            </div>

                            <div class="col-lg-6">

                                <div class="form-group">

                                    <label for="streaming_url">{{tr('ANGULAR_SITE_URL')}}</label>

                                    <!-- <p class="example-note">{{tr('angular_url_note')}}</p> -->

                                    <input type="text" value="{{ Setting::get('ANGULAR_SITE_URL')}}" class="form-control" name="ANGULAR_SITE_URL" id="ANGULAR_SITE_URL" placeholder="Enter App URL">
                                </div> 

                            </div>


                            <div class="col-md-3">
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
                            <div class="col-md-3">
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
                      <!-- /.box-body -->

                      <div class="box-footer">
                        @if(Setting::get('admin_delete_control') == 1)
                            <button type="submit" class="btn btn-primary" disabled>{{tr('submit')}}</button>
                        @else
                            <button type="submit" class="btn btn-primary">{{tr('submit')}}</button>
                        @endif
                      </div>
                    
                    </form>
                
                </div>

                <div class="tab-pane" id="video_settings">

                    <form action="{{ (Setting::get('admin_delete_control') == 1) ? '' : route('admin.save.common-settings')}}" method="POST" enctype="multipart/form-data" role="form">


                        <div class="box-body">

                            <h3 class="settings-sub-header">{{tr('player_configuration')}}</h3>
                            <hr>

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

                            <div class="clearfix"></div>

                            <!-- Streaming Configuration start -->

                            <h3 class="settings-sub-header">{{tr('streaming_configuration')}}</h3>
                            <hr>


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

                            <div class="clearfix"></div>

                            <!-- Streaming Configuration END -->

                            <h3 class="settings-sub-header">{{tr('s3_settings')}}</h3>
                            <hr>

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
                            <div class="clearfix"></div>

                        </div>
                        <div class="box-footer">
                            @if(Setting::get('admin_delete_control') == 1) 
                                <button type="submit" class="btn btn-primary" disabled>{{tr('submit')}}</button>
                            @else
                                <button type="submit" class="btn btn-primary">{{tr('submit')}}</button>
                            @endif
                      </div>
                    </form>

                </div>

                <div class="tab-pane" id="revenue_settings">

                    <form action="{{ (Setting::get('admin_delete_control') == 1) ? '' : route('admin.save.common-settings')}}" method="POST" enctype="multipart/form-data" role="form">
                        <div class="box-body">
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
                        <div class="box-footer">
                            @if(Setting::get('admin_delete_control') == 1) 
                                <button type="submit" class="btn btn-primary" disabled>{{tr('submit')}}</button>
                            @else
                                <button type="submit" class="btn btn-primary">{{tr('submit')}}</button>
                            @endif
                      </div>
                    </form>

                </div>

                <div class="tab-pane" id="social_settings">

                    <form action="{{ (Setting::get('admin_delete_control') == 1) ? '' : route('admin.save.common-settings')}}" method="POST" enctype="multipart/form-data" role="form">
                        <div class="box-body">
                            <h4>{{tr('fb_settings')}}</h4>
                            <hr>
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
                            <!-- <h4>{{tr('twitter_settings')}}</h4>
                            <hr>
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
                            <h4>{{tr('google_settings')}}</h4>
                            <hr>
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
                        <div class="box-footer">
                            @if(Setting::get('admin_delete_control') == 1) 
                                <button type="submit" class="btn btn-primary" disabled>{{tr('submit')}}</button>
                            @else
                                <button type="submit" class="btn btn-primary">{{tr('submit')}}</button>
                            @endif
                      </div>
                    </form>

                </div>

                <div class="tab-pane" id="payment_settings">

                    <form action="{{ (Setting::get('admin_delete_control') == 1) ? '' : route('admin.save.common-settings')}}" method="POST" enctype="multipart/form-data" role="form">
                        <div class="box-body">

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

                            <hr>

                            <h3>{{tr('stripe_settings')}}</h3>

                            <hr>

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
                        <div class="box-footer">
                            @if(Setting::get('admin_delete_control') == 1) 
                                <button type="submit" class="btn btn-primary" disabled>{{tr('submit')}}</button>
                            @else
                                <button type="submit" class="btn btn-primary">{{tr('submit')}}</button>
                            @endif
                      </div>
                    </form>

                </div>

                <div class="tab-pane" id="email_settings">
                    <form action="{{route('admin.email.settings.save')}}" method="POST" enctype="multipart/form-data" role="form">
                        
                        <div class="box-body">

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
                      <!-- /.box-body -->

                        <div class="box-footer">
                            @if(Setting::get('admin_delete_control'))
                                <a href="#" class="btn btn-success pull-right" disabled>{{tr('submit')}}</a>
                            @else
                                <button type="submit" class="btn btn-success pull-right">{{tr('submit')}}</button>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="tab-pane" id="site_url_settings">

                    <form action="{{ (Setting::get('admin_delete_control') == 1) ? '' : route('admin.save.common-settings')}}" method="POST" enctype="multipart/form-data" role="form">
                        <div class="box-body">
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
                        <div class="box-footer">
                            @if(Setting::get('admin_delete_control') == 1) 
                                <button type="submit" class="btn btn-primary" disabled>{{tr('submit')}}</button>
                            @else
                                <button type="submit" class="btn btn-primary">{{tr('submit')}}</button>
                            @endif
                      </div>
                    </form>

                </div>

                <div class="tab-pane" id="mobile_settings">

                    <form action="{{ (Setting::get('admin_delete_control') == 1) ? '' : route('admin.save.settings')}}" method="POST" enctype="multipart/form-data" role="form">
                        
                        <div class="box-body">

                            <div class="col-md-12">

                                <h4 class="text-uppercase text-blue">{{tr('fcm_settings')}}</h4>

                            </div>

                            <div class="col-md-6">
                                <div class="form-group">

                                    <label for="FCM_SERVER_KEY">{{tr('FCM_SERVER_KEY')}}</label>

                                    <input type="url" class="form-control" name="FCM_SERVER_KEY" id="FCM_SERVER_KEY"
                                    value="{{envfile('FCM_SERVER_KEY')}}" placeholder="{{tr('FCM_SERVER_KEY')}}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">

                                    <label for="FCM_SENDER_ID">{{tr('FCM_SENDER_ID')}}</label>

                                    <input type="url" class="form-control" name="FCM_SENDER_ID" id="FCM_SENDER_ID"
                                    value="{{envfile('FCM_SENDER_ID')}}" placeholder="{{tr('FCM_SENDER_ID')}}">
                                </div>
                            </div>

                            <div class="col-md-12">

                                <h4 class="text-uppercase text-blue">{{tr('app_url_settings')}}</h4>

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

                        <div class="box-footer">
                            @if(Setting::get('admin_delete_control') == 1) 
                                <button type="submit" class="btn btn-primary" disabled>{{tr('submit')}}</button>
                            @else
                                <button type="submit" class="btn btn-primary">{{tr('submit')}}</button>
                            @endif
                      </div>
                    </form>

                </div>

                <div class="tab-pane" id="other_settings">

                    <form action="{{(Setting::get('admin_delete_control') == 1) ? '' : route('admin.other.settings.save')}}" method="POST" enctype="multipart/form-data" r  ole="form">
                        
                        <div class="box-body"> 

                            <div class="col-md-6">

                                <div class="form-group">
                                    <label for="FCM_SERVER_KEY">{{tr('FCM_SERVER_KEY')}}</label>

                                    <input type="" name="">
                                    <textarea class="form-control" id="FCM_SERVER_KEY" name="FCM_SERVER_KEY">{{Setting::get('FCM_SERVER_KEY')}}</textarea>
                                </div>

                            </div>

                            <div class="col-md-6">

                                <div class="form-group">
                                    <label for="FCM_SENDER_ID">{{tr('FCM_SENDER_ID')}}</label>
                                    <textarea class="form-control" id="FCM_SENDER_ID" name="FCM_SENDER_ID">{{Setting::get('FCM_SENDER_ID')}}</textarea>
                                </div>

                            </div>

                            <div class="clearfix"></div>

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
                      <!-- /.box-body -->

                      <div class="box-footer">
                        @if(Setting::get('admin_delete_control') == 1) 
                            <button type="submit" class="btn btn-primary" disabled>{{tr('submit')}}</button>
                        @else
                            <button type="submit" class="btn btn-primary">{{tr('submit')}}</button>
                        @endif
                      </div>
                    </form>
                
                </div>

                <div class="tab-pane" id="seo_settings">

                    @if(Setting::get('admin_delete_control'))

                        <form class="form-horizontal" action="#" role="form">

                    @else

                        <form action="{{route('admin.save.settings')}}" method="POST" enctype="multipart/form-data" role="form">

                    @endif

                        <div class="box-body">

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

                        <div class="box-footer">
                            <button type="reset" class="btn btn-warning">{{tr('cancel')}}</button>
                            <button type="submit" class="btn btn-success pull-right" @if(Setting::get('admin_delete_control')) disabled @endif>{{tr('submit')}}</button>                            
                        </div>
                    </form>

                </div>

            </div>
        </div>
    </div>

</div> */?>
