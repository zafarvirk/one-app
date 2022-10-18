<?php
/*
 * File name: HighlightRepository.php
 * Last modified: 2022.08.29 at 05:52:15
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
*/

namespace App\Repositories;

use App\Models\Highlight;
use InfyOm\Generator\Common\BaseRepository;

/**
 * Class HighlightRepository
 * @package App\Repositories
 * @version January 13, 2021, 8:02 pm UTC
 *
 * @method Highlight findWithoutFail($id, $columns = ['*'])
 * @method Highlight find($id, $columns = ['*'])
 * @method Highlight first($columns = ['*'])
 */
class HighlightRepository extends BaseRepository
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
        return Highlight::class;
    }
}
