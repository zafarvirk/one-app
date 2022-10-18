@if($customFields)
    <h5 class="col-12 pb-4">{!! trans('lang.main_fields') !!}</h5>
@endif
<div class="d-flex flex-column col-sm-12 col-md-6">
    <!-- Name Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('name', trans("lang.request_name"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::text('name', null,  ['class' => 'form-control','placeholder'=>  trans("lang.request_name_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.request_name_help") }}
            </div>
        </div>
    </div>


    <!-- Description Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('required_datetime', trans("lang.request_required_datetime"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::date('required_datetime', null, ['class' => 'form-control','placeholder'=>
             trans("lang.request_required_datetime_placeholder")  ]) !!}
            <div class="form-text text-muted">{{ trans("lang.request_required_datetime_help") }}</div>
        </div>
    </div>
    <!-- Type Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('type', trans("lang.request_type"),['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::select('type', ['pickup' => 'Pickup' , 'delivery' => 'Delivery'], null, ['data-empty'=>trans("lang.request_type_placeholder"), 'class' => 'select not-required form-control']) !!}
            <div class="form-text text-muted">{{ trans("lang.request_type_help") }}</div>
        </div>
    </div>    
    <!-- Type Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('business_category', trans("lang.request_business_category"),['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::select('business_category_id', $business_category, null, ['data-empty'=>trans("lang.request_business_category_placeholder"), 'class' => 'select not-required form-control']) !!}
            <div class="form-text text-muted">{{ trans("lang.request_business_category_help") }}</div>
        </div>
    </div>
    <!-- Type Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('transaction_status', trans("lang.request_transaction_status"),['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::select('transaction_status_id', $transaction_status, null, ['data-empty'=>trans("lang.request_transaction_status_placeholder"), 'class' => 'select not-required form-control']) !!}
            <div class="form-text text-muted">{{ trans("lang.request_transaction_status_help") }}</div>
        </div>
    </div>
    <!-- Type Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('address', trans("lang.request_address"),['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::select('address_id', $address, null, ['data-empty'=>trans("lang.request_address_placeholder"), 'class' => 'select not-required form-control']) !!}
            <div class="form-text text-muted">{{ trans("lang.request_address_help") }}</div>
        </div>
    </div>
    <!-- Type Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('user', trans("lang.request_user"),['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::select('user_id', $user, null, ['data-empty'=>trans("lang.request_user_placeholder"), 'class' => 'select not-required form-control']) !!}
            <div class="form-text text-muted">{{ trans("lang.request_user_help") }}</div>
        </div>
    </div>
</div>
<div class="d-flex flex-column col-sm-12 col-md-6">
    <!-- Image Field -->
    <div class="form-group align-items-start d-flex flex-column flex-md-row">
        {!! Form::label('image', trans("lang.request_image"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            <div style="width: 100%" class="dropzone image" id="image" data-field="image">
                <input type="hidden" name="image">
            </div>
            <a href="#loadMediaModal" data-dropzone="image" data-toggle="modal" data-target="#mediaModal" class="btn btn-outline-{{setting('theme_color','primary')}} btn-sm float-right mt-1">{{ trans('lang.media_select')}}</a>
            <div class="form-text text-muted w-50">
                {{ trans("lang.request_image_help") }}
            </div>
        </div>
    </div>
    @prepend('scripts')
        <script type="text/javascript">
            var var16110650672130312723ble = '';
            @if(isset($request) && $request->hasMedia('image'))
                var16110650672130312723ble = {
                name: "{!! $request->getFirstMedia('image')->name !!}",
                size: "{!! $request->getFirstMedia('image')->size !!}",
                type: "{!! $request->getFirstMedia('image')->mime_type !!}",
                collection_name: "{!! $request->getFirstMedia('image')->collection_name !!}"
            };
            @endif
            var dz_var16110650672130312723ble = $(".dropzone.image").dropzone({
                url: "{!!url('uploads/store')!!}",
                addRemoveLinks: true,
                maxFiles: 1,
                init: function () {
                    @if(isset($request) && $request->hasMedia('image'))
                    dzInit(this, var16110650672130312723ble, '{!! url($request->getFirstMediaUrl('image')) !!}')
                    @endif
                },
                accept: function (file, done) {
                    dzAccept(file, done, this.element, "{!!config('medialibrary.icons_folder')!!}");
                },
                sending: function (file, xhr, formData) {
                    dzSending(this, file, formData, '{!! csrf_token() !!}');
                },
                maxfilesexceeded: function (file) {
                    dz_var16110650672130312723ble[0].mockFile = '';
                    dzMaxfile(this, file);
                },
                complete: function (file) {
                    dzComplete(this, file, var16110650672130312723ble, dz_var16110650672130312723ble[0].mockFile);
                    dz_var16110650672130312723ble[0].mockFile = file;
                },
                removedfile: function (file) {
                    dzRemoveFile(
                        file, var16110650672130312723ble, '{!! url("requests/remove-media") !!}',
                        'image', '{!! isset($request) ? $request->id : 0 !!}', '{!! url("uplaods/clear") !!}', '{!! csrf_token() !!}'
                    );
                }
            });
            dz_var16110650672130312723ble[0].mockFile = var16110650672130312723ble;
            dropzoneFields['image'] = dz_var16110650672130312723ble;
        </script>
    @endprepend

    <!-- Type Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('scope', trans("lang.request_scope"),['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::select('scope', ['pickup' => 'Pickup' , 'delivery' => 'Delivery'], null, ['data-empty'=>trans("lang.request_scope_placeholder"), 'class' => 'select not-required form-control']) !!}
            <div class="form-text text-muted">{{ trans("lang.request_scope_help") }}</div>
        </div>
    </div> 
    <!-- Description Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('address_from_text', trans("lang.request_address_from_text"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::text('address_from_text', null, ['class' => 'form-control','placeholder'=>
             trans("lang.request_address_from_text_placeholder")  ]) !!}
            <div class="form-text text-muted">{{ trans("lang.request_address_from_text_help") }}</div>
        </div>
    </div> 
    <!-- Description Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('address_from_coordinates', trans("lang.request_address_from_coordinates"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::text('address_from_coordinates', null, ['class' => 'form-control','placeholder'=>
             trans("lang.request_address_from_coordinates_placeholder")  ]) !!}
            <div class="form-text text-muted">{{ trans("lang.request_address_from_coordinates_help") }}</div>
        </div>
    </div>
    <!-- Type Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('request_type', trans("lang.request_request_type"),['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::select('request_type', ['delivery' => 'Delivery' , 'order' => 'Order' , 'booking' => 'Booking'], null, ['data-empty'=>trans("lang.request_request_type_placeholder"), 'class' => 'select not-required form-control']) !!}
            <div class="form-text text-muted">{{ trans("lang.request_request_type_help") }}</div>
        </div>
    </div>
    <!-- Type Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('price_type', trans("lang.request_price_type"),['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::select('price_type', ['fixed' => 'Fixed' , 'range' => 'Range' , 'starting_from' => 'Starting From'], null, ['data-empty'=>trans("lang.request_price_type_placeholder"), 'class' => 'select not-required form-control']) !!}
            <div class="form-text text-muted">{{ trans("lang.request_price_type_help") }}</div>
        </div>
    </div>
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('price', trans("lang.request_price"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::number('price', null, ['class' => 'form-control','placeholder'=>
             trans("lang.request_price_placeholder")  ]) !!}
            <div class="form-text text-muted">{{ trans("lang.request_price_help") }}</div>
        </div>
    </div> 
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('price_from', trans("lang.request_price_from"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::number('price_from', null, ['class' => 'form-control','placeholder'=>
             trans("lang.request_price_from_placeholder")  ]) !!}
            <div class="form-text text-muted">{{ trans("lang.request_price_from_help") }}</div>
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
        <i class="fa fa-save"></i> {{trans('lang.save')}} {{trans('lang.request')}}
    </button>
    <a href="{!! route('requests.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i> {{trans('lang.cancel')}}</a>
</div>
