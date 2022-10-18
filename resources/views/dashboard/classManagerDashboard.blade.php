@extends('layouts.app')
@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header content-header{{setting('fixed_header')}}">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-bold">{{trans('lang.dashboard')}}<small class="mx-3">|</small><small>{{trans('lang.dashboard_overview')}}</small></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb bg-white float-sm-right rounded-pill px-4 py-2 d-none d-md-flex">
                        <li class="breadcrumb-item"><a href="#"><i class="fas fa-tachometer-alt"></i> {{trans('lang.dashboard')}}</a></li>
                        <li class="breadcrumb-item active">{{trans('lang.dashboard')}}</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <div class="content">
        <!-- Small boxes (Stat box) -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-white shadow-sm">
                    <div class="inner">
                        <h3 class="text-{{setting('theme_color','primary')}}">{{$bookingsCount}}</h3>

                        <p>{{trans('lang.dashboard_total_bookings')}}</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <a href="{{route('bookings.index')}}" class="small-box-footer">{{trans('lang.dashboard_more_info')}}
                        <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-white shadow-sm">
                    <div class="inner">
                        <h3 class="text-{{setting('theme_color','primary')}}">0</h3>

                        <p>{{trans('lang.cancel')}} <span style="font-size: 11px">({{trans('lang.dashboard_after taxes')}})</span></p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <a href="{{route('bookings.index')}}" class="small-box-footer">{{trans('lang.dashboard_more_info')}}
                        <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-white shadow-sm">
                    <div class="inner">
                        <h3 class="text-{{setting('theme_color','primary')}}">0</h3>
                        <p>{{trans('lang.no_show')}}</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <a href="{{route('bookings.index')}}" class="small-box-footer">{{trans('lang.dashboard_more_info')}}
                        <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-white shadow-sm">
                    <div class="inner">
                        <h3 class="text-{{setting('theme_color','primary')}}">{{$membersCount}}</h3>

                        <p>{{trans('lang.dashboard_total_customers')}}</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <a href="{!! route('users.index') !!}" class="small-box-footer">{{trans('lang.dashboard_more_info')}}
                        <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->

        </div>
        <!-- /.row -->

        <div class="row">
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header no-border">
                        <h3 class="card-title">{{trans('lang.class_article_plural')}}</h3>
                        <div class="card-tools">
                            <a href="{{route('class_article.index')}}" class="btn btn-tool btn-sm"><i class="fas fa-bars"></i> </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped table-valign-middle">
                            <thead>
                            <tr>
                                <th>{{trans('lang.salon_image')}}</th>
                                <th>{{trans('lang.class_article')}}</th>
                                <th>{{trans('lang.salon_address')}}</th>
                                <th>{{trans('lang.actions')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($classArticle as $class)

                                <tr>
                                    <td>
                                        {!! getMediaColumn($class, 'image','img-circle mr-2') !!}
                                    </td>
                                    <td>{!! $class->name !!}</td>
                                    <td>
                                        {!! $class->address == null ? '' : $class->address->address !!}
                                    </td>
                                    <td class="text-center">
                                        <a href="{!! route('class_article.edit',$class->id) !!}" class="text-muted"> <i class="fas fa-edit"></i> </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header no-border">
                        <h3 class="card-title">{{trans('lang.up_comming_birthday')}}</h3>
                        <div class="card-tools">
                            <a href="{{route('users.index')}}" class="btn btn-tool btn-sm"><i class="fas fa-bars"></i> </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped table-valign-middle">
                            <thead>
                            <tr>
                                <th>{{trans('lang.user_avatar')}}</th>
                                <th>{{trans('lang.user_name')}}</th>
                                <th>{{trans('lang.actions')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($salons as $salon)

                                <tr>
                                    <td>
                                        {!! getMediaColumn($salon, 'image','img-circle mr-2') !!}
                                    </td>
                                    <td>{!! $salon->name !!}</td>
                                    <td class="text-center">
                                        <a href="{!! route('users.edit',$salon->id) !!}" class="text-muted"> <i class="fas fa-edit"></i> </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header no-border">
                        <h3 class="card-title">{{trans('lang.new_signup')}}</h3>
                        <div class="card-tools">
                            <a href="{{route('users.index')}}" class="btn btn-tool btn-sm"><i class="fas fa-bars"></i> </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped table-valign-middle">
                            <thead>
                            <tr>
                                <th>{{trans('lang.user_avatar')}}</th>
                                <th>{{trans('lang.user_name')}}</th>
                                <th>{{trans('lang.actions')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($salons as $salon)

                                <tr>
                                    <td>
                                        {!! getMediaColumn($salon, 'image','img-circle mr-2') !!}
                                    </td>
                                    <td>{!! $salon->name !!}</td>
                                    <td class="text-center">
                                        <a href="{!! route('users.edit',$salon->id) !!}" class="text-muted"> <i class="fas fa-edit"></i> </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header no-border">
                        <h3 class="card-title">{{trans('lang.no_attendees')}}</h3>
                        <div class="card-tools">
                            <a href="{{route('users.index')}}" class="btn btn-tool btn-sm"><i class="fas fa-bars"></i> </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped table-valign-middle">
                            <thead>
                            <tr>
                                <th>{{trans('lang.user_avatar')}}</th>
                                <th>{{trans('lang.user_name')}}</th>
                                <th>{{trans('lang.actions')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($salons as $salon)

                                <tr>
                                    <td>
                                        {!! getMediaColumn($salon, 'image','img-circle mr-2') !!}
                                    </td>
                                    <td>{!! $salon->name !!}</td>
                                    <td class="text-center">
                                        <a href="{!! route('users.edit',$salon->id) !!}" class="text-muted"> <i class="fas fa-edit"></i> </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@push('scripts_lib')
    <script src="{{asset('vendor/chart.js/Chart.min.js')}}"></script>
@endpush

