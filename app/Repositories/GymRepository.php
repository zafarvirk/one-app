<?php
/*
 * File name: GymRepository.php
 * Last modified: 2022.02.12 at 02:17:42
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Repositories;

use App\Models\Business;
use InfyOm\Generator\Common\BaseRepository;

/**
 * Class GymRepository
 * @package App\Repositories
 * @version January 13, 2021, 11:11 am UTC
 *
 * @method Gym findWithoutFail($id, $columns = ['*'])
 * @method Gym find($id, $columns = ['*'])
 * @method Gym first($columns = ['*'])
 */
class GymRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name',
        'business_category_id',
        'address_id',
        'description',
        'phone_number',
        'mobile_number',
        'availability_range',
        'available',
        'closed',
        'featured'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Business::class;
    }
}
