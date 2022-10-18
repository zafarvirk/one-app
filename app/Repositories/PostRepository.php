<?php
/*
 * File name: PostRepository.php
 * Last modified: 2022.03.11 at 22:26:16
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Repositories;

use App\Models\Post;
use InfyOm\Generator\Common\BaseRepository;
use Prettus\Repository\Contracts\CacheableInterface;
use Prettus\Repository\Traits\CacheableRepository;

/**
 * Class PostRepository
 * @package App\Repositories
 * @version January 19, 2021, 1:59 pm UTC
 *
 * @method EService findWithoutFail($id, $columns = ['*'])
 * @method EService find($id, $columns = ['*'])
 * @method EService first($columns = ['*'])
 */
class PostRepository extends BaseRepository implements CacheableInterface
{

    use CacheableRepository;

    /**
     * @var array
     */
    protected $fieldSearchable = [
        'title',
        'text',
        'post_type',
        'is_deleted',
        'type',
        'user_id',
        'article_id',
        'business_id',
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Post::class;
    }

}
