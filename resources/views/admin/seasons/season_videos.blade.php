
<slick dots="false" class="episode-slider">

@foreach($genre_videos as $genre_video_details) 

	<div>
		<div class="episode-list-box">

			<div class="episode-img bg-img" style="background-image: url({{$genre_video_details->default_image}});">
				<div class="episode-img-overlay">
					<div class="episode-img-inner">

						<div class="epi-count1">
							{{$genre_video_details->age}} / <i class="fa fa-eye"></i>&nbsp;{{$genre_video_details->watch_count}}
						</div>

					</div>

					<div class="epi-play-icon-outer">

						@if($genre_video_details->pay_per_view_status)

							<a href="{{Setting::get('ANGULAR_SITE_URL').'video/'.$genre_video_details->id}}" class="epi-play-icon">
								<i class="fa fa-play"></i>
							</a>

						@else

							<a href="{{Setting::get('ANGULAR_SITE_URL').'payment-option/'.$genre_video_details->id}}" class="epi-play-icon">
								<i class="fa fa-play"></i>
							</a>

						@endif
					</div>
				</div>
			</div>

			<div class="episode-list-content">
				<div class="row">
					<div class="col-sm-12 col-md-8">
						<h4 class="epi-tit gray-color1 txt-overflow">{{$genre_video_details->title}}</h4>
					</div>

					<div class="col-md-4 text-right hidden-sm">
						<p class="bold epi-tit gray-color1 txt-overflow">{{$genre_video_details->duration}}</p>
					</div>
				</div>

				<div class=""> 
					<p class="content-txt overview-des more-link mt-0">{{$genre_video_details->description}}</p>
				</div>
			</div>

		</div>

	</div>

@endforeach

</slick>

<script type="text/javascript">
	
var window_width = $(window).width();

if (window_width > 991) {

 	$(".episode-slider").attr('slides-to-show',4);

 	$(".episode-slider").attr('slides-to-scroll',4);

}  

if (window_width > 767 && window_width < 992) {

 	$(".episode-slider").attr('slides-to-show',3);

 	$(".episode-slider").attr('slides-to-scroll',3);
}  

if (window_width > 479 && window_width < 768) {

 	$(".episode-slider").attr('slides-to-show',2);

 	$(".episode-slider").attr('slides-to-scroll',2);

}  

if (window_width < 480) {

 	$(".episode-slider").attr('slides-to-show', 1);

 	$(".episode-slider").attr('slides-to-scroll', 1);

}    


</script>