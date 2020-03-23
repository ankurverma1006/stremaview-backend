@extends('layouts.admin')

@section('title', tr('edit_genre'))

@section('content-header')

    <span style="color:#1d880c !important">{{$subcategory->name}} </span> - {{tr('edit_genre') }}

@endsection

@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
    <li><a href="{{route('admin.categories')}}"><i class="fa fa-suitcase"></i>{{tr('categories')}}</a></li>
    <li><a href="{{route('admin.sub_categories', array('category' => $subcategory->category_id))}}"><i class="fa fa-suitcase"></i> {{tr('sub_categories')}}</a></li>
    <li><a href="{{route('admin.genres' , array('sub_category' => $subcategory->id))}}"><i class="fa fa-suitcase"></i> {{tr('genres')}}</a></li>
    <li class="active"><i class="fa fa-suitcase"></i> {{tr('add_genre')}}</li>
@endsection

@section('content')

@include('admin.categories.subcategories.genres._form')

@endsection

