@extends('layouts.app')
@push('css_lib')
  <link rel="stylesheet" href="{{asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css')}}">
  <link rel="stylesheet" href="{{asset('vendor/select2/css/select2.min.css')}}">
  <link rel="stylesheet" href="{{asset('vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css')}}">
  <link rel="stylesheet" href="{{asset('vendor/summernote/summernote-bs4.min.css')}}">
  <link rel="stylesheet" href="{{asset('vendor/dropzone/min/dropzone.min.css')}}">
@endpush
@section('content')
<!-- Content Header (Page header) -->
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">{{trans('lang.market_plural')}}<small class="ml-3 mr-3">|</small><small>{{trans('lang.market_desc')}}</small></h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{url('/dashboard')}}"><i class="fa fa-dashboard"></i> {{trans('lang.dashboard')}}</a></li>
          <li class="breadcrumb-item"><a href="{!! route('markets.index') !!}">{{trans('lang.market_plural')}}</a>
          </li>
          <li class="breadcrumb-item active">{{trans('lang.market_edit')}}</li>
        </ol>
      </div><!-- /.col -->
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->
<div class="content">
  <div class="clearfix"></div>
  @include('flash::message')
  @include('adminlte-templates::common.errors')
  <div class="clearfix"></div>
  <div class="card">
    <div class="card-header">
      <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
        @can('markets.index')
        <li class="nav-item">
          <a class="nav-link" href="{!! route('markets.index') !!}"><i class="fa fa-list mr-2"></i>{{trans('lang.market_table')}}</a>
        </li>
        @endcan
        @can('markets.create')
        <li class="nav-item">
          <a class="nav-link" href="{!! route('markets.create') !!}"><i class="fa fa-plus mr-2"></i>{{trans('lang.market_create')}}</a>
        </li>
        @endcan
        <li class="nav-item">
          <a class="nav-link active" href="{!! url()->current() !!}"><i class="fa fa-pencil mr-2"></i>{{trans('lang.market_edit')}}</a>
        </li>
      </ul>
    </div>
    <div class="card-body">
      {!! Form::model($market, ['route' => ['markets.update', $market->id], 'method' => 'patch']) !!}
      <div class="row">
        @include('markets.fields')
      </div>
      {!! Form::close() !!}
      <div class="clearfix"></div>
    </div>
  </div>
</div>
@include('layouts.media_modal')
@endsection
@push('scripts_lib')
    <script src="{{asset('vendor/select2/js/select2.full.min.js')}}"></script>
    <script src="{{asset('vendor/summernote/summernote.min.js')}}"></script>
    <script src="{{asset('vendor/dropzone/min/dropzone.min.js')}}"></script>
    <script type="text/javascript">
        Dropzone.autoDiscover = false;
        var dropzoneFields = [];
    </script>
@endpush