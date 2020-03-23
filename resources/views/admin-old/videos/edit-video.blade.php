@extends('layouts.admin')

@section('title', tr('edit_video'))

@section('content-header', tr('edit_video'))

@section('styles')

<!--admin_delete_control-->
    <link rel="stylesheet" href="{{asset('admin-css/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css')}}">

    <link rel="stylesheet" href="{{asset('assets/css/wizard.css')}}">

    <link rel="stylesheet" href="{{asset('admin-css/plugins/iCheck/all.css')}}">
@endsection

@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
    <li><a href="{{request()->headers->get('referer')}}"><i class="fa fa-video-camera"></i>{{tr('videos')}}</a></li>
    <li class="active"> {{tr('edit_video')}}</li>
@endsection 

@section('content')

@include('notification.notify')


@if (envfile('QUEUE_DRIVER') != 'redis') 

 <div class="alert alert-warning">
    <button type="button" class="close" data-dismiss="alert">×</button>
        {{tr('warning_error_queue')}}
</div>

@endif

@if(checkSize())


 <div class="alert alert-warning">
    <button type="button" class="close" data-dismiss="alert">×</button>
        {{tr('max_upload_size')}} <b>{{ini_get('upload_max_filesize')}}</b>&nbsp;&amp;&nbsp;{{tr('post_max_size')}} <b>{{ini_get('post_max_size')}}</b>
</div>

@endif

