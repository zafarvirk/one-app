<?php
/*
 * File name: SubscriptionRepository.php
 * Last modified: 2022.08.29 at 05:52:15
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
*/

namespace App\Repositories;

use App\Models\Subscription;
use InfyOm\Generator\Common\BaseRepository;

/**
 * Class SubscriptionRepository
 * @package App\Repositories
 * @version January 13, 2021, 8:02 pm UTC
 *
 * @method Plan findWithoutFail($id, $columns = ['*'])
 * @method Plan find($id, $columns = ['*'])
 * @method Plan first($columns = ['*'])
 */
class SubscriptionRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'user_id',
        'plan_id',
        'expiry_date',
        'available_sessions',
        'payment_id',
        'is_active',
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Subscription::class;
    }
}
