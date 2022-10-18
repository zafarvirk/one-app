<?php
/*
 * File name: BusinessCategoryAPIController.php
 * Last modified: 2022.02.03 at 10:46:03
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use App\Models\BusinessCategory;
use App\Repositories\BusinessCategoryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Criteria\Categories\ParentCriteria;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class BusinessCategoryController
 * @package App\Http\Controllers\API
 */
class BusinessCategoryAPIController extends Controller
{
    /** @var  BusinessCategoryRepository */
    private $businessCategoryRepository;

    public function __construct(BusinessCategoryRepository $businessCategoryRepo)
    {
        $this->businessCategoryRepository = $businessCategoryRepo;
    }

    /**
     * Display a listing of the BusinessCategory.
     * GET|HEAD /BusinessCategories
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $this->businessCategoryRepository->pushCriteria(new RequestCriteria($request));
            $this->businessCategoryRepository->pushCriteria(new LimitOffsetCriteria($request));
            $this->businessCategoryRepository->pushCriteria(new ParentCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $businessCategories = $this->businessCategoryRepository->all();

        return $this->sendResponse($businessCategories->toArray(), 'Business categories retrieved successfully');
    }

    /**
     * Display the specified BusinessCategory.
     * GET|HEAD /BusinessCategories/{id}
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function show($id)
    {
        /** @var BusinessCategory $businessCategory */
        if (!empty($this->businessCategoryRepository)) {
            $businessCategory = $this->businessCategoryRepository->findWithoutFail($id);
        }

        if (empty($businessCategory)) {
            return $this->sendError('Business categories not found');
        }

        return $this->sendResponse($businessCategory->toArray(), 'Business categories retrieved successfully');
    }
}
