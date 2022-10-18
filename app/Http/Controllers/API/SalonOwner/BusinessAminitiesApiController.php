<?php

namespace App\Http\Controllers\API\SalonOwner;

use Illuminate\Http\Request;
use App\Models\Business;
use App\Models\Aminities;
use App\Http\Controllers\Controller;
use App\Repositories\AminitiesRepository;
use App\Repositories\BusinessRepository;
use App\Repositories\CustomFieldRepository;
use App\Repositories\UploadRepository;
use Illuminate\Http\JsonResponse;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;
use DB;

class BusinessAminitiesApiController extends Controller
{
    /** @var  AminitiesRepository */
        private $aminitiesRepository;

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

    public function __construct(AminitiesRepository $aminitiesRepo, CustomFieldRepository $customFieldRepo, BusinessRepository $businessRepo, UploadRepository $uploadRepo)
    {
        parent::__construct();
        $this->aminitiesRepository = $aminitiesRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->businessRepository = $businessRepo;
        $this->uploadRepository = $uploadRepo;
    }

    /**
     * Display a listing of the Aminities.
     *
     * @param AminitiesDataTable $aminitiesDataTable
     * @return mixed
    */
 
    public function index(Request $request): JsonResponse
    {
        try {
            $this->aminitiesRepository->pushCriteria(new RequestCriteria($request));
            $this->aminitiesRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $aminities = $this->aminitiesRepository->all();
        $this->filterCollection($request, $aminities);

        return $this->sendResponse($aminities->toArray(), 'Aminitieses retrieved successfully');

        // $businessId = Business::join('business_users', 'business_users.business_id', '=', 'businesses.id')
        //                 ->where('business_users.user_id', auth()->user()->id)->get()->toArray();
        // $aminities = Aminities::with('aminities_business')->get();
        // $data = [];
        // foreach($aminities as $key => $a){
        //     $data[] = Aminities::find($a->id);
        //     $is_added =  true;
        //     foreach($businessId as $b){
        //         if($a->aminities_business[0]->id == $b['id']){
        //             $is_added = false;
        //         }
        //     }
        //     $data[$key]->is_added = $is_added;
                
        // }
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
            $this->aminitiesRepository->pushCriteria(new RequestCriteria($request));
            $this->aminitiesRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $aminities = $this->aminitiesRepository->findWithoutFail($id);
        if (empty($aminities)) {
            return $this->sendError('aminities not found');
        }
        $this->filterModel($request, $aminities);

        return $this->sendResponse($aminities->toArray(), 'aminities retrieved successfully');
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
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->aminitiesRepository->model());
        try {
            $aminities = $this->aminitiesRepository->create($input);
            $aminities->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));
            if (isset($input['image']) && $input['image'] && is_array($input['image'])) {
                foreach ($input['image'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                    $mediaItem = $cacheUpload->getMedia('image')->first();
                    $mediaItem->copy($aminities, 'image');
                }
            }
        } catch (ValidatorException $e) {
            // Flash::error($e->getMessage());
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($aminities, 'Aminities created successfully');

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
        $aminities = $this->aminitiesRepository->findWithoutFail($id);

        if (empty($aminities)) {
            return $this->sendError('Aminities not found');
        }

        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->aminitiesRepository->model());
        try {
            $aminities = $this->aminitiesRepository->update($input, $id);
            if (isset($input['image']) && $input['image'] && is_array($input['image'])) {
                if ($aminities->hasMedia('image')) {
                    $aminities->getMedia('image')->each->delete();
                }
                foreach ($input['image'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                    $mediaItem = $cacheUpload->getMedia('image')->first();
                    $mediaItem->copy($aminities, 'image');
                }
            }
            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $aminities->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        return $this->sendResponse($aminities, 'Aminities created successfully');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        $aminities = $this->aminitiesRepository->findWithoutFail($id);

        if (empty($aminities)) {
            return $this->sendError('Aminities not found');
        }

        $this->aminitiesRepository->delete($id);

        return $this->sendResponse('', 'Aminities deleted successfully');
    }

    public function removeMedia(Request $request)
    {
        $input = $request->all();
        $aminities = $this->aminitiesRepository->findWithoutFail($input['id']);
        try {
            if ($aminities->hasMedia($input['collection'])) {
                $aminities->getFirstMedia($input['collection'])->delete();
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    public function getBusinessAminities(Request $request)
    {
        $request->validate([
            'business_id' => ['required'],
        ]);
        $input = $request->all();
        
        $aminities = Aminities::with('media')->get();
        foreach($aminities as $key => $a){
            $check = DB::table('aminities_business')->where('business_id' , $input['business_id'])->where('aminities_id' , $a->id)->first();
            if($check){
                $aminities[$key]->is_added = false;
            }
            else{
                $aminities[$key]->is_added = true;
            } 
        }
        return $this->sendResponse($aminities, 'Aminitieses retrived successfully');
    }

    public function assignAminitiesToBusiness(Request $request)
    {
        $request->validate([
            'business_id' => ['required'],
        ]);
        $input = $request->all();
        DB::table('aminities_business')->where('business_id' , $input['business_id'])->delete();
        foreach($input['aminities_id'] as $a) {
            // $check = DB::table('aminities_business')->where('business_id' , $input['business_id'])->where('aminities_id' , $a)->first();
            // if(!$check){
                DB::table('aminities_business')->insert(['aminities_id' => $a , 'business_id' => $input['business_id']]);
            // }
        }
        return $this->sendResponse('', 'Aminitieses assign successfully');
    }


}
