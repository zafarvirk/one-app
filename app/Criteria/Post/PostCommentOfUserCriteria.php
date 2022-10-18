<?php
/*
 * File name: CommentOfUserCriteria.php
 * Last modified: 2022.02.02 at 21:26:20
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Criteria\Post;

use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class CommentOfUserCriteria.
 *
 * @package namespace App\Criteria\Post;
 */
class PostCommentOfUserCriteria implements CriteriaInterface
{
    /**
     * @var int
     */
    private $userId;

    /**
     * CommentOfUserCriteria constructor.
     */
    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Apply criteria in query repository
     *
     * @param string $model
     * @param RepositoryInterface $repository
     *
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {

        return $model->select('post_comment.*')->where('user_id' , $this->userId)->groupBy('post_comment.id');
    }
}
