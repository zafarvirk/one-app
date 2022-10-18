<?php
/*
 * File name: ArticleAPIController.php
 * Last modified: 2022.02.12 at 18:58:42
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Http\Controllers\API;


use App\Criteria\Post\PostOfUserCriteria;
use App\Criteria\EServices\NearCriteria;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Repositories\ArticleRepository;
use App\Repositories\PostRepository;
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
class PostAPIController extends Controller
{
    
    /** @var  ArticleRepository */
    private $articleRepository;
    /** @var UserRepository */
    private $userRepository;
    /**
     * @var UploadRepository
     */
    private $uploadRepository;
    /**
     * @var PostRepository
     */
    private $postRepository;

    public function __construct(PostRepository $postRepo, ArticleRepository $articleRepo, UserRepository $userRepository, UploadRepository $uploadRepository)
    {
        parent::__construct();
        $this->postRepository = $postRepo;
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
            $this->postRepository->pushCriteria(new RequestCriteria($request));
            $this->postRepository->pushCriteria(new PostOfUserCriteria(auth()->id()));
            // $this->postRepository->pushCriteria(new NearCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $posts = $this->postRepository->all();

        return $this->sendResponse($posts->toArray(), 'Posts retrieved successfully');
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
            $this->postRepository->pushCriteria(new RequestCriteria($request));
            $this->postRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $post = $this->postRepository->findWithoutFail($id);
        if (empty($post)) {
            return $this->sendError('Post not found');
        }

        return $this->sendResponse($post->toArray(), 'Post retrieved successfully');
    }

    /**
     * Store a newly created EService in storage.
     *
     * @param CreatePostRequest $request
     *
     * @return JsonResponse
     */
    public function store(CreatePostRequest $request): JsonResponse
    {
        try {
            $input = $request->all();
            $input['user_id'] = auth()->id();
            $post = $this->postRepository->create($input);
            if (isset($input['image']) && $input['image'] && is_array($input['image'])) {
                foreach ($input['image'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                    $mediaItem = $cacheUpload->getMedia('image')->first();
                    $mediaItem->copy($post, 'image');
                }
            }
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse($post->toArray(), __('lang.saved_successfully', ['operator' => __('lang.post')]));
    }

    /**
     * Update the specified EService in storage.
     *
     * @param int $id
     * @param UpdatePostRequest $request
     *
     * @return JsonResponse
     * @throws RepositoryException
     */
    public function update(int $id, UpdatePostRequest $request): JsonResponse
    {
        $this->postRepository->pushCriteria(new PostOfUserCriteria(auth()->id()));
        $post = $this->postRepository->findWithoutFail($id);

        if (empty($post)) {
            return $this->sendError('Post not found');
        }
        try {
            $input = $request->all();
            $post = $this->postRepository->update($input, $id);
            if (isset($input['image']) && $input['image'] && is_array($input['image'])) {
                if ($post->hasMedia('image')) {
                    $post->getMedia('image')->each->delete();
                }
                foreach ($input['image'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                    $mediaItem = $cacheUpload->getMedia('image')->first();
                    $mediaItem->copy($post, 'image');
                }
            }
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($post->toArray(), __('lang.updated_successfully', ['operator' => __('lang.post')]));
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
        $this->postRepository->pushCriteria(new PostOfUserCriteria(auth()->id()));
        $post = $this->postRepository->findWithoutFail($id);

        if (empty($post)) {
            return $this->sendError('Post not found');
        }

        $post = $this->postRepository->delete($id);

        return $this->sendResponse($post, __('lang.deleted_successfully', ['operator' => __('lang.post')]));

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
            $this->postRepository->pushCriteria(new PostOfUserCriteria(auth()->id()));
            $post = $this->postRepository->findWithoutFail($input['id']);
            if ($post->hasMedia($input['collection'])) {
                $post->getFirstMedia($input['collection'])->delete();
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
