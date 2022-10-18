@if($customFields)
    <h5 class="col-12 pb-4">{!! trans('lang.main_fields') !!}</h5>
@endif
<div class="d-flex flex-column col-sm-12 col-md-6">
    <!-- Name Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('name', trans("lang.modules_name"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::text('name', null,  ['class' => 'form-control','placeholder'=>  trans("lang.modules_name_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.modules_name_help") }}
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
    <!-- 'Boolean Default Field' -->
    <div class="d-flex flex-row justify-content-between align-items-center">
        {!! Form::label('status', trans("lang.modules_status"),['class' => 'control-label my-0 mx-3']) !!} {!! Form::hidden('status', 0, ['id'=>"hidden_status"]) !!}
        <span class="icheck-{{setting('theme_color')}}">
            {!! Form::checkbox('status', 1, null) !!} <label for="status"></label> </span>
    </div>
    <button type="submit" class="btn bg-{{setting('theme_color')}} mx-md-3 my-lg-0 my-xl-0 my-md-0 my-2">
        <i class="fa fa-save"></i> {{trans('lang.save')}} {{trans('lang.modules')}}</button>
    <a href="{!! route('modules.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i> {{trans('lang.cancel')}}</a>
</div>