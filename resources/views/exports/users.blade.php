<!DOCTYPE html>
<html>

<head>
    <title>{{$title}}</title>
</head>
<style type="text/css">

    table{
        font-family: arial, sans-serif;
        border-collapse: collapse;
    }

    .first_row_design{
        background-color: #187d7d;
        color: #ffffff;
    }

    .row_col_design{
        background-color: #cccccc;
    }

    th{
        border: 1px solid #dddddd;
        text-align: left;
        padding: 8px;
        font-weight: bold;

    }

    td {
        border: 1px solid #dddddd;
        text-align: left;
        padding: 8px;

    }
    
</style>

<body>

    <table>

        <!-- HEADER START  -->

        <tr class="first_row_design">

            <th>{{tr('s_no')}}</th>

            <th >{{tr('username')}}</th>

            <th>{{tr('email')}}</th>

            <th>{{tr('mobile')}}</th>

            <th >{{tr('picture')}}</th>

            <th >{{tr('user_type')}}</th>

            <th >{{tr('status')}}</th>

            <th >{{tr('email_notification')}}</th>

            <th >{{tr('validity_days')}}</th>

            <th>{{tr('active_plan')}}</th>

            <th>{{tr('sub_profiles')}}</th>

            <th >{{tr('is_moderator')}}</th>

            <th >{{tr('no_of_account')}}</th>

            <th >{{tr('device_type')}}</th>

            <th >{{tr('login_by')}}</th>

            <th >{{tr('timezone')}}</th>

            <th >{{tr('created')}}</th>

            <th >{{tr('updated')}}</th>
        </tr>

        <!--- HEADER END  -->

        @foreach($data as $i => $user_details)

            <tr @if($i % 2 == 0) class="row_col_design" @endif >

                <td>{{$i+1}}</td>

                <td>{{$user_details->name}}</td>

                <td>{{$user_details->email}}</td>

                <td>{{$user_details->mobile}}</td>

                <td>
                    @if($user_details->picture) {{$user_details->picture}} @else {{asset('admin-css/dist/img/avatar.png')}} @endif
                </td>

                <td>
                    @if($user_details->user_type) 
                        {{tr('paid_user')}}
                    @else 
                        {{tr('normal_user')}}
                    @endif
                </td>

                <td>
                    @if($user_details->is_activated) 

                        {{tr('approved')}}

                    @else 

                        {{tr('pending')}}

                    @endif
                </td>

                <td>
                    @if($user_details->email_notification_status) 

                        {{tr('yes')}}

                    @else 

                        {{tr('no')}}

                    @endif
                </td>

                <td>
                    @if($user_details->user_type)
                        <p style="color:#cc181e">
                        {{tr('no_of_days_expiry')}} 
                        <b>{{get_expiry_days($user_details->id)}} days</b>
                        </p>
                    @endif
                </td>

                <td>
                    <?php echo active_plan($user_details->id);?>
                </td>

                <td>
                                                    
                     {{count($user_details->subProfile)}} {{tr('sub_profiles')}}
                                                   
                </td>

                <td>
                    @if($user_details->is_moderator) 

                        {{tr('yes')}}

                    @else 

                        {{tr('no')}}

                    @endif
                </td>

                <td>{{$user_details->no_of_account}}</td>

                <td>{{$user_details->device_type}}</td>

                <td>{{$user_details->login_by}}</td>

                <td>{{$user_details->timezone}}</td>

                <td>{{convertTimeToUSERzone($user_details->created_at, Auth::guard('admin')->user()->timezone, 'd-m-Y H:i a')}}</td>

                <td>{{convertTimeToUSERzone($user_details->updated_at, Auth::guard('admin')->user()->timezone, 'd-m-Y H:i a')}}</td>
            </tr>

        @endforeach
    </table>

</body>

</html>