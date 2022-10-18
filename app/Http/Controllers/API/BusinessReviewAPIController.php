<?php
/*
 * File name: BusinessReviewAPIController.php
 * Last modified: 2022.02.12 at 02:17:42
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Http\Controllers\API;


use App\Criteria\Bookings\BookingsOfUserCriteria;
use App\Criteria\Orders\OrdersOfUserCriteria;
use App\Criteria\BusinessReviews\BusinessReviewsOfUserCriteria;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateBusinessReviewRequest;
use App\Repositories\BookingRepository;
use App\Repositories\OrderRepository;
use App\Repositories\BusinessReviewRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class BusinessReviewController
 * @package App\Http\Controllers\API
 */
class BusinessReviewAPIController extends Controller
{
    /** @var  BusinessReviewRepository */
    private $BusinessReviewRepository;

    /** @var  BookingRepository */
    private $bookingRepository;

    /** @var  OrderRepository */
    private $orderRepository;

    public function __construct(BusinessReviewRepository $BusinessReviewRepo, BookingRepository $bookingRepository, OrderRepository $orderRepository)
    {
        $this->BusinessReviewRepository = $BusinessReviewRepo;
        $this->bookingRepository = $bookingRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Display a listing of the BusinessReview.
     * GET|HEAD /BusinessReviews
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $this->BusinessReviewRepository->pushCriteria(new RequestCriteria($request));
            if (auth()->check()) {
                $this->BusinessReviewRepository->pushCriteria(new BusinessReviewsOfUserCriteria(auth()->id()));
            }
            $this->BusinessReviewRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $BusinessReviews = $this->BusinessReviewRepository->all();
        $this->filterCollection($request, $BusinessReviews);

        return $this->sendResponse($BusinessReviews->toArray(), 'Business Reviews retrieved successfully');
    }

    /**
     * Display the specified BusinessReview.
     * GET|HEAD /BusinessReviews/{id}
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function show(int $id, Request $request): JsonResponse
    {
        try {
            $this->BusinessReviewRepository->pushCriteria(new RequestCriteria($request));
            $this->BusinessReviewRepository->pushCriteria(new LimitOffsetCriteria($request));

        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $BusinessReview = $this->BusinessReviewRepository->findWithoutFail($id);
        if (empty($BusinessReview)) {
            return $this->sendError(__('lang.not_found', ['operator' => __('lang.business_review')]));
        }
        $this->filterModel($request, $BusinessReview);

        return $this->sendResponse($BusinessReview->toArray(), 'Business Review retrieved successfully');
    }

    /**
     * Store a newly created Review in storage.
     *
     * @param CreateBusinessReviewRequest $request
     *
     * @return JsonResponse
     */
    public function store(CreateBusinessReviewRequest $request): JsonResponse
    {
        $bookingId = $request->only('booking_id');
        $orderId = $request->only('order_id');
        $input = $request->only('rate', 'review');
        $input['user_id'] = auth()->id();
        try {
            if($orderId){
                $this->orderRepository->pushCriteria(new OrdersOfUserCriteria(auth()->id()));
                $order = $this->orderRepository->findWithoutFail($orderId);
                if (empty($order)) {
                    return $this->sendError(__('lang.not_found', ['operator' => __('lang.order')]));
                }
                $input['business_id'] = $order[0]->productOrders[0]->article->business->id;
                $review = $this->BusinessReviewRepository->updateOrCreate($orderId, $input);
                return $this->sendResponse($review->toArray(), __('lang.saved_successfully', ['operator' => __('lang.business_review')]));
            }
            if($bookingId){
                $this->bookingRepository->pushCriteria(new BookingsOfUserCriteria(auth()->id()));
                $booking = $this->bookingRepository->findWithoutFail($bookingId);
                if (empty($booking)) {
                    return $this->sendError(__('lang.not_found', ['operator' => __('lang.booking')]));
                }
                $input['business_id'] = $booking[0]->business->id;
                $review = $this->BusinessReviewRepository->updateOrCreate($bookingId, $input);
                return $this->sendResponse($review->toArray(), __('lang.saved_successfully', ['operator' => __('lang.business_review')]));
            }
            
        } catch (RepositoryException | ValidatorException $e) {
            return $this->sendError(__('lang.not_found', ['operator' => __('lang.business_review')]));
        }

        return $this->sendResponse('', 'False', ['operator' => __('lang.business_review')]);
    }
}
