<?php
/*
 * File name: BusinessOfUserCriteria.php
 * Last modified: 2022.02.02 at 21:26:20
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Criteria\Salons;

use App\Models\User;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class BusinessOfUserCriteria.
 *
 * @package namespace App\Criteria\Salons;
 */
class BusinessOfUserCriteria implements CriteriaInterface
{

    /**
     * @var User
     */
    private $userId;

    /**
     * BusinessOfUserCriteria constructor.
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
        } elseif (auth()->user()->hasRole('salon owner')) {
            return $model->join('business_users', 'business_users.business_id', '=', 'businesses.id')
                ->select('businesses.*')
                ->where('business_users.user_id', $this->userId);
        } elseif (auth()->user()->hasRole('class_manager')) {
            return $model->join('business_users', 'business_users.business_id', '=', 'businesses.id')
                ->select('businesses.*')
                ->where('business_users.user_id', $this->userId);
        } else {
            return $model;
        }
    }
}
