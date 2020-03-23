@extends('layouts.admin')

@section('title', tr('view_video'))


@section('content-header') 

{{tr('view_video')}} 

<a href="#" id="help-popover" class="btn btn-danger" style="font-size: 14px;font-weight: 600" title="Any Help ?">HELP ?</a>

<div id="help-content" style="display: none">

    <ul class="popover-list">
        <li><b>{{tr('redeems')}} - </b>{{tr('moderator_earnings')}}</li>
        <li><b>{{tr('viewers_cnt')}} - </b>{{tr('total_watch_count')}} </li>
        <li><b>{{tr('ppv_created_by')}} - </b>{{tr('admin_moderator_names')}} </li>
    </ul>
    
</div>

@endsection

@section('styles')

<style>
    dt {
        padding: 4px !important; 
    }

    dd {
        padding: 4px !important;
    }

    table {
        font-family: arial, sans-serif;
        border-collapse: collapse;
        width: 100%;
    }

    td, th {
        border: 1px solid #dddddd;
        text-align: left;
        padding: 8px;
    }

    tr:nth-child(even) {
        background-color: #f1f1f1;
    }

    td:nth-child(odd) {
        color:#0000008a;
    }

    .rv-desc {
        line-height: 1.6;
        letter-spacing: 0.6px;
        font-size: 14px;
    }

</style>

<style>
    
    .btn-flat {

        cursor: default !important;

    }
    hr {
        margin-bottom: 10px;
        margin-top: 10px;
    }

    .box-title {
        line-height: 1.5;
    }

    .ppv-amount-label  {

        font-size: 16px; 

    }
    .ppv-amount-label label {
        padding: 8px 15px;
    }

    .info-box {
        box-shadow: 1px 0px 3px 3px rgba(0,0,0,0.1);
    }

    #revenue-section {
        margin-top: 20px !important;
    }

</style>

@endsection

@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
    <li><a href="{{route('admin.videos')}}"><i class="fa fa-video-camera"></i> {{tr('videos')}}</a></li>
    <li class="active">{{tr('video')}}</li>
@endsection 

@section('content')

    <?php $url = $trailer_url = ""; ?>

    <div class="row">

        @include('notification.notify')

        <div class="col-lg-12">

            <div class="box">

                <div class="box-header with-border bg-blue">

                    <div class='pull-left'>

                        <h3 class="box-title">
                            <b>{{$video->title}}</b>
                        </h3>

                        <br>

                        <span style="margin-left:0px" class="description text-uppercase">
                           
                            {{tr('created_at')}} :

                            {{convertTimeToUSERzone($video->video_date, Auth::guard('admin')->user()->timezone, 'd-m-Y h:i A')}}
                        </span>

                    </div>
                    <div class='pull-right'>
                        @if ($video->compress_status <   OVERALL_COMPRESS_COMPLETED) 
                            <span class="label label-danger">{{tr('compress')}}</span>
                        @else
                            <a href="{{route('admin.videos.edit' , array('id' => $video->video_id))}}" class="btn bg-orange text-uppercase">
                                <i class="fa fa-pencil"></i> 
                                <b>{{tr('edit')}}</b>
                            </a>
                        @endif
                    </div>

                    <div class="clearfix"></div>
                </div>
                <!-- /.box-header -->

                <div class="box-body">

                    @if( $video->is_pay_per_view)

                        <h3 style="margin-top: 0">{{tr('pay_per_view')}}</h3>

                        <hr>

                        <section id="revenue-section">

                            <div class="row">

                                <div class="col-md-4 col-sm-6 col-xs-12">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-blue"><i class="fa fa-money"></i></span>

                                        <div class="info-box-content">
                                            <span class="info-box-text">{{tr('total')}}</span>
                                            <span class="info-box-number">
                                                {{Setting::get('currency')}} {{$video->admin_amount + $video->user_amount}}
                                            </span>
                                        </div>
                                        <!-- /.info-box-content -->
                                    </div>
                                    <!-- /.info-box -->
                                </div>
                                <!-- /.col -->
                                <div class="col-md-4 col-sm-6 col-xs-12">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-green"><i class="fa fa-flag-o"></i></span>

                                        <div class="info-box-content">
                                            <span class="info-box-text">{{tr('admin_amount')}}</span>
                                            <span class="info-box-number">
                                                {{Setting::get('currency')}} {{$video->admin_amount ? $video->admin_amount : 0.00}}
                                            </span>
                                        </div>
                                        <!-- /.info-box-content -->
                                    </div>
                                    <!-- /.info-box -->
                                </div>
                                <!-- /.col -->
                                <!-- /.col -->
                                <div class="col-md-4 col-sm-6 col-xs-12">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-red"><i class="fa fa-star-o"></i></span>

                                        <div class="info-box-content">
                                            <span class="info-box-text">{{tr('moderator_amount')}}</span>
                                            <span class="info-box-number">
                                                {{Setting::get('currency')}} {{$video->user_amount ? $video->user_amount : 0.00}}
                                            </span>
                                        </div>
                                        <!-- /.info-box-content -->
                                    </div>
                                    <!-- /.info-box -->
                                </div>
                                <!-- /.col -->
                            
                            </div>

                            <hr>

                        </section>

                    @endif


                    <a class="btn bg-maroon btn-flat margin">

                        <i class="fa fa-thumbs-up"></i>
                        {{number_format_short($video->getScopeLikeCount->count())}} {{tr('likes')}}
                    </a>

                    <a class="btn bg-orange btn-flat margin">

                        <i class="fa fa-thumbs-down"></i>
                        {{number_format_short($video->getScopeDisLikeCount->count())}} {{tr('dislikes')}}
                    </a>

                    <a class="btn bg-navy btn-flat margin">

                        <i class="fa fa-eye"></i>
                        {{$video->watch_count ? number_format_short($video->watch_count) : 0}} {{tr('viewers_cnt')}}
                    </a>

                    <a class="btn bg-olive btn-flat margin">

                        <i class="fa fa-money"></i>
                        {{Setting::get('currency')}} {{$video->redeem_amount ? $video->redeem_amount : 0}} {{tr('redeems')}}
                    </a>

                    <a class="btn bg-red btn-flat margin">

                        <i class="fa fa-male"></i>
                        {{$video->age}} {{tr('age')}}
                    </a>

                    <!-- VIDEO STATUS -->

                    @if ($video->compress_status < OVERALL_COMPRESS_COMPLETED)
                        
                        <a class="btn bg-warning btn-flat margin text-uppercase">
                            {{tr('compress')}}

                        </a>
                    
                    @else

                        @if($video->is_approved == VIDEO_APPROVED)

                            <a class="btn bg-green btn-flat margin text-uppercase">
                                {{tr('approved')}}
                            </a>
                        @else

                            <a class="btn bg-warning btn-flat margin text-uppercase">
                                {{tr('pending')}}
                            </a>
                        @endif
                    @endif

                    <!-- VIDEO STATUS -->

                    @if($video->status == VIDEO_PUBLISHED)

                        <a class="btn bg-blue btn-flat margin text-uppercase">

                            {{tr('published')}}
                            
                        </a>

                    @else

                        <a class="btn bg-blue btn-flat margin text-uppercase">

                            {{tr('not_yet_published')}}
                            
                        </a>

                    @endif

                    <hr>

                    <section id="video-details-with-images">

                        <div class="row">

                            <div class="col-md-6">

                                <h4 class="text-uppercase"><b>{{tr('details')}}</b></h4>

                                <table>
                                    
                                    <tr>
                                        <td>{{tr('category')}}</td>
                                        <td>{{$video->category_name}}</td>
                                    </tr>

                                    <tr>
                                        <td>{{tr('sub_category')}}</td>
                                        <td>{{$video->sub_category_name}}</td>
                                    </tr>

                                    <tr>
                                        <td>{{tr('video_type')}}</td>
                                        <td>
                                            @if($video->video_type == 1)
                                                {{tr('video_upload_link')}}
                                            @endif

                                            @if($video->video_type == 2)
                                                {{tr('youtube')}}
                                            @endif

                                            @if($video->video_type == 3)
                                                {{tr('other_link')}}
                                            @endif
                                        </td>
                                    </tr>



                                    @if (in_array($video->video_upload_type , [VIDEO_UPLOAD_TYPE_s3,VIDEO_UPLOAD_TYPE_DIRECT]))

                                        <tr>
                                            <td>{{tr('video_upload_type')}}</td>

                                            <td>
                                                @if($video->video_upload_type == VIDEO_UPLOAD_TYPE_s3)
                                                    {{tr('s3')}}
                                                @endif

                                                @if($video->video_upload_type == VIDEO_UPLOAD_TYPE_DIRECT)
                                                    {{tr('direct')}}
                                                @endif 
                                            </td>

                                        </tr>

                                    @endif

                                    <tr>
                                        <td><b>{{tr('is_kids_video')}}</b></td>
                                        <td>
                                            @if($video->is_kids_video ==  YES) 

                                                <span class="text-green text-uppercase"><b>{{tr('yes')}}</b></span>

                                            @else
                                                <span class="text-red text-uppercase"><b>{{tr('no')}}</b></span>

                                            @endif
                                        </td>

                                    </tr>

                                    <tr>
                                        <td>{{tr('trailer_duration')}}</td>
                                        <td>{{$video->trailer_duration}}</td>
                                    </tr>
                                    <tr>
                                        <td>{{tr('duration')}}</td>
                                        <td>{{$video->duration}}</td>
                                    </tr>

                                    <tr>
                                        <td>{{tr('publish_time')}}</td>
                                        <td>{{$video->publish_time}}</td>
                                    </tr>

                                    <tr>
                                        <td>{{tr('uploaded_by')}}</td>
                                        <td>
                                            @if(is_numeric($video->uploaded_by))

                                                <a href="{{route('admin.moderators.view',['moderator_id' => $video->uploaded_by] )}}">{{$video->moderator ? $video->moderator->name : ''}}</a>

                                            @else 

                                                <span class="text-uppercase">{{$video->uploaded_by}}</span>

                                            @endif
                                        </td>
                                    
                                    </tr>

                                    <tr>
                                        <td>{{tr('cast_crews')}}</td>
                                        <td>{{$video_cast_crews ? implode(', ', $video_cast_crews) : '-'}}</td>
                                    </tr>

                                    <tr>
                                        <td>{{tr('ratings')}}</td>

                                        <td>
                                            <span class="starRating-view">
                                                <input id="rating5" type="radio" name="ratings" value="5" @if($video->ratings == 5) checked @endif>
                                                <label for="rating5">5</label>

                                                <input id="rating4" type="radio" name="ratings" value="4" @if($video->ratings == 4) checked @endif>
                                                <label for="rating4">4</label>

                                                <input id="rating3" type="radio" name="ratings" value="3" @if($video->ratings == 3) checked @endif>
                                                <label for="rating3">3</label>

                                                <input id="rating2" type="radio" name="ratings" value="2" @if($video->ratings == 2) checked @endif>
                                                <label for="rating2">2</label>

                                                <input id="rating1" type="radio" name="ratings" value="1" @if($video->ratings == 1) checked @endif>
                                                <label for="rating1">1</label>
                                            </span>

                                        </td>
                                    </tr>

                                </table>

                                @if(Setting::get('is_payper_view') == YES)


                                    <h4 class="text-uppercase text-red"><b>{{tr('pay_per_view')}} {{tr('details')}}</b></h4>

                                    <table>

                                        <tr>
                                            <td><b>{{tr('is_pay_per_view')}}</b></td>
                                            <td>
                                                @if($video->amount > 0) 

                                                    <span class="text-green text-uppercase"><b>{{tr('yes')}}</b></span>

                                                @else
                                                    <span class="text-red text-uppercase"><b>{{tr('no')}}</b></span>

                                                @endif
                                            </td>

                                        </tr>

                                        <tr>
                                            <td>{{tr('ppv_created_by')}}</td>
                                            <td>
                                                @if(is_numeric($video->ppv_created_by))

                                                    <a href="{{route('admin.moderators.view',['moderator_id' => $video->ppv_created_by] )}}">{{$video->moderator ? $video->moderator->name : ''}}</a>

                                                @else 

                                                    <span class="text-uppercase">{{$video->ppv_created_by ?: "-"}}</span>

                                                @endif
                                            </td>
                                        </tr>

                                        @if($video->amount > 0)
                                        
                                            <tr>
                                                <td>{{tr('type_of_user')}}</td>
                                                <td>
                                                    @if($video->type_of_user == NORMAL_USER)
                                                        {{tr('normal_user')}}
                                                    @elseif($video->type_of_user == PAID_USER)
                                                        {{tr('paid_user')}}
                                                    @elseif($video->type_of_user == BOTH_USERS) 
                                                        {{tr('both_user')}}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>{{tr('type_of_subscription')}}</td>
                                                <td>
                                                    @if($video->type_of_subscription == ONE_TIME_PAYMENT)
                                                        {{tr('one_time_payment')}}
                                                    @elseif($video->type_of_subscription == RECURRING_PAYMENT)
                                                        {{tr('recurring_payment')}}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>

                                            <tr>

                                                <td>{{tr('amount')}}</td>

                                                <td>{{Setting::get('currency')}} {{$video->amount}}</td>
                                                
                                            </tr>

                                        @endif

                                    </table>

                                @endif

                            </div>

                            <!-- Images start -->

                            <div class="col-lg-6">

                                <h4 class="text-uppercase">

                                    <i class="fa fa-file-picture-o margin-r-5"></i> 
                                    
                                    <b>{{tr('images')}}</b>

                                </h4>


                                <div id="carousel-example-generic" class="carousel slide" data-ride="carousel">
                                    
                                    <ol class="carousel-indicators">
                                        <li data-target="#carousel-example-generic" data-slide-to="0" class="active"></li>
                                        <li data-target="#carousel-example-generic" data-slide-to="1" class=""></li>
                                        <li data-target="#carousel-example-generic" data-slide-to="2" class=""></li>
                                    </ol>

                                    <div class="carousel-inner">
                                        <div class="item active">
                                            <img src="{{isset($video->default_image) ? $video->default_image : ''}}" alt="{{tr('default_image')}}">

                                            <div class="carousel-caption">
                                                {{tr('default_image')}}
                                            </div>
                                        </div>

                                        @foreach($video_images as $i => $image)

                                            <div class="item">
                                                <img src="{{$image->image}}" alt="">

                                                <div class="carousel-caption">
                                                  Other Image {{$i+2}}
                                                </div>
                                              </div>

                                        @endforeach                                      
                                    
                                    </div>

                                    <a class="left carousel-control" href="#carousel-example-generic" data-slide="prev">
                                      <span class="fa fa-angle-left"></span>
                                    </a>
                                    <a class="right carousel-control" href="#carousel-example-generic" data-slide="next">
                                      <span class="fa fa-angle-right"></span>
                                    </a>

                                </div>

                                @if($video->is_banner) 

                                    <div class="row margin-bottom" style="margin-top: 10px;">

                                        <div class="col-md-12">

                                            <h4 class="text-uppercase">

                                                <i class="fa fa-file-picture-o margin-r-5"></i> 

                                                <b>{{tr('banner_image')}}</b>

                                            </h4>                                        
                                            
                                            <img alt="Photo" src="{{$video->banner_image}}" class="img img-thumbnail" width="470" style="height: 250px">

                                        </div>
                                            
                                    </div>

                                @endif

                                <hr>

                            </div>

                            <!-- Images End -->
                            
                        </div>

                        <hr>

                    </section>

                    <section id="description-and-reviews">

                        <div class="row">

                            <div class="col-lg-12">

                                <h4 class="text-uppercase">

                                    <i class="fa fa-file-text-o margin-r-5"></i>

                                    <b>{{tr('description')}}</b>

                                </h4>

                                <p style="margin-top: 10px;"><?= $video->description; ?></p>

                            </div>
                        </div>

                        <hr>

                    </section>

                    <section id="video-detailed-description">

                        @if($video->details)

                        <div class="row">

                            <div class="col-lg-12">

                                <h4 class="text-uppercase">

                                    <i class="fa fa-file-text-o margin-r-5"></i>

                                    <b>{{tr('details')}}</b>

                                </h4>

                                <p style="margin-top: 10px;"><?= $video->details ?></p>

                            </div>
                        </div>

                        <hr>

                        @endif

                    </section>

                    <section id="video-player-section">
                  
                        <div class="row">
                            
                            @if($video->trailer_video)
                                
                                <div class="col-lg-6">

                                    <h5 class="text-uppercase"><i class="fa fa-video-camera margin-r-5"></i> {{tr('trailer_video')}}</h5>

                                    <div class="image" id="trailer_video_setup_error" style="display:none">
                                        <img src="{{asset('error.jpg')}}" alt="{{Setting::get('site_name')}}" style="width: 100%">
                                    </div>

                                      
                                    <div class="">
                                        @if($video->video_upload_type == 1)
                                        <?php $trailer_url = $video->trailer_video; ?>
                                            <div id="trailer-video-player"></div>
                                        @else

                                            @if(check_valid_url($video->trailer_video))

                                                <?php $trailer_url = (Setting::get('streaming_url')) ? Setting::get('streaming_url').get_video_end($video->trailer_video) : $video->trailer_video; ?>

                                                <div id="trailer-video-player"></div>

                                            @else
                                                <div class="image">
                                                    <img src="{{asset('error.jpg')}}" alt="{{Setting::get('site_name')}}" style="width: 100%">
                                                </div>
                                            @endif

                                        @endif
                                    </div>
                                    <div class="embed-responsive embed-responsive-16by9" id="flash_error_display_trailer" style="display: none;">
                                       <div style="width: 100%;background: black; color:#fff;height:350px;">
                                             <div style="text-align: center;padding-top:25%">{{tr('flash_miss_error')}} <a target="_blank" href="https://get.adobe.com/flashplayer/" class="underline">{{tr('adobe')}}</a>.</div>
                                       </div>
                                    </div>
                                
                                </div>

                            @endif
                            
                            <div class="col-lg-6">

                                <h5 class="text-uppercase"><i class="fa fa-video-camera margin-r-5"></i> {{tr('full_video')}}</h5>

                                <div class="image" id="main_video_setup_error" style="display:none">
                                    <img src="{{asset('error.jpg')}}" alt="{{Setting::get('site_name')}}" style="width: 100%">
                                </div>

                                <div class="">
                                        @if($video->video_upload_type == 1)
                                        <?php $url = $video->video; ?>
                                        <div id="main-video-player"></div>
                                    @else
                                        @if(check_valid_url($video->video))

                                            <?php $url = (Setting::get('streaming_url')) ? Setting::get('streaming_url').get_video_end($video->video) : $video->video; ?>
                                            <div id="main-video-player"></div>
                                        @else
                                            <div class="image">
                                                <img src="{{asset('error.jpg')}}" alt="{{Setting::get('site_name')}}" style="width: 100%">
                                            </div>
                                        @endif

                                    @endif
                                </div>
                                <div class="embed-responsive embed-responsive-16by9" id="flash_error_display_main" style="display: none;">
                                   <div style="width: 100%;background: black; color:#fff;height:350px;">
                                         <div style="text-align: center;padding-top:25%">{{tr('flash_miss_error')}} <a target="_blank" href="https://get.adobe.com/flashplayer/" class="underline">{{tr('adobe')}}</a>.</div>
                                   </div>
                                </div>
                            
                            </div>
                                                    
                        </div>

                    </section>

                <!-- /.box-body -->
                </div>
            </div>
        </div>
    
    </div>


@endsection

@section('scripts')
    
    <script src="{{asset('jwplayer/jwplayer.js')}}"></script>

    <script>jwplayer.key="{{Setting::get('JWPLAYER_KEY')}}";</script>

    <script type="text/javascript">

        $(document).ready(function(){
            $('#help-popover').popover({
                html : true, 
                content: function() {
                    return $('#help-content').html();
                } 
            });  
        });
        
        jQuery(document).ready(function(){

                  var is_mobile = false;

                  var isMobile = {
                      Android: function() {
                          return navigator.userAgent.match(/Android/i);
                      },
                      BlackBerry: function() {
                          return navigator.userAgent.match(/BlackBerry/i);
                      },
                      iOS: function() {
                          return navigator.userAgent.match(/iPhone|iPad|iPod/i);
                      },
                      Opera: function() {
                          return navigator.userAgent.match(/Opera Mini/i);
                      },
                      Windows: function() {
                          return navigator.userAgent.match(/IEMobile/i);
                      },
                      any: function() {
                          return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
                      }
                  };


                  function getBrowser() {

                      // Opera 8.0+
                      var isOpera = (!!window.opr && !!opr.addons) || !!window.opera || navigator.userAgent.indexOf(' OPR/') >= 0;

                      // Firefox 1.0+
                      var isFirefox = typeof InstallTrigger !== 'undefined';

                      // Safari 3.0+ "[object HTMLElementConstructor]" 
                      var isSafari = /constructor/i.test(window.HTMLElement) || (function (p) { return p.toString() === "[object SafariRemoteNotification]"; })(!window['safari'] || safari.pushNotification);

                      // Internet Explorer 6-11
                      var isIE = /*@cc_on!@*/false || !!document.documentMode;

                      // Edge 20+
                      var isEdge = !isIE && !!window.StyleMedia;

                      // Chrome 1+
                      var isChrome = !!window.chrome && !!window.chrome.webstore;

                      // Blink engine detection
                      var isBlink = (isChrome || isOpera) && !!window.CSS;

                      var b_n = '';

                      switch(true) {

                          case isFirefox :

                                  b_n = "Firefox";

                                  break;
                          case isChrome :

                                  b_n = "Chrome";

                                  break;

                          case isSafari :

                                  b_n = "Safari";

                                  break;
                          case isOpera :

                                  b_n = "Opera";

                                  break;

                          case isIE :

                                  b_n = "IE";

                                  break;

                          case isEdge : 

                                  b_n = "Edge";

                                  break;

                          case isBlink : 

                                  b_n = "Blink";

                                  break;

                          default :

                                  b_n = "Unknown";

                                  break;

                      }

                      return b_n;

                  }


                  if(isMobile.any()) {

                      var is_mobile = true;

                  }


                  var browser = getBrowser();


                  if ((browser == 'Safari') || (browser == 'Opera') || is_mobile) {

                    var video = "{{$ios_video}}";

                    var trailer_video = "{{$ios_trailer_video}}";

                  } else {

                    var video = "{{$videoStreamUrl}}";

                    var trailer_video = "{{$trailerstreamUrl}}";

                  }

                console.log("Video " +video);
                    
                console.log("Trailer "+trailer_video);

                @if($url)

                    var playerInstance = jwplayer("main-video-player");


                    @if($videoPath) 

                        var videoPath = "{{$videoPath}}";
                        var videoPixels = "{{$video_pixels}}";

                        var path = [];

                        var splitVideo = videoPath.split(',');

                        var splitVideoPixel = videoPixels.split(',');


                        for (var i = 0 ; i < splitVideo.length; i++) {
                            path.push({file : splitVideo[i], label : splitVideoPixel[i]});
                        }
                        playerInstance.setup({
                            sources: path,
                            image: "{{$video->default_image}}",
                            width: "100%",
                            aspectratio: "16:9",
                            primary: "flash",
                            controls : true,
                            "controlbar.idlehide" : false,
                            controlBarMode:'floating',
                            "controls": {
                              "enableFullscreen": false,
                              "enablePlay": false,
                              "enablePause": false,
                              "enableMute": true,
                              "enableVolume": true
                            },
                            // autostart : true,
                            "sharing": {
                                "sites": ["reddit","facebook","twitter"]
                              },

                              tracks : [{
                              file : "{{$video->video_subtitle}}",
                              kind : "captions",
                              default : true,
                            }]
                        });

                    @else 
                        
                        
                         playerInstance.setup({
                            file: video,
                            image: "{{$video->default_image}}",
                            width: "100%",
                            aspectratio: "16:9",
                            primary: "flash",
                            controls : true,
                            "controlbar.idlehide" : false,
                            controlBarMode:'floating',
                            "controls": {
                              "enableFullscreen": false,
                              "enablePlay": false,
                              "enablePause": false,
                              "enableMute": true,
                              "enableVolume": true
                            },
                            // autostart : true,
                            "sharing": {
                                "sites": ["reddit","facebook","twitter"]
                              },

                           tracks : [{
                              file : "{{$video->video_subtitle}}",
                              kind : "captions",
                              default : true,
                            }]
                        });

                    @endif

                    playerInstance.on('setupError', function() {

                                jQuery("#main-video-player").css("display", "none");
                               // jQuery('#trailer_video_setup_error').hide();
                               

                                var hasFlash = false;
                                try {
                                    var fo = new ActiveXObject('ShockwaveFlash.ShockwaveFlash');
                                    if (fo) {
                                        hasFlash = true;
                                    }
                                } catch (e) {
                                    if (navigator.mimeTypes
                                            && navigator.mimeTypes['application/x-shockwave-flash'] != undefined
                                            && navigator.mimeTypes['application/x-shockwave-flash'].enabledPlugin) {
                                        hasFlash = true;
                                    }
                                }

                                if (hasFlash == false) {
                                    jQuery('#flash_error_display_main').show();
                                    return false;
                                }

                                jQuery('#main_video_setup_error').css("display", "block");

                               // confirm('The video format is not supported in this browser. Please option some other browser.');

                            });

                @endif

                @if($trailer_url)

                    var playerInstance = jwplayer("trailer-video-player");

                    @if($trailer_video_path)

                           var trailerVideoPath = "{{$trailer_video_path}}";
                            var trailerVideoPixels = "{{$trailer_pixels}}";

                            var trailerPath = [];

                            var splitTrailer = trailerVideoPath.split(',');

                            var splitTrailerPixel = trailerVideoPixels.split(',');


                            for (var i = 0 ; i < splitTrailer.length; i++) {

                                trailerPath.push({file : splitTrailer[i], label : splitTrailerPixel[i]});
                            }

                            playerInstance.setup({
                                sources : trailerPath,
                                image: "{{$video->default_image}}",
                                width: "100%",

                                aspectratio: "16:9",
                                primary: "flash",
                                 tracks : [{
                                  file : "{{$video->trailer_subtitle}}",
                                  kind : "captions",
                                  default : true,
                                }]
                            }); 

                    @else

                        playerInstance.setup({
                                file : trailer_video,
                                image: "{{$video->default_image}}",
                                width: "100%",

                                aspectratio: "16:9",
                                primary: "flash",
                                 tracks : [{
                                file : "{{$video->trailer_subtitle}}",
                                kind : "captions",
                                default : true,
                              }]
                            });

                            

                    @endif

                    playerInstance.on('setupError', function() {

                                jQuery("#trailer-video-player").css("display", "none");
                               // jQuery('#trailer_video_setup_error').hide();
                               

                                var hasFlash = false;
                                try {
                                    var fo = new ActiveXObject('ShockwaveFlash.ShockwaveFlash');
                                    if (fo) {
                                        hasFlash = true;
                                    }
                                } catch (e) {
                                    if (navigator.mimeTypes
                                            && navigator.mimeTypes['application/x-shockwave-flash'] != undefined
                                            && navigator.mimeTypes['application/x-shockwave-flash'].enabledPlugin) {
                                        hasFlash = true;
                                    }
                                }

                                if (hasFlash == false) {
                                    jQuery('#flash_error_display_trailer').show();
                                    return false;
                                }

                                jQuery('#trailer_video_setup_error').css("display", "block");

                               // confirm('The video format is not supported in this browser. Please option some other browser.');
                            
                            });
                @endif
        });

    </script>

@endsection