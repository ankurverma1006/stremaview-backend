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

    <table >

        <!------ HEADER START  ------>

        <tr class="first_row_design">
            <th>{{tr('s_no')}}</th>

            <th>{{tr('username')}}</th>


            <th>{{tr('subscriptions')}}</th>

            <th>{{tr('payment_mode')}}</th>

            <th>{{tr('payment_id')}}</th>

            <th>{{tr('coupon_code')}}</th>

            <th>{{tr('coupon_amount')}}</th>

            <th>{{tr('plan_amount')}}</th>

            <th>{{tr('final_amount')}}</th>

            <th>{{tr('expiry_date')}}</th>

            <th>{{tr('is_coupon_applied')}}</th>

            <th>{{tr('coupon_reason')}}</th>

            <th>{{tr('status')}}</th>
        </tr>

        <!------ HEADER END  ------>

        @foreach($data as $i => $subscription_details)

            <tr @if($i % 2 == 0) class="row_col_design" @endif >

                <td>{{$i+1}}</td>
               
                <td> {{($subscription_details->user) ? $subscription_details->user->name : ''}}</td>

                <td>
                    {{$subscription_details->subscription ? $subscription_details->subscription->title : ''}}
                </td>

                <td>{{$subscription_details->payment_mode ? $subscription_details->payment_mode : 'free-plan'}}</td>

                <td>
                    {{$subscription_details->payment_id}}
                </td>

                <td>
                {{$subscription_details->coupon_code}}
                </td>

                <td>
                {{Setting::get('currency')}} {{$subscription_details->coupon_amount? $subscription_details->coupon_amount : "0.00"}}
                </td>

                <td>
                {{Setting::get('currency')}} {{$subscription_details->subscription_amount ? $subscription_details->subscription_amount : "0.00"}}
                </td>

                <td>
                {{Setting::get('currency')}} {{$subscription_details->amount ? $subscription_details->amount : "0.00" }}
                </td>

                <td>
               {{date('d M Y',strtotime($subscription_details->expiry_date))}}
                </td>

                <td>@if($subscription_details->is_coupon_applied)
                    {{tr('yes')}}
                @else
                    {{tr('no')}}
                @endif
                </td>

                <td>{{$subscription_details->coupon_reason ? $subscription_details->coupon_reason : '-'}}</td>

                <td>@if($subscription_details->status)
                    {{tr('paid')}}
                @else
                    {{tr('not_paid')}}
                @endif</td>

            </tr>

        @endforeach
    </table>

</body>

</html>