<aside class="main-sidebar">

    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">

        <!-- Sidebar user panel -->
        <div class="user-panel">

            <div class="pull-left image">
                <img src="{{Auth::guard('moderator')->user()->picture ?: asset('admin-css/dist/img/avatar.png')}}" class="img-circle" alt="{{Auth::guard('moderator')->user()->name}}">
            </div>

            <div class="pull-left info">
                
                <p>{{Auth::guard('moderator')->user()->name}}</p>

                <a href="{{route('moderator.profile')}}">{{tr('moderator')}}</a>

            </div>
        </div>

        <!-- sidebar menu: : style can be found in sidebar.less -->

        <ul class="sidebar-menu">

            <li id="dashboard">
                <a href="{{route('moderator.dashboard')}}">
                    <i class="fa fa-dashboard"></i> <span>{{tr('dashboard')}}</span>
                </a>
            
            </li>

            <li class="treeview" id="videos">
                <a href="{{route('moderator.videos')}}">
                    <i class="fa fa-video-camera"></i> <span>{{tr('videos')}}</span><i class="fa fa-angle-left pull-right"></i>
                </a>

                <ul class="treeview-menu">
                    <li id="videos-create">
                        <a href="{{route('moderator.videos.create')}}">
                            <i class="fa fa-circle-o"></i>
                            {{tr('add_video')}}
                        </a>
                    </li>
                    <li id="videos-view">
                        <a href="{{route('moderator.videos')}}">
                            <i class="fa fa-circle-o"></i>
                            {{tr('view_videos')}}
                        </a>
                    </li>
                </ul>
            </li>


            <li class="treeview" id="revenues">

                <a href="{{route('moderator.revenues')}}">
                    <i class="fa fa-money"></i> <span>{{tr('revenue')}} </span><i class="fa fa-angle-left pull-right"></i>
                </a>

                <ul class="treeview-menu">


                    <li id="revenues-dashboard">
                        <a href="{{route('moderator.revenues')}}">
                            <i class="fa fa-circle-o"></i>
                            {{tr('revenues')}}
                        </a>
                    </li>

                     <li id="revenues-redeems">
                        <a href="{{route('moderator.redeems')}}">
                            <i class="fa fa-circle-o"></i>
                            {{tr('redeems')}}
                        </a>
                    </li>

                    <li id="revenues-ppv_payments">
                        <a href="{{route('moderator.revenues.ppv_payments')}}">
                            <i class="fa fa-circle-o"></i>
                            {{tr('ppv_payments')}}
                        </a>
                    </li>
                    
                </ul>
            </li>

            <li id="profile">
                <a href="{{route('moderator.profile')}}">
                    <i class="fa fa-diamond"></i> <span>{{tr('profile')}}</span>
                </a>
            
            </li>

            <li>
                <a href="{{route('moderator.logout')}}">
                    <i class="fa fa-sign-out"></i> <span>{{tr('sign_out')}}</span>
                </a>
            </li>

        </ul>

    </section>

    <!-- /.sidebar -->

</aside>