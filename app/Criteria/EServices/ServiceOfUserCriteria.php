<?php
/*
 * File name: ArticleOfUserCriteria.php
 * Last modified: 2022.02.02 at 21:26:20
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Criteria\EServices;

use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class ServiceOfUserCriteria.
 *
 * @package namespace App\Criteria\EServices;
 */
class ServiceOfUserCriteria implements CriteriaInterface
{
    /**
     * @var int
     */
    private $userId;

    /**
     * ServiceOfUserCriteria constructor.
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
        if (auth()->check() && auth()->user()->hasRole('salon owner')) {
            return $model->join('business_users', 'business_users.business_id', '=', 'article.business_id')
                ->where('business_users.user_id', $this->userId)
                ->select('article.*')->groupBy('article.id');
        }else if (auth()->check() && auth()->user()->hasRole('class_manager')) {
            return $model->join('business_users', 'business_users.business_id', '=', 'article.business_id')
                ->where('business_users.user_id', $this->userId)
                ->where('article.type', 'class')
                ->select('article.*')->groupBy('article.id');
        } else {
            return $model->select('article.*')->groupBy('article.id');
        }
    }
}
