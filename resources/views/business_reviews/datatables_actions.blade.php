<div class='btn-group btn-group-sm'>
    @can('businessReviews.show')
        <a data-toggle="tooltip" data-placement="left" title="{{trans('lang.view_details')}}" href="{{ route('businessReviews.show', $id) }}" class='btn btn-link'>
            <i class="fas fa-eye"></i> </a> @endcan

    @can('businessReviews.edit')
        <a data-toggle="tooltip" data-placement="left" title="{{trans('lang.business_review_edit')}}" href="{{ route('businessReviews.edit', $id) }}" class='btn btn-link'>
            <i class="fas fa-edit"></i> </a> @endcan

    @can('businessReviews.destroy') {!! Form::open(['route' => ['businessReviews.destroy', $id], 'method' => 'delete']) !!} {!! Form::button('<i class="fas fa-trash"></i>', [ 'type' => 'submit', 'class' => 'btn btn-link text-danger', 'onclick' => "return confirm('Are you sure?')" ]) !!} {!! Form::close() !!} @endcan
</div>
