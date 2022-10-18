<!-- Id Field -->
<div class="form-group align-items-baseline d-flex flex-column flex-md-row">
    {!! Form::label('id', 'Id:', ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        <p>{!! $businessReviews->id !!}</p>
    </div>
</div>

<!-- Review Field -->
<div class="form-group align-items-baseline d-flex flex-column flex-md-row">
    {!! Form::label('review', 'Review:', ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        <p>{!! $businessReviews->review !!}</p>
    </div>
</div>

<!-- Rate Field -->
<div class="form-group align-items-baseline d-flex flex-column flex-md-row">
    {!! Form::label('rate', 'Rate:', ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        <p>{!! $businessReviews->rate !!}</p>
    </div>
</div>

<!-- User Id Field -->
<div class="form-group align-items-baseline d-flex flex-column flex-md-row">
    {!! Form::label('user_id', 'User Id:', ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        <p>{!! $businessReviews->user_id !!}</p>
    </div>
</div>

<!-- E Service Id Field -->
<div class="form-group align-items-baseline d-flex flex-column flex-md-row">
    {!! Form::label('e_service_id', 'E Service Id:', ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        <p>{!! $businessReviews->e_service_id !!}</p>
    </div>
</div>

<!-- Created At Field -->
<div class="form-group align-items-baseline d-flex flex-column flex-md-row">
    {!! Form::label('created_at', 'Created At:', ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        <p>{!! $businessReviews->created_at !!}</p>
    </div>
</div>

<!-- Updated At Field -->
<div class="form-group align-items-baseline d-flex flex-column flex-md-row">
    {!! Form::label('updated_at', 'Updated At:', ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        <p>{!! $businessReviews->updated_at !!}</p>
    </div>
</div>


