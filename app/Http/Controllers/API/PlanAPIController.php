<?php
/*
 * File name: OptionGroupAPIController.php
 * Last modified: 2021.02.07 at 21:56:54
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */

namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use App\Repositories\PlanRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class PlanController
 * @package App\Http\Controllers\API
 */
class PlanAPIController extends Controller
{
    /** @var  PlanRepository */
    private $planRepository;

    public function __construct(PlanRepository $planRepo)
    {
        $this->planRepository = $planRepo;
    }


    public function index(Request $request)
    {
        try {
            $this->planRepository->pushCriteria(new RequestCriteria($request));
            $this->planRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $plans = $this->planRepository->all();
        $this->filterCollection($request, $plans);

        return $this->sendResponse($plans->toArray(), 'Plans retrieved successfully');
    }

    public function show($id)
    {
        if (!empty($this->planRepository)) {
            $plan = $this->planRepository->findWithoutFail($id);
        }

        if (empty($plan)) {
            return $this->sendError('Plan not found');
        }

        return $this->sendResponse($plan->toArray(), 'Plan retrieved successfully');
    }
}
