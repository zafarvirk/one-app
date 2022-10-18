<?php
/*
 * File name: FeatureRepository.php
 * Last modified: 2022.08.29 at 05:52:15
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
*/

namespace App\Repositories;

use App\Models\Features;
use InfyOm\Generator\Common\BaseRepository;

/**
 * Class FeatureRepository
 * @package App\Repositories
 * @version January 13, 2021, 8:02 pm UTC
 *
 * @method Features findWithoutFail($id, $columns = ['*'])
 * @method Features find($id, $columns = ['*'])
 * @method Features first($columns = ['*'])
 */
class FeatureRepository extends BaseRepository
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
        return Features::class;
    }
}
