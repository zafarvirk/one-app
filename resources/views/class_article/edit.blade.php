@extends('layouts.app')
@push('css_lib')
    <link rel="stylesheet" href="{{asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('vendor/select2/css/select2.min.css')}}">
    <link rel="stylesheet" href="{{asset('vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{asset('vendor/summernote/summernote-bs4.min.css')}}">
    <link rel="stylesheet" href="{{asset('vendor/dropzone/min/dropzone.min.css')}}">
    <link rel="stylesheet" href="{{asset('vendor/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css')}}">
@endpush
@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-bold">{{trans('lang.class_article_plural')}} <small class="mx-3">|</small><small>{{trans('lang.class_article_desc')}}</small>
                    </h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb bg-white float-sm-right rounded-pill px-4 py-2 d-none d-md-flex">
                        <li class="breadcrumb-item"><a href="{{url('/dashboard')}}"><i class="fas fa-tachometer-alt"></i> {{trans('lang.dashboard')}}</a></li>
                        <li class="breadcrumb-item">
                            <a href="{!! route('class_article.index') !!}">{{trans('lang.class_article_plural')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{trans('lang.class_article_edit')}}</li>
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
        <div class="card shadow-sm">
            <div class="card-header">
                <ul class="nav nav-tabs d-flex flex-row align-items-start card-header-tabs">
                    @can('class_article.index')
                        <li class="nav-item">
                            <a class="nav-link" href="{!! route('class_article.index') !!}"><i class="fas fa-list mr-2"></i>{{trans('lang.class_article_table')}}</a>
                        </li>
                    @endcan
                    @can('class_article.create')
                        <li class="nav-item">
                            <a class="nav-link" href="{!! route('class_article.create') !!}"><i class="fas fa-plus mr-2"></i>{{trans('lang.class_article_create')}}</a>
                        </li>
                    @endcan
                    <li class="nav-item">
                        <a class="nav-link active" href="{!! url()->current() !!}"><i class="fas fa-edit mr-2"></i>{{trans('lang.class_article_edit')}}</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                {!! Form::model($classArticle, ['route' => ['class_article.update', $classArticle->id], 'method' => 'patch']) !!}
                <div class="row">
                    @include('class_article.fields')
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
    <script src="{{asset('vendor/moment/moment.min.js')}}"></script>
    <script src="{{asset('vendor/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js')}}"></script>
    <script type="text/javascript">
        let base_url = "{{ env('APP_URL') }}";

        Dropzone.autoDiscover = false;
        var dropzoneFields = [];

        $( document ).ready(function() {
            var val = $('#is_staff_required').val();
            if(val == 0){
                $('#my').show(); 
            }
            if(val == 1){
                $('#my').hide(); 
            }
        });

        function staffRequriedCheck(e) {
            if(e.value == 0){
                $('#my').show(); 
            }
            if(e.value == 1){
                $('#my').hide(); 
            }
        }

        function getBusinessUsers(businessId) {
            let staffDropdown = $("#article_staff\\[\\]");
            let selected = staffDropdown.select2('data');

            let selectedValues = [];
            if (selected.length) {
                for(var i = 0;i < selected.length; i++) {
                    if (selected[i].selected) {
                        selectedValues.push(Number(selected[i].id));
                    }
                }
            }

            staffDropdown.attr('disabled', true);

            $.ajax({
                url: base_url + "/businesses/" + businessId + "/users",
                cache: false,
                type: "get",
                success: function(data) {
                    staffDropdown.empty().select2({
                        tags: true,
                        tokenSeparators: [',', ' '],
                        multiple: true
                    }).trigger('change');

                    if (data.success) {
                        staffDropdown.attr('disabled', false);
                        for (let index = 0; index < data.data.users.length; index++) {
                            let users = data.data.users;

                            // console.log(users[index].id +' option selected >>', selectedValues.includes(parseInt(users[index].id)));
                            
                            let newOption = new Option(users[index].name, users[index].id, selectedValues.includes(parseInt(users[index].id)), selectedValues.includes(users[index].id));
                            staffDropdown.append(newOption);
                        }
                    }

                    staffDropdown.attr('disabled', false);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    staffDropdown.attr('disabled', false);
                    console.log(textStatus, errorThrown);
                }
            });
        }

        $('#business_id').change(function() {
            let businessId = $('#business_id').val();

            getBusinessUsers(businessId);
        });
    </script>
@endpush
