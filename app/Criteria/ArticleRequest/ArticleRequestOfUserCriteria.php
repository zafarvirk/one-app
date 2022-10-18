<?php
/**
 * File name: ProductsOfUserCriteria.php
 * Last modified: 2020.04.30 at 08:24:08
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2020
 *
 */

namespace App\Criteria\ArticleRequest;

use App\Models\Business;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class ArticleRequestOfUserCriteria.
 *
 * @package namespace App\Criteria\Products;
 */
class ArticleRequestOfUserCriteria implements CriteriaInterface
{
    /**
     * @var int
     */
    private $userId;

    /**
     * ArticleRequestOfUserCriteria constructor.
     */
    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Apply criteria in query repository
     *
     * @param string $model
     * @param RepositoryInterface $repository
     *
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        if(auth()->user()->hasRole('admin')){
            return $model;
        } elseif (auth()->user()->hasRole('salon owner')) {
            $businessId = Business::join('business_users', 'business_users.business_id', '=', 'businesses.id')
                        ->where('business_users.user_id', $this->userId)->get('business_category_id')->toArray();
            $business_categories = [];
            foreach($businessId as $b){
                $business_categories[] = $b['business_category_id'];
            }
            return $model->whereIn('business_category_id', $business_categories);
        } else {
            return $model->where('user_id', $this->userId);
        }
    }
}
