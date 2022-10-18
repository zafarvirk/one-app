<?php
/*
 * File name: ArticleRepository.php
 * Last modified: 2022.03.11 at 22:26:16
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Repositories;

use App\Models\Article;
use InfyOm\Generator\Common\BaseRepository;
use Prettus\Repository\Contracts\CacheableInterface;
use Prettus\Repository\Traits\CacheableRepository;

/**
 * Class ArticleRepository
 * @package App\Repositories
 * @version January 19, 2021, 1:59 pm UTC
 *
 * @method EService findWithoutFail($id, $columns = ['*'])
 * @method EService find($id, $columns = ['*'])
 * @method EService first($columns = ['*'])
 */
class ArticleRepository extends BaseRepository implements CacheableInterface
{

    use CacheableRepository;

    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name',
        'type',
        'price',
        'discount_price',
        'duration',
        'description',
        'featured',
        'available',
        'enable_booking',
        'enable_at_salon',
        'enable_at_customer_address',
        'business_id'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Article::class;
    }

    /**
     * @return array
     */
    public function groupedBySalons(): array
    {
        $article = [];
        foreach ($this->all() as $model) {
            if (!empty($model->business)) {
                $article[$model->business->name][$model->id] = $model->name;
            }
        }
        return $article;
    }
}
