<?php
/*
 * File name: SlideRepository.php
 * Last modified: 2022.02.02 at 21:22:03
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Repositories;

use App\Models\Slide;
use InfyOm\Generator\Common\BaseRepository;

/**
 * Class SlideRepository
 * @package App\Repositories
 * @version January 25, 2021, 10:54 am UTC
 *
 * @method Slide findWithoutFail($id, $columns = ['*'])
 * @method Slide find($id, $columns = ['*'])
 * @method Slide first($columns = ['*'])
 */
class SlideRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'order',
        'text',
        'button',
        'text_position',
        'text_color',
        'button_color',
        'background_color',
        'indicator_color',
        'image_fit',
        'article_id',
        'business_id',
        'enabled'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Slide::class;
    }
}
