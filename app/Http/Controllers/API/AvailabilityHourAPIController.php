<?php
/*
 * File name: AvailabilityHourAPIController.php
 * Last modified: 2022.05.07 at 15:58:23
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use App\Repositories\AvailabilityHourRepository;
use App\Repositories\BookingRepository;
use App\Repositories\BusinessRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class AvailabilityHourController
 * @package App\Http\Controllers\API
 */
class AvailabilityHourAPIController extends Controller
{
    /** @var  AvailabilityHourRepository */
    private $availabilityHourRepository;

    /** @var  BusinessRepository */
    private $businessRepository;

    /** @var BookingRepository */
    private $bookingRepository;

    public function __construct(AvailabilityHourRepository $availabilityHourRepo, BusinessRepository $businessRepo, BookingRepository $bookingRepository)
    {
        $this->availabilityHourRepository = $availabilityHourRepo;
        $this->businessRepository = $businessRepo;
        $this->bookingRepository = $bookingRepository;
    }


    /**
     * Display a listing of the AvailabilityHour.
     * GET|HEAD /availabilityHours
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $this->availabilityHourRepository->pushCriteria(new RequestCriteria($request));
            $this->availabilityHourRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $availabilityHours = $this->availabilityHourRepository->all();

        return $this->sendResponse($availabilityHours->toArray(), 'Availability Hours retrieved successfully');
    }

    /**
     * Display the specified AvailabilityHour.
     * GET|HEAD /availabilityHours/{id}
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function show(int $id, Request $request): JsonResponse
    {
        try {
            $this->businessRepository->pushCriteria(new RequestCriteria($request));
            $this->businessRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $employeeId = $request->get('employee_id', 0);
        $business = $this->businessRepository->findWithoutFail($id);
        if (empty($business)) {
            return $this->sendError('Business not found');
        }
        $calendar = [];
        $date = $request->input('date');
        if (!empty($date)) {
            $date = Carbon::createFromFormat('Y-m-d', $date);
            $calendar = $business->weekCalendarRange($date, $employeeId);
        }

        return $this->sendResponse($calendar, 'Availability Hours retrieved successfully');

    }

    public function store(Request $request): JsonResponse
    {
        $input = $request->all();
        try {
            $business = $this->businessRepository->findWithoutFail($input['data'][0]['business_id']);
            if (empty($business)) {
                return $this->sendError('Business not found');
            }
            $this->availabilityHourRepository->where('business_id' , $input['data'][0]['business_id'])->delete();
            foreach($input['data'] as $d) {
                $this->availabilityHourRepository->create($d);
            }

        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse('', 'Availability Hours added successfully');

    }


}
