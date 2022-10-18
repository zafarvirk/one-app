<?php
/*
 * File name: RequestOfferRepository.php
 * Last modified: 2022.02.02 at 21:22:02
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Repositories;

use App\Models\RequestOffer;
use InfyOm\Generator\Common\BaseRepository;

/**
 * Class RequestOfferRepository
 * @package App\Repositories
 * @version January 16, 2021, 4:08 pm UTC
 *
 * @method RequestOffer findWithoutFail($id, $columns = ['*'])
 * @method RequestOffer find($id, $columns = ['*'])
 * @method RequestOffer first($columns = ['*'])
 */
class RequestOfferRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'article_request_id',
        'quote_amount',
        'status',
        'user_id',
        'business_id'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return RequestOffer::class;
    }
}
