<?php
/**
 * File name: DriversOfMarketCriteria.php
 * Last modified: 2020.04.30 at 08:21:09
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2020
 *
 */

namespace App\Criteria\Users;

use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class DriversOfMarketCriteria.
 *
 * @package namespace App\Criteria\Users;
 */
class DriversOfMarketCriteria implements CriteriaInterface
{
    /**
     * @var int
     */
    private $marketId;

    /**
     * DriversOfMarketCriteria constructor.
     */
    public function __construct(int $marketId)
    {
        $this->marketId = $marketId;
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
        return $model->join('driver_businesses','users.id','=','driver_businesses.user_id')
            ->where('driver_businesses.business_id',$this->marketId);
    }
}
