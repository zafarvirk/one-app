<?php
/*
 * File name: PlanRepository.php
 * Last modified: 2022.08.29 at 05:52:15
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
*/

namespace App\Repositories;

use App\Models\Plan;
use InfyOm\Generator\Common\BaseRepository;

/**
 * Class PlanRepository
 * @package App\Repositories
 * @version January 13, 2021, 8:02 pm UTC
 *
 * @method Plan findWithoutFail($id, $columns = ['*'])
 * @method Plan find($id, $columns = ['*'])
 * @method Plan first($columns = ['*'])
 */
class PlanRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name',
        'description',
        'status',
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Plan::class;
    }
}
