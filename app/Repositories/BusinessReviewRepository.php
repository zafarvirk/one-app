<?php
/*
 * File name: BusinessReviewRepository.php
 * Last modified: 2022.02.12 at 02:17:42
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Repositories;

use App\Models\BusinessReview;
use InfyOm\Generator\Common\BaseRepository;

/**
 * Class BusinessReviewRepository
 * @package App\Repositories
 * @version January 23, 2021, 7:42 pm UTC
 *
 * @method BusinessReview findWithoutFail($id, $columns = ['*'])
 * @method BusinessReview find($id, $columns = ['*'])
 * @method BusinessReview first($columns = ['*'])
 */
class BusinessReviewRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'review',
        'rate',
        'booking_id'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return BusinessReview::class;
    }
}
