<?php
/*
 * File name: BusinessReviewsOfUserCriteria.php
 * Last modified: 2022.02.15 at 16:30:52
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Criteria\BusinessReviews;

use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class BusinessReviewsOfUserCriteria.
 *
 * @package namespace App\Criteria\BusinessReviews;
 */
class BusinessReviewsOfUserCriteria implements CriteriaInterface
{
    /**
     * @var int
     */
    private $userId;

    /**
     * BusinessReviewsOfUserCriteria constructor.
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
        if (auth()->check() && auth()->user()->hasRole('admin')) {
            return $model->select('business_reviews.*');
        } else if (auth()->check() && auth()->user()->hasRole('salon owner')) {
            return $model->join("bookings", "bookings.id", "=", "business_reviews.booking_id")
                ->join("business_users", "business_users.business_id", "=", "bookings.business->id")
                ->where('business_users.user_id', $this->userId)
                ->groupBy('business_reviews.id')
                ->select('business_reviews.*');
        } else if (auth()->check() && auth()->user()->hasRole('customer')) {
            return $model->join("bookings", "bookings.id", "=", "business_reviews.booking_id")
                ->where('bookings.user_id', $this->userId)
                ->select('business_reviews.*');
        } else {
            return $model->select('business_reviews.*');
        }
    }
}
