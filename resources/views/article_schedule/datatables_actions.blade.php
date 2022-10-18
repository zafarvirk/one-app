<div class='btn-group btn-group-sm'>
    {{-- @can('article_schedule.show')
        <a data-toggle="tooltip" data-placement="left" title="{{trans('lang.view_details')}}" href="{{ route('article_schedule.show', $id) }}" class='btn btn-link'>
            <i class="fas fa-eye"></i> </a> @endcan --}}

    @can('article_schedule.edit')
        <a data-toggle="tooltip" data-placement="left" title="{{trans('lang.article_schedule_edit')}}" href="{{ route('article_schedule.edit', $id) }}" class='btn btn-link'>
            <i class="fas fa-edit"></i> </a> @endcan

    @can('article_schedule.destroy') {!! Form::open(['route' => ['article_schedule.destroy', $id], 'method' => 'delete']) !!} {!! Form::button('<i class="fas fa-trash"></i>', [ 'type' => 'submit', 'class' => 'btn btn-link text-danger', 'onclick' => "return confirm('Are you sure?')" ]) !!} {!! Form::close() !!} @endcan
</div>
