<?php
/*
 * File name: ArticleController.php
 * Last modified: 2022.02.03 at 18:14:47
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Http\Controllers;

use App\Criteria\EServices\ServiceOfUserCriteria;
use App\Criteria\Business\BusinessOfUserCriteria;
use App\DataTables\ServiceDataTable;
use App\Http\Requests\CreateServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Repositories\ArticleCategoryRepository;
use App\Repositories\CustomFieldRepository;
use App\Repositories\ServiceRepository;
use App\Repositories\BusinessRepository;
use App\Repositories\UploadRepository;
use Exception;
use Flash;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

class ArticleController extends Controller
{
    /** @var  ServiceRepository */
    private $serviceRepository;

    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;

    /**
     * @var UploadRepository
     */
    private $uploadRepository;
    /**
     * @var ArticleCategoryRepository
     */
    private $articleCategoryRepository;
    /**
     * @var BusinessRepository
     */
    private $businessRepository;

    public function __construct(ServiceRepository $serviceRepo, CustomFieldRepository $customFieldRepo, UploadRepository $uploadRepo
        , ArticleCategoryRepository                       $categoryRepo
        , BusinessRepository                          $businessRepo)
    {
        parent::__construct();
        $this->serviceRepository = $serviceRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->uploadRepository = $uploadRepo;
        $this->articleCategoryRepository = $categoryRepo;
        $this->businessRepository = $businessRepo;
    }

    /**
     * Display a listing of the article.
     *
     * @param ServiceDataTable $serviceDataTable
     * @return Response
     */
    public function index(ServiceDataTable $serviceDataTable)
    {
        return $serviceDataTable->render('article.index');
    }

    /**
     * Show the form for creating a new article.
     *
     * @return Application|Factory|Response|View
     */
    public function create()
    {
        $article_category = $this->articleCategoryRepository->pluck('name', 'id');
        $business = $this->businessRepository->getByCriteria(new BusinessOfUserCriteria(auth()->id()))->pluck('name', 'id');
        $articleCategoriesSelected = [];
        $hasCustomField = in_array($this->serviceRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->serviceRepository->model());
            $html = generateCustomField($customFields);
        }
        return view('article.create')->with("customFields", isset($html) ? $html : false)->with("article_category", $article_category)->with("articleCategoriesSelected", $articleCategoriesSelected)->with("business", $business);
    }

    /**
     * Store a newly created article in storage.
     *
     * @param CreateServiceRequest $request
     *
     * @return Application|RedirectResponse|Redirector|Response
     */
    public function store(CreateServiceRequest $request)
    {
        $input = $request->all();
        $input['type'] = 'service';
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->serviceRepository->model());
        try {
            $service = $this->serviceRepository->create($input);
            $service->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));
            if (isset($input['image']) && $input['image'] && is_array($input['image'])) {
                foreach ($input['image'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                    $mediaItem = $cacheUpload->getMedia('image')->first();
                    $mediaItem->copy($service, 'image');
                }
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.saved_successfully', ['operator' => __('lang.article')]));

        return redirect(route('article.index'));
    }

    /**
     * Display the specified article.
     *
     * @param int $id
     *
     * @return Application|RedirectResponse|Redirector|Response
     * @throws RepositoryException
     */
    public function show(int $id)
    {
        $this->serviceRepository->pushCriteria(new ServiceOfUserCriteria(auth()->id()));
        $service = $this->serviceRepository->findWithoutFail($id);

        if (empty($service)) {
            Flash::error('E Service not found');

            return redirect(route('article.index'));
        }

        return view('article.show')->with('article', $service);
    }

    /**
     * Show the form for editing the specified article.
     *
     * @param int $id
     *
     * @return Application|RedirectResponse|Redirector|Response
     * @throws RepositoryException
     */
    public function edit(int $id)
    {
        $this->serviceRepository->pushCriteria(new ServiceOfUserCriteria(auth()->id()));
        $service = $this->serviceRepository->findWithoutFail($id);
        if (empty($service)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.article')]));

            return redirect(route('article.index'));
        }
        $article_category = $this->articleCategoryRepository->pluck('name', 'id');
        $business = $this->businessRepository->getByCriteria(new BusinessOfUserCriteria(auth()->id()))->pluck('name', 'id');
        $articleCategoriesSelected = $service->article_categories()->pluck('article_categories.id')->toArray();

        $customFieldsValues = $service->customFieldsValues()->with('customField')->get();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->serviceRepository->model());
        $hasCustomField = in_array($this->serviceRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }
        return view('article.edit')->with('article', $service)->with("customFields", isset($html) ? $html : false)->with("article_category", $article_category)->with("articleCategoriesSelected", $articleCategoriesSelected)->with("business", $business);
    }

    /**
     * Update the specified article in storage.
     *
     * @param int $id
     * @param UpdateServiceRequest $request
     *
     * @return Application|RedirectResponse|Redirector|Response
     * @throws RepositoryException
     */
    public function update(int $id, UpdateServiceRequest $request)
    {
        $this->serviceRepository->pushCriteria(new ServiceOfUserCriteria(auth()->id()));
        $service = $this->serviceRepository->findWithoutFail($id);

        if (empty($service)) {
            Flash::error('E Service not found');
            return redirect(route('article.index'));
        }
        $input = $request->all();
        $input['type'] = 'service';
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->serviceRepository->model());
        try {
            $input['categories'] = isset($input['categories']) ? $input['categories'] : [];
            $service = $this->serviceRepository->update($input, $id);
            if (isset($input['image']) && $input['image'] && is_array($input['image'])) {
                foreach ($input['image'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                    $mediaItem = $cacheUpload->getMedia('image')->first();
                    $mediaItem->copy($service, 'image');
                }
            }
            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $service->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.updated_successfully', ['operator' => __('lang.article')]));

        return redirect(route('article.index'));
    }

    /**
     * Remove the specified article from storage.
     *
     * @param int $id
     *
     * @return Application|RedirectResponse|Redirector|Response
     * @throws RepositoryException
     */
    public function destroy(int $id)
    {
        $this->serviceRepository->pushCriteria(new ServiceOfUserCriteria(auth()->id()));
        $service = $this->serviceRepository->findWithoutFail($id);

        if (empty($service)) {
            Flash::error('E Service not found');

            return redirect(route('article.index'));
        }

        $this->serviceRepository->delete($id);

        Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.article')]));

        return redirect(route('article.index'));
    }

    /**
     * Remove Media of article
     * @param Request $request
     */
    public function removeMedia(Request $request)
    {
        $input = $request->all();
        $service = $this->serviceRepository->findWithoutFail($input['id']);
        try {
            if ($service->hasMedia($input['collection'])) {
                $service->getFirstMedia($input['collection'])->delete();
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
