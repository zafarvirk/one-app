<div class='btn-group btn-group-sm'>
    {{-- @can('gyms.show')
        <a data-toggle="tooltip" data-placement="left" title="{{trans('lang.view_details')}}" href="{{ route('gyms.show', $id) }}" class='btn btn-link'>
            <i class="fas fa-eye"></i> </a>
    @endcan --}}

    @can('gyms.edit')
        <a data-toggle="tooltip" data-placement="left" title="{{trans('lang.gym_edit')}}" href="{{ route('gyms.edit', $id) }}" class='btn btn-link'>
            <i class="fas fa-edit"></i> </a>
    @endcan

    @can('gyms.destroy')
        {!! Form::open(['route' => ['gyms.destroy', $id], 'method' => 'delete']) !!}
        {!! Form::button('<i class="fas fa-trash"></i>', [
        'type' => 'submit',
        'class' => 'btn btn-link text-danger',
        'onclick' => "return confirm('Are you sure?')"
        ]) !!}
        {!! Form::close() !!}
    @endcan
</div>
