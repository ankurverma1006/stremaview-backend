@extends('layouts.moderator')

@section('title', tr('dashboard'))

@section('content-header', tr('dashboard'))

@section('breadcrumb')
    <li class="active"><i class="fa fa-dashboard"></i> {{tr('dashboard')}}</a></li>
@endsection

<style type="text/css">
  .center-card{
    	width: 30% !important;
	}
  .small-box .icon {
    top: 0px !important;
  }
</style>

@section('content')

	<div class="row">

		<!-- Total Users -->
	
         <div class="col-lg-3 col-xs-6">

          	<div class="small-box bg-maroon">
            	<div class="inner">
              		<h3>{{$today_videos}}</h3>
              		<p>{{tr('total_videos')}}</p>
            	</div>
            	
            	<div class="icon">
              		<i class="fa fa-video-camera"></i>
            	</div>

            	<a target="_blank" href="{{route('moderator.videos')}}" class="small-box-footer">
              		{{tr('more_info')}}
              		<i class="fa fa-arrow-circle-right"></i>
            	</a>
          	</div>
        
        </div>

        <div class="col-lg-3 col-xs-6">

            <div class="small-box bg-blue">
              <div class="inner">
                  <h3>{{Setting::get('currency')}} {{$total_revenue}}</h3>
                  <p>{{tr('total_revenue')}}</p>
              </div>
              
              <div class="icon">
                  <i class="fa fa-money"></i>
              </div>

              <a target="_blank" href="{{route('moderator.revenues')}}" class="small-box-footer">
                  {{tr('more_info')}}
                  <i class="fa fa-arrow-circle-right"></i>
              </a>
            </div>
        
        </div>

        <div class="col-lg-3 col-xs-6"> 

            <div class="small-box bg-green">
              <div class="inner">
                
                  <h3>{{Setting::get('currency')}} {{$watch_count_revenue}}</h3>
                 
                  <p>{{tr('watch_count_revenue')}}</p>
              </div>
              
              <div class="icon">
                  <i class="fa fa-money"></i>
              </div>

              <a target="_blank" href="{{route('moderator.revenues')}}" class="small-box-footer">
                  {{tr('more_info')}}
                  <i class="fa fa-arrow-circle-right"></i>
              </a>
            </div>
        
        </div>


        <div class="col-lg-3 col-xs-6">
           
            <div class="small-box bg-red">
              <div class="inner">
                  <h3>{{Setting::get('currency')}} {{$ppv_revenue}}</h3>
                  <p>{{tr('ppv_revenue')}}</p>
              </div>
              
              <div class="icon">
                  <i class="fa fa-money"></i>
              </div>

              <a target="_blank" href="{{route('moderator.revenues.ppv_payments')}}" class="small-box-footer">
                  {{tr('more_info')}}
                  <i class="fa fa-arrow-circle-right"></i>
              </a>
            </div>
        
        </div>

	</div>


@endsection


