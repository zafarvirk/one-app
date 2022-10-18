@if($customFields)
    <h5 class="col-12 pb-4">{!! trans('lang.main_fields') !!}</h5>
@endif
<div class="d-flex flex-column col-sm-12 col-md-6 px-4">
    <!-- Name Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('name', trans("lang.class_article_name"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::text('name', null,  ['class' => 'form-control','placeholder'=>  trans("lang.class_article_name_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.class_article_name_help") }}
            </div>
        </div>
    </div>

    <!-- Categories Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row ">
        {!! Form::label('article_categories[]', trans("lang.class_article_categories"),['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::select('article_categories[]', $article_category, $articleCategoriesSelected, ['class' => 'select2 form-control not-required' , 'data-empty'=>trans('lang.class_article_categories_placeholder'),'multiple'=>'multiple']) !!}
            <div class="form-text text-muted">{{ trans("lang.class_article_categories_help") }}</div>
        </div>
    </div>

    <!-- Price Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row ">
        {!! Form::label('price', trans("lang.class_article_price"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            <div class="input-group">
                {!! Form::number('price', null, ['class' => 'form-control','step'=>'any', 'min'=>'0', 'placeholder'=> trans("lang.class_article_price_placeholder")]) !!}
                <div class="input-group-append">
                    <div class="input-group-text text-bold px-3">{{setting('default_currency','$')}}</div>
                </div>
            </div>
            <div class="form-text text-muted">
                {{ trans("lang.class_article_price_help") }}
            </div>
        </div>
    </div>

    <!-- Discount Price Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row ">
        {!! Form::label('discount_price', trans("lang.class_article_discount_price"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            <div class="input-group">
                {!! Form::number('discount_price', null, ['class' => 'form-control','step'=>'any', 'min'=>'0', 'placeholder'=> trans("lang.class_article_discount_price_placeholder")]) !!}
                <div class="input-group-append">
                    <div class="input-group-text text-bold px-3">{{setting('default_currency','$')}}</div>
                </div>
            </div>
            <div class="form-text text-muted">
                {{ trans("lang.class_article_discount_price_help") }}
            </div>
        </div>
    </div>

    <!-- Duration Field -->
    {{-- <div class="form-group align-items-baseline d-flex flex-column flex-md-row ">
        {!! Form::label('duration', trans("lang.class_article_duration"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            <div class="input-group timepicker duration" data-target-input="nearest">
                {!! Form::text('duration', null,  ['class' => 'form-control datetimepicker-input','placeholder'=>  trans("lang.class_article_duration_placeholder"), 'data-target'=>'.timepicker.duration','data-toggle'=>'datetimepicker','autocomplete'=>'off']) !!}
                <div id="widgetParentId"></div>
                <div class="input-group-append" data-target=".timepicker.duration" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="fas fa-business-time"></i></div>
                </div>
            </div>
            <div class="form-text text-muted">
                {{ trans("lang.class_article_duration_help") }}
            </div>
        </div>
    </div> --}}

    <!-- E Provider Id Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row ">
        {!! Form::label('business_id', trans("lang.class_article_business_id"),['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::select('business_id', $business, null, ['class' => 'select2 form-control']) !!}
            <div class="form-text text-muted">{{ trans("lang.class_article_business_id_help") }}</div>
        </div>
    </div>
    <!-- Max Appointment Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('max_appoiintments', trans("lang.class_article_max_appoiintments"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::text('max_appoiintments', null,  ['class' => 'form-control','placeholder'=>  trans("lang.class_article_max_appoiintments_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.class_article_max_appoiintments_help") }}
            </div>
        </div>
    </div>
    <!-- E Provider Id Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row ">
        {!! Form::label('is_staff_required', trans("lang.class_article_is_staff_required"),['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::select('is_staff_required',[
                '1' => 'No',
                '0' => 'yes'
            ] , null, ['class' => 'select2 form-control' , 'onchange'=>'staffRequriedCheck(this);' , 'id' => 'is_staff_required']) !!}
            <div class="form-text text-muted">{{ trans("lang.class_article_is_staff_required") }}</div>
        </div>
    </div>
    {{-- <div class="d-flex flex-row justify-content-between align-items-center">
        {!! Form::label('is_staff_required', trans("lang.class_article_is_staff_required"),['class' => 'control-label my-0 mx-3']) !!} {!! Form::hidden('is_staff_required', 0, ['id'=>"hidden_is_staff_required"]) !!}
        <span class="icheck-{{setting('theme_color')}}">
            {!! Form::checkbox('is_staff_required',null,['onclick'=>'staffRequriedCheck(this);']) !!} <label for="is_staff_required"></label> </span>
    </div> --}}
    <!-- Categories Field -->
    <div id="my">
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row" id="staffClass">
        {!! Form::label('article_staff[]', trans("lang.class_article_staff"),['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::select('article_staff[]', $staff, $staffSelected, ['class' => 'select2 form-control not-required' , 'data-empty'=>trans('lang.class_article_staff_placeholder'),'multiple'=>'multiple']) !!}
            <div class="form-text text-muted">{{ trans("lang.class_article_staff_help") }}</div>
        </div>
    </div>
    </div>
</div>
<div class="d-flex flex-column col-sm-12 col-md-6 px-4">

    <!-- Image Field -->
    <div class="form-group align-items-start d-flex flex-column flex-md-row">
        {!! Form::label('image', trans("lang.class_article_image"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            <div style="width: 100%" class="dropzone image" id="image" data-field="image">
            </div>
            <a href="#loadMediaModal" data-dropzone="image" data-toggle="modal" data-target="#mediaModal" class="btn btn-outline-{{setting('theme_color','primary')}} btn-sm float-right mt-1">{{ trans('lang.media_select')}}</a>
            <div class="form-text text-muted w-50">
                {{ trans("lang.class_article_image_help") }}
            </div>
        </div>
    </div>
    @prepend('scripts')
        <script type="text/javascript">
            var var16110647911349350349ble = [];
            @if(isset($article) && $article->hasMedia('image'))
            @forEach($article->getMedia('image') as $media)
            var16110647911349350349ble.push({
                name: "{!! $media->name !!}",
                size: "{!! $media->size !!}",
                type: "{!! $media->mime_type !!}",
                uuid: "{!! $media->getCustomProperty('uuid'); !!}",
                thumb: "{!! $media->getUrl('thumb'); !!}",
                collection_name: "{!! $media->collection_name !!}"
            });
            @endforeach
            @endif
            var dz_var16110647911349350349ble = $(".dropzone.image").dropzone({
                url: "{!!url('uploads/store')!!}",
                addRemoveLinks: true,
                maxFiles: 5 - var16110647911349350349ble.length,
                init: function () {
                    @if(isset($article) && $article->hasMedia('image'))
                    var16110647911349350349ble.forEach(media => {
                        dzInit(this, media, media.thumb);
                    });
                    @endif
                },
                accept: function (file, done) {
                    dzAccept(file, done, this.element, "{!!config('medialibrary.icons_folder')!!}");
                },
                sending: function (file, xhr, formData) {
                    dzSendingMultiple(this, file, formData, '{!! csrf_token() !!}');
                },
                complete: function (file) {
                    dzCompleteMultiple(this, file);
                    dz_var16110647911349350349ble[0].mockFile = file;
                },
                removedfile: function (file) {
                    dzRemoveFileMultiple(
                        file, var16110647911349350349ble, '{!! url("class_article/remove-media") !!}',
                        'image', '{!! isset($article) ? $article->id : 0 !!}', '{!! url("uploads/clear") !!}', '{!! csrf_token() !!}'
                    );
                }
            });
            dz_var16110647911349350349ble[0].mockFile = var16110647911349350349ble;
            dropzoneFields['image'] = dz_var16110647911349350349ble;
        </script>
    @endprepend
    <!-- Description Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row ">
        {!! Form::label('description', trans("lang.class_article_description"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::textarea('description', null, ['class' => 'form-control','placeholder'=>
             trans("lang.class_article_description_placeholder")  ]) !!}
            <div class="form-text text-muted">{{ trans("lang.class_article_description_help") }}</div>
        </div>
    </div>

</div>
{{-- <div class="d-flex flex-column col-sm-12 col-md-3 px-4">
</div> --}}
@if($customFields)
    <div class="clearfix"></div>
    <div class="col-12 custom-field-container">
        <h5 class="col-12 pb-4">{!! trans('lang.custom_field_plural') !!}</h5>
        {!! $customFields !!}
    </div>
@endif
<!-- Submit Field -->
<div class="form-group col-12 d-flex flex-column flex-md-row justify-content-md-end justify-content-sm-center border-top pt-4">
    
    <div class="d-flex flex-row justify-content-between align-items-center">
        {!! Form::label('featured', trans("lang.class_article_featured"),['class' => 'control-label my-0 mx-3']) !!} {!! Form::hidden('featured', 0, ['id'=>"hidden_featured"]) !!}
        <span class="icheck-{{setting('theme_color')}}">
            {!! Form::checkbox('featured', 1, null) !!} <label for="featured"></label> </span>
    </div>
    {{-- <div class="d-flex flex-row justify-content-between align-items-center">
        {!!  Form::label('enable_booking', trans("lang.class_article_enable_booking"),['class' => 'control-label my-0 mx-3'], false)  !!} {!! Form::hidden('enable_booking', 0, ['id'=>"hidden_enable_booking"]) !!}
        <span class="icheck-{{setting('theme_color')}}">
            {!! Form::checkbox('enable_booking', 1, null) !!} <label for="enable_booking"></label> </span>
    </div>
    <div class="d-flex flex-row justify-content-between align-items-center">
        {!!  Form::label('enable_at_salon', trans("lang.class_article_enable_at_salon"),['class' => 'control-label my-0 mx-3'], false)  !!} {!! Form::hidden('enable_at_salon', 0, ['id'=>"hidden_enable_at_salon"]) !!}
        <span class="icheck-{{setting('theme_color')}}">
            {!! Form::checkbox('enable_at_salon', 1, null) !!} <label for="enable_at_salon"></label> </span>
    </div>
    <div class="d-flex flex-row justify-content-between align-items-center">
        {!!  Form::label('enable_at_customer_address', trans("lang.class_article_enable_at_customer_address"),['class' => 'control-label my-0 mx-3'], false)  !!} {!! Form::hidden('enable_at_customer_address', 0, ['id'=>"hidden_enable_at_customer_address"]) !!}
        <span class="icheck-{{setting('theme_color')}}">
            {!! Form::checkbox('enable_at_customer_address', 1, null) !!} <label for="enable_at_customer_address"></label> </span>
    </div> --}}
    <div class="d-flex flex-row justify-content-between align-items-center">
        {!! Form::label('available', trans("lang.class_article_available"),['class' => 'control-label my-0 mx-3']) !!} {!! Form::hidden('available', 0, ['id'=>"hidden_available"]) !!}
        <span class="icheck-{{setting('theme_color')}}">
            {!! Form::checkbox('available', 1, null) !!} <label for="available"></label> </span>
    </div>
    <button type="submit" class="btn bg-{{setting('theme_color')}} mx-md-3 my-lg-0 my-xl-0 my-md-0 my-2">
        <i class="fa fa-save"></i> {{trans('lang.save')}} {{trans('lang.class_article')}}</button>
    <a href="{!! route('article.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i> {{trans('lang.cancel')}}</a>
</div>

