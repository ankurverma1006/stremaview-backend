<aside class="main-sidebar">

    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">

        <!-- Sidebar user panel -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="@if(Auth::guard('admin')->user()->picture){{Auth::guard('admin')->user()->picture}} @else {{asset('placeholder.png')}} @endif" class="img-circle" alt="User Image">
            </div>
            <div class="pull-left info">
                <p>{{Auth::guard('admin')->user()->name}}</p>
                <a href="{{route('admin.profile')}}">{{ tr('admin') }}</a>
            </div>
        </div>

        <!-- sidebar menu: : style can be found in sidebar.less -->
        <ul class="sidebar-menu">

            <li id="dashboard">
              <a href="{{route('admin.dashboard')}}">
                <i class="fa fa-dashboard"></i> <span>{{tr('dashboard')}}</span>
              </a>              
            </li>

            <li class="treeview" id="users">
                <a href="#">
                    <i class="fa fa-user"></i> <span>{{tr('users')}}</span> <i class="fa fa-angle-left pull-right"></i>
                </a>

                <ul class="treeview-menu">
                    <li id="users-create"><a href="{{route('admin.users.create')}}"><i class="fa fa-circle-o"></i>{{tr('add_user')}}</a></li>
                    <li id="users-view"><a href="{{route('admin.users.index')}}"><i class="fa fa-circle-o"></i>{{tr('view_users')}}</a></li>
                </ul>    
            </li>

            <li class="treeview" id="moderators">                
                <a href="#">
                    <i class="fa fa-users"></i> <span>{{tr('moderators')}}</span> <i class="fa fa-angle-left pull-right"></i>
                </a>

                <ul class="treeview-menu">
                    <li id="moderators-create"><a href="{{route('admin.moderators.create')}}"><i class="fa fa-circle-o"></i>{{tr('add_moderator')}}</a></li>
                    <li id="moderators-view"><a href="{{route('admin.moderators.index')}}"><i class="fa fa-circle-o"></i>{{tr('view_moderators')}}</a></li>
                </ul>            
            </li>

            <li class="treeview" id="cast-crews">
                <a href="#">
                    <i class="fa fa-male"></i><span>{{tr('cast_crews')}}</span><i class="fa fa-angle-left pull-right"></i>
                </a>

                <ul class="treeview-menu">

                    <li id="cast-crews-create"><a href="{{route('admin.cast_crews.create')}}"><i class="fa fa-circle-o"></i>{{tr('add_cast_crew')}}</a></li>
                    <li id ="cast-crews-view"><a href="{{route('admin.cast_crews.index')}}"><i class="fa fa-circle-o"></i>{{tr('cast_crews')}}</a></li>
                </ul>
            </li>

            <li class="treeview" id="videos">
                <a href="{{route('admin.videos')}}">
                    <i class="fa fa-video-camera"></i> <span>{{tr('videos')}}</span> <i class="fa fa-angle-left pull-right"></i>
                </a>

                <ul class="treeview-menu">
                    <li id="admin_videos_create">
                        <a href="{{route('admin.videos.create')}}"><i class="fa fa-circle-o"></i>{{tr('add_video')}}</a>
                    </li>

                    <li id="view-videos">
                        <a href="{{route('admin.videos')}}"><i class="fa fa-circle-o"></i>{{tr('view_videos')}}</a>
                    </li>

                     @if(Setting::get('is_spam'))

                        <li id="spam_videos">
                            <a href="{{route('admin.spam-videos')}}">
                                <i class="fa fa-circle-o"></i><span>{{tr('spam_videos')}}</span>
                            </a>
                        </li>

                    @endif

                    <li id="view-banner-videos"><a href="{{route('admin.videos',['banner'=>BANNER_VIDEO])}}"><i class="fa fa-circle-o"></i>{{tr('banner_videos')}}</a></li>
                </ul>
            </li>


            <li class="treeview" id="coupons">
                <a href="#">
                    <i class="fa fa-gift"></i><span>{{tr('coupons')}}</span><i class="fa fa-angle-left pull-right"></i>
                </a>

                <ul class="treeview-menu">
                    <li id="coupons-create"><a href="{{route('admin.coupons.create')}}"> <i class="fa fa-circle-o"></i>{{tr('add_coupon')}}</a></li>
                    <li id ="coupons-view"><a href="{{route('admin.coupons.index')}}"><i class="fa fa-circle-o"></i>{{tr('view_coupon')}}</a></li>
                </ul>
            </li>

            <li id="custom-push">
                <a href="{{route('admin.push')}}">
                    <i class="fa fa-send"></i> <span>{{tr('custom_push')}}</span>
                </a>
            </li>

            <li id="email_templates">
                <a href="{{route('admin.templates.index')}}">
                    <i class="fa fa-envelope"></i> <span>{{tr('email_templates')}}</span>
                </a>
            </li>

            <li class="treeview" id="pages">
                <a href="#">
                    <i class="fa fa-picture-o"></i> <span>{{tr('pages')}}</span> <i class="fa fa-angle-left pull-right"></i>
                </a>

                <ul class="treeview-menu">

                    <li id="pages-create"><a href="{{route('admin.pages.create')}}"><i class="fa fa-circle-o"></i>Add Page</a></li>
                    <li id="pages-view"><a href="{{route('admin.pages.index')}}"><i class="fa fa-circle-o"></i>View Page</a></li>
                </ul>
            </li> 

            <li id="mail_camp">
                <a href="{{route('admin.mailcamp.create')}}">
                    <i class="fa fa-envelope"></i> <span>{{tr('mail_camp')}}</span> 
                </a>
            </li>

            <li>
                <a href="{{route('admin.logout')}}">
                    <i class="fa fa-sign-out"></i> <span>{{tr('sign_out')}}</span>
                </a>
            </li>

        </ul>

    </section>

    <!-- /.sidebar -->

</aside>
