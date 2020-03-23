<!DOCTYPE html>
<html>

<head>
    <title>{{$title}}</title>
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
</head>

<body>

    <table>

        <!------ HEADER START  ------>

        <tr class="first_row_design">
            <th>{{tr('s_no')}}</th>

            <th>{{tr('username')}}</th>

            <th>{{tr('email')}}</th>

            <th>{{tr('mobile')}}</th>

            <th>{{tr('address')}}</th>

            <th>{{tr('picture')}}</th>

            <th>{{tr('status')}}</th>

            <th>{{tr('is_user')}}</th>

            <th>{{tr('total_videos')}}</th>

            <th>{{tr('total_earnings')}}</th>

            <th>{{tr('total_ppv_amount')}}</th>
                
            <th>{{tr('total_viewer_count_amount')}}</th>
            
            <th>{{tr('total_admin_commission')}}</th>

            <th>{{tr('total_moderator_commission')}}</th>

            <th>{{tr('wallet')}}</th>

            <th>{{tr('admin_paid_amount')}}</th>


            <th>{{tr('timezone')}}</th>

            <th>{{tr('created')}}</th>

            <th>{{tr('updated')}}</th>
        </tr>

        <!------ HEADER END  ------>

        @foreach($data as $i => $moderator_details)

            <tr @if($i % 2 == 0) class="row_col_design" @endif >

                <td>{{$i+1}}</td>

                <td>{{$moderator_details->name}}</td>

                <td>{{$moderator_details->email}}</td>

                <td>{{$moderator_details->mobile}}</td>

                <td>{{$moderator_details->address}}</td>

                <td>
                    @if($moderator_details->picture) {{$moderator_details->picture}} @else {{asset('admin-css/dist/img/avatar.png')}} @endif
                </td>

                <td>
                @if($moderator_details->is_activated) 
                  {{tr('approved')}}

                @else 
                    {{tr('pending')}}
                @endif
                </td>


                <td>
                @if($moderator_details->is_user) 

                    {{tr('yes')}}

                @else 

                    {{tr('no')}}

                @endif
                </td>

               <td>{{$moderator_details->moderatorVideos ? $moderator_details->moderatorVideos->count() : "0.00"}}</td>

               <td>{{Setting::get('currency')}} {{$moderator_details->moderatorRedeem ? $moderator_details->moderatorRedeem->total_moderator_amount : "0.00"}}</td>
               

                <td>{{Setting::get('currency')}} <?php echo total_moderator_video_revenue($moderator_details->id) ?> </td>

                <td>{{Setting::get('currency')}} <?php echo redeem_amount($moderator_details->id) ?></td>


                <td>{{Setting::get('currency')}} {{$moderator_details->moderatorRedeem ? $moderator_details->moderatorRedeem->total_admin_amount : "0.00"}}</td>

                <td>{{Setting::get('currency')}}{{$moderator_details->moderatorRedeem ? $moderator_details->moderatorRedeem->total_moderator_amount : "0.00"}}</td>

                <td>{{Setting::get('currency')}} {{$moderator_details->moderatorRedeem ? $moderator_details->moderatorRedeem->remaining : "0.00"}}</td>

                <td>{{Setting::get('currency')}} {{$moderator_details->paid_amount}}</td>

                <td>{{$moderator_details->timezone}}</td>

                <td>{{convertTimeToUSERzone($moderator_details->created_at, Auth::guard('admin')->user()->timezone, 'd-m-Y H:i a')}}</td>

                <td>{{convertTimeToUSERzone($moderator_details->updated_at, Auth::guard('admin')->user()->timezone, 'd-m-Y H:i a')}}</td>
            </tr>

        @endforeach
    </table>

</body>

</html>