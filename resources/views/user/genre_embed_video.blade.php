@extends('layouts.user')

@section('content')

<div style="height: 100% !important">

<div style="width: 100% !important;height: 100% !important">

<div id="video-player">
	
</div>

<div class="embed-responsive embed-responsive-16by9" id="genre_video_error" style="display:none;position: relative;">
    <img src="{{asset('error.jpg')}}" alt="{{Setting::get('site_name')}}" style="width: 100%">
    <div id="flash_error_display" style="display: none;position: absolute;top:0;bottom: 0;left: 0;right: 0;align-items: center; background:#000; opacity: 0.6;display: flex;">
       <div style="width: 100%;color:#fff;">
             <div style="text-align: center;align-items: center;">{{tr('flash_miss_error')}} <a target="_blank" href="https://get.adobe.com/flashplayer/" class="underline">{{tr('adobe')}}</a>.</div>
       </div>
    </div>
</div>


</div>

</div>

@endsection

@section('scripts')
<script src="{{asset('jwplayer/jwplayer.js')}}"></script>

<script>jwplayer.key="{{Setting::get('JWPLAYER_KEY')}}";</script>

<script type="text/javascript">
        
jQuery(document).ready(function(){

jwplayer.key= "{{Setting::get('JWPLAYER_KEY')}}";

var playerInstance = jwplayer("video-player");


playerInstance.setup({
    file: "{{$model->video}}",
    image: "{{$model->default_image}}",
    width: "100%",
    height: "100%",
    primary: "flash",
    autostart : true,
    tracks : [{
      file : "{{$model->subtitle}}",
      kind : "captions",
      default : true,
    }]
});


playerInstance.on('error', function() {

           jQuery("#video-player").css("display", "none");
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
                jQuery('#flash_error_display').show();
                return false;
            }

            jQuery('#genre_video_error').css("display", "block");

            confirm('The video format is not supported in this browser. Please option some other browser.');
        
        });

});
</script>
@endsection