@extends('layouts.admin')

@section('title', tr('edit_subscription'))

@section('content-header', tr('edit_subscription'))

@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
    <li><a href="{{route('admin.subscriptions.index')}}"><i class="fa fa-key"></i> {{tr('subscriptions')}}</a></li>
    <li class="active">{{tr('edit_subscription')}}</li>
@endsection

@section('content')

    <div class="row">

        <div class="col-md-10 ">
            
            <div class="box box-primary">

                <div class="box-header label-primary">
                    <b>{{tr('edit_subscription')}}</b>
                    <a href="{{route('admin.subscriptions.index')}}" style="float:right" class="btn btn-default">{{tr('view_subscriptions')}}</a>
                </div>

                @include('admin.subscriptions._form')

            </div>

        </div>

    </div>

@endsection
