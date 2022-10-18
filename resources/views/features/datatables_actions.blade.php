<div class='btn-group btn-group-sm'>
    {{-- @can('aminities.show')
        <a data-toggle="tooltip" data-placement="left" title="{{trans('lang.view_details')}}" href="{{ route('aminities.show', $id) }}" class='btn btn-link'>
            <i class="fas fa-eye"></i> </a>
    @endcan --}}

    @can('features.edit')
        <a data-toggle="tooltip" data-placement="left" title="{{trans('lang.features_edit')}}" href="{{ route('features.edit', $id) }}" class='btn btn-link'>
            <i class="fas fa-edit"></i> </a>
    @endcan

    @can('features.destroy')
        {!! Form::open(['route' => ['features.destroy', $id], 'method' => 'delete']) !!}
        {!! Form::button('<i class="fas fa-trash"></i>', [
        'type' => 'submit',
        'class' => 'btn btn-link text-danger',
        'onclick' => "return confirm('Are you sure?')"
        ]) !!}
        {!! Form::close() !!}
    @endcan
</div>
