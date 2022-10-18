<?php
/*
 * File name: PostReactionRepository.php
 * Last modified: 2022.03.11 at 22:26:16
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Repositories;

use App\Models\PostReaction;
use InfyOm\Generator\Common\BaseRepository;
use Prettus\Repository\Contracts\CacheableInterface;
use Prettus\Repository\Traits\CacheableRepository;

/**
 * Class PostReactionRepository
 * @package App\Repositories
 * @version January 19, 2021, 1:59 pm UTC
 *
 * @method EService findWithoutFail($id, $columns = ['*'])
 * @method EService find($id, $columns = ['*'])
 * @method EService first($columns = ['*'])
 */
class PostReactionRepository extends BaseRepository implements CacheableInterface
{

    use CacheableRepository;

    /**
     * @var array
     */
    protected $fieldSearchable = [
        'reaction',
        'is_deleted',
        'user_id',
        'post_id',
        'post_comment_id'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return PostReaction::class;
    }

}
