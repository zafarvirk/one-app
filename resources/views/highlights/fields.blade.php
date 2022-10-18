@if($customFields)
    <h5 class="col-12 pb-4">{!! trans('lang.main_fields') !!}</h5>
@endif
<div class="d-flex flex-column col-sm-12 col-md-6">
    <!-- Name Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('name', trans("lang.highlights_name"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::text('name', null,  ['class' => 'form-control','placeholder'=>  trans("lang.highlights_name_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.highlights_name_help") }}
            </div>
        </div>
    </div>
    <!-- Image Field -->
    <div class="form-group align-items-start d-flex flex-column flex-md-row">
        {!! Form::label('image', trans("lang.highlights_image"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            <div style="width: 100%" class="dropzone image" id="image" data-field="image">
            </div>
            <a href="#loadMediaModal" data-dropzone="image" data-toggle="modal" data-target="#mediaModal" class="btn btn-outline-{{setting('theme_color','primary')}} btn-sm float-right mt-1">{{ trans('lang.media_select')}}</a>
            <div class="form-text text-muted w-50">
                {{ trans("lang.highlights_image_help") }}
            </div>
        </div>
    </div>
    @prepend('scripts')
        <script type="text/javascript">
            var var16110647911349350349ble = [];
            @if(isset($highlights) && $highlights->hasMedia('image'))
                @forEach($highlights->getMedia('image') as $media)
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
                    @if(isset($highlights) && $highlights->hasMedia('image'))
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
                        file, var16110647911349350349ble, '{!! url("highlight/remove-media") !!}',
                        'image', '{!! isset($highlights) ? $highlights->id : 0 !!}', '{!! url("uploads/clear") !!}', '{!! csrf_token() !!}'
                    );
                }
            });
            dz_var16110647911349350349ble[0].mockFile = var16110647911349350349ble;
            dropzoneFields['image'] = dz_var16110647911349350349ble;
        </script>
    @endprepend

    <div class="form-group align-items-baseline d-flex flex-column flex-md-row ">
        {!! Form::label('highlight_businesses[]', trans("lang.highlights_business"),['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::select('highlight_businesses[]', $business, $businessSelected, ['class' => 'select2 form-control not-required' , 'data-empty'=>trans('lang.highlights_business_placeholder'),'multiple'=>'multiple']) !!}
            <div class="form-text text-muted">{{ trans("lang.highlights_business_help") }}</div>
        </div>
    </div>
</div>
<div class="d-flex flex-column col-sm-12 col-md-6">
    <!-- Description Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('description', trans("lang.highlights_description"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::textarea('description', null, ['class' => 'form-control','placeholder'=>
             trans("lang.highlights_description_placeholder")  ]) !!}
            <div class="form-text text-muted">{{ trans("lang.highlights_description_help") }}</div>
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
    <div class="d-flex flex-row justify-content-between align-items-center">
        {!! Form::label('status', trans("lang.highlights_status"),['class' => 'control-label my-0 mx-3'],false) !!} {!! Form::hidden('status', 0, ['id'=>"hidden_featured"]) !!}
        <span class="icheck-{{setting('theme_color')}}">
            {!! Form::checkbox('status', 1, null) !!} <label for="status"></label> </span>
    </div>
    <button type="submit" class="btn bg-{{setting('theme_color')}} mx-md-3 my-lg-0 my-xl-0 my-md-0 my-2">
        <i class="fas fa-save"></i> {{trans('lang.save')}} {{trans('lang.highlights')}}</button>
    <a href="{!! route('highlights.index') !!}" class="btn btn-default"><i class="fas fa-undo"></i> {{trans('lang.cancel')}}</a>
</div>