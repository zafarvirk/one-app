<?php


namespace App\Criteria\Post;

use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class CommentOfUserCriteria.
 *
 * @package namespace App\Criteria\Post;
 */
class PostReactionOfUserCriteria implements CriteriaInterface
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

        return $model->select('post_reaction.*')->where('user_id' , $this->userId)->groupBy('post_reaction.id');
    }
}
