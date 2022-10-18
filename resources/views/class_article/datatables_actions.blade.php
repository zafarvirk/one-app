<div class='btn-group btn-group-sm'>
    {{-- @can('class_article.show')
        <a data-toggle="tooltip" data-placement="left" title="{{trans('lang.view_details')}}" href="{{ route('class_article.show', $id) }}" class='btn btn-link'>
            <i class="fas fa-eye"></i> </a> @endcan --}}

    @can('class_article.edit')
        <a data-toggle="tooltip" data-placement="left" title="{{trans('lang.class_article_edit')}}" href="{{ route('class_article.edit', $id) }}" class='btn btn-link'>
            <i class="fas fa-edit"></i> </a> @endcan

    @can('class_article.destroy') {!! Form::open(['route' => ['class_article.destroy', $id], 'method' => 'delete']) !!} {!! Form::button('<i class="fas fa-trash"></i>', [ 'type' => 'submit', 'class' => 'btn btn-link text-danger', 'onclick' => "return confirm('Are you sure?')" ]) !!} {!! Form::close() !!} @endcan
</div>
