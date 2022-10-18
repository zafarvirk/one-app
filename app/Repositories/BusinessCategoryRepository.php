<?php
/*
 * File name: BusinessCategoryRepository.php
 * Last modified: 2022.02.03 at 14:23:26
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Repositories;

use App\Models\BusinessCategory;
use InfyOm\Generator\Common\BaseRepository;

/**
 * Class BusinessCategoryRepository
 * @package App\Repositories
 * @version January 13, 2021, 10:56 am UTC
 *
 * @method SalonLevel findWithoutFail($id, $columns = ['*'])
 * @method SalonLevel find($id, $columns = ['*'])
 * @method SalonLevel first($columns = ['*'])
 */
class BusinessCategoryRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name',
        'commission',
        'disabled',
        'default',
        'color',
        'description',
        'featured',
        'order',
        'parent_id'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return BusinessCategory::class;
    }
}
