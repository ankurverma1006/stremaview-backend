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

            <th>{{tr('video')}}</th>


            <th>{{tr('username')}}</th>


            <th>{{tr('payment_id')}}</th>

            <th>{{tr('payment_mode')}}</th>

            <th>{{tr('ppv_amount')}}</th>

            <th>{{tr('coupon_amount')}}</th>

            <th>{{tr('total')}}</th>

            <th>{{tr('admin')}}</th>

            <th>{{tr('moderator')}}</th>

            <th>{{tr('is_coupon_applied')}}</th>

            <th>{{tr('coupon_reason')}}</th>

            <th>{{tr('status')}}</th>
        </tr>

        <!------ HEADER END  ------>

        @foreach($data as $i => $payperview_details)

            <tr @if($i % 2 == 0)  class="row_col_design" @endif >

                <td>{{$i+1}}</td>

                <td>
                    {{$payperview_details->adminVideo ? $payperview_details->adminVideo->title : ''}}
                </td>

                <td> {{($payperview_details->userVideos) ? $payperview_details->userVideos->name : ''}}</td>


                <td>
                    {{$payperview_details->payment_id}}
                </td>

                <td>{{$payperview_details->payment_mode ? $payperview_details->payment_mode : 'free-plan'}}</td>


                <td>
                {{Setting::get('currency')}} {{$payperview_details->ppv_amount ? $payperview_details->ppv_amount : "0.00"}}
                </td>
                
                <td>
                {{Setting::get('currency')}} {{$payperview_details->coupon_amount ? 
                $payperview_details->coupon_amount : "0.00"}} @if($payperview_details->coupon_code) ({{$payperview_details->coupon_code}}) @endif
                </td>

                <td>
                {{Setting::get('currency')}} {{$payperview_details->amount? $payperview_details->amount : "0.00"}}
                </td>


                <td>
                {{Setting::get('currency')}} {{$payperview_details->admin_amount ?
                    $payperview_details->admin_amount : "0.00"}}
                </td>

                <td>
               {{Setting::get('currency')}} {{$payperview_details->moderator_amount ? $payperview_details->moderator_amount : "0.00"}}
                </td>

                <td>@if($payperview_details->is_coupon_applied)
                    {{tr('yes')}}
                @else
                    {{tr('no')}}
                @endif
                </td>

                <td>{{$payperview_details->coupon_reason ? $payperview_details->coupon_reason : '-'}}</td>

                <td>@if($payperview_details->amount <= 0)

                    @if($payperview_details->coupon_amount <= 0)

                       {{tr('not_paid')}}

                    @else 

                        {{tr('paid')}}

                    @endif

                @else
                    {{tr('paid')}}

                @endif 
                </td>

            </tr>

        @endforeach
    </table>

</body>

</html>