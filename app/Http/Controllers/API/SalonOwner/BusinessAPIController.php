<?php
/*
 * File name: SalonAPIController.php
 * Last modified: 2022.02.04 at 17:15:36
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Http\Controllers\API\SalonOwner;


use App\Criteria\Business\BusinessOfUserCriteria;
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
use DB;

/**
 * Class BusinessController
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
        parent::__construct();
    }

    /**
     * Display a listing of the Business.
     * GET|HEAD /Businesss
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $this->businessRepository->pushCriteria(new RequestCriteria($request));
            $this->businessRepository->pushCriteria(new LimitOffsetCriteria($request));
            // $this->businessRepository->pushCriteria(new AcceptedCriteria());
            $this->businessRepository->pushCriteria(new BusinessOfUserCriteria(auth()->id()));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $business = $this->businessRepository->all();
        $this->filterCollection($request, $business);
        
        return $this->sendResponse($business->toArray(), 'Businesses retrieved successfully');
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

        return $this->sendResponse($business->toArray(), 'Business retrieved successfully');
    }

    /**
     * Store a newly created Business in storage.
     *
     * @param CreateBusinessRequest $request
     *
     * @return Application|RedirectResponse|Redirector|Response
     */
    public function store(CreateBusinessRequest $request)
    {
        $input = $request->all();
        $input['users'] = ['0' => auth()->user()->id];
        //$input['modules'] = json_encode($input['modules']);
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->businessRepository->model());
        try {
            $business = $this->businessRepository->create($input);
            $business->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));
            if (isset($input['image']) && $input['image'] && is_array($input['image'])) {
                foreach ($input['image'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                    $mediaItem = $cacheUpload->getMedia('image')->first();
                    $mediaItem->copy($business, 'image');
                }
            }
            event(new BusinessChangedEvent($business, $business));
        } catch (ValidatorException $e) {
            // Flash::error($e->getMessage());
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($business, 'Business created successfully');
    }

    /**
     * Update the specified Salon in storage.
     *
     * @param int $id
     * @param CreateBusinessRequest $request
     *
     * @return Application|RedirectResponse|Redirector|Response
     * @throws RepositoryException
     */
    public function update(int $id, CreateBusinessRequest $request)
    {
        // $this->businessRepository->pushCriteria(new BusinessOfUserCriteria(auth()->id()));
        $oldBusiness = $this->businessRepository->findWithoutFail($id);

        if (empty($oldBusiness)) {
            return $this->sendError('Business not found');
        }
        $input = $request->all();
        $input['users'] = ['0' => auth()->user()->id];
        //$input['modules'] = json_encode($input['modules']);
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->businessRepository->model());
        try {
            $input['users'] = isset($input['users']) ? $input['users'] : [];
            $input['taxes'] = isset($input['taxes']) ? $input['taxes'] : [];
            $business = $this->businessRepository->update($input, $id);
            if (isset($input['image']) && $input['image'] && is_array($input['image'])) {
                if ($business->hasMedia('image')) {
                    $business->getMedia('image')->each->delete();
                }
                foreach ($input['image'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                    $mediaItem = $cacheUpload->getMedia('image')->first();
                    $mediaItem->copy($business, 'image');
                }
            }
            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $business->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }
            event(new BusinessChangedEvent($business, $oldBusiness));
        } catch (ValidatorException $e) {  
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($business, 'Business updated successfully');
    }

    public function modulesUpdate(int $id , Request $request)
    {
        $oldBusiness = $this->businessRepository->findWithoutFail($id);

        if (empty($oldBusiness)) {
            return $this->sendError('Business not found');
        }
        $input = $request->all();
        try {
            DB::table('business_modules')->where('business_id' , $id)->delete();
            foreach($input['module_ids'] as $module_id){
                DB::table('business_modules')->insert(['business_id' => $id , 'module_id' => $module_id]);
            }
        } catch (ValidatorException $e) {  
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse('', 'Business module updated successfully');
    }


}