<div class="row">
    <div class="col-lg-12">
        <section>
        <div class="wizard">
            <div class="wizard-inner">
                <div class="connecting-line"></div>
                <ul class="nav nav-tabs" role="tablist">
                <!--class="disabled"-->
                    <li role="presentation" class="active">
                        <a href="#step1" data-toggle="tab" aria-controls="step1" role="tab" title="Video Details">
                            <span class="round-tab">
                                <i class="glyphicon glyphicon-book"></i>
                            </span>
                        </a>
                    </li>

                    <li role="presentation">
                        <a href="#step2" data-toggle="tab" aria-controls="step2" role="tab" title="{{tr('category')}}">
                            <span class="round-tab">
                                <i class="fa fa-suitcase"></i>
                            </span>
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="#step3" data-toggle="tab" aria-controls="step3" role="tab" title="{{tr('sub_category')}}">
                            <span class="round-tab">
                                <i class="glyphicon glyphicon-folder-open"></i>
                            </span>
                        </a>
                    </li>

                    <li role="presentation">
                        <a href="#complete" data-toggle="tab" aria-controls="complete" role="tab" title="{{tr('upload_video_image')}}">
                            <span class="round-tab">
                                <i class="glyphicon glyphicon-picture"></i>
                            </span>
                        </a>
                    </li>
                </ul>
            </div>

            <form id="video-upload" action="{{(Setting::get('admin_delete_control') == 1) ? '' : route('admin.save.edit.video')}}" method="POST" enctype="multipart/form-data" role="form">
                <div class="tab-content">
                    <div class="tab-pane active" role="tabpanel" id="step1">
                        <!-- <h3>Video Details</h3> -->
                        <div style="margin-left: 15px"><small>{{tr('note')}} : <span style="color:red">*</span>{{tr('fields_mandatory')}}</small></div> 
                        <hr>
                        <div class="">
                            <input type="hidden" value="{{$video->video_id}}" name="id">
                            <input type="hidden" value="1" name="ajax_key">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label for="title" class="">{{tr('title')}} * </label>
                                    <input type="text" required class="form-control" id="title" name="title" placeholder="{{tr('title')}}" value="{{$video->title}}">
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label for="datepicker" class="">{{tr('publish_time')}} * </label>

                                    <input type="text" name="publish_time" placeholder="{{tr('select_publish_time')}}" class="form-control pull-right" id="datepicker" value="{{$video->publish_time}}" readonly>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label>{{tr('duration')}} * : </label><!-- <small> {{tr('duration_note')}}</small> -->

                                    <div class="input-group">
                                        <div class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                        <input required type="text" name="duration" class="form-control" data-inputmask="'alias': 'hh:mm:ss'" data-mask id="duration" value="{{$video->duration}}" placeholder="{{tr('duration_note')}}">
                                    </div>
                                </div>
                            </div>

                            <input type="hidden"  name="is_banner" value="{{$video->is_banner}}" id="is_banner">

                            @if(!$video->is_banner)

                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label for="age" class="">{{tr('age')}} * </label>
                                    <input type="text" required class="form-control" id="age" name="age" placeholder="{{tr('age')}} : {{tr('example_format')}}" maxlength="3" value="{{$video->age}}">
                                </div>
                            </div>

                            @endif
    

                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label for="ratings" class="">{{tr('ratings')}}</label>
                                    <div class="starRating">
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
                                    </div>
                                </div>
                            </div>

                            <!-- <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label for="video" class="">{{tr('sub_title')}}</label>
                                    <input type="file" id="subtitle" name="subtitle" accept="text/plain">
                                    <p class="help-block">{{tr('subtitle_validate')}}</p>
                                </div>
                            </div> -->

                            <div class="clearfix"></div>


                            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label for="description" class="">{{tr('description')}} * </label>
                                    <textarea  style="overflow:auto;resize:none" class="form-control" required rows="4" cols="50" id="description" name="description">{{$video->description}}</textarea>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label for="reviews" class="">{{tr('reviews')}} * </label>
                                    <textarea  style="overflow:auto;resize:none" class="form-control" required rows="4" cols="50" id="reviews" name="reviews">{{$video->reviews}}</textarea>
                                </div>
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label for="details" class="">{{tr('details')}} </label>
                                    <!-- <textarea  style="overflow:auto;resize:none" class="form-control" rows="4" cols="50" id="ckeditor" name="details">{{$video->details}}</textarea> -->

                                     <textarea id="ckeditor" name="details" class="form-control">{{$video->details}}</textarea>

                                     <!-- <textarea name="details" style="display: none" id="details_video"></textarea> -->
                                </div>
                            </div>
                        </div>
                        <ul class="list-inline pull-right">
                            <li>
                                <button type="button" style="display: none;" id="{{REQUEST_STEP_1}}" class="btn btn-primary next-step">{{tr('next')}}</button>
                                <button type="button" class="btn btn-primary" onclick="saveVideoDetails({{REQUEST_STEP_1}})">{{tr('next')}}</button>
                            </li>
                        </ul>
                    </div>
                    <div class="tab-pane" role="tabpanel" id="step2">
                        <h3>{{tr('category')}}</h3>
                        <hr>
                        <div id="category">
                            @foreach($categories as $category)
                            <?php
                                $css = ($category->id == $video->category_id) ? 'category-item-active' : '';
                            ?>
                            <div class="col-lg-4 col-md-4 col-sm-12 col-sx-12">
                                <a onclick="saveCategory({{$category->id}}, {{REQUEST_STEP_2}})" class="{{$css}} category-item text-center">
                                    <div style="background-image: url({{$category->picture}})" class="category-img bg-img"></div>
                                    <h3 class="category-tit"><i id="{{$category->id}}_i" class="fa fa-check-circle" style="display:none;color:#51af33" aria-hidden="true"></i> {{$category->name}}</h3>
                                </a>
                            </div>
                            @endforeach
                            <input type="hidden" name="category_id" id="category_id" value="{{$video->category_id}}"/>
                        </div>
                        <div class="clearfix"></div>
                        <ul class="list-inline">
                            <li class="pull-left"><button type="button" class="btn btn-danger prev-step">{{tr('previous')}}</button></li>
                            <li class="pull-right"><button type="button" class="btn btn-primary next-step" id="{{REQUEST_STEP_2}}">{{tr('next')}}</button></li>
                            <div class="clearfix"></div>
                        </ul>
                    </div>
                    <div class="tab-pane" role="tabpanel" id="step3">
                        <h3>{{tr('sub_category')}}</h3>
                        <hr>
                        <div id="sub_category">
                            @foreach($subCategories as $subCategory)
                             <?php
                                $subcss = ($subCategory->id == $video->sub_category_id) ? 'category-item-active' : '';
                            ?>
                            <div class="col-lg-4 col-md-4 col-sm-12 col-sx-12">
                                <a onclick="saveSubCategory({{$subCategory->id}}, {{REQUEST_STEP_3}})" class="{{$subcss}} category-item text-center">
                                    <div style="background-image: url({{$subCategory->picture}})" class="category-img bg-img"></div>
                                    <h3 class="category-tit"><i id="{{$subCategory->id}}_sub_i" class="fa fa-check-circle" style="display:none;color:#51af33" aria-hidden="true"></i> {{$subCategory->name}}</h3>
                                </a>
                            </div>
                            @endforeach
                        </div>
                        <input type="hidden" name="sub_category_id" id="sub_category_id" value="{{$video->sub_category_id}}"/>
                        <div class="clearfix"></div>
                        <ul class="list-inline">
                            <li><button type="button" class="btn btn-danger prev-step">{{tr('previous')}}</button></li>
                            <!-- <li><button type="button" class="btn btn-default next-step">Skip</button></li> -->
                            <li class="pull-right" style="display: none"><button  id="{{REQUEST_STEP_3}}" type="button" class="btn btn-primary btn-info-full next-step">{{tr('next')}}</button></li>
                            <div class="clearfix"></div>
                        </ul>
                    </div>
                    <div class="tab-pane" role="tabpanel" id="complete">
                        <h3>{{tr('upload_video_image')}}</h3>
                        <hr>
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">

                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" id="genre_id">

                                    <div class="form-group">

                                        <label for="genre" class="">{{tr('select_genre')}}</label>

                                        <br>

                                        <select id="genre" name="genre_id" class=" form-control select2" @if(!$video->is_series) disabled @endif>
                                            @if($video->genre_id)
                                                <option value="{{$video->genre_id}}">{{$video->genre_name}}</option>
                                            @else
                                                <option value="">{{tr('select_genre')}}</option>
                                            @endif
                                        </select>
                                    </div>


                                   
                                </div>

                                @if($video->is_banner)
                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <div class="form-group">
                                            <label for="banner_image" class="">{{tr('banner_image')}}</label>
                                            <input type="file" id="banner_image" name="banner_image" accept="image/png, image/jpeg" placeholder="{{tr('banner_image')}}" style="display:none" onchange="loadFile(this, 'banner_img')">
                                            <div>
                                                <img src="{{$video->banner_image ? $video->banner_image : asset('images/320x150.png')}}" style="width:150px;height:75px;" 
                                                onclick="$('#banner_image').click();return false;" id="banner_img"/>
                                            </div>
                                            <p class="help-block">{{tr('image_validate')}} {{tr('rectangle_image')}}</p>
                                        </div>
                                    </div>
                                @endif
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                   <small style="color:brown">{{tr('view_images_video_note')}}</small>
                                </div>
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <div class="form-group">
                                        <label for="default_image" class="">{{tr('default_image')}} *</label>
                                        <input type="file" id="default_image" accept="image/png,image/jpeg" name="default_image" placeholder="{{tr('default_image')}}" style="display:none" onchange="loadFile(this,'default_img')">
                                        <div>
                                            <img src="{{($video->default_image) ? $video->default_image : asset('images/320x150.png')}}" style="width:150px;height:75px;" 
                                            onclick="$('#default_image').click();return false;" id="default_img"/>
                                        </div>
                                        <p class="help-block">{{tr('image_validate')}} {{tr('rectangle_image')}}</p>
                                    </div>
                                </div>
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <?php $videoimages = get_video_image($video->video_id);?>
                                    <div class="form-group">
                                        <label for="other_image1" class="">{{tr('other_image1')}} * </label>
                                        <input type="file" id="other_image1" accept="image/png,image/jpeg" name="other_image1" placeholder="{{tr('other_image1')}}" style="display:none" onchange="loadFile(this,'other_img1')">
                                        <div>
                                            <img src="{{isset($videoimages[0]->image) ? $videoimages[0]->image : asset('images/320x150.png')}}" style="width:150px;height:75px;" 
                                            onclick="$('#other_image1').click();return false;" id="other_img1"/>
                                        </div>
                                        <p class="help-block">{{tr('image_validate')}} {{tr('rectangle_image')}}</p>
                                    </div>
                                </div>
                            
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <div class="form-group">
                                        <label for="other_image2" class="">{{tr('other_image2')}} *</label>
                                        <input type="file" id="other_image2" accept="image/png,image/jpeg" name="other_image2" placeholder="{{tr('other_image2')}}" style="display:none" onchange="loadFile(this,'other_img2')">
                                        <div>
                                            <img src="{{isset($videoimages[1]->image) ? $videoimages[1]->image : asset('images/320x150.png')}}" style="width:150px;height:75px;" 
                                            onclick="$('#other_image2').click();return false;" id="other_img2"/>
                                        </div>
                                        <p class="help-block">{{tr('image_validate')}} {{tr('rectangle_image')}}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <div class="form-group">
                                        <label for="video_type" class="">{{tr('video_type')}}</label></br>

                                        <label style="margin-top:10px" id="video_upload">
                                            <input required type="radio" name="video_type" value="1"  @if($video->video_type == 1) checked @endif>
                                            {{tr('video_upload_link')}}
                                        </label>

                                        <label style="margin-top:10px" id="youtube">
                                            <input required type="radio" name="video_type" value="2" @if($video->video_type == 2) checked @endif>
                                            {{tr('youtube')}}
                                        </label>

                                        <label style="margin-top:10px" id="other_link">
                                            <input required type="radio" name="video_type" value="3"@if($video->video_type == 3) checked @endif>
                                            {{tr('other_link')}}
                                        </label>

                                    </div>
                                </div>
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" id="compress">
                                    <div class="form-group">
                                        <label>{{tr('compress_video')}}</label>
                                        <br>
                                        <div>
                                            <input type="radio" name="compress_video" class="minimal-red" value="1"> <label>{{tr('yes')}}</label> &nbsp;&nbsp;
                                            <input type="radio" name="compress_video" class="minimal-red" value="0" checked> <label>{{tr('no')}}</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" id="resolution">
                                    <div class="form-group">
                                        <label>{{tr('resize_video_resolutions')}}</label>
                                        <br>
                                        <div>
                                            @foreach(getVideoResolutions() as $resolution)
                                                <input type="checkbox" class="minimal-red" name="video_resolutions[]" value="{{$resolution->value}}"> <label>{{$resolution->value}}</label> &nbsp;&nbsp;
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                                <div id="upload">
                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <div class="form-group">

                                            <label for="video_upload_type" class="">{{tr('video_upload_type')}}</label></br>

                                            @if(check_s3_configure())

                                            <label style="margin-top:10px" >
                                                <input type="radio"  name="video_upload_type" value="1"  @if($video->video_upload_type == 1) checked @endif>
                                                {{tr('s3')}}
                                            </label>

                                            @endif

                                            <label style="margin-top:10px">
                                                <input type="radio" name="video_upload_type" value="2" @if($video->video_upload_type == 2) checked @endif>
                                                {{tr('direct')}}
                                            </label>

                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                        <div class="form-group">
                                            <label for="video" class="">{{tr('video')}}</label>
                                            <input type="file" id="video" accept="video/mp4,video/x-matroska" name="video" placeholder="{{tr('picture')}}">
                                            <p class="help-block">{{tr('video_validate')}}</p>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                        <div class="form-group">
                                            <label for="video" class="">{{tr('sub_title')}}</label>
                                            <input type="file" id="video_subtitle" name="video_subtitle" onchange="checksrt(this, this.id)">
                                            <p class="help-block">{{tr('subtitle_validate')}}</p>
                                        </div>
                                    </div>

                                    <div class="clearfix"></div>

                                    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                        <div class="form-group">
                                           <label for="trailer_video" class="">{{tr('trailer_video')}}</label>
                                            <input type="file" id="trailer_video" accept="video/mp4,video/x-matroska" name="trailer_video" placeholder="{{tr('trailer_video')}}">
                                            <p class="help-block">{{tr('video_validate')}}</p>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                        <div class="form-group">
                                            <label for="video" class="">{{tr('sub_title')}}</label>
                                            <input type="file" id="trailer_subtitle" name="trailer_subtitle" onchange="checksrt(this, this.id)">
                                            <p class="help-block">{{tr('subtitle_validate')}}</p>
                                        </div>
                                    </div>
                                </div>
                                <div id="others">
                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <div class="form-group">
                                            <label for="other_video" class="">{{tr('video')}}</label>
                                            <input type="url" class="form-control" id="other_video" name="other_video" placeholder="{{tr('video')}}" @if($video->video_type == 2 || $video->video_type == 3) value="{{$video->video}}" @endif>
                                        </div>
                                    </div>

                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <div class="form-group">
                                            <label for="other_trailer_video" class="">{{tr('trailer_video')}}</label>
                                            <input type="url" class="form-control" id="other_trailer_video" name="other_trailer_video" placeholder="{{tr('trailer_video')}}" @if($video->video_type == 2 || $video->video_type == 3) value="{{$video->trailer_video}}" @endif>
                                        </div>
                                    </div>
                                </div>

                                <div class="clearfix"></div>

                                <div class="col-lg-12">

                                    <label>{{tr('send_notification')}}</label>&nbsp;&nbsp;<input type="checkbox" name="send_notification" value="1" />

                                </div>
                            </div>
                        </div>

                        <div id="error_message" class="text-danger"></div>
                        <div class="clearfix"></div>
                        <hr>
                        <ul class="list-inline">
                            <li><button type="button" class="btn btn-danger prev-step">{{tr('previous')}}</button></li>
                            <!-- <li><button type="button" class="btn btn-default next-step">Skip</button></li> -->
                            @if(Setting::get('admin_delete_control') == 1) 
                            <li class="pull-right"><button disabled id="{{REQUEST_STEP_FINAL}}" type="button" class="btn btn-primary btn-info-full">{{tr('finish')}}</button></li>
                            @else
                            
                            <li class="pull-right"><button id="{{REQUEST_STEP_FINAL}}" type="submit" class="btn btn-primary btn-info-full">{{tr('finish')}}</button></li>
                            <li class="pull-right">
                                <div class="progress">
                                    <div class="bar"></div >
                                    <div class="percent">0%</div >
                                </div>
                            <li>
                            @endif
                            <div class="clearfix"></div>
                        </ul>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </form>
        </div>
    </section>
   </div>
