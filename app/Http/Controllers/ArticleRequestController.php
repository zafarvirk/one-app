<?php
/*
 * File name: ArticleRequestController.php
 * Last modified: 2022.03.09 at 21:10:28
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Http\Controllers;

use App\DataTables\ArticleRequestDataTable;
use App\Http\Requests\CreateArticleRequestRequest;
use App\Http\Requests\UpdateArticleRequestRequest;
use App\Repositories\ArticleRequestRepository;
use App\Repositories\CustomFieldRepository;
use App\Repositories\AddressRepository;
use App\Repositories\BusinessCategoryRepository;
use App\Repositories\UserRepository;
use App\Repositories\TransactionStatusRepository;
use App\Repositories\BusinessRepository;
use App\Repositories\UploadRepository;
use App\Models\Business;
use Exception;
use Flash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Prettus\Validator\Exceptions\ValidatorException;

class ArticleRequestController extends Controller
{
    /** @var  ArticleRequestRepository */
    private $articleRequestRepository;

    /** @var  AddressRepository */
    private $addressRepository;

    /** @var  BusinessCategoryRepository */
    private $businessCategoryRepository;

    /** @var  UserRepository */
    private $userRepository;

    /** @var  TransactionStatusRepository */
    private $transactionStatusRepository;

    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;

    /** @var  BusinessRepository */
    private $businessRepository;

    /**
     * @var UploadRepository
     */
    private $uploadRepository;

    public function __construct(ArticleRequestRepository $requestRepo, CustomFieldRepository $customFieldRepo, UploadRepository $uploadRepo,
    AddressRepository $addressRepo, BusinessCategoryRepository $businessCategoryRepo ,
     TransactionStatusRepository $transactionStatusRepo , UserRepository $userRepo, BusinessRepository $businessRepo)
    {
        parent::__construct();
        $this->articleRequestRepository = $requestRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->uploadRepository = $uploadRepo;
        $this->addressRepository = $addressRepo;
        $this->businessCategoryRepository = $businessCategoryRepo;
        $this->transactionStatusRepository = $transactionStatusRepo;
        $this->userRepository = $userRepo;
        $this->businessRepository = $businessRepo;
    }

    /**
     * Display a listing of the request.
     *
     * @param ArticleRequestDataTable $articleRequestDataTable
     * @return Response
     */
    public function index(ArticleRequestDataTable $articleRequestDataTable)
    {
        return $articleRequestDataTable->render('requests.index');
    }

    /**
     * Show the form for creating a new request.
     *
     * @return Response
     */
    public function create()
    {
        $address = $this->addressRepository->pluck('address', 'id');
        $businessCategory = $this->businessCategoryRepository->pluck('name', 'id');
        $transactionStatus = $this->transactionStatusRepository->where('type' , 'request')->pluck('status', 'id');
        $user = $this->userRepository->pluck('name', 'id');
        $hasCustomField = in_array($this->articleRequestRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->articleRequestRepository->model());
            $html = generateCustomField($customFields);
        }
        return view('requests.create')->with("customFields", isset($html) ? $html : false)->with('address' , $address)->with('business_category' , $businessCategory)->with('transaction_status' , $transactionStatus)->with('user' , $user);
    }

    /**
     * Store a newly created request in storage.
     *
     * @param CreateArticleRequestRequest $request
     *
     * @return Response
     */
    public function store(CreateArticleRequestRequest $request)
    {
        $input = $request->all();
        // dd($input);
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->articleRequestRepository->model());
        try {
            $article_request = $this->articleRequestRepository->create($input);
            $article_request->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $article_request));
            if (isset($input['image']) && $input['image']) {
                $cacheUpload = $this->uploadRepository->getByUuid($input['image']);
                $mediaItem = $cacheUpload->getMedia('image')->first();
                $mediaItem = $mediaItem->forgetCustomProperty('generated_conversions');
                $mediaItem->copy($article_request, 'image');
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.saved_successfully', ['operator' => __('lang.request')]));

        return redirect(route('requests.index'));
    }

    /**
     * Display the specified request.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $request = $this->articleRequestRepository->findWithoutFail($id);

        if (empty($request)) {
            Flash::error('request not found');

            return redirect(route('requests.index'));
        }

        return view('requests.show')->with('request', $request);
    }

    /**
     * Show the form for editing the specified request.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $request = $this->articleRequestRepository->findWithoutFail($id);
        $address = $this->addressRepository->pluck('address', 'id');
        $businessCategory = $this->businessCategoryRepository->pluck('name', 'id');
        $transactionStatus = $this->transactionStatusRepository->where('type' , 'request')->pluck('status', 'id');
        $user = $this->userRepository->pluck('name', 'id');
        if (empty($request)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.request')]));

            return redirect(route('requests.index'));
        }
        $customFieldsValues = $request->customFieldsValues()->with('customField')->get();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->articleRequestRepository->model());
        $hasCustomField = in_array($this->articleRequestRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        return view('requests.edit')->with('request', $request)->with("customFields", isset($html) ? $html : false)->with('address' , $address)->with('business_category' , $businessCategory)->with('transaction_status' , $transactionStatus)->with('user' , $user);
    }

    /**
     * Update the specified request in storage.
     *
     * @param int $id
     * @param UpdateArticleRequestRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateArticleRequestRequest $request)
    {
        $article_request_old = $this->articleRequestRepository->findWithoutFail($id);

        if (empty($article_request_old)) {
            Flash::error('request not found');
            return redirect(route('requests.index'));
        }
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->articleRequestRepository->model());
        try {
            $article_request = $this->articleRequestRepository->update($input, $id);

            if (isset($input['image']) && $input['image']) {
                $cacheUpload = $this->uploadRepository->getByUuid($input['image']);
                $mediaItem = $cacheUpload->getMedia('image')->first();
                $mediaItem = $mediaItem->forgetCustomProperty('generated_conversions');
                $mediaItem->copy($article_request, 'image');
            }
            foreach (getCustomFieldsValues($customFields, $article_request) as $value) {
                $article_request->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }
            if(isset($input['business_category_id']) && $input['business_category_id'] != $article_request_old->business_category_id){
                // rejected offer business owner notification
                $businesses = Business::where('business_category_id' , $input['business_category_id'])->get();
                foreach($businesses as $b){
                    $business = $this->businessRepository->find($b->id);
                    $notification = [
                        'title' => trans('lang.notification_new_request'),
                        'body' => trans('lang.notification_new_request_description', ['request_id' => $article_request->id, 'offer_status' => $article_request->status]),
                        'icon' => $business->hasMedia('image')?$business->getFirstMediaUrl('image', 'thumb'):asset('images/image_default.png'),
                        'click_action' => "FLUTTER_NOTIFICATION_CLICK",
                        'id' => 'App\\Notifications\\newRequest',
                        'status' => 'done',
                    ];
                    $data = $notification;
                    $data['requestId'] = $article_request->id;
                    foreach($business->users as $owner){
                        notify($data , $owner->id , trans('lang.notification_new_request'));
                    }
                }
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.updated_successfully', ['operator' => __('lang.request')]));

        return redirect(route('requests.index'));
    }

    /**
     * Remove the specified request from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $request = $this->articleRequestRepository->findWithoutFail($id);

        if (empty($request)) {
            Flash::error('request not found');

            return redirect(route('requests.index'));
        }

        $this->articleRequestRepository->delete($id);

        Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.request')]));

        return redirect(route('requests.index'));
    }

    /**
     * Remove Media of request
     * @param Request $request
     */
    public function removeMedia(Request $request)
    {
        $input = $request->all();
        $request = $this->articleRequestRepository->findWithoutFail($input['id']);
        try {
            if ($request->hasMedia($input['collection'])) {
                $request->getFirstMedia($input['collection'])->delete();
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
