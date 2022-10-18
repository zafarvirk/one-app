<?php
/*
 * File name: ArticleAPIController.php
 * Last modified: 2022.02.12 at 18:58:42
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Http\Controllers\API;


use App\Criteria\Post\PostReactionOfUserCriteria;
use App\Criteria\EServices\NearCriteria;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePostReactionRequest;
use App\Http\Requests\UpdatePostReactionRequest;
use App\Repositories\PostReactionRepository;
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
class PostReactionAPIController extends Controller
{
    
    /** @var UserRepository */
    private $userRepository;
    /**
     * @var UploadRepository
     */
    private $uploadRepository;
    /**
     * @var PostReactionRepository
     */
    private $postReactionRepository;

    public function __construct(PostReactionRepository $postReactionRepo, UserRepository $userRepository, UploadRepository $uploadRepository)
    {
        parent::__construct();
        $this->postReactionRepository = $postReactionRepo;
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
            $this->postReactionRepository->pushCriteria(new RequestCriteria($request));
            $this->postReactionRepository->pushCriteria(new PostReactionOfUserCriteria(auth()->id()));
            // $this->postReactionRepository->pushCriteria(new NearCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $postReactions = $this->postReactionRepository->all();

        return $this->sendResponse($postReactions->toArray(), 'Post Reactions retrieved successfully');
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
            $this->postReactionRepository->pushCriteria(new RequestCriteria($request));
            $this->postReactionRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $postReaction = $this->postReactionRepository->findWithoutFail($id);
        if (empty($postReaction)) {
            return $this->sendError('Post Reaction not found');
        }

        return $this->sendResponse($postReaction->toArray(), 'Post Reaction retrieved successfully');
    }

    /**
     * Store a newly created EService in storage.
     *
     * @param CreatePostReactionRequest $request
     *
     * @return JsonResponse
     */
    public function store(CreatePostReactionRequest $request): JsonResponse
    {
        try {
            $input = $request->all();
            $input['user_id'] = auth()->id();
            $postReaction = $this->postReactionRepository->create($input);
            if (isset($input['image']) && $input['image'] && is_array($input['image'])) {
                foreach ($input['image'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                    $mediaItem = $cacheUpload->getMedia('image')->first();
                    $mediaItem->copy($postReaction, 'image');
                }
            }
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse($postReaction->toArray(), __('lang.saved_successfully', ['operator' => __('lang.postReaction')]));
    }

    /**
     * Update the specified EService in storage.
     *
     * @param int $id
     * @param UpdatePostReactionRequest $request
     *
     * @return JsonResponse
     * @throws RepositoryException
     */
    public function update(int $id, UpdatePostReactionRequest $request): JsonResponse
    {
        $this->postReactionRepository->pushCriteria(new PostReactionOfUserCriteria(auth()->id()));
        $postReaction = $this->postReactionRepository->findWithoutFail($id);

        if (empty($postReaction)) {
            return $this->sendError('Post Reaction not found');
        }
        try {
            $input = $request->all();
            $postReaction = $this->postReactionRepository->update($input, $id);
            if (isset($input['image']) && $input['image'] && is_array($input['image'])) {
                if ($postReaction->hasMedia('image')) {
                    $postReaction->getMedia('image')->each->delete();
                }
                foreach ($input['image'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                    $mediaItem = $cacheUpload->getMedia('image')->first();
                    $mediaItem->copy($postReaction, 'image');
                }
            }
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($postReaction->toArray(), __('lang.updated_successfully', ['operator' => __('lang.postReaction')]));
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
        $this->postReactionRepository->pushCriteria(new PostReactionOfUserCriteria(auth()->id()));
        $postReaction = $this->postReactionRepository->findWithoutFail($id);

        if (empty($postReaction)) {
            return $this->sendError('Post Reaction not found');
        }

        $postReaction = $this->postReactionRepository->delete($id);

        return $this->sendResponse($postReaction, __('lang.deleted_successfully', ['operator' => __('lang.postReaction')]));

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
            $this->postReactionRepository->pushCriteria(new PostReactionOfUserCriteria(auth()->id()));
            $postReaction = $this->postReactionRepository->findWithoutFail($input['id']);
            if ($postReaction->hasMedia($input['collection'])) {
                $postReaction->getFirstMedia($input['collection'])->delete();
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
