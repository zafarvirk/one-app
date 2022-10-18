<?php

namespace App\Http\Controllers\API;


use App\Models\ArticleOrder;
use App\Repositories\ArticleOrderRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Illuminate\Support\Facades\Response;
use Prettus\Repository\Exceptions\RepositoryException;
use Flash;

/**
 * Class ProductOrderController
 * @package App\Http\Controllers\API
 */

class ArticleOrderAPIController extends Controller
{
    /** @var  ArticleOrderRepository */
    private $articleOrderRepository;

    public function __construct(ArticleOrderRepository $articleOrderRepo)
    {
        $this->articleOrderRepository = $articleOrderRepo;
    }

    /**
     * Display a listing of the ProductOrder.
     * GET|HEAD /productOrders
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try{
            $this->articleOrderRepository->pushCriteria(new RequestCriteria($request));
            $this->articleOrderRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $productOrders = $this->articleOrderRepository->all();

        return $this->sendResponse($productOrders->toArray(), 'Product Orders retrieved successfully');
    }

    /**
     * Display the specified ProductOrder.
     * GET|HEAD /productOrders/{id}
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        /** @var ProductOrder $productOrder */
        if (!empty($this->articleOrderRepository)) {
            $productOrder = $this->articleOrderRepository->findWithoutFail($id);
        }

        if (empty($productOrder)) {
            return $this->sendError('Product Order not found');
        }

        return $this->sendResponse($productOrder->toArray(), 'Product Order retrieved successfully');
    }
}
