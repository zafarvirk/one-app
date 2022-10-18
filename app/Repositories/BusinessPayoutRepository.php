<?php
/*
 * File name: BusinessPayoutRepository.php
 * Last modified: 2022.02.02 at 21:22:02
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Repositories;

use App\Models\BusinessPayout;
use InfyOm\Generator\Common\BaseRepository;

/**
 * Class BusinessPayoutRepository
 * @package App\Repositories
 * @version January 30, 2021, 11:17 am UTC
 *
 * @method BusinessPayout findWithoutFail($id, $columns = ['*'])
 * @method BusinessPayout find($id, $columns = ['*'])
 * @method BusinessPayout first($columns = ['*'])
 */
class BusinessPayoutRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'business_id',
        'method',
        'amount',
        'paid_date',
        'note'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return BusinessPayout::class;
    }
}
