<?php
/*
 * File name: EarningOfUserCriteria.php
 * Last modified: 2022.02.03 at 18:14:47
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Criteria\Earnings;

use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class EarningOfUserCriteria.
 *
 * @package namespace App\Criteria\Earnings;
 */
class EarningOfUserCriteria implements CriteriaInterface
{
    private $userId;

    /**
     * EarningOfUserCriteria constructor.
     * @param $salonId
     */
    public function __construct($userId)
    {
        $this->userId = $userId;
    }


    /**
     * Apply criteria in query repository
     *
     * @param string              $model
     * @param RepositoryInterface $repository
     *
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        if (auth()->user()->hasRole('admin')) {
            return $model;
        } else if ((auth()->user()->hasRole('salon owner'))) {
            return $model->join("business_users", "business_users.business_id", "=", "earnings.business_id")
                ->groupBy('earnings.id')
                ->where('business_users.user_id', $this->userId);
        } else {
            return $model;
        }
    }
}
