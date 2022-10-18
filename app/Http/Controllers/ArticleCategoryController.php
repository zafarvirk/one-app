<?php
/*
 * File name: ArticleCategoryController.php
 * Last modified: 2022.03.09 at 21:10:28
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Http\Controllers;

use App\DataTables\ArticleCategoriesDataTable;
use App\Http\Requests\CreateCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Repositories\ArticleCategoryRepository;
use App\Repositories\CustomFieldRepository;
use App\Repositories\UploadRepository;
use Exception;
use Flash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Prettus\Validator\Exceptions\ValidatorException;

class ArticleCategoryController extends Controller
{
    /** @var  ArticleCategoryRepository */
    private $articleCategoryRepository;

    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;

    /**
     * @var UploadRepository
     */
    private $uploadRepository;

    public function __construct(ArticleCategoryRepository $categoryRepo, CustomFieldRepository $customFieldRepo, UploadRepository $uploadRepo)
    {
        parent::__construct();
        $this->articleCategoryRepository = $categoryRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->uploadRepository = $uploadRepo;
    }

    /**
     * Display a listing of the Category.
     *
     * @param ArticleCategoriesDataTable $articleCategoriesDataTable
     * @return Response
     */
    public function index(ArticleCategoriesDataTable $articleCategoriesDataTable)
    {
        return $articleCategoriesDataTable->render('article_categories.index');
    }

    /**
     * Show the form for creating a new Category.
     *
     * @return Response
     */
    public function create()
    {
        $parentCategory = $this->articleCategoryRepository->pluck('name', 'id');

        $hasCustomField = in_array($this->articleCategoryRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->articleCategoryRepository->model());
            $html = generateCustomField($customFields);
        }
        return view('article_categories.create')->with("customFields", isset($html) ? $html : false)->with("parentCategory", $parentCategory);
    }

    /**
     * Store a newly created Category in storage.
     *
     * @param CreateCategoryRequest $request
     *
     * @return Response
     */
    public function store(CreateCategoryRequest $request)
    {
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->articleCategoryRepository->model());
        try {
            $category = $this->articleCategoryRepository->create($input);
            $category->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));
            if (isset($input['image']) && $input['image']) {
                $cacheUpload = $this->uploadRepository->getByUuid($input['image']);
                $mediaItem = $cacheUpload->getMedia('image')->first();
                $mediaItem = $mediaItem->forgetCustomProperty('generated_conversions');
                $mediaItem->copy($category, 'image');
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.saved_successfully', ['operator' => __('lang.category')]));

        return redirect(route('article_categories.index'));
    }

    /**
     * Display the specified Category.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $category = $this->articleCategoryRepository->findWithoutFail($id);

        if (empty($category)) {
            Flash::error('Category not found');

            return redirect(route('article_categories.index'));
        }

        return view('article_categories.show')->with('category', $category);
    }

    /**
     * Show the form for editing the specified Category.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $category = $this->articleCategoryRepository->findWithoutFail($id);
        $parentCategory = $this->articleCategoryRepository->pluck('name', 'id')->prepend(__('lang.category_parent_id_placeholder'), '');

        if (empty($category)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.category')]));

            return redirect(route('article_categories.index'));
        }
        $customFieldsValues = $category->customFieldsValues()->with('customField')->get();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->articleCategoryRepository->model());
        $hasCustomField = in_array($this->articleCategoryRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        return view('article_categories.edit')->with('category', $category)->with("customFields", isset($html) ? $html : false)->with("parentCategory", $parentCategory);
    }

    /**
     * Update the specified Category in storage.
     *
     * @param int $id
     * @param UpdateCategoryRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateCategoryRequest $request)
    {
        $category = $this->articleCategoryRepository->findWithoutFail($id);

        if (empty($category)) {
            Flash::error('Category not found');
            return redirect(route('article_categories.index'));
        }
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->articleCategoryRepository->model());
        try {
            $category = $this->articleCategoryRepository->update($input, $id);

            if (isset($input['image']) && $input['image']) {
                $cacheUpload = $this->uploadRepository->getByUuid($input['image']);
                $mediaItem = $cacheUpload->getMedia('image')->first();
                $mediaItem = $mediaItem->forgetCustomProperty('generated_conversions');
                $mediaItem->copy($category, 'image');
            }
            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $category->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.updated_successfully', ['operator' => __('lang.category')]));

        return redirect(route('article_categories.index'));
    }

    /**
     * Remove the specified Category from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $category = $this->articleCategoryRepository->findWithoutFail($id);

        if (empty($category)) {
            Flash::error('Category not found');

            return redirect(route('article_categories.index'));
        }

        $this->articleCategoryRepository->delete($id);

        Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.category')]));

        return redirect(route('article_categories.index'));
    }

    /**
     * Remove Media of Category
     * @param Request $request
     */
    public function removeMedia(Request $request)
    {
        $input = $request->all();
        $category = $this->articleCategoryRepository->findWithoutFail($input['id']);
        try {
            if ($category->hasMedia($input['collection'])) {
                $category->getFirstMedia($input['collection'])->delete();
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
