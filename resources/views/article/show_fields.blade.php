<!-- Id Field -->
<div class="form-group row col-6">
    {!! Form::label('id', 'Id:', ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        <p>{!! $article->id !!}</p>
    </div>
</div>

<!-- Name Field -->
<div class="form-group row col-6">
    {!! Form::label('name', 'Name:', ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        <p>{!! $article->name !!}</p>
    </div>
</div>

<!-- Image Field -->
<div class="form-group row col-6">
    {!! Form::label('image', 'Image:', ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        <p>{!! $article->image !!}</p>
    </div>
</div>

<!-- Price Field -->
<div class="form-group row col-6">
    {!! Form::label('price', 'Price:', ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        <p>{!! $article->price !!}</p>
    </div>
</div>

<!-- Discount Price Field -->
<div class="form-group row col-6">
    {!! Form::label('discount_price', 'Discount Price:', ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        <p>{!! $article->discount_price !!}</p>
    </div>
</div>

<!-- Duration Field -->
<div class="form-group row col-6">
    {!! Form::label('duration', 'Duration:', ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        <p>{!! $article->duration !!}</p>
    </div>
</div>

<!-- Description Field -->
<div class="form-group row col-6">
    {!! Form::label('description', 'Description:', ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        <p>{!! $article->description !!}</p>
    </div>
</div>

<!-- Categories Field -->
<div class="form-group row col-6">
    {!! Form::label('categories', 'Categories:', ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        <p>{!! $article->categories !!}</p>
    </div>
</div>

<!-- Featured Field -->
<div class="form-group row col-6">
    {!! Form::label('featured', 'Featured:', ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        <p>{!! $article->featured !!}</p>
    </div>
</div>

<!-- Available Field -->
<div class="form-group row col-6">
    {!! Form::label('available', 'Available:', ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        <p>{!! $article->available !!}</p>
    </div>
</div>

<!-- E Provider Id Field -->
<div class="form-group row col-6">
    {!! Form::label('salon_id', 'E Provider Id:', ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        <p>{!! $article->salon_id !!}</p>
    </div>
</div>

<!-- Created At Field -->
<div class="form-group row col-6">
    {!! Form::label('created_at', 'Created At:', ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        <p>{!! $article->created_at !!}</p>
    </div>
</div>

<!-- Updated At Field -->
<div class="form-group row col-6">
    {!! Form::label('updated_at', 'Updated At:', ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        <p>{!! $article->updated_at !!}</p>
    </div>
</div>

