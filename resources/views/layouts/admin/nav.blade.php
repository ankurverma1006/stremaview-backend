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

            <!-- account_management start -->

            <li class="header text-uppercase">{{tr('account_management')}}</li>

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

            <li class="treeview" id="sub-admins">
                <a href="#">
                    <i class="fa fa-support"></i> <span>{{tr('sub_admins')}}</span> <i class="fa fa-angle-left pull-right"></i>
                </a>

                <ul class="treeview-menu">
                   
                    <li id="sub-admins-create"><a href="{{route('admin.sub_admins.create')}}"><i class="fa fa-circle-o"></i>{{tr('sub_admin_create')}}</a></li>
                   
                    <li id="sub-admins-view"><a href="{{route('admin.sub_admins.index')}}"><i class="fa fa-circle-o"></i>{{tr('sub_admin_view')}}</a></li>

                </ul>    
            
            </li>

            <!-- account_management end -->

            <!-- video_management start -->

            <li class="header text-uppercase">{{tr('video_management')}}</li>

            <li class="treeview" id="categories">
                <a href="{{route('admin.categories.index')}}">
                    <i class="fa fa-suitcase"></i> <span>{{tr('categories')}}</span> <i class="fa fa-angle-left pull-right"></i>
                </a>

                <ul class="treeview-menu">
                    <li id="categories-create"><a href="{{route('admin.categories.create')}}"><i class="fa fa-circle-o"></i>{{tr('add_category')}}</a></li>
                    <li id="categories-view"><a href="{{route('admin.categories.index')}}"><i class="fa fa-circle-o"></i>{{tr('view_categories')}}</a></li>
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

            <!-- video_management end -->

            <!-- payments_management start -->

            <li class="header text-uppercase">{{tr('payments_management')}}</li>

            <li id="payments">
                <a href="{{route('admin.user.payments')}}">
                    <i class="fa fa-credit-card"></i> <span>{{tr('payments')}}</span> <i class="fa fa-angle-left pull-right"></i>
                </a>

                <ul class="treeview-menu">

                    <li id="revenue_system"><a href="{{route('admin.revenue.dashboard')}}"><i class="fa fa-circle-o"></i>{{tr('revenue_system')}}</a></li>
                    
                    <li id="user-payments">
                        <a href="{{route('admin.user.payments')}}">
                            <i class="fa fa-circle-o"></i> <span>{{tr('subscription_payments')}}</span>
                        </a>
                    </li>

                    @if(Setting::get('is_payper_view') == TRUE)
                    
                        <li id="video-subscription">
                            <a href="{{ route('admin.user.video-payments') }}">
                                <i class="fa fa-circle-o"></i> <span>{{tr('ppv_payments')}}</span>
                            </a>
                        </li>

                    @endif
                    
                </ul>
            
            </li>

            <li class="treeview" id="subscriptions">
                <a href="#">
                    <i class="fa fa-key"></i> <span>{{tr('subscriptions')}}</span> <i class="fa fa-angle-left pull-right"></i>
                </a>

                <ul class="treeview-menu">

                    <li id="subscriptions-create"><a href="{{route('admin.subscriptions.create')}}"><i class="fa fa-circle-o"></i>{{tr('add_subscription')}}</a></li>

                    <li id="subscriptions-view"><a href="{{route('admin.subscriptions.index')}}"><i class="fa fa-circle-o"></i>{{tr('view_subscriptions')}}</a></li>
                    
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

            @if(Setting::get('redeem_control') == YES )

            <li id="redeems">
                <a href="{{route('admin.moderators.redeems')}}">
                    <i class="fa fa-trophy"></i> <span>{{tr('redeems')}}</span> 
                </a>
            </li>

            @endif

            <!-- payments_management end -->

            <!-- site_management start -->

            <li class="header text-uppercase">{{tr('site_management')}}</li>

            <li class="treeview" id="settings">

                <a href="#">
                    <i class="fa fa-gears"></i><span>{{tr('settings')}}</span><i class="fa fa-angle-left pull-right"></i>
                </a>

                <ul class="treeview-menu">

                    <li id="site_settings"><a href="{{route('admin.settings')}}"><i class="fa fa-circle-o"></i>{{tr('site_settings')}}</a></li>
                    <li id = "home_page_settings"><a href="{{route('admin.homepage.settings')}}"><i class="fa fa-circle-o"></i>{{tr('home_page_settings')}}</a></li>
                </ul>
            
            </li>

            <li id="custom-push">
                <a href="{{route('admin.push')}}">
                    <i class="fa fa-send"></i> <span>{{tr('custom_push')}}</span>
                </a>
            </li>

            <li class="treeview" id="pages">
                <a href="#">
                    <i class="fa fa-picture-o"></i> <span>{{tr('pages')}}</span> <i class="fa fa-angle-left pull-right"></i>
                </a>

                <ul class="treeview-menu">

                    <li id="pages-create"><a href="{{route('admin.pages.create')}}"><i class="fa fa-circle-o"></i>{{tr('add_page')}}</a></li>

                    <li id="pages-view"><a href="{{route('admin.pages.index')}}"><i class="fa fa-circle-o"></i>{{tr('view_page')}}</a></li>

                </ul>
            </li>

            <!-- site_management end -->

            <!-- lookups_management start -->

            <li class="header text-uppercase">{{tr('lookups_management')}}</li>

            <li id="email_templates">
                <a href="{{route('admin.templates.index')}}">
                    <i class="fa fa-envelope"></i> <span>{{tr('email_templates')}}</span>
                </a>
            </li>

            @if(Setting::get('admin_language_control') == FALSE)
                
                <li id="languages">
                    <a href="{{route('admin.languages.index')}}">
                        <i class="fa fa-globe"></i> <span>{{tr('languages')}}</span>
                    </a>

                    <ul class="treeview-menu">

                        <li id="languages-create"><a href="{{route('admin.languages.create')}}"><i class="fa fa-circle-o"></i>{{tr('add_language')}}</a></li>

                        <li id="languages-view"><a href="{{route('admin.languages.index')}}"><i class="fa fa-circle-o"></i>{{tr('view_languages')}}</a></li>
                        
                    </ul>
                
                </li>
                
            @endif

            <li id="mail_camp">
                <a href="{{route('admin.mailcamp.create')}}">
                    <i class="fa fa-envelope"></i> <span>{{tr('mail_camp')}}</span> 
                </a>
            </li>

            <!-- lookups_management end -->

            <!-- admin_account end -->
             
            <li class="header text-uppercase">{{tr('admin_account')}}</li>

            <li id="profile">
                <a href="{{route('admin.profile')}}">
                    <i class="fa fa-diamond"></i> <span>{{tr('account')}}</span>
                </a>
            </li>


            <li id="help">
                <a href="{{route('admin.help')}}">
                    <i class="fa fa-question-circle"></i> <span>{{tr('help')}}</span>
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
