<?php
/*
 * File name: GalleryAPIController.php
 * Last modified: 2021.03.05 at 23:25:13
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */

namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Repositories\GalleryRepository;
use App\Repositories\UploadRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Criteria\Galleries\GalleriesOfActiveCriteria;
use App\Criteria\Galleries\GalleriesOfUserCriteria;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class GalleryController
 * @package App\Http\Controllers\API
 */
class GalleryAPIController extends Controller
{
    /** @var  GalleryRepository */
    private $galleryRepository;
    /**
     * @var UploadRepository
     */
    private $uploadRepository;

    public function __construct(GalleryRepository $galleryRepo, UploadRepository $uploadRepository)
    {
        $this->galleryRepository = $galleryRepo;
        $this->uploadRepository = $uploadRepository;
        parent::__construct();
    }

    /**
     * Display a listing of the Gallery.
     * GET|HEAD /galleries
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $this->galleryRepository->pushCriteria(new GalleriesOfActiveCriteria());
            $this->galleryRepository->pushCriteria(new RequestCriteria($request));
            $this->galleryRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $galleries = $this->galleryRepository->all();

        return $this->sendResponse($galleries->toArray(), 'Galleries retrieved successfully');
    }

    public function ownerGalleries(Request $request): JsonResponse
    {
        try {
            $this->galleryRepository->pushCriteria(new GalleriesOfUserCriteria(auth()->user()->id));
            $this->galleryRepository->pushCriteria(new RequestCriteria($request));
            $this->galleryRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $galleries = $this->galleryRepository->all();

        return $this->sendResponse($galleries->toArray(), 'Galleries retrieved successfully');
    }

    /**
     * Display the specified Gallery.
     * GET|HEAD /galleries/{id}
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function show($id)
    {
        /** @var Gallery $gallery */
        if (!empty($this->galleryRepository)) {
            $gallery = $this->galleryRepository->findWithoutFail($id);
        }

        if (empty($gallery)) {
            return $this->sendError('Gallery not found');
        }

        return $this->sendResponse($gallery->toArray(), 'Gallery retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $input = $request->all();
            $gallery = $this->galleryRepository->create($input);
            if (isset($input['image']) && $input['image'] && is_array($input['image'])) {
                foreach ($input['image'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($input['image']);
                    $mediaItem = $cacheUpload->getMedia('image')->first();
                    $mediaItem->copy($gallery, 'image');
                }
            }
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse($gallery->toArray(), __('lang.saved_successfully', ['operator' => 'Gallery']));
    }

    public function update(int $id, Request $request): JsonResponse
    {
        $gallery = $this->galleryRepository->findWithoutFail($id);

        if (empty($gallery)) {
            return $this->sendError('gallery not found');
        }
        try {
            $input = $request->all();
            $gallery = $this->galleryRepository->update($input, $id);
            if (isset($input['image']) && $input['image'] && is_array($input['image'])) {
                if ($gallery->hasMedia('image')) {
                    $gallery->getMedia('image')->each->delete();
                }
                foreach ($input['image'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($input['image']);
                    $mediaItem = $cacheUpload->getMedia('image')->first();
                    $mediaItem->copy($gallery, 'image');
                }
            }
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($gallery->toArray(), __('lang.updated_successfully', ['operator' => 'gallery']));
    }
    public function destroy(int $id): JsonResponse
    {
        $gallery = $this->galleryRepository->findWithoutFail($id);

        if (empty($gallery)) {
            return $this->sendError('gallery not found');
        }

        $gallery = $this->galleryRepository->delete($id);

        return $this->sendResponse($gallery, __('lang.deleted_successfully', ['operator' => 'gallery']));

    }

    public function removeMedia(Request $request)
    {
        $input = $request->all();
        try {
            $gallery = $this->galleryRepository->findWithoutFail($input['id']);
            if ($gallery->hasMedia($input['collection'])) {
                $gallery->getFirstMedia($input['collection'])->delete();
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

}
