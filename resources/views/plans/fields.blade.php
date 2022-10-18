@if($customFields)
    <h5 class="col-12 pb-4">{!! trans('lang.main_fields') !!}</h5>
@endif
<div class="d-flex flex-column col-sm-12 col-md-6">
    <!-- Name Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('name', trans("lang.plans_name"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::text('name', null,  ['class' => 'form-control','placeholder'=>  trans("lang.plans_name_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.plans_name_placeholder") }}
            </div>
        </div>
    </div>
    
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row ">
        {!! Form::label('price_type', trans("lang.plans_price_type"),['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::select('price_type',[
                'free' => trans('lang.free'),

                'recuring' => trans('lang.recuring'),

                'one_time' => trans('lang.onetime'),
            ], isset($plans) ? $plans->price_type : '', ['class' => 'select2 form-control']) !!}
            <div class="form-text text-muted">{{ trans("lang.plans_price_type_help") }}</div>
        </div>
    </div>

    <div class="form-group align-items-baseline d-flex flex-column flex-md-row ">
        {!! Form::label('price_frequency', trans("lang.plans_price_frequency"),['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::select('price_frequency',[
                'day' => trans('lang.day'),
                'month' => trans('lang.month'),
                'year' => trans('lang.year'),
            ], isset($plans) ? $plans->price_frequency : '', ['class' => 'select2 form-control']) !!}
            <div class="form-text text-muted">{{ trans("lang.plans_price_frequency_help") }}</div>
        </div>
    </div>

    <!-- Price Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row ">
        {!! Form::label('price', trans("lang.plans_price"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            <div class="input-group">
                {!! Form::number('price', null, ['class' => 'form-control','step'=>'any', 'min'=>'0', 'placeholder'=> trans("lang.plans_price_placeholder")]) !!}
                <div class="input-group-append">
                    <div class="input-group-text text-bold px-3">{{setting('default_currency','$')}}</div>
                </div>
            </div>
            <div class="form-text text-muted">
                {{ trans("lang.plans_price_help") }}
            </div>
        </div>
    </div>
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row ">
        {!! Form::label('business_id', trans("lang.class_article_business_id"),['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::select('plan_business_id', $business, null, ['class' => 'select2 form-control']) !!}
            <div class="form-text text-muted">{{ trans("lang.class_article_business_id_help") }}</div>
        </div>
    </div>
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row ">
        {!! Form::label('plans_article[]', trans("lang.plans_article"),['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::select('plans_article[]', $article, $articleSelected, ['class' => 'select2 form-control not-required' , 'data-empty'=>trans('lang.plans_article_placeholder'),'multiple'=>'multiple']) !!}
            <div class="form-text text-muted">{{ trans("lang.plans_article_help") }}</div>
        </div>
    </div>
</div>
<div class="d-flex flex-column col-sm-12 col-md-6">
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row ">
        {!! Form::label('type', trans("lang.plans_type"),['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::select('type',[
                'package' => trans('lang.package'),

                'memebership' => trans('lang.memebership'),

            ], isset($plans) ? $plans->type : '', ['class' => 'select2 form-control']) !!}
            <div class="form-text text-muted">{{ trans("lang.plans_type_help") }}</div>
        </div>
    </div>

    <div class="form-group align-items-baseline d-flex flex-column flex-md-row ">
        {!! Form::label('no_of_sessions', trans("lang.plans_no_of_sessions"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            <div class="input-group">
                {!! Form::number('no_of_sessions', null, ['class' => 'form-control','step'=>'any', 'min'=>'0', 'placeholder'=> trans("lang.plans_no_of_sessions_placeholder")]) !!}
            </div>
            <div class="form-text text-muted">
                {{ trans("lang.plans_no_of_sessions_help") }}
            </div>
        </div>
    </div>

    <!-- Duration Field -->
    <!-- Price Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row ">
        {!! Form::label('plan_duration', trans("lang.duration"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            <div class="input-group">
                {!! Form::number('plan_duration', null, ['class' => 'form-control','step'=>'any', 'min'=>'0', 'placeholder'=> trans("lang.duration_placeholder")]) !!}
            </div>
            <div class="form-text text-muted">
                {{ trans("lang.duration_help") }}
            </div>
        </div>
    </div>
    
    <!-- Description Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('description', trans("lang.plans_description"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::textarea('description', null, ['class' => 'form-control','placeholder'=>
             trans("lang.plans_description_placeholder")  ]) !!}
            <div class="form-text text-muted">{{ trans("lang.plans_description_help") }}</div>
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
    <!-- 'Boolean Default Field' -->
    <div class="d-flex flex-row justify-content-between align-items-center">
        {!! Form::label('allow_canceltion', trans("lang.plans_allow_canceltion"),['class' => 'control-label my-0 mx-3']) !!} {!! Form::hidden('allow_canceltion', 0, ['id'=>"hidden_allow_canceltion"]) !!}
        <span class="icheck-{{setting('theme_color')}}">
            {!! Form::checkbox('allow_canceltion', 1, null) !!} <label for="allow_canceltion"></label> </span>
    </div>
    <button type="submit" class="btn bg-{{setting('theme_color')}} mx-md-3 my-lg-0 my-xl-0 my-md-0 my-2">
        <i class="fa fa-save"></i> {{trans('lang.save')}} {{trans('lang.plans')}}</button>
    <a href="{!! route('plans.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i> {{trans('lang.cancel')}}</a>
</div>