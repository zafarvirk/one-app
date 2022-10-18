<?php
/*
 * File name: ArticleAPIController.php
 * Last modified: 2022.02.12 at 18:58:42
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Http\Controllers\API;


use App\Criteria\Post\PostCommentOfUserCriteria;
use App\Criteria\EServices\NearCriteria;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Repositories\PostCommentRepository;
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
class PostCommentAPIController extends Controller
{
    
    /** @var UserRepository */
    private $userRepository;
    /**
     * @var UploadRepository
     */
    private $uploadRepository;
    /**
     * @var PostCommentRepository
     */
    private $postCommentRepository;

    public function __construct(PostCommentRepository $postCommentRepo, UserRepository $userRepository, UploadRepository $uploadRepository)
    {
        parent::__construct();
        $this->postCommentRepository = $postCommentRepo;
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
            $this->postCommentRepository->pushCriteria(new RequestCriteria($request));
            $this->postCommentRepository->pushCriteria(new PostCommentOfUserCriteria(auth()->id()));
            // $this->postCommentRepository->pushCriteria(new NearCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $postComments = $this->postCommentRepository->all();

        return $this->sendResponse($postComments->toArray(), 'Post Comments retrieved successfully');
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
            $this->postCommentRepository->pushCriteria(new RequestCriteria($request));
            $this->postCommentRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $postComment = $this->postCommentRepository->findWithoutFail($id);
        if (empty($postComment)) {
            return $this->sendError('Post Comment not found');
        }

        return $this->sendResponse($postComment->toArray(), 'Post Comment retrieved successfully');
    }

    /**
     * Store a newly created EService in storage.
     *
     * @param CreateCommentRequest $request
     *
     * @return JsonResponse
     */
    public function store(CreateCommentRequest $request): JsonResponse
    {
        try {
            $input = $request->all();
            $input['user_id'] = auth()->id();
            $postComment = $this->postCommentRepository->create($input);
            if (isset($input['image']) && $input['image'] && is_array($input['image'])) {
                foreach ($input['image'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                    $mediaItem = $cacheUpload->getMedia('image')->first();
                    $mediaItem->copy($postComment, 'image');
                }
            }
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse($postComment->toArray(), __('lang.saved_successfully', ['operator' => __('lang.postComment')]));
    }

    /**
     * Update the specified EService in storage.
     *
     * @param int $id
     * @param UpdateCommentRequest $request
     *
     * @return JsonResponse
     * @throws RepositoryException
     */
    public function update(int $id, UpdateCommentRequest $request): JsonResponse
    {
        $this->postCommentRepository->pushCriteria(new PostCommentOfUserCriteria(auth()->id()));
        $postComment = $this->postCommentRepository->findWithoutFail($id);

        if (empty($postComment)) {
            return $this->sendError('Post Comment not found');
        }
        try {
            $input = $request->all();
            $postComment = $this->postCommentRepository->update($input, $id);
            if (isset($input['image']) && $input['image'] && is_array($input['image'])) {
                if ($postComment->hasMedia('image')) {
                    $postComment->getMedia('image')->each->delete();
                }
                foreach ($input['image'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                    $mediaItem = $cacheUpload->getMedia('image')->first();
                    $mediaItem->copy($postComment, 'image');
                }
            }
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($postComment->toArray(), __('lang.updated_successfully', ['operator' => __('lang.postComment')]));
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
        $this->postCommentRepository->pushCriteria(new PostCommentOfUserCriteria(auth()->id()));
        $postComment = $this->postCommentRepository->findWithoutFail($id);

        if (empty($postComment)) {
            return $this->sendError('Post Comment not found');
        }

        $postComment = $this->postCommentRepository->delete($id);

        return $this->sendResponse($postComment, __('lang.deleted_successfully', ['operator' => __('lang.postComment')]));

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
            $this->postCommentRepository->pushCriteria(new PostCommentOfUserCriteria(auth()->id()));
            $postComment = $this->postCommentRepository->findWithoutFail($input['id']);
            if ($postComment->hasMedia($input['collection'])) {
                $postComment->getFirstMedia($input['collection'])->delete();
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
