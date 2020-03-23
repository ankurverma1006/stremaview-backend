@extends('layouts.admin')

@section('title', tr('edit_genre'))

@section('content-header')

    <span style="color:#1d880c !important">{{ $sub_category_details->name }} </span> - {{ tr('edit_genre')  }}

@endsection

@section('breadcrumb')
    <li><a href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i>{{ tr('home') }}</a></li>
    <li><a href="{{ route('admin.categories.index') }}"><i class="fa fa-suitcase"></i>{{ tr('categories') }}</a></li>
    <li><a href="{{ route('admin.sub_categories.index', ['category_id' => $sub_category_details->category_id] ) }}"><i class="fa fa-suitcase"></i> {{ tr('sub_categories') }}</a></li>
    <li><a href="{{ route('admin.genres.index' , ['sub_category_id' => $sub_category_details->id] ) }}"><i class="fa fa-suitcase"></i> {{ tr('genres') }}</a></li>
    <li class="active"><i class="fa fa-suitcase"></i> {{ tr('edit_genre') }}</li>
@endsection

@section('content')

	@include('admin.categories.sub_categories.genres._form')

@endsection

