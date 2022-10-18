@if($customFields)
    <h5 class="col-12 pb-4">{!! trans('lang.main_fields') !!}</h5>
@endif
<div class="d-flex flex-column col-sm-12 col-md-6 px-4">

    <!-- Categories Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row ">
        {!! Form::label('article_id', trans("lang.class_article"),['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::select('article_id', $article, null, ['class' => 'select2 form-control']) !!}
            <div class="form-text text-muted">{{ trans("lang.class_article_help") }}</div>
        </div>
    </div>
    @php
        $start_date = isset($articleSchedule) && $articleSchedule->start_date ? $articleSchedule->start_date->format('Y-m-d') : null;
        $end_date = isset($articleSchedule) && $articleSchedule->end_date ? $articleSchedule->end_date->format('Y-m-d') : null;
    @endphp
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('start_date', trans("lang.article_schedule_start_date"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::date('start_date', $start_date,  ['class' => 'form-control','placeholder'=>  trans("lang.article_schedule_start_date_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.article_schedule_start_date_help") }}
            </div>
        </div>
    </div>

    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('end_date', trans("lang.article_schedule_end_date"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::date('end_date', $end_date,  ['class' => 'form-control','placeholder'=>  trans("lang.article_schedule_end_date_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.article_schedule_end_date_help") }}
            </div>
        </div>
    </div>

    <div class="form-group align-items-baseline d-flex flex-column flex-md-row ">
        {!! Form::label('start_time', trans("lang.article_schedule_start_time"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            <div class="input-group timepicker start_time" data-target-input="nearest">
                {!! Form::text('start_time', null,  ['class' => 'form-control datetimepicker-input','placeholder'=>  trans("lang.article_schedule_start_time_placeholder"), 'data-target'=>'.timepicker.start_time','data-toggle'=>'datetimepicker','autocomplete'=>'off']) !!}
                <div id="widgetParentId"></div>
                <div class="input-group-append" data-target=".timepicker.start_time" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="fas fa-business-time"></i></div>
                </div>
            </div>
            <div class="form-text text-muted">
                {{ trans("lang.article_schedule_start_time_help") }}
            </div>
        </div>
    </div>

    {{-- <div class="form-group align-items-baseline d-flex flex-column flex-md-row ">
        {!! Form::label('end_time', trans("lang.article_schedule_end_time"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            <div class="input-group timepicker end_time" data-target-input="nearest">
                {!! Form::text('end_time', null,  ['class' => 'form-control datetimepicker-input','placeholder'=>  trans("lang.article_schedule_end_time_placeholder"), 'data-target'=>'.timepicker.end_time','data-toggle'=>'datetimepicker','autocomplete'=>'off']) !!}
                <div id="widgetParentId"></div>
                <div class="input-group-append" data-target=".timepicker.end_time" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="fas fa-business-time"></i></div>
                </div>
            </div>
            <div class="form-text text-muted">
                {{ trans("lang.article_schedule_end_time_help") }}
            </div>
        </div>
    </div> --}}

    <div class="form-group align-items-baseline d-flex flex-column flex-md-row ">
        {!! Form::label('duration', trans("lang.article_schedule_duration"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            <div class="input-group timepicker duration" data-target-input="nearest">
                {!! Form::text('duration', null,  ['id' => 'duration', 'class' => 'form-control datetimepicker-input','placeholder'=>  trans("lang.article_schedule_duration_placeholder"), 'data-target'=>'.timepicker.duration','data-toggle'=>'datetimepicker','autocomplete'=>'off']) !!}
                <div id="widgetParentId"></div>
                <div class="input-group-append" data-target=".timepicker.duration" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="fas fa-business-time"></i></div>
                </div>
            </div>
            <div class="form-text text-muted">
                {{ trans("lang.article_schedule_duration_help") }}
            </div>
        </div>
    </div>

    <div class="form-group align-items-baseline d-flex flex-column flex-md-row ">
        {!! Form::label('repeat', trans("lang.article_schedule_repeat"),['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::select('repeat',[
                'never' => 'Never',
                'weekly' => 'Weekly'
            ] , null, ['class' => 'select2 form-control' , 'onchange'=>'staffRequriedCheck(this);' , 'id' => 'repeat']) !!}
            <div class="form-text text-muted">{{ trans("lang.article_schedule_repeat_help") }}</div>
        </div>
    </div>

    <div id="my">
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row" id="staffClass">
        {!! Form::label('days[]', trans("lang.article_schedule_days"),['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::select('days[]', $days, $selectedDyas, ['class' => 'select2 form-control not-required' , 'data-empty'=>trans('lang.article_schedule_days_placeholder'),'multiple'=>'multiple']) !!}
            <div class="form-text text-muted">{{ trans("lang.article_schedule_days_help") }}</div>
        </div>
    </div>
    </div>
</div>

@if($customFields)
    <div class="clearfix"></div>
    <div class="col-12 custom-field-container">
        <h5 class="col-12 pb-4">{!! trans('lang.custom_field_plural') !!}</h5>
        {!! $customFields !!}
    </div>
@endif
<!-- Submit Field -->
<div class="form-group col-12 d-flex flex-column flex-md-row justify-content-md-end justify-content-sm-center border-top pt-4">

    <button type="submit" class="btn bg-{{setting('theme_color')}} mx-md-3 my-lg-0 my-xl-0 my-md-0 my-2">
        <i class="fa fa-save"></i> {{trans('lang.save')}} {{trans('lang.article_schedule')}}</button>
    <a href="{!! route('article_schedule.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i> {{trans('lang.cancel')}}</a>
</div>

