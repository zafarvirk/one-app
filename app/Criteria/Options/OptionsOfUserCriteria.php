<?php
/*
 * File name: OptionsOfUserCriteria.php
 * Last modified: 2022.02.02 at 21:26:20
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Criteria\Options;

use App\Models\User;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class OptionsOfUserCriteria.
 *
 * @package namespace App\Criteria\Options;
 */
class OptionsOfUserCriteria implements CriteriaInterface
{

    /**
     * @var User
     */
    private $userId;

    /**
     * OptionsOfUserCriteria constructor.
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
            return $model->join('article', 'options.article_id', '=', 'article.id')
                ->join('business_users', 'business_users.business_id', '=', 'article.business_id')
                ->groupBy('options.id')
                ->select('options.*')
                ->where('business_users.user_id', $this->userId);
        } else {
            return $model;
        }
    }
}