</div>

<div class="overlay">
    <div id="loading-img"></div>
</div>

@endsection

@section('scripts')


    <script src="{{asset('admin-css/plugins/bootstrap-datetimepicker/js/moment.min.js')}}"></script> 

    <script src="{{asset('admin-css/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.js')}}"></script> 

    <script src="{{asset('admin-css/plugins/iCheck/icheck.min.js')}}"></script>
    
    <script src="{{asset('admin-css/plugins/jquery.form.js')}}"></script>

    <script type="text/javascript">

        var banner_image = "{{$video->is_banner}}";

        var cat_url = "{{ url('select/sub_category')}}";
        var step3 = "{{REQUEST_STEP_3}}";
        var sub_cat_url = "{{ url('select/genre')}}";
        var final = "{{REQUEST_STEP_FINAL}}";
        var video_id = "{{$video->video_id}}";
        var genreId = "{{$video->genre_id}}";

        var video_type = "{{$video->video_type}}";

        $("#"+"{{$video->category_id}}"+"_i").show();
        $("#"+"{{$video->sub_category_id}}"+"_sub_i").show();

        $(function () {

            $('#datepicker').datetimepicker({
                minTime: "00:00:00",
                maxDate: moment(),
                defaultDate: "{{$video->publish_time}}",
                autoclose:true,
                format:'dd-mm-yyyy hh:ii',
            });

            $('#upload').hide();
            $('#others').hide();
            $("#compress").hide();
            $("#resolution").hide();

            @if($video->video_type == 1)
                $('#upload').show();
                $("#compress").show();
                $("#resolution").show();
            @else
                $('#others').show();
                $("#compress").hide();
                $("#resolution").hide();
            @endif

            $("#video_upload").click(function(){

                $("#upload").show();
                $("#compress").show();
                $("#resolution").show();
                $("#others").hide();

                if(!video_id || video_type != 1) {

                    $("#trailer_video").attr('required', true);

                    $("#video").attr('required', true);

                }
            });

            $("#youtube").click(function(){

                $("#trailer_video").attr('required', false);

                $("#video").attr('required', false);

                $("#others").show();
                $("#upload").hide();
                $("#compress").hide();
                $("#resolution").hide();
            });

            $("#other_link").click(function(){

                $("#trailer_video").attr('required', false);

                $("#video").attr('required', false);
                
                $("#others").show();
                $("#upload").hide();
                $("#compress").hide();
                $("#resolution").hide();
            });
        });
    </script>  
    <script src="{{asset('assets/js/wizard.js')}}"></script>
    <script>
        loadGenre(genreId);
        $('form').submit(function () {
           window.onbeforeunload = null;
        });
        window.onbeforeunload = function() {
             return "Data will be lost if you leave the page, are you sure?";
        };
    </script>

    <script src="https://cdn.ckeditor.com/4.5.5/standard/ckeditor.js"></script>
    <script>
        CKEDITOR.replace( 'ckeditor' );
    </script>
@endsection

