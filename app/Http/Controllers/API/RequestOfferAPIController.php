<?php
/*
 * File name: AvailabilityHourAPIController.php
 * Last modified: 2022.05.07 at 15:58:23
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use App\Repositories\RequestOfferRepository;
use App\Repositories\ArticleRequestRepository;
use App\Repositories\BusinessRepository;
use App\Http\Requests\CreateRequestOfferRequest;
use Carbon\Carbon;
use App\Models\RequestOffer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class AvailabilityHourController
 * @package App\Http\Controllers\API
 */
class RequestOfferAPIController extends Controller
{
    /** @var  RequestOfferRepository */
    private $requestOfferRepository;

    /** @var  BusinessRepository */
    private $businessRepository;

    /** @var ArticleRequestRepository */
    private $articleRequestRepository;

    public function __construct(RequestOfferRepository $requestOfferRepo, BusinessRepository $businessRepo, ArticleRequestRepository $articleRequestRepo)
    {
        $this->requestOfferRepository = $requestOfferRepo;
        $this->businessRepository = $businessRepo;
        $this->articleRequestRepository = $articleRequestRepo;
    }


    /**
     * Display a listing of the AvailabilityHour.
     * GET|HEAD /requestOffers
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $request->validate([
            'request_id' => 'required|exists:article_requests,id',
        ]);
        try {
            $this->requestOfferRepository->pushCriteria(new RequestCriteria($request));
            $this->requestOfferRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $offers = $this->requestOfferRepository->where('article_request_id' , $request->request_id)->get();

        return $this->sendResponse($offers->toArray(), 'Offers retrieved successfully');
    }

    /**
     * Display the specified AvailabilityHour.
     * GET|HEAD /requestOffers/{id}
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function show(int $id, Request $request): JsonResponse
    {
        try {
            $this->requestOfferRepository->pushCriteria(new RequestCriteria($request));
            $this->requestOfferRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $offer = $this->requestOfferRepository->findWithoutFail($id);
        if (empty($offer)) {
            return $this->sendError('Offer not found');
        }

        return $this->sendResponse($offer->toArray(), 'Offer retrieved successfully');

    }

    public function store(CreateRequestOfferRequest $request): JsonResponse
    {
        if(auth()->user()->hasRole('salon owner')){
            $input = $request->all();
            $input['user_id'] = auth()->user()->id;
            try {
                $request = $this->articleRequestRepository->findWithoutFail($input['article_request_id']);
                $data = ['no_of_offers' => $request->no_of_offers + 1];
                $request = $this->articleRequestRepository->update($data , $input['article_request_id']);
                $offer = $this->requestOfferRepository->create($input);
                $business = $this->businessRepository->find($input['business_id']);
                $notification = [
                    'title' => trans('lang.notification_new_offer'),
                    'body' => trans('lang.notification_new_offer_description', ['offer_id' => $offer->id, 'offer_status' => $offer->status]),
                    'icon' => $business->hasMedia('image')?$business->getFirstMediaUrl('image', 'thumb'):asset('images/image_default.png'),
                    'click_action' => "FLUTTER_NOTIFICATION_CLICK",
                    'id' => 'App\\Notifications\\NewOffer',
                    'status' => 'done',
                ];
                $data = $notification;
                $data['offerId'] = $offer->id;
                notify($data , $request->user_id , trans('lang.notification_new_offer'));
            } catch (RepositoryException $e) {
                return $this->sendError($e->getMessage());
            }

            return $this->sendResponse($offer, 'Offer added successfully');
        } else {
            return $this->sendError('user has no right to add offer');
        }

    }

    public function acceptOffer(Request $request): JsonResponse
    {
        $request->validate([
            'offer_id' => 'required|exists:request_offers,id',
            'transaction_status_id' => 'required|exists:transaction_statuses,id',
        ]);
        $input = $request->all();
        $input['user_id'] = auth()->user()->id;
        $offer = $this->requestOfferRepository->findWithoutFail($input['offer_id']);
        if (empty($offer)) {
            return $this->sendError('Offer not found');
        }
        try {
            $offer = $this->requestOfferRepository->update(['status' => 'accepted'] , $input['offer_id']);
            RequestOffer::where('article_request_id' , $offer->article_request_id)->where('id', '!=' , $offer->id)->update(['status' => 'rejected']);

            $request = $this->articleRequestRepository->findWithoutFail($offer->article_request_id);
            $data1 = ['transaction_status_id' => $input['transaction_status_id'] , 'offer_accepted_by_business_id' => $offer->business_id];
            $request = $this->articleRequestRepository->update($data1 , $offer->article_request_id);

            // accepted offer business owner notification
            $business = $this->businessRepository->find($offer->business_id);
            $notification = [
                'title' => trans('lang.notification_accepted_offer'),
                'body' => trans('lang.notification_accepted_offer_description', ['request_id' => $offer->article_request_id, 'offer_status' => $offer->status]),
                'icon' => $business->hasMedia('image')?$business->getFirstMediaUrl('image', 'thumb'):asset('images/image_default.png'),
                'click_action' => "FLUTTER_NOTIFICATION_CLICK",
                'id' => 'App\\Notifications\\AcceptedOffer',
                'status' => 'done',
            ];
            $data = $notification;
            $data['offerId'] = $offer->id;
            foreach($business->users as $owner){
                notify($data , $owner->id , trans('lang.notification_accepted_offer'));
            }

            // rejected offer business owner notification
            $offerRejected = RequestOffer::where('article_request_id' , $offer->article_request_id)->where('status', 'rejected')->get();
            foreach($offerRejected as $o){
                $business = $this->businessRepository->find($o->business_id);
                $notification = [
                    'title' => trans('lang.notification_rejected_offer'),
                    'body' => trans('lang.notification_rejected_offer_description', ['request_id' => $o->article_request_id, 'offer_status' => $o->status]),
                    'icon' => $business->hasMedia('image')?$business->getFirstMediaUrl('image', 'thumb'):asset('images/image_default.png'),
                    'click_action' => "FLUTTER_NOTIFICATION_CLICK",
                    'id' => 'App\\Notifications\\AcceptedOffer',
                    'status' => 'done',
                ];
                $data = $notification;
                $data['offerId'] = $o->id;
                foreach($business->users as $owner){
                    notify($data , $owner->id , trans('lang.notification_rejected_offer'));
                }
            }


        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($offer, 'Offer added successfully');
    } 



}
