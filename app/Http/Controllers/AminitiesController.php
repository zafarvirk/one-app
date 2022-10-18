<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Criteria\Business\BusinessOfUserCriteria;
use App\DataTables\AminitiesDataTable;
use App\Http\Requests\CreateAminitiesRequest;
use App\Http\Requests\UpdateAminitiesRequest;
use App\Repositories\AminitiesRepository;
use App\Repositories\BusinessRepository;
use App\Repositories\CustomFieldRepository;
use App\Repositories\UploadRepository;
use Flash;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

class AminitiesController extends Controller
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
 
    public function index(AminitiesDataTable $aminitiesDataTable)
    {
        return $aminitiesDataTable->render('aminities.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $hasCustomField = in_array($this->aminitiesRepository->model(), setting('custom_field_models', []));
        $business = $this->businessRepository->getByCriteria(new BusinessOfUserCriteria(auth()->id()))->pluck('name', 'id');
        $businessSelected = [];
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->aminitiesRepository->model());
            $html = generateCustomField($customFields);
        }
        return view('aminities.create')->with("customFields", isset($html) ? $html : false)->with("businessSelected",$businessSelected)->with("business", $business);
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
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.saved_successfully', ['operator' => __('lang.aminities')]));

        return redirect(route('aminities.index'));

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $aminities = $this->aminitiesRepository->findWithoutFail($id);
        if (empty($aminities)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.aminities')]));

            return redirect(route('aminities.index'));
        }

        $business = $this->businessRepository->getByCriteria(new BusinessOfUserCriteria(auth()->id()))->pluck('name', 'id');
        $businessSelected = $aminities->aminities_business()->pluck('business_id')->toArray();

        $customFieldsValues = $aminities->customFieldsValues()->with('customField')->get();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->aminitiesRepository->model());
        $hasCustomField = in_array($this->aminitiesRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        return view('aminities.edit')->with("aminities", $aminities)->with("customFields", isset($html) ? $html : false)->with("business",$business)->with("businessSelected",$businessSelected);
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
            Flash::error('Aminities not found');
            return redirect(route('aminities.index'));
        }

        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->aminitiesRepository->model());
        try {
            $aminities = $this->aminitiesRepository->update($input, $id);
            if (isset($input['image']) && $input['image'] && is_array($input['image'])) {
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

        Flash::success(__('lang.updated_successfully', ['operator' => __('lang.aminities')]));

        return redirect(route('aminities.index'));

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
            Flash::error('Aminities not found');

            return redirect(route('aminities.index'));
        }

        $this->aminitiesRepository->delete($id);

        Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.aminities')]));

        return redirect(route('aminities.index'));
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
}
