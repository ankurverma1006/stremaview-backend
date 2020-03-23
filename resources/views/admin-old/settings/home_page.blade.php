@extends('layouts.admin')

@section('title', tr('home_page_settings'))

@section('content-header', tr('home_page_settings'))


@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
    <li class="active"><i class="fa fa-gears"></i> {{tr('home_page_settings')}}</li>
@endsection

@section('content')

    <div class="row">

    @include('notification.notify')
    
    <div class="col-md-12">

        <div class="nav-tabs-custom">
               
                <div class="tab-content">
                   
                    <div>

                        <form action="{{(Setting::get('admin_delete_control') == 1) ? '' : route('admin.save.settings')}}" method="POST" enctype="multipart/form-data" role="form">

                            <div class="box-body">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="home_banner_heading">{{tr('banner_heading')}}</label>
                                        <input type="text" class="form-control" maxlength="80" name="home_banner_heading" value="{{ Setting::get('home_banner_heading') }}" id="home_banner_heading" placeholder="{{tr('banner_heading')}}">
                                    </div>
                                </div>

                                <div class="col-lg-6">

                                     <div class="form-group">
                                        <label for="home_banner_description">{{tr('banner_description')}}</label>
                                        <textarea class="form-control" id="home_banner_description" maxlength="150"name="home_banner_description">{{Setting::get('home_banner_description')}}</textarea>
                                    </div>


                                </div>

                                <div class="box-body">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="home_about_site">{{tr('home_about_site')}}</label>
                                        <textarea class="form-control" id="home_about_site" name="home_about_site">{{Setting::get('home_about_site')}}</textarea>
                                    </div>
                                </div>

                                <div class="col-lg-6">

                                     <div class="form-group">
                                        <label for="home_cancel_content">{{tr('home_cancel_content')}}</label>
                                        <textarea class="form-control" id="home_cancel_content" name="home_cancel_content">{{Setting::get('home_cancel_content')}}</textarea>
                                    </div>


                                </div>



                                <div class="col-md-3">
                                    <div class="form-group">
                                       
                                        <label for="home_browse_desktop_image">{{tr('home_browse_desktop_image')}}</label>

                                        <br>

                                        @if(Setting::get('home_browse_desktop_image'))
                                            <img class="settings-img-preview " src="{{Setting::get('home_browse_desktop_image')}}" title="{{tr('home_browse_desktop_image')}}">
                                        @endif

                                        <input type="file" id="home_browse_desktop_image" name="home_browse_desktop_image" accept="image/png, image/jpeg">
                                        <p class="help-block">{{tr('image_validate')}}</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">

                                        <label for="home_browse_tv_image">{{tr('home_browse_tv_image')}}</label>

                                        <br>

                                        @if(Setting::get('home_browse_tv_image'))
                                            <img class="settings-img-preview " src="{{Setting::get('home_browse_tv_image')}}" title="{{tr('home_browse_tv_image')}}">
                                        @endif
                                        <input type="file" id="home_browse_tv_image" name="home_browse_tv_image" accept="image/png, image/jpeg">
                                        <p class="help-block">{{tr('image_validate')}}</p>
                                    </div>
                                </div>

                                <div class="col-md-3">

                                    <div class="form-group">


                                        <label for="home_browse_mobile_image">{{tr('home_browse_mobile_image')}}</label>

                                        <br>

                                        @if(Setting::get('home_browse_mobile_image'))
                                            <img class="settings-img-preview " src="{{Setting::get('home_browse_mobile_image')}}" title="{{tr('home_browse_mobile_image')}}">
                                        @endif

                                        <input type="file" id="home_browse_mobile_image" name="home_browse_mobile_image" accept="image/png, image/jpeg">
                                        <p class="help-block">{{tr('image_validate')}}</p>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        
                                        <label for="home_cancel_image">{{tr('home_cancel_image')}}</label>
                                        <br>
                                        @if(Setting::get('home_cancel_image'))
                                            <img class="settings-img-preview " src="{{Setting::get('home_cancel_image')}}" title="{{tr('home_cancel_image')}}">
                                        @endif
                                        <input type="file" id="home_cancel_image" name="home_cancel_image" accept="image/png, image/jpeg">
                                        <p class="help-block">{{tr('image_validate')}}</p>
                                    </div>
                                </div>

                                 <div class="col-md-3">

                                    <div class="form-group">


                                        <label for="home_page_bg_image">{{tr('home_page_bg_image')}}</label>

                                        <br>

                                        @if(Setting::get('home_page_bg_image'))
                                            <img class="settings-img-preview " src="{{Setting::get('home_page_bg_image')}}" title="{{Setting::get('sitename')}}">
                                        @endif

                                        <input type="file" id="home_page_bg_image" name="home_page_bg_image" accept="image/png, image/jpeg">
                                        <p class="help-block">{{tr('image_note_help')}}</p>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        
                                        <label for="common_bg_image">{{tr('common_bg_image')}}</label>
                                        <br>
                                        @if(Setting::get('common_bg_image'))
                                            <img class="settings-img-preview " src="{{Setting::get('common_bg_image')}}" title="{{Setting::get('sitename')}}">
                                        @endif
                                        <input type="file" id="common_bg_image" name="common_bg_image" accept="image/png, image/jpeg">
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
                
                </div>
            </div>
        </div>
    
    </div>


@endsection