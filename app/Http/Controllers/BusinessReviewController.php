<?php
/*
 * File name: BusinessReviewController.php
 * Last modified: 2022.02.12 at 02:17:42
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Http\Controllers;

use App\Criteria\BusinessReviews\BusinessReviewsOfUserCriteria;
use App\Criteria\Users\SalonsCriteria;
use App\DataTables\BusinessReviewDataTable;
use App\Http\Requests\CreateBusinessReviewRequest;
use App\Http\Requests\UpdateBusinessReviewRequest;
use App\Repositories\CustomFieldRepository;
use App\Repositories\BusinessReviewRepository;
use App\Repositories\UserRepository;
use Flash;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

class BusinessReviewController extends Controller
{
    /** @var  BusinessReviewRepository */
    private $businessReviewRepository;

    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;


    public function __construct(BusinessReviewRepository $businessReviewRepo, CustomFieldRepository $customFieldRepo)
    {
        parent::__construct();
        $this->businessReviewRepository = $businessReviewRepo;
        $this->customFieldRepository = $customFieldRepo;
    }

    /**
     * Display a listing of the BusinessReview.
     *
     * @param BusinessReviewDataTable $businessReviewDataTable
     * @return Response
     */
    public function index(BusinessReviewDataTable $businessReviewDataTable)
    {
        return $businessReviewDataTable->render('business_reviews.index');
    }

    /**
     * Store a newly created BusinessReview in storage.
     *
     * @param CreateBusinessReviewRequest $request
     *
     * @return Application|Redirector|RedirectResponse
     */
    public function store(CreateBusinessReviewRequest $request)
    {
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->businessReviewRepository->model());
        try {
            $businessReview = $this->businessReviewRepository->create($input);
            $businessReview->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));

        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.saved_successfully', ['operator' => __('lang.business_review')]));

        return redirect(route('businessReviews.index'));
    }

    /**
     * Display the specified BusinessReview.
     *
     * @param int $id
     *
     * @return Application|Factory|Redirector|RedirectResponse|View
     * @throws RepositoryException
     */
    public function show(int $id)
    {
        $this->businessReviewRepository->pushCriteria(new BusinessReviewsOfUserCriteria(auth()->id()));
        $businessReview = $this->businessReviewRepository->findWithoutFail($id);

        if (empty($businessReview)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.business_review')]));
            return redirect(route('businessReviews.index'));
        }
        return view('business_reviews.show')->with('businessReviews', $businessReview);
    }

    /**
     * Show the form for editing the specified BusinessReview.
     *
     * @param int $id
     *
     * @return Application|Factory|Redirector|RedirectResponse|View
     * @throws RepositoryException
     */
    public function edit(int $id)
    {
        $this->businessReviewRepository->pushCriteria(new BusinessReviewsOfUserCriteria(auth()->id()));
        $businessReview = $this->businessReviewRepository->findWithoutFail($id);
        if (empty($businessReview)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.business_review')]));
            return redirect(route('businessReviews.index'));
        }

        $customFieldsValues = $businessReview->customFieldsValues()->with('customField')->get();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->businessReviewRepository->model());
        $hasCustomField = in_array($this->businessReviewRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }
        return view('business_reviews.edit')->with('businessReviews', $businessReview)->with("customFields", $html ?? false);
    }

    /**
     * Update the specified BusinessReview in storage.
     *
     * @param int $id
     * @param UpdateBusinessReviewRequest $request
     *
     * @return Application|Redirector|RedirectResponse
     * @throws RepositoryException
     */
    public function update(int $id, UpdateBusinessReviewRequest $request)
    {
        $this->businessReviewRepository->pushCriteria(new BusinessReviewsOfUserCriteria(auth()->id()));
        $businessReview = $this->businessReviewRepository->findWithoutFail($id);

        if (empty($businessReview)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.business_review')]));
            return redirect(route('businessReviews.index'));
        }
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->businessReviewRepository->model());
        try {
            $businessReview = $this->businessReviewRepository->update($input, $id);

            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $businessReview->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }
        Flash::success(__('lang.updated_successfully', ['operator' => __('lang.business_review')]));
        return redirect(route('businessReviews.index'));
    }

    /**
     * Remove the specified BusinessReview from storage.
     *
     * @param int $id
     *
     * @return Application|Redirector|RedirectResponse
     * @throws RepositoryException
     */
    public function destroy(int $id)
    {
        $this->businessReviewRepository->pushCriteria(new BusinessReviewsOfUserCriteria(auth()->id()));
        $businessReview = $this->businessReviewRepository->findWithoutFail($id);

        if (empty($businessReview)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.business_review')]));
            return redirect(route('businessReviews.index'));
        }

        $this->businessReviewRepository->delete($id);

        Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.business_review')]));
        return redirect(route('businessReviews.index'));
    }

}
