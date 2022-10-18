<?php
/*
 * File name: BusinessPayoutAPIController.php
 * Last modified: 2022.02.02 at 21:21:33
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use App\Models\BusinessPayout;
use App\Repositories\BusinessPayoutRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class BusinessPayoutController
 * @package App\Http\Controllers\API
 */
class BusinessPayoutAPIController extends Controller
{
    /** @var  BusinessPayoutRepository */
    private $businessPayoutRepository;

    public function __construct(BusinessPayoutRepository $businessPayoutRepo)
    {
        $this->businessPayoutRepository = $businessPayoutRepo;
    }

    /**
     * Display a listing of the BusinessPayout.
     * GET|HEAD /salonPayouts
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $this->businessPayoutRepository->pushCriteria(new RequestCriteria($request));
            $this->businessPayoutRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $businessPayouts = $this->businessPayoutRepository->all();

        return $this->sendResponse($businessPayouts->toArray(), 'Business Payouts retrieved successfully');
    }

    /**
     * Display the specified BusinessPayout.
     * GET|HEAD /salonPayouts/{id}
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function show($id)
    {
        /** @var BusinessPayout $businessPayout */
        if (!empty($this->businessPayoutRepository)) {
            $businessPayout = $this->businessPayoutRepository->findWithoutFail($id);
        }

        if (empty($businessPayout)) {
            return $this->sendError('Business Payout not found');
        }

        return $this->sendResponse($businessPayout->toArray(), 'Business Payout retrieved successfully');
    }
}
