@extends('layouts.moderator')

@section('title', tr('redeem_amount'))

@section('content-header')

{{tr('redeem_amount')}} -  {{Setting::get('currency')}} {{$redeem_details->remaining ?: 0.00}}

@endsection

@section('breadcrumb')

    <li class="active"><i class="fa fa-credit-card  "></i> {{tr('redeem_amount')}}</li>

@endsection


@section('styles')

<style>

    .circle {
      position: relative;
      display: block;
      margin: 1em 0;
      background-color: transparent;
      color: #222;
      text-align: center;
    }

    .circle:after {
      display: block;
      padding-bottom: 100%;
      width: 100%;
      height: 0;
      border-radius: 50%;
      background-color: #f1f1f1;
      content: "";
      box-shadow: 2px 5px 10px grey;
    }

    .circle__inner {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }

    .circle__wrapper {
        display: table;
        width: 100%;
        height: 100%;
    }

    .circle__content {
        display: table-cell;
        padding: 1em;
        vertical-align: middle;
        color: #D9230F;
    }

    @media (min-width: 480px) {
        .circle__content {
            font-size: 1.3em;
        }
    }

    @media (min-width: 768px) {
      .circle__content {
        font-size: 1.3em;
      }
    }

    .redeem-content {
        margin:3em 0 1em 0;line-height: 1.8em;
    }

    table {
        box-shadow: 0px 1px 5px grey !important;
    }
    thead>tr>th {
        padding: 1% !important;
    }

</style>

@endsection

@section('content')

@include('notification.notify')


        <div class="row">

            <div class="col-sm-12">

                <div class="">

                    <div class="">

                        <div><h4>{{tr('redeems')}}</h4></div>

                    </div>


                    <div class="row">

                        <div class="col-lg-2 col-md-3 col-sm-5 col-xs-5 ">

                            <div class="circle">
                                <div class="circle__inner">
                                    <div class="circle__wrapper">
                                        <div class="circle__content">
                                            <b>{{Setting::get('currency')}} {{ $redeem_details->remaining}}</b>
                                        </div>
                                    </div>
                                </div>
                            
                            </div>
                        </div>

                        <div class="col-lg-10 col-md-9 col-sm-7 col-xs-7">

                            <p class="redeem-content">{{tr('redeem_content')}}
                            </p>

                            <?php 

                                $remaining = $redeem_details ? $redeem_details->remaining: 0;

                                $min_status = Setting::get('minimum_redeem') < $remaining;
                            ?>

                            @if(count($redeem_details) > 0 && $min_status)

                                <a href="{{route('moderator.redeems.send_request')}}" class="btn btn-success"> 
                                    {{tr('send_redeem')}}
                                </a>

                            @else
                                <a href="javascript:void(0);" disabled class="btn btn-success">{{tr('send_redeem')}}</a>

                            @endif
                            
                        </div>
                    
                    </div>

                    @if(count($redeem_requests) > 0)

                        <div class="row">

                            <div class="col-md-12">

                                <table class="table">

                                    <thead>
                                        <tr>
                                            <th>{{tr('redeem_amount')}}</th>
                                            <th>{{tr('sent_date')}}</th>
                                            <th>{{tr('paid_amount')}}</th>
                                            <th>{{tr('paid_date')}}</th>
                                            <th>{{tr('status')}}</th>
                                            <th>{{tr('action')}}</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                         
                                        @foreach($redeem_requests as $rr => $redeem_request_details)

                                            <tr>

                                                <td>
                                                    <b>{{Setting::get('currency')}} {{$redeem_request_details->request_amount}}</b>
                                                </td>

                                                <td>{{$redeem_request_details->created_at->diffForHumans()}}</td>
                                                
                                                <td>
                                                    <b>{{Setting::get('currency')}} {{$redeem_request_details->paid_amount}}</b>
                                                </td>

                                                <td>{{$redeem_request_details->created_at->diffForHumans()}}</td>

                                                <td>
                                                    <span class="btn btn-primary btn-xs"> <b>

                                                        {{redeem_request_status($redeem_request_details->status)}}

                                                        </b>

                                                    </span>
                                                </td>

                                                <td>
                                                    @if(in_array($redeem_request_details->status, [REDEEM_REQUEST_SENT , REDEEM_REQUEST_PROCESSING]))
                                                        <a href="{{route('moderator.redeems.cancel_request' , ['redeem_request_id' => $redeem_request_details->id])}}" class="btn btn-danger btn-sm">{{tr('cancel')}}</a>
                                                    @else
                                                        <span class="text-center">-</span>
                                                    @endif
                                                </td>
                                            </tr>

                                        @endforeach
                                    
                                    </tbody>
                                
                                </table>

                            </div>

                        </div>

                    @endif
            
                </div>
            
            </div>
    
        </div>
 

@endsection