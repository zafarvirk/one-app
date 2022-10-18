<?php

namespace App\Repositories;

use App\Models\ArticleOrder;
use InfyOm\Generator\Common\BaseRepository;

/**
 * Class ArticleOrderRepository
 * @package App\Repositories
 * @version August 31, 2019, 11:18 am UTC
 *
 * @method ProductOrder findWithoutFail($id, $columns = ['*'])
 * @method ProductOrder find($id, $columns = ['*'])
 * @method ProductOrder first($columns = ['*'])
*/
class ArticleOrderRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'price',
        'quantity',
        'article_id',
        'order_id'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return ArticleOrder::class;
    }
}
