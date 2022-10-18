@if($customFields)
    <h5 class="col-12 pb-4">{!! trans('lang.main_fields') !!}</h5>
@endif
<div class="d-flex flex-column col-sm-12 col-md-6">
    <!-- Name Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row ">
        {!! Form::label('name', trans("lang.market_name"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-9">
            {!! Form::text('name', null,  ['class' => 'form-control','placeholder'=>  trans("lang.market_name_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.market_name_help") }}
            </div>
        </div>
    </div>
    <!-- E Provider Type Id Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('business_category_id', trans("lang.salon_business_category_id"),['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::select('business_category_id', $businessCatgory, null, ['class' => 'select2 form-control']) !!}
            <div class="form-text text-muted">{{ trans("lang.salon_business_category_id_help") }}</div>
        </div>
    </div>
    <!-- fields Field -->
    {{-- <div class="form-group align-items-baseline d-flex flex-column flex-md-row ">
        {!! Form::label('fields[]', trans("lang.market_fields"),['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-9">
            {!! Form::select('fields[]', $field, $fieldsSelected, ['class' => 'select2 form-control' , 'multiple'=>'multiple']) !!}
            <div class="form-text text-muted">{{ trans("lang.market_fields_help") }}</div>
        </div>
    </div> --}}

    @hasanyrole('admin|manager')
    <!-- Users Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('drivers[]', trans("lang.market_drivers"),['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-9">
            {!! Form::select('drivers[]', $drivers, $driversSelected, ['class' => 'select2 form-control' , 'multiple'=>'multiple']) !!}
            <div class="form-text text-muted">{{ trans("lang.market_drivers_help") }}</div>
        </div>
    </div>
    <!-- delivery_fee Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('delivery_fee', trans("lang.market_delivery_fee"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-9">
            {!! Form::number('delivery_fee', null,  ['class' => 'form-control','step'=>'any','placeholder'=>  trans("lang.market_delivery_fee_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.market_delivery_fee_help") }}
            </div>
        </div>
    </div>
    <!-- business_modules Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row ">
        {!! Form::label('business_modules[]', trans("lang.salons_business_modules"),['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::select('business_modules[]', $modules, $modulesSelected, ['class' => 'select2 form-control not-required' , 'data-empty'=>trans('lang.salons_business_modules_placeholder'),'multiple'=>'multiple']) !!}
            <div class="form-text text-muted">{{ trans("lang.salons_business_modules_help") }}</div>
        </div>
    </div>

    <!-- delivery_range Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('delivery_range', trans("lang.market_delivery_range"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-9">
            {!! Form::number('delivery_range', null,  ['class' => 'form-control', 'step'=>'any','placeholder'=>  trans("lang.market_delivery_range_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.market_delivery_range_help") }}
            </div>
        </div>
    </div>

    <!-- default_tax Field -->
        <!-- Taxes Field -->
        <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
            {!! Form::label('taxes[]', trans("lang.salon_taxes"),['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
            <div class="col-md-9">
                {!! Form::select('taxes[]', $tax, $taxesSelected, ['class' => 'select2 form-control' , 'multiple'=>'multiple']) !!}
                <div class="form-text text-muted">{{ trans("lang.salon_taxes_help") }}</div>
            </div>
        </div>

    @endhasanyrole

    <!-- Phone Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('phone', trans("lang.market_phone"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-9">
            {!! Form::text('phone_number', null,  ['class' => 'form-control','placeholder'=>  trans("lang.market_phone_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.market_phone_help") }}
            </div>
        </div>
    </div>

    <!-- Mobile Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('mobile', trans("lang.market_mobile"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-9">
            {!! Form::text('mobile_number', null,  ['class' => 'form-control','placeholder'=>  trans("lang.market_mobile_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.market_mobile_help") }}
            </div>
        </div>
    </div>

    <!-- Address Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('address_id', trans("lang.market_address"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-9">
            {!! Form::select('address_id', $address, null, ['class' => 'select2 form-control']) !!}
            <div class="form-text text-muted">
                {{ trans("lang.market_address_help") }}
            </div>
        </div>
    </div>

    <!-- 'Boolean closed Field' -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('closed', trans("lang.market_closed"),['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="checkbox icheck">
            <label class="col-9 ml-2 form-check-inline">
                {!! Form::hidden('closed', 0) !!}
                {!! Form::checkbox('closed', 1, null) !!}
            </label>
        </div>
    </div>

    <!-- 'Boolean available_for_delivery Field' -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('available_for_delivery', trans("lang.market_available_for_delivery"),['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="checkbox icheck">
            <label class="col-9 ml-2 form-check-inline">
                {!! Form::hidden('available_for_delivery', 0) !!}
                {!! Form::checkbox('available_for_delivery', 1, null) !!}
            </label>
        </div>
    </div>

</div>
<div class="d-flex flex-column col-sm-12 col-md-6">

    <!-- Image Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('image', trans("lang.market_image"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-9">
            <div style="width: 100%" class="dropzone image" id="image" data-field="image">
                <input type="hidden" name="image">
            </div>
            <a href="#loadMediaModal" data-dropzone="image" data-toggle="modal" data-target="#mediaModal" class="btn btn-outline-{{setting('theme_color','primary')}} btn-sm float-right mt-1">{{ trans('lang.media_select')}}</a>
            <div class="form-text text-muted w-50">
                {{ trans("lang.market_image_help") }}
            </div>
        </div>
    </div>
    @prepend('scripts')
        <script type="text/javascript">
            var var15671147011688676454ble = '';
            @if(isset($market) && $market->hasMedia('image'))
                var15671147011688676454ble = {
                name: "{!! $market->getFirstMedia('image')->name !!}",
                size: "{!! $market->getFirstMedia('image')->size !!}",
                type: "{!! $market->getFirstMedia('image')->mime_type !!}",
                collection_name: "{!! $market->getFirstMedia('image')->collection_name !!}"
            };
                    @endif
            var dz_var15671147011688676454ble = $(".dropzone.image").dropzone({
                    url: "{!!url('uploads/store')!!}",
                    addRemoveLinks: true,
                    maxFiles: 1,
                    init: function () {
                        @if(isset($market) && $market->hasMedia('image'))
                        dzInit(this, var15671147011688676454ble, '{!! url($market->getFirstMediaUrl('image','thumb')) !!}')
                        @endif
                    },
                    accept: function (file, done) {
                        dzAccept(file, done, this.element, "{!!config('medialibrary.icons_folder')!!}");
                    },
                    sending: function (file, xhr, formData) {
                        dzSending(this, file, formData, '{!! csrf_token() !!}');
                    },
                    maxfilesexceeded: function (file) {
                        dz_var15671147011688676454ble[0].mockFile = '';
                        dzMaxfile(this, file);
                    },
                    complete: function (file) {
                        dzComplete(this, file, var15671147011688676454ble, dz_var15671147011688676454ble[0].mockFile);
                        dz_var15671147011688676454ble[0].mockFile = file;
                    },
                    removedfile: function (file) {
                        dzRemoveFile(
                            file, var15671147011688676454ble, '{!! url("markets/remove-media") !!}',
                            'image', '{!! isset($market) ? $market->id : 0 !!}', '{!! url("uplaods/clear") !!}', '{!! csrf_token() !!}'
                        );
                    }
                });
            dz_var15671147011688676454ble[0].mockFile = var15671147011688676454ble;
            dropzoneFields['image'] = dz_var15671147011688676454ble;
        </script>
@endprepend

<!-- Description Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row ">
        {!! Form::label('description', trans("lang.market_description"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-9">
            {!! Form::textarea('description', null, ['class' => 'form-control','placeholder'=>
             trans("lang.market_description_placeholder")  ]) !!}
            <div class="form-text text-muted">{{ trans("lang.market_description_help") }}</div>
        </div>
    </div>
    <!-- Information Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row ">
        {!! Form::label('information', trans("lang.market_information"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-9">
            {!! Form::textarea('information', null, ['class' => 'form-control','placeholder'=>
             trans("lang.market_information_placeholder")  ]) !!}
            <div class="form-text text-muted">{{ trans("lang.market_information_help") }}</div>
        </div>
    </div>

</div>

@hasrole('admin')
<div class="col-12 custom-field-container">
    <h5 class="col-12 pb-4">{!! trans('lang.admin_area') !!}</h5>
    <div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">
        <!-- Users Field -->
        <div class="form-group align-items-baseline d-flex flex-column flex-md-row ">
            {!! Form::label('users[]', trans("lang.market_users"),['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
            <div class="col-9">
                {!! Form::select('users[]', $user, $usersSelected, ['class' => 'select2 form-control' , 'multiple'=>'multiple']) !!}
                <div class="form-text text-muted">{{ trans("lang.market_users_help") }}</div>
            </div>
        </div>
        
    </div>
    <div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">
        <!-- admin_commission Field -->
        <div class="form-group align-items-baseline d-flex flex-column flex-md-row ">
            {!! Form::label('admin_commission', trans("lang.market_admin_commission"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
            <div class="col-9">
                {!! Form::number('admin_commission', null,  ['class' => 'form-control', 'step'=>'any', 'placeholder'=>  trans("lang.market_admin_commission_placeholder")]) !!}
                <div class="form-text text-muted">
                    {{ trans("lang.market_admin_commission_help") }}
                </div>
            </div>
        </div>
        <div class="form-group align-items-baseline d-flex flex-column flex-md-row ">
            {!! Form::label('active', trans("lang.market_active"),['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
            <div class="checkbox icheck">
                <label class="col-9 ml-2 form-check-inline">
                    {!! Form::hidden('active', 0) !!}
                    {!! Form::checkbox('active', 1, null) !!}
                </label>
            </div>
        </div>
        <div class="form-group align-items-baseline d-flex flex-column flex-md-row ">
            {!! Form::label('is_populer', trans("lang.salon_populer"),['class' => 'col-md-3 control-label text-md-right mx-1']) !!} {!! Form::hidden('is_populer', 0, ['id'=>"hidden_populer"]) !!}
            <span class="icheck-{{setting('theme_color')}}">
                {!! Form::checkbox('is_populer', 1, null) !!} <label for="is_populer"></label> </span>
        </div>
    </div>
</div>
@endhasrole

@if($customFields)
    <div class="clearfix"></div>
    <div class="col-12 custom-field-container">
        <h5 class="col-12 pb-4">{!! trans('lang.custom_field_plural') !!}</h5>
        {!! $customFields !!}
    </div>
@endif
<!-- Submit Field -->
<div class="form-group col-12 text-right">
    <button type="submit" class="btn btn-{{setting('theme_color')}}"><i class="fa fa-save"></i> {{trans('lang.save')}} {{trans('lang.market')}}</button>
    <a href="{!! route('markets.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i> {{trans('lang.cancel')}}</a>
</div>
