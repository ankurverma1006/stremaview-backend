@extends('layouts.admin')

@section('title', tr('edit_sub_category'))

@section('content-header')

    {{ tr('edit_sub_category')  }} - <span style="color:#1d880c !important">{{ $sub_category->name }} </span>

@endsection

@section('breadcrumb')
    <li><a href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i>{{ tr('home') }}</a></li>
    <li><a href="{{ route('admin.categories.index') }}"><i class="fa fa-suitcase"></i> {{ tr('categories') }}</a></li>
    <li><a href="{{ route('admin.sub_categories.index' , ['category_id' => $category->id] ) }}"><i class="fa fa-suitcase"></i> {{ tr('sub_categories') }}</a></li>
    <li class="active"><i class="fa fa-suitcase"></i> {{ tr('edit_sub_category') }}</li>
@endsection

@section('content')

    <div class="row">

        <div class="col-md-8">

            <div class="box box-primary">

                <div class="box-header label-primary">
                    <b style="font-size:18px;">{{ tr('sub_categories') }}</b>
                    <a href="{{ route('admin.sub_category.create' , ['category_id' => $category->i] ) }}" class="btn btn-default pull-right">{{ tr('add_sub_category') }}</a>
                </div>

                @include('admin.categories.sub_categories._form')
            
            </div>

        </div>

    </div>

@endsection
