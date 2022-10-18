<?php

namespace App\Repositories;

use App\Models\ArticleRequest;
use InfyOm\Generator\Common\BaseRepository;

/**
 * Class ArticleRequestRepository
 * @package App\Repositories
 * @version September 4, 2019, 3:38 pm UTC
 *
 * @method ArticleRequest findWithoutFail($id, $columns = ['*'])
 * @method ArticleRequest find($id, $columns = ['*'])
 * @method ArticleRequest first($columns = ['*'])
*/
class ArticleRequestRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name',
        'required_datetime',
        'type',
        'business_category_id',
        'transaction_status_id',
        'address_id',
        'scope',
        'no_of_offers',
        'merchants_informed',
        'order_id',
        'address_from_text',
        'address_from_coordinates',
        'request_type',
        'price_type',
        'price',
        'price_from'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return ArticleRequest::class;
    }
}
