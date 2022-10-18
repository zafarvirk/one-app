<?php
/*
 * File name: ArticleCategoryAPIController.php
 * Last modified: 2021.03.24 at 21:33:26
 * Copyright (c) 2021
 */

namespace App\Http\Controllers\API;


use App\Criteria\Categories\NearCriteria;
use App\Criteria\Categories\ParentCriteria;
use App\Http\Controllers\Controller;
use App\Models\ArticleCategories;
use App\Repositories\ArticleCategoryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class ArticleCategoryController
 * @package App\Http\Controllers\API
 */
class ArticleCategoryAPIController extends Controller
{
    /** @var  ArticleCategoryRepository */
    private $articleCategoryRepository;

    public function __construct(ArticleCategoryRepository $categoryRepo)
    {
        $this->articleCategoryRepository = $categoryRepo;
    }

    /**
     * Display a listing of the Category.
     * GET|HEAD /categories
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        try{
            $this->articleCategoryRepository->pushCriteria(new RequestCriteria($request));
            $this->articleCategoryRepository->pushCriteria(new ParentCriteria($request));
            $this->articleCategoryRepository->pushCriteria(new NearCriteria($request));
            $this->articleCategoryRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $articleCategories = $this->articleCategoryRepository->all();

        return $this->sendResponse($articleCategories->toArray(), 'Article categories retrieved successfully');
    }

    /**
     * Display the specified Category.
     * GET|HEAD /categories/{id}
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function show($id)
    {
        /** @var Category $category */
        if (!empty($this->articleCategoryRepository)) {
            $articleCategory = $this->articleCategoryRepository->findWithoutFail($id);
        }

        if (empty($articleCategory)) {
            return $this->sendError('Article Category not found');
        }

        return $this->sendResponse($articleCategory->toArray(), 'Article Category retrieved successfully');
    }
}
