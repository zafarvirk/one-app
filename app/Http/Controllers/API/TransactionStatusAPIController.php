<?php
/*
 * File name: TransactionStatusAPIController.php
 * Last modified: 2021.02.12 at 11:06:02
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */

namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use App\Models\TransactionStatus;
use App\Repositories\TransactionStatusRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class TransactionStatusController
 * @package App\Http\Controllers\API
 */
class TransactionStatusAPIController extends Controller
{
    /** @var  TransactionStatusRepository */
    private $transactionStatusRepository;

    public function __construct(TransactionStatusRepository $transactionStatusRepo)
    {
        $this->transactionStatusRepository = $transactionStatusRepo;
    }

    /**
     * Display a listing of the TransactionStatus.
     * GET|HEAD /TransactionStatuses
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        try{
            $this->transactionStatusRepository->pushCriteria(new RequestCriteria($request));
            $this->transactionStatusRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $transactionStatuses = $this->transactionStatusRepository->all();
        $this->filterCollection($request, $transactionStatuses);

        return $this->sendResponse($transactionStatuses->toArray(), 'Transaction Statuses retrieved successfully');
    }

    /**
     * Display the specified transactionStatus.
     * GET|HEAD /TransactionStatuses/{id}
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function show($id)
    {
        /** @var TransactionStatus $transactionStatus */
        if (!empty($this->transactionStatusRepository)) {
            $transactionStatus = $this->transactionStatusRepository->findWithoutFail($id);
        }

        if (empty($transactionStatus)) {
            return $this->sendError('Transaction Status not found');
        }

        return $this->sendResponse($transactionStatus->toArray(), 'Transaction Status retrieved successfully');
    }
}
