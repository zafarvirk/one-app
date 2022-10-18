<?php
/*
 * File name: ModuleRepository.php
 * Last modified: 2022.08.29 at 05:52:15
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
*/

namespace App\Repositories;

use App\Models\Module;
use InfyOm\Generator\Common\BaseRepository;

/**
 * Class ModuleRepository
 * @package App\Repositories
 * @version January 13, 2021, 8:02 pm UTC
 *
 * @method Module findWithoutFail($id, $columns = ['*'])
 * @method Module find($id, $columns = ['*'])
 * @method Module first($columns = ['*'])
 */
class ModuleRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name',
        'status',
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Module::class;
    }
}
