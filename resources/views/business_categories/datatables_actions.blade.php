<div class='btn-group btn-group-sm'>
    @can('businessCategories.show')
        <a data-toggle="tooltip" data-placement="left" title="{{trans('lang.view_details')}}" href="{{ route('businessCategories.show', $id) }}" class='btn btn-link'>
            <i class="fas fa-eye"></i> </a>
    @endcan

    @can('businessCategories.edit')
        <a data-toggle="tooltip" data-placement="left" title="{{trans('lang.business_category_edit')}}" href="{{ route('businessCategories.edit', $id) }}" class='btn btn-link'>
            <i class="fas fa-edit"></i> </a>
    @endcan

    @can('businessCategories.destroy')
        {!! Form::open(['route' => ['businessCategories.destroy', $id], 'method' => 'delete']) !!}
        {!! Form::button('<i class="fas fa-trash"></i>', [
        'type' => 'submit',
        'class' => 'btn btn-link text-danger',
        'onclick' => "return confirm('Are you sure?')"
        ]) !!}
        {!! Form::close() !!}
    @endcan
</div>
