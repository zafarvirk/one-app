<?php
/*
 * File name: TransactionStatusRepository.php
 * Last modified: 2021.01.25 at 22:00:21
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */

namespace App\Repositories;

use App\Models\TransactionStatus;
use InfyOm\Generator\Common\BaseRepository;

/**
 * Class TransactionStatusRepository
 * @package App\Repositories
 * @version January 25, 2021, 7:18 pm UTC
 *
 * @method TransactionStatus findWithoutFail($id, $columns = ['*'])
 * @method TransactionStatus find($id, $columns = ['*'])
 * @method TransactionStatus first($columns = ['*'])
 */
class TransactionStatusRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'status',
        'order',
        'type'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return TransactionStatus::class;
    }
}
