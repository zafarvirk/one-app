<?php
/**
 * File name: OrdersOfUserCriteria.php
 * Last modified: 2020.04.30 at 08:24:08
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2020
 *
 */

namespace App\Criteria\Orders;

use App\Models\User;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class OrdersOfUserCriteria.
 *
 * @package namespace App\Criteria\Orders;
 */
class OrdersOfUserCriteria implements CriteriaInterface
{
    /**
     * @var User
     */
    private $userId;

    /**
     * OrdersOfUserCriteria constructor.
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
        if (auth()->user()->hasRole('admin')) {
            return $model;
        } else if (auth()->user()->hasRole('salon owner')) {
            return $model->join("article_orders", "orders.id", "=", "article_orders.order_id")
                ->join("article", "article.id", "=", "article_orders.article_id")
                ->join("business_users", "business_users.business_id", "=", "article.business_id")
                ->where('business_users.user_id', $this->userId)
                ->groupBy('orders.id')
                ->select('orders.*');

        } else if (auth()->user()->hasRole('customer')) {
            return $model->where('orders.user_id', $this->userId)
                ->groupBy('orders.id');
        } else if (auth()->user()->hasRole('driver')) {
            return $model->newQuery()->where('orders.driver_id', $this->userId)
                ->groupBy('orders.id');
        } else {
            return $model;
        }
    }
}
