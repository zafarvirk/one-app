<?php
/*
 * File name: BookingsOfSalonCriteria.php
 * Last modified: 2022.02.02 at 21:26:20
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Criteria\Bookings;

use Illuminate\Support\Facades\DB;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class BookingsOfSalonCriteria.
 *
 * @package namespace App\Criteria\Bookings;
 */
class BookingsOfSalonCriteria implements CriteriaInterface
{
    /**
     * @var int
     */
    private $businessId;

    /**
     * BookingsOfbusinessCriteria constructor.
     */
    public function __construct($businessId)
    {
        $this->businessId = $businessId;
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
        $businessId = DB::raw("json_extract(business, '$.id')");
        return $model->where($businessId, $this->businessId)
            ->where('payment_status_id', '2')
            ->groupBy('bookings.id')
            ->select('bookings.*');

    }
}
