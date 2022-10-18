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
use App\Criteria\Users\OwnerCriteria;
use App\DataTables\ClassArticleDataTable;
use App\Http\Requests\CreateClassArticleRequest;
use App\Http\Requests\UpdateClassArticleRequest;
use App\Repositories\ArticleCategoryRepository;
use App\Repositories\CustomFieldRepository;
use App\Repositories\ClassArticleRepository;
use App\Repositories\BusinessRepository;
use App\Repositories\UserRepository;
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

class ClassArticleController extends Controller
{
    /** @var  ClassArticleRepository */
    private $classArticleRepository;

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
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(ClassArticleRepository $classArticleRepo, CustomFieldRepository $customFieldRepo, UploadRepository $uploadRepo
        , ArticleCategoryRepository                       $categoryRepo
        , BusinessRepository                          $businessRepo, UserRepository                          $userRepo)
    {
        parent::__construct();
        $this->classArticleRepository = $classArticleRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->uploadRepository = $uploadRepo;
        $this->articleCategoryRepository = $categoryRepo;
        $this->businessRepository = $businessRepo;
        $this->userRepository = $userRepo;
    }

    /**
     * Display a listing of the article.
     *
     * @param ClassArticleDataTable $classArticleDataTable
     * @return Response
     */
    public function index(ClassArticleDataTable $classArticleDataTable)
    {
        return $classArticleDataTable->render('class_article.index');
    }

    /**
     * Show the form for creating a new article.
     *
     * @return Application|Factory|Response|View
     */
    public function create()
    {
        $article_category = $this->articleCategoryRepository->pluck('name', 'id');
        $this->userRepository->pushCriteria(new OwnerCriteria);
        $staff = $this->userRepository->pluck('name', 'id');
        $business = $this->businessRepository->getByCriteria(new BusinessOfUserCriteria(auth()->id()))->pluck('name', 'id');
        $articleCategoriesSelected = [];
        $staffSelected = [];
        $hasCustomField = in_array($this->classArticleRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->classArticleRepository->model());
            $html = generateCustomField($customFields);
        }
        return view('class_article.create')->with("customFields", isset($html) ? $html : false)->with("article_category", $article_category)->with("articleCategoriesSelected", $articleCategoriesSelected)->with("staff", $staff)->with("staffSelected", $staffSelected)->with("business", $business);
    }

    /**
     * Store a newly created article in storage.
     *
     * @param CreateClassArticleRequest $request
     *
     * @return Application|RedirectResponse|Redirector|Response
     */
    public function store(CreateClassArticleRequest $request)
    {
        $input = $request->all();
        $input['type'] = 'class';
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->classArticleRepository->model());
        try {
            $classArticle = $this->classArticleRepository->create($input);
            $classArticle->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));
            if (isset($input['image']) && $input['image'] && is_array($input['image'])) {
                foreach ($input['image'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                    $mediaItem = $cacheUpload->getMedia('image')->first();
                    $mediaItem->copy($classArticle, 'image');
                }
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.saved_successfully', ['operator' => __('lang.article')]));

        return redirect(route('class_article.index'));
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
        $this->classArticleRepository->pushCriteria(new ServiceOfUserCriteria(auth()->id()));
        $classArticle = $this->classArticleRepository->findWithoutFail($id);

        if (empty($classArticle)) {
            Flash::error('E Service not found');

            return redirect(route('class_article.index'));
        }

        return view('class_article.show')->with('classArticle', $classArticle);
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
        $this->classArticleRepository->pushCriteria(new ServiceOfUserCriteria(auth()->id()));
        $classArticle = $this->classArticleRepository->findWithoutFail($id);
        if (empty($classArticle)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.article')]));

            return redirect(route('class_article.index'));
        }
        $article_category = $this->articleCategoryRepository->pluck('name', 'id');
        $this->userRepository->pushCriteria(new OwnerCriteria);
        $staff = $this->userRepository->pluck('name', 'id');
        $business = $this->businessRepository->getByCriteria(new BusinessOfUserCriteria(auth()->id()))->pluck('name', 'id');
        $articleCategoriesSelected = $classArticle->article_categories()->pluck('article_categories.id')->toArray();
        $staffSelected = $classArticle->article_staff()->pluck('user_id')->toArray();

        $customFieldsValues = $classArticle->customFieldsValues()->with('customField')->get();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->classArticleRepository->model());
        $hasCustomField = in_array($this->classArticleRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }
        return view('class_article.edit')->with('classArticle', $classArticle)->with("customFields", isset($html) ? $html : false)->with("article_category", $article_category)->with("articleCategoriesSelected", $articleCategoriesSelected)->with("staff", $staff)->with("staffSelected", $staffSelected)->with("business", $business);
    }

    /**
     * Update the specified article in storage.
     *
     * @param int $id
     * @param UpdateClassArticleRequest $request
     *
     * @return Application|RedirectResponse|Redirector|Response
     * @throws RepositoryException
     */
    public function update(int $id, UpdateClassArticleRequest $request)
    {
        $this->classArticleRepository->pushCriteria(new ServiceOfUserCriteria(auth()->id()));
        $classArticle = $this->classArticleRepository->findWithoutFail($id);

        if (empty($classArticle)) {
            Flash::error('E Service not found');
            return redirect(route('class_article.index'));
        }
        $input = $request->all();
        $input['type'] = 'class';
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->classArticleRepository->model());
        try {
            $input['categories'] = isset($input['categories']) ? $input['categories'] : [];
            $classArticle = $this->classArticleRepository->update($input, $id);
            if (isset($input['image']) && $input['image'] && is_array($input['image'])) {
                foreach ($input['image'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                    $mediaItem = $cacheUpload->getMedia('image')->first();
                    $mediaItem->copy($classArticle, 'image');
                }
            }
            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $classArticle->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.updated_successfully', ['operator' => __('lang.article')]));

        return redirect(route('class_article.index'));
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
        $this->classArticleRepository->pushCriteria(new ServiceOfUserCriteria(auth()->id()));
        $classArticle = $this->classArticleRepository->findWithoutFail($id);

        if (empty($classArticle)) {
            Flash::error('E Service not found');

            return redirect(route('class_article.index'));
        }

        $this->classArticleRepository->delete($id);

        Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.article')]));

        return redirect(route('class_article.index'));
    }

    /**
     * Remove Media of article
     * @param Request $request
     */
    public function removeMedia(Request $request)
    {
        $input = $request->all();
        $classArticle = $this->classArticleRepository->findWithoutFail($input['id']);
        try {
            if ($classArticle->hasMedia($input['collection'])) {
                $classArticle->getFirstMedia($input['collection'])->delete();
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
