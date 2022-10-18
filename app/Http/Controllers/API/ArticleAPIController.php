<?php
/*
 * File name: ArticleAPIController.php
 * Last modified: 2022.02.12 at 18:58:42
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Http\Controllers\API;


use App\Criteria\EServices\ArticleOfUserCriteria;
use App\Criteria\EServices\NearCriteria;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Models\Subscription;
use App\Repositories\ArticleRepository;
use App\Repositories\UploadRepository;
use App\Repositories\UserRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class ArticleController
 * @package App\Http\Controllers\API
 */
class ArticleAPIController extends Controller
{
    /** @var  ArticleRepository */
    private $articleRepository;
    /** @var UserRepository */
    private $userRepository;
    /**
     * @var UploadRepository
     */
    private $uploadRepository;

    public function __construct(ArticleRepository $articleRepo, UserRepository $userRepository, UploadRepository $uploadRepository)
    {
        parent::__construct();
        $this->articleRepository = $articleRepo;
        $this->userRepository = $userRepository;
        $this->uploadRepository = $uploadRepository;
    }

    /**
     * Display a listing of the EService.
     * GET|HEAD /eServices
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $this->articleRepository->pushCriteria(new RequestCriteria($request));
            $this->articleRepository->pushCriteria(new ArticleOfUserCriteria(auth()->id()));
            $this->articleRepository->pushCriteria(new NearCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $articles = $this->articleRepository->all();

        $this->availableEServices($articles);
        $this->availableSalon($request, $articles);
        $this->orderByRating($request, $articles);
        $this->limitOffset($request, $articles);
        $this->filterCollection($request, $articles);
        $articles = array_values($articles->toArray());

        return $this->sendResponse($articles, 'Articles retrieved successfully');
    }

    /**
     * @param Collection $articles
     */
    private function availableEServices(Collection &$articles)
    {
        $articles = $articles->where('available', true);
    }

    /**
     * @param Request $request
     * @param Collection $articles
     */
    private function availableSalon(Request $request, Collection &$articles)
    {
        if ($request->has('available_salon')) {
            $articles = $articles->filter(function ($element) {
                return !$element->salon->closed;
            });
        }
    }

    /**
     * @param Request $request
     * @param Collection $articles
     */
    private function orderByRating(Request $request, Collection &$articles)
    {
        if ($request->has('rating')) {
            $articles = $articles->sortBy('rate', SORT_REGULAR, true);
        }
    }

    /**
     * Display the specified EService.
     * GET|HEAD /eServices/{id}
     *
     * @param Request $request
     * @param int $id
     *
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $this->articleRepository->pushCriteria(new RequestCriteria($request));
            $this->articleRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }

        $article = $this->articleRepository->findWithoutFail($id);
        if (empty($article)) {
            return $this->sendError('Article not found');
        }

        if ($request->has('api_token')) {
            $user = $this->userRepository->findByField('api_token', $request->input('api_token'))->first();
            if (!empty($user)) {
                auth()->login($user, true);
            }

            $userPlanIds = Subscription::where('user_id', auth()->id())->where('is_active', 1)->where('available_sessions', '>', 0)->whereRaw("DATE(expiry_date) >= '".date('Y-m-d')."'")->pluck('plan_id')->toArray();
            $article->user_plan_id = null;
            if (count($userPlanIds)) {
                $articlePlans = $this->articleRepository->with('plans')->where('id', $id)->first();
                $articlePlanIds = $articlePlans->plans->pluck('id')->toArray();
                $useablePlanIds = array_intersect($userPlanIds, $articlePlanIds);
                $article->user_plan_id = count($useablePlanIds) ? $useablePlanIds[0] : null;
            }
        }
        $this->filterModel($request, $article);

        return $this->sendResponse($article->toArray(), 'Article retrieved successfully');
    }

    /**
     * Store a newly created EService in storage.
     *
     * @param CreateArticleRequest $request
     *
     * @return JsonResponse
     */
    public function store(CreateArticleRequest $request): JsonResponse
    {
        try {
            $input = $request->all();
            $article = $this->articleRepository->create($input);
            if (isset($input['image']) && $input['image'] && is_array($input['image'])) {
                foreach ($input['image'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                    $mediaItem = $cacheUpload->getMedia('image')->first();
                    $mediaItem->copy($article, 'image');
                }
            }
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse($article->toArray(), __('lang.saved_successfully', ['operator' => __('lang.e_service')]));
    }

    /**
     * Update the specified EService in storage.
     *
     * @param int $id
     * @param UpdateArticleRequest $request
     *
     * @return JsonResponse
     * @throws RepositoryException
     */
    public function update(int $id, UpdateArticleRequest $request): JsonResponse
    {
        $this->articleRepository->pushCriteria(new ArticleOfUserCriteria(auth()->id()));
        $article = $this->articleRepository->findWithoutFail($id);

        if (empty($article)) {
            return $this->sendError('Article not found');
        }
        try {
            $input = $request->all();
            $input['article_categories'] = isset($input['article_categories']) ? $input['article_categories'] : [];
            $article = $this->articleRepository->update($input, $id);
            if (isset($input['image']) && $input['image'] && is_array($input['image'])) {
                if ($article->hasMedia('image')) {
                    $article->getMedia('image')->each->delete();
                }
                foreach ($input['image'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                    $mediaItem = $cacheUpload->getMedia('image')->first();
                    $mediaItem->copy($article, 'image');
                }
            }
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($article->toArray(), __('lang.updated_successfully', ['operator' => __('lang.e_service')]));
    }

    /**
     * Remove the specified EService from storage.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws RepositoryException
     */
    public function destroy(int $id): JsonResponse
    {
        $this->articleRepository->pushCriteria(new ArticleOfUserCriteria(auth()->id()));
        $article = $this->articleRepository->findWithoutFail($id);

        if (empty($article)) {
            return $this->sendError('Article not found');
        }

        $article = $this->articleRepository->delete($id);

        return $this->sendResponse($article, __('lang.deleted_successfully', ['operator' => __('lang.e_service')]));

    }

    /**
     * Remove Media of EService
     * @param Request $request
     * @throws RepositoryException
     */
    public function removeMedia(Request $request)
    {
        $input = $request->all();
        try {
            $this->articleRepository->pushCriteria(new ArticleOfUserCriteria(auth()->id()));
            $article = $this->articleRepository->findWithoutFail($input['id']);
            if ($article->hasMedia($input['collection'])) {
                $article->getFirstMedia($input['collection'])->delete();
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
