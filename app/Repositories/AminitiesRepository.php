<?php
/*
 * File name: AminitiesRepository.php
 * Last modified: 2022.08.29 at 05:52:15
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
*/

namespace App\Repositories;

use App\Models\Aminities;
use InfyOm\Generator\Common\BaseRepository;

/**
 * Class AminitiesRepository
 * @package App\Repositories
 * @version January 13, 2021, 8:02 pm UTC
 *
 * @method Aminities findWithoutFail($id, $columns = ['*'])
 * @method Aminities find($id, $columns = ['*'])
 * @method Aminities first($columns = ['*'])
 */
class AminitiesRepository extends BaseRepository
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
        return Aminities::class;
    }
}
