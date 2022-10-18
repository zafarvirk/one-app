<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Criteria\Business\BusinessOfUserCriteria;
use App\DataTables\FeaturesDataTable;
use App\Http\Requests\CreateFeaturesRequest;
use App\Http\Requests\UpdateFeaturesRequest;
use App\Repositories\FeatureRepository;
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

class FeatureController extends Controller
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
     * Display a listing of the Aminities.
     *
     * @param FeaturesDataTable $featuresDataTable
     * @return mixed
    */
    public function index(FeaturesDataTable $featuresDataTable)
    {
        return $featuresDataTable->render('features.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $hasCustomField = in_array($this->featureRepository->model(), setting('custom_field_models', []));
        $business = $this->businessRepository->getByCriteria(new BusinessOfUserCriteria(auth()->id()))->pluck('name', 'id');
        $businessSelected = [];
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->featureRepository->model());
            $html = generateCustomField($customFields);
        }
        return view('features.create')->with("customFields", isset($html) ? $html : false)->with("businessSelected",$businessSelected)->with("business", $business);
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
            $features = $this->featureRepository->create($input);
            $features->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));
            if (isset($input['image']) && $input['image'] && is_array($input['image'])) {
                foreach ($input['image'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                    $mediaItem = $cacheUpload->getMedia('image')->first();
                    $mediaItem->copy($features, 'image');
                }
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.saved_successfully', ['operator' => __('lang.features')]));

        return redirect(route('features.index'));
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
        $features = $this->featureRepository->findWithoutFail($id);
        if (empty($features)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.features')]));

            return redirect(route('features.index'));
        }

        $business = $this->businessRepository->getByCriteria(new BusinessOfUserCriteria(auth()->id()))->pluck('name', 'id');
        $businessSelected = $features->features_businesses()->pluck('business_id')->toArray();

        $customFieldsValues = $features->customFieldsValues()->with('customField')->get();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->featureRepository->model());
        $hasCustomField = in_array($this->featureRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        return view('features.edit')->with("features", $features)->with("customFields", isset($html) ? $html : false)->with("business",$business)->with("businessSelected",$businessSelected);
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
        $features = $this->featureRepository->findWithoutFail($id);

        if (empty($features)) {
            Flash::error('features not found');
            return redirect(route('features.index'));
        }

        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->featureRepository->model());
        try {
            $features = $this->featureRepository->update($input, $id);
            if (isset($input['image']) && $input['image'] && is_array($input['image'])) {
                foreach ($input['image'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                    $mediaItem = $cacheUpload->getMedia('image')->first();
                    $mediaItem->copy($features, 'image');
                }
            }
            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $features->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.updated_successfully', ['operator' => __('lang.features')]));

        return redirect(route('features.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        $features = $this->featureRepository->findWithoutFail($id);

        if (empty($features)) {
            Flash::error('Features not found');

            return redirect(route('features.index'));
        }

        $this->featureRepository->delete($id);

        Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.features')]));

        return redirect(route('features.index'));
    }

    public function removeMedia(Request $request)
    {
        $input = $request->all();
        $features = $this->featureRepository->findWithoutFail($input['id']);
        try {
            if ($features->hasMedia($input['collection'])) {
                $features->getFirstMedia($input['collection'])->delete();
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
