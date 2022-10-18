@extends('layouts.settings.default')
@push('css_lib')
    <!-- iCheck -->
    <link rel="stylesheet" href="{{asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css')}}">
    <!-- select2 -->
    <link rel="stylesheet" href="{{asset('vendor/select2/css/select2.min.css')}}">
    <link rel="stylesheet" href="{{asset('vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css')}}">
    <!-- bootstrap wysihtml5 - text editor -->
    <link rel="stylesheet" href="{{asset('vendor/summernote/summernote-bs4.min.css')}}">
    {{--dropzone--}}
    <link rel="stylesheet" href="{{asset('vendor/dropzone/min/dropzone.min.css')}}">
    {{--Color Picker--}}
    <link rel="stylesheet" href="{{asset('vendor/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css')}}">
@endpush
@section('settings_title',trans('lang.app_setting_mobile'))
@section('settings_content')
    @include('flash::message')
    @include('adminlte-templates::common.errors')
    <div class="clearfix"></div>
    <div class="card shadow-sm">
        <div class="card-header">
            <ul class="nav nav-tabs d-flex flex-row align-items-start card-header-tabs">
                <li class="nav-item">
                    <a class="nav-link active" href="{!! url()->current() !!}"><i class="fas fa-cog mr-2"></i>{{trans('lang.app_setting_mobile_'.$tab)}}</a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            {!! Form::open(['url' => ['settings/update'], 'method' => 'patch']) !!}
            <div class="row">
                <h5 class="col-12 pb-4"><i class="mr-3 fas fa-tasks"></i>{!! trans('lang.app_setting_mobile_section_builder') !!}</h5>
            @for($i = 1 ; $i <= 18 ; $i++)
                <!-- Theme Color Field -->
                    <div class="form-group row col-12">
                        {!! Form::label('home_section_'.$i, trans("lang.app_setting_mobile_section_desc")." - $i",['class' => 'col-3 control-label']) !!}
                        <div class="col-6">
                            {!! Form::select('home_section_'.$i,
                            [
                            'empty' => trans('lang.app_setting_mobile_empty'),
                            'slider' => trans('lang.app_setting_mobile_slider'),
                            'search' => trans('lang.app_setting_mobile_search'),
                            'feature_categories_icon_grid' => trans('lang.app_setting_mobile_feature_categories_icon_grid'),
                            'feature_categories_colored_grid' => trans('lang.app_setting_mobile_feature_categories_colored_grid'),
                            'feature_business_grid' => trans('lang.app_setting_mobile_feature_business_grid'),
                            'feature_business_list' => trans('lang.app_setting_mobile_feature_business_list'),
                            'feature_business_carousel' => trans('lang.app_setting_mobile_feature_business_carousel'),
                            'recently_visited_business_grid' => trans('lang.app_setting_mobile_recently_visited_business_grid'),
                            'recently_visited_business_list' => trans('lang.app_setting_mobile_recently_visited_business_list'),
                            'recently_visited_business_carousel' => trans('lang.app_setting_mobile_recently_visited_business_carousel'),
                            'top_10_business_in_user_city_grid' => trans('lang.app_setting_mobile_top_10_business_in_user_city_grid'),
                            'top_10_business_in_user_city_list' => trans('lang.app_setting_mobile_top_10_business_in_user_city_list'),
                            'top_10_business_in_user_city_carousel' => trans('lang.app_setting_mobile_top_10_business_in_user_city_carousel'),
                            'near_by_business_grid' => trans('lang.app_setting_mobile_near_by_business_grid'),
                            'near_by_business_list' => trans('lang.app_setting_mobile_near_by_business_list'),
                            'near_by_business_carousel' => trans('lang.app_setting_mobile_near_by_business_carousel'),
                            'banners' => trans('lang.app_setting_mobile_banners'),
                            ]
                            , setting('home_section_'.$i,'empty'), ['class' => 'select2 form-control']) !!}
                            <div class="form-text text-muted">{{ trans("lang.app_setting_mobile_section_help") }}</div>
                        </div>
                    </div>
            @endfor

            <!-- Submit Field -->
                <div class="form-group mt-4 col-12 text-right">
                    <button type="submit" class="btn bg-{{setting('theme_color')}} mx-md-3 my-lg-0 my-xl-0 my-md-0 my-2">
                        <i class="fas fa-save"></i> {{trans('lang.save')}} {{trans('lang.app_setting')}}
                    </button>
                    <a href="{!! route('users.index') !!}" class="btn btn-default"><i class="fas fa-undo"></i> {{trans('lang.cancel')}}</a>
                </div>
            </div>
            {!! Form::close() !!}
            <div class="clearfix"></div>
        </div>
    </div>
    </div>
    @include('layouts.media_modal',['collection'=>null])
@endsection
@push('scripts_lib')
    <!-- iCheck -->

    <!-- select2 -->
    <script src="{{asset('vendor/select2/js/select2.full.min.js')}}"></script>
    <!-- AdminLTE dashboard demo (This is only for demo purposes) -->
    <script src="{{asset('vendor/summernote/summernote.min.js')}}"></script>
    {{--dropzone--}}
    <script src="{{asset('vendor/dropzone/min/dropzone.min.js')}}"></script>
    <script src="{{asset('vendor/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js')}}"></script>
    <script type="text/javascript">
        // $("input[name$='color']").colorpicker({
        $(".colorpicker-component, input[name$='color']").colorpicker({
            customClass: 'colorpicker',
            format: 'hex',
            sliders: {
                saturation: {
                    maxLeft: 200,
                    maxTop: 200
                },
                hue: {
                    maxTop: 200
                },
                alpha: {
                    maxTop: 200
                }
            }
        });
        Dropzone.autoDiscover = false;
        var dropzoneFields = [];
    </script>
@endpush
