<?php

namespace App\Http\Controllers\API\SalonOwner;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\FeatureRepository;
use App\Repositories\BusinessRepository;
use App\Repositories\CustomFieldRepository;
use App\Repositories\UploadRepository;
use Illuminate\Http\JsonResponse;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

class BusinessFeatureApiController extends Controller
{
    /** @var  FeatureRepository */
        private $featureRepository;

    /**
      * @var CustomFieldRepository
    */
    private $customFieldRepository;

    /**
      * @var BusinessRepository
    */
    private $businessRepository;

    /**
     * @var UploadRepository
     */
    private $uploadRepository;

    public function __construct(FeatureRepository $featureRepo, CustomFieldRepository $customFieldRepo, BusinessRepository $businessRepo, UploadRepository $uploadRepo)
    {
        parent::__construct();
        $this->featureRepository = $featureRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->businessRepository = $businessRepo;
        $this->uploadRepository = $uploadRepo;
    }

    /**
     * Display a listing of the feature.
     *
     * @param featureDataTable $featureDataTable
     * @return mixed
    */
 
    public function index(Request $request): JsonResponse
    {
        try {
            $this->featureRepository->pushCriteria(new RequestCriteria($request));
            $this->featureRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $feature = $this->featureRepository->all();
        $this->filterCollection($request, $feature);
        
        return $this->sendResponse($feature->toArray(), 'featurees retrieved successfully');
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
            $this->featureRepository->pushCriteria(new RequestCriteria($request));
            $this->featureRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $feature = $this->featureRepository->findWithoutFail($id);
        if (empty($feature)) {
            return $this->sendError('feature not found');
        }
        $this->filterModel($request, $feature);

        return $this->sendResponse($feature->toArray(), 'feature retrieved successfully');
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->featureRepository->model());
        try {
            $feature = $this->featureRepository->create($input);
            $feature->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));
            if (isset($input['image']) && $input['image'] && is_array($input['image'])) {
                foreach ($input['image'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                    $mediaItem = $cacheUpload->getMedia('image')->first();
                    $mediaItem->copy($feature, 'image');
                }
            }
        } catch (ValidatorException $e) {
            // Flash::error($e->getMessage());
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($feature, 'feature created successfully');

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $feature = $this->featureRepository->findWithoutFail($id);

        if (empty($feature)) {
            return $this->sendError('feature not found');
        }

        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->featureRepository->model());
        try {
            $feature = $this->featureRepository->update($input, $id);
            if (isset($input['image']) && $input['image'] && is_array($input['image'])) {
                if ($feature->hasMedia('image')) {
                    $feature->getMedia('image')->each->delete();
                }
                foreach ($input['image'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                    $mediaItem = $cacheUpload->getMedia('image')->first();
                    $mediaItem->copy($feature, 'image');
                }
            }
            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $feature->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        return $this->sendResponse($feature, 'feature created successfully');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        $feature = $this->featureRepository->findWithoutFail($id);

        if (empty($feature)) {
            return $this->sendError('feature not found');
        }

        $this->featureRepository->delete($id);

        return $this->sendResponse('', 'feature deleted successfully');
    }

    public function removeMedia(Request $request)
    {
        $input = $request->all();
        $feature = $this->featureRepository->findWithoutFail($input['id']);
        try {
            if ($feature->hasMedia($input['collection'])) {
                $feature->getFirstMedia($input['collection'])->delete();
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
