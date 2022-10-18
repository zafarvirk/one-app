<?php
/*
 * File name: SalonAPIController.php
 * Last modified: 2022.02.04 at 17:24:24
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Http\Controllers\API;


use App\Criteria\Salons\NearCriteria;
use App\Criteria\Business\AcceptedCriteria;
use App\Http\Controllers\Controller;
use App\Repositories\BusinessRepository;
use App\Repositories\CustomFieldRepository;
use App\Repositories\UploadRepository;
use App\Http\Requests\CreateBusinessRequest;
use App\Events\BusinessChangedEvent;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class SalonController
 * @package App\Http\Controllers\API
 */
class BusinessAPIController extends Controller
{
    /** @var  BusinessRepository */
    private $businessRepository;
    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;
    
    /**
     * @var UploadRepository
     */
    private $uploadRepository;

    public function __construct(BusinessRepository $businessRepo, CustomFieldRepository $customFieldRepo, UploadRepository $uploadRepo)
    {
        $this->businessRepository = $businessRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->uploadRepository = $uploadRepo;
    }

    /**
     * Display a listing of the Salon.
     * GET|HEAD /salons
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $this->businessRepository->pushCriteria(new RequestCriteria($request));
            $this->businessRepository->pushCriteria(new LimitOffsetCriteria($request));
            $this->businessRepository->pushCriteria(new AcceptedCriteria());
            $this->businessRepository->pushCriteria(new NearCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $businesses = $this->businessRepository->all();
        $this->filterCollection($request, $businesses);

        return $this->sendResponse($businesses->toArray(), 'Businesses retrieved successfully');
    }

    /**
     * Display the specified Salon.
     * GET|HEAD /salons/{id}
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
        $business = $this->businessRepository->findWithoutFail($id);
        if (empty($business)) {
            return $this->sendError('Business not found');
        }
        $this->filterModel($request, $business);
        $array = $this->orderAvailabilityHours($business);
        return $this->sendResponse($array, 'Business retrieved successfully');
    }

    private function orderAvailabilityHours($business)
    {
        $array = $business->toArray();
        if (isset($array['availability_hours'])) {
            $availabilityHours = $array['availability_hours'];
            $availabilityHours = collect($availabilityHours);
            $availabilityHours = $availabilityHours->sortBy(function ($item, $key) {
                return Carbon::createFromIsoFormat('dddd', $item['day'])->dayOfWeek;
            });
            $array['availability_hours'] = array_values($availabilityHours->toArray());
        }
        return $array;
    }
}
