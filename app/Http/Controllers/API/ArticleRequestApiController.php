<?php

namespace App\Http\Controllers\API;


use App\Http\Requests\CreateArticleRequestRequest;
use App\Models\ArticleRequest;
use App\Repositories\ArticleRequestRepository;
use App\Repositories\UploadRepository;
use App\Repositories\RequestOfferRepository;
use App\Models\Business;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Criteria\ArticleRequest\ArticleRequestOfUserCriteria;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Illuminate\Support\Facades\Response;
use Prettus\Repository\Exceptions\RepositoryException;
use Flash;
use Prettus\Validator\Exceptions\ValidatorException;
Use DB;

/**
 * Class CartController
 * @package App\Http\Controllers\API
 */

class ArticleRequestApiController extends Controller
{
    /** @var  ArticleRequestRepository */
    private $articleRequestRepository;
    /**
     * @var UploadRepository
     */
    private $uploadRepository;

    /** @var  RequestOfferRepository */
    private $requestOfferRepository;

    public function __construct(ArticleRequestRepository $articleRequestRepo, UploadRepository $uploadRepository,RequestOfferRepository $requestOfferRepo)
    {
        $this->articleRequestRepository = $articleRequestRepo;
        $this->uploadRepository = $uploadRepository;
        $this->requestOfferRepository = $requestOfferRepo;
    }

    /**
     * Display a listing of the Cart.
     * GET|HEAD /carts
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try{
            $this->articleRequestRepository->pushCriteria(new ArticleRequestOfUserCriteria(auth()->user()->id));
            $this->articleRequestRepository->pushCriteria(new RequestCriteria($request));
            $this->articleRequestRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        if (auth()->user()->hasRole('salon owner')) {
            $businesses = Business::join('business_users', 'business_users.business_id', '=', 'businesses.id')
                        ->where('business_users.user_id', auth()->user()->id)->get('id')->toArray();
            $businessId = [];
            foreach($businesses as $b){
                $businessId[] = $b['id'];
            }
            // dd($businessId);
            $articleRequests = $this->articleRequestRepository->whereIn('offer_accepted_by_business_id' , $businessId)->orWhere('offer_accepted_by_business_id' , null)->orderBy('id' , 'desc')->get();
        }
        else {
            $articleRequests = $this->articleRequestRepository->orderBy('id' , 'desc')->all();
        }
        foreach($articleRequests as $key => $r){
            // $articleRequests[$key]->audio = DB::table('media')->where('model_type' , 'App\Models\ArticleRequest')
            //                                 ->where('model_id' , $r->id)->where('collection_name' , 'image')->get();
            $articleRequests[$key]->image = $r->getMedia('image');
            $articleRequests[$key]->audio = $r->getMedia('audio');
            unset($articleRequests[$key]->media);
        }

        return $this->sendResponse($articleRequests->toArray(), 'Requests retrieved successfully');
    }

    public function show($id)
    {
        $articleRequest = $this->articleRequestRepository->findWithoutFail($id);

        if (empty($articleRequest)) {
            return $this->sendError('Request not found');
        }

        return $this->sendResponse($articleRequest->toArray(), 'Request retrieved successfully');
    }

    public function store(CreateArticleRequestRequest $request)
    {
        $input = $request->all();
        if (isset($input['image'])) {
            $input['image'] = explode(',',$input['image']);
        }
        try {
            $input['user_id'] = auth()->user()->id;
            $articleRequest = $this->articleRequestRepository->create($input);
            if (isset($input['image']) && $input['image'] && is_array($input['image'])) {
                if ($articleRequest->hasMedia('image')) {
                    $articleRequest->getMedia('image')->each->delete();
                }
                foreach ($input['image'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                    $mediaItem = $cacheUpload->getMedia('image')->first();
                    $mediaItem->copy($articleRequest, 'image');
                }
            }
            if (isset($input['file']) && isset($input['uuid']) && isset($input['field'])) {
                $articleRequest->addMedia($input['file'])
                    ->withCustomProperties(['uuid' => $input['uuid'], 'user_id' => auth()->id()])
                    ->toMediaCollection($input['field']);
            }
            return $this->sendResponse($articleRequest->toArray(), __('lang.saved_successfully',['operator' => 'Request']));
        } catch (ValidatorException $e) {
            return $this->sendError($e->getMessage());
        }
    }

    public function update($id, Request $request)
    {
        $articleRequest = $this->articleRequestRepository->findWithoutFail($id);

        if (empty($articleRequest)) {
            return $this->sendError('Request not found');
        }
        $input = $request->all();
        if (isset($input['image'])) {
            $input['image'] = explode(',',$input['image']);
        }
        try {
            $articleRequest = $this->articleRequestRepository->update($input, $id);
            if (isset($input['image']) && $input['image'] && is_array($input['image'])) {
                if ($articleRequest->hasMedia('image')) {
                    $articleRequest->getMedia('image')->each->delete();
                }
                foreach ($input['image'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                    $mediaItem = $cacheUpload->getMedia('image')->first();
                    $mediaItem->copy($articleRequest, 'image');
                }
            }
            if (isset($input['file']) && isset($input['uuid']) && isset($input['field'])) {
                $articleRequest->getMedia('audio')->each->delete();
                $articleRequest->addMedia($input['file'])
                    ->withCustomProperties(['uuid' => $input['uuid'], 'user_id' => auth()->id()])
                    ->toMediaCollection($input['field']);
            }

        } catch (ValidatorException $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($articleRequest->toArray(), __('lang.updated_successfully',['operator' => 'Request']));
    }

    /**
     * Remove the specified Favorite from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $articleRequest = $this->articleRequestRepository->findWithoutFail($id);

        if (empty($articleRequest)) {
            return $this->sendError('Request not found');

        }

        $articleRequest = $this->articleRequestRepository->delete($id);

        return $this->sendResponse($articleRequest, __('lang.deleted_successfully',['operator' => 'Request']));

    }

}
