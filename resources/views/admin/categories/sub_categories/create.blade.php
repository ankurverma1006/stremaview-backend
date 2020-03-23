@extends('layouts.admin')

@section('title', tr('add_sub_category'))

@section('content-header')

    <span style="color:#1d880c !important">{{$category_details->name }} </span> - {{tr('add_sub_category')  }}

@endsection

@section('breadcrumb')
    <li><a href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i>{{ tr('home') }}</a></li>
    <li><a href="{{ route('admin.categories.index') }}"><i class="fa fa-suitcase"></i> {{ tr('categories') }}</a></li>
    <li><a href="{{ route('admin.sub_categories.index' , ['category_id' => $category_details->id] ) }}"><i class="fa fa-suitcase"></i> {{ tr('sub_categories') }}</a></li>
    <li class="active"><i class="fa fa-suitcase"></i> {{ tr('add_sub_category') }}</li>
@endsection

@section('content')

    <div class="row">

        <div class="col-md-10">
            
            <div class="box box-primary">

                <div class="box-header label-primary">

                    <b style="font-size:18px;">{{ tr('add_sub_category') }}</b>

                    <a href="{{  route('admin.sub_categories.index' , ['category_id' => $category_details->id] ) }}" class="btn btn-default pull-right">{{ tr('sub_categories') }}</a>

                </div>

                @include('admin.categories.sub_categories._form')

            </div>

        </div>

    </div>

@endsection
