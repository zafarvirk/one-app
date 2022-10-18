<div class='btn-group btn-group-sm'>
    @can('transactionStatuses.show')
        <a data-toggle="tooltip" data-placement="left" title="{{trans('lang.view_details')}}" href="{{ route('transactionStatuses.show', $id) }}" class='btn btn-link'>
            <i class="fas fa-eye"></i> </a> @endcan

    @can('transactionStatuses.edit')
        <a data-toggle="tooltip" data-placement="left" title="{{trans('lang.transaction_status_edit')}}" href="{{ route('transactionStatuses.edit', $id) }}" class='btn btn-link'>
            <i class="fas fa-edit"></i> </a> @endcan

    @can('transactionStatuses.destroy') {!! Form::open(['route' => ['transactionStatuses.destroy', $id], 'method' => 'delete']) !!} {!! Form::button('<i class="fas fa-trash"></i>', [ 'type' => 'submit', 'class' => 'btn btn-link text-danger', 'onclick' => "return confirm('Are you sure?')" ]) !!} {!! Form::close() !!} @endcan
</div>
