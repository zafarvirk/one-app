<!-- Id Field -->
<div class="form-group row col-6">
    {!! Form::label('id', 'Id:', ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        <p>{!! $gym->id !!}</p>
    </div>
</div>

<!-- Image Field -->
<div class="form-group row col-6">
    {!! Form::label('image', 'Image:', ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        <p>{!! $gym->image !!}</p>
    </div>
</div>

<!-- Name Field -->
<div class="form-group row col-6">
    {!! Form::label('name', 'Name:', ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        <p>{!! $gym->name !!}</p>
    </div>
</div>

<!-- E Provider Type Id Field -->
<div class="form-group row col-6">
    {!! Form::label('gym_level_id', 'E Provider Type Id:', ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        <p>{!! $gym->gym_level_id !!}</p>
    </div>
</div>

<!-- Description Field -->
<div class="form-group row col-6">
    {!! Form::label('description', 'Description:', ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        <p>{!! $gym->description !!}</p>
    </div>
</div>

<!-- Users Field -->
<div class="form-group row col-6">
    {!! Form::label('users', 'Users:', ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        <p>{!! $gym->users !!}</p>
    </div>
</div>

<!-- Phone Number Field -->
<div class="form-group row col-6">
    {!! Form::label('phone_number', 'Phone Number:', ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        <p>{!! $gym->phone_number !!}</p>
    </div>
</div>

<!-- Mobile Number Field -->
<div class="form-group row col-6">
    {!! Form::label('mobile_number', 'Mobile Number:', ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        <p>{!! $gym->mobile_number !!}</p>
    </div>
</div>

<!-- Addresses Field -->
<div class="form-group row col-6">
    {!! Form::label('addresses', 'Addresses:', ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        <p>{!! $gym->address !!}</p>
    </div>
</div>

<!-- Availability Range Field -->
<div class="form-group row col-6">
    {!! Form::label('availability_range', 'Availability Range:', ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        <p>{!! $gym->availability_range !!}</p>
    </div>
</div>

<!-- Available Field -->
<div class="form-group row col-6">
    {!! Form::label('available', 'Available:', ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        <p>{!! $gym->available !!}</p>
    </div>
</div>

<!-- Taxes Field -->
<div class="form-group row col-6">
    {!! Form::label('taxes', 'Taxes:', ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        <p>{!! $gym->taxes !!}</p>
    </div>
</div>

<!-- Featured Field -->
<div class="form-group row col-6">
    {!! Form::label('featured', 'Featured:', ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        <p>{!! $gym->featured !!}</p>
    </div>
</div>

