<?php
/*
 * File name: OptionGroupAPIController.php
 * Last modified: 2021.02.07 at 21:56:54
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */

namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use App\Criteria\Subscription\SubscriptionOfUserCriteria;
use App\Repositories\SubscriptionRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class SubscriptionController
 * @package App\Http\Controllers\API
 */
class SubscriptionAPIController extends Controller
{
    /** @var  SubscriptionRepository */
    private $subscriptionRepository;

    public function __construct(SubscriptionRepository $subscriptionRepo)
    {
        $this->subscriptionRepository = $subscriptionRepo;
    }


    public function index(Request $request)
    {
        try {
            $this->subscriptionRepository->pushCriteria(new RequestCriteria($request));
            $this->subscriptionRepository->pushCriteria(new SubscriptionOfUserCriteria(auth()->id()));
            $this->subscriptionRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $subscriptions = $this->subscriptionRepository->all();
        $this->filterCollection($request, $subscriptions);

        return $this->sendResponse($subscriptions->toArray(), 'subscriptions retrieved successfully');
    }

    public function show($id)
    {
        if (!empty($this->subscriptionRepository)) {
            $subscription = $this->subscriptionRepository->findWithoutFail($id);
        }

        if (empty($subscription)) {
            return $this->sendError('subscription not found');
        }

        return $this->sendResponse($subscription->toArray(), 'subscription retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $input = $request->all();
            $input['user_id'] = auth()->id();
            $subscription = $this->subscriptionRepository->create($input);
            
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse($subscription->toArray(), __('lang.saved_successfully', ['operator' => 'subscription']));
    }
}
