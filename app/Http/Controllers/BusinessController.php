<?php
/*
 * File name: SalonAPIController.php
 * Last modified: 2022.02.04 at 17:24:24
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Http\Controllers;


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
class BusinessController extends Controller
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

    public function getBusinessUsers($business_id)
    {
        $business = $this->businessRepository->with('users')->where('id', $business_id)->first();
        if (!$business) {
            return $this->sendResponse([], 'No business found!');
        }

        if (!$business->users->count()) {
            return $this->sendResponse([], 'No business user found!');
        }

        return $this->sendResponse(['users' => $business->users], 'Zone tables retrieved successfully');
    }
}
