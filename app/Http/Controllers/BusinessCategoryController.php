<?php
/*
 * File name: BusinessCategoryController.php
 * Last modified: 2022.02.03 at 10:46:03
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Http\Controllers;

use App\DataTables\BusinessCategoryDataTable;
use App\Http\Requests\CreateBusinessCategoryRequest;
use App\Http\Requests\UpdateBusinessCategoryRequest;
use App\Repositories\CustomFieldRepository;
use App\Repositories\BusinessCategoryRepository;
use App\Repositories\UploadRepository;
use Exception;
use Flash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Prettus\Validator\Exceptions\ValidatorException;

class BusinessCategoryController extends Controller
{
    /** @var  BusinessCategoryRepository */
    private $businessCategoryRepository;

    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;

    /**
     * @var UploadRepository
     */
    private $uploadRepository;

    public function __construct(BusinessCategoryRepository $businessCategoryRepo, CustomFieldRepository $customFieldRepo, UploadRepository $uploadRepo)
    {
        parent::__construct();
        $this->businessCategoryRepository = $businessCategoryRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->uploadRepository = $uploadRepo;

    }

    /**
     * Display a listing of the BusinessCategory.
     *
     * @param BusinessCategoryDataTable $businessCategoryDataTable
     * @return Response
     */
    public function index(BusinessCategoryDataTable $businessCategoryDataTable)
    {
        return $businessCategoryDataTable->render('business_categories.index');
    }

    /**
     * Show the form for creating a new BusinessCategory.
     *
     * @return Response
     */
    public function create()
    {

        $parentCategory = $this->businessCategoryRepository->pluck('name', 'id');
        $hasCustomField = in_array($this->businessCategoryRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->businessCategoryRepository->model());
            $html = generateCustomField($customFields);
        }
        return view('business_categories.create')->with("customFields", isset($html) ? $html : false)->with("parentCategory", $parentCategory);
    }

    /**
     * Store a newly created BusinessCategory in storage.
     *
     * @param CreateBusinessCategoryRequest $request
     *
     * @return Response
     */
    public function store(CreateBusinessCategoryRequest $request)
    {
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->businessCategoryRepository->model());
        try {
            $businessCategory = $this->businessCategoryRepository->create($input);
            $businessCategory->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));
            if (isset($input['image']) && $input['image']) {
                $cacheUpload = $this->uploadRepository->getByUuid($input['image']);
                $mediaItem = $cacheUpload->getMedia('image')->first();
                $mediaItem = $mediaItem->forgetCustomProperty('generated_conversions');
                $mediaItem->copy($businessCategory, 'image');
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.saved_successfully', ['operator' => __('lang.business_category')]));

        return redirect(route('businessCategories.index'));
    }

    /**
     * Display the specified BusinessCategory.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $businessCategory = $this->businessCategoryRepository->findWithoutFail($id);

        if (empty($businessCategory)) {
            Flash::error('Business category not found');

            return redirect(route('businessCategories.index'));
        }

        return view('business_categories.show')->with('businessCategory', $businessCategory);
    }

    /**
     * Show the form for editing the specified BusinessCategory.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $businessCategory = $this->businessCategoryRepository->findWithoutFail($id);
        $parentCategory = $this->businessCategoryRepository->pluck('name', 'id')->prepend(__('lang.category_parent_id_placeholder'), '');

        if (empty($businessCategory)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.business_category')]));

            return redirect(route('businessCategories.index'));
        }
        $customFieldsValues = $businessCategory->customFieldsValues()->with('customField')->get();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->businessCategoryRepository->model());
        $hasCustomField = in_array($this->businessCategoryRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        return view('business_categories.edit')->with('businessCategory', $businessCategory)->with("customFields", isset($html) ? $html : false)->with("parentCategory", $parentCategory);
    }

    /**
     * Update the specified BusinessCategory in storage.
     *
     * @param int $id
     * @param UpdateBusinessCategoryRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateBusinessCategoryRequest $request)
    {
        $businessCategory = $this->businessCategoryRepository->findWithoutFail($id);

        if (empty($businessCategory)) {
            Flash::error('Business category not found');
            return redirect(route('businessCategories.index'));
        }
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->businessCategoryRepository->model());
        try {
            $businessCategory = $this->businessCategoryRepository->update($input, $id);

            if (isset($input['image']) && $input['image']) {
                $cacheUpload = $this->uploadRepository->getByUuid($input['image']);
                $mediaItem = $cacheUpload->getMedia('image')->first();
                $mediaItem = $mediaItem->forgetCustomProperty('generated_conversions');
                $mediaItem->copy($businessCategory, 'image');
            }
            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $businessCategory->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.updated_successfully', ['operator' => __('lang.business_category')]));

        return redirect(route('businessCategories.index'));
    }

    /**
     * Remove the specified BusinessCategory from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $businessCategory = $this->businessCategoryRepository->findWithoutFail($id);

        if (empty($businessCategory)) {
            Flash::error('Business category not found');

            return redirect(route('businessCategories.index'));
        }

        $this->businessCategoryRepository->delete($id);

        Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.business_category')]));

        return redirect(route('businessCategories.index'));
    }

    /**
     * Remove Media of BusinessCategory
     * @param Request $request
     */ 
    public function removeMedia(Request $request)
    {
        $input = $request->all();
        $businessCategory = $this->businessCategoryRepository->findWithoutFail($input['id']);
        try {
            if ($businessCategory->hasMedia($input['collection'])) {
                $businessCategory->getFirstMedia($input['collection'])->delete();
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
