<?php
/*
 * File name: CouponController.php
 * Last modified: 2022.02.03 at 18:14:47
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Http\Controllers;

use App\Criteria\Coupons\CouponsOfUserCriteria;
use App\Criteria\EServices\ArticleOfUserCriteria;
use App\Criteria\Salons\AcceptedCriteria;
use App\Criteria\Business\BusinessOfUserCriteria;
use App\DataTables\CouponDataTable;
use App\Http\Requests\CreateCouponRequest;
use App\Http\Requests\UpdateCouponRequest;
use App\Repositories\ArticleCategoryRepository;
use App\Repositories\CouponRepository;
use App\Repositories\CustomFieldRepository;
use App\Repositories\DiscountableRepository;
use App\Repositories\ArticleRepository;
use App\Repositories\BusinessRepository;
use Flash;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

class CouponController extends Controller
{
    /** @var  CouponRepository */
    private $couponRepository;

    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;

    /**
     * @var ArticleRepository
     */
    private $articleRepository;
    /**
     * @var BusinessRepository
     */
    private $businessRepository;
    /**
     * @var ArticleCategoryRepository
     */
    private $articleCategoryRepository;
    /**
     * @var DiscountableRepository
     */
    private $discountableRepository;

    public function __construct(CouponRepository $couponRepo, CustomFieldRepository $customFieldRepo, ArticleRepository $articleRepo
        , BusinessRepository                        $businessRepo
        , ArticleCategoryRepository                     $categoryRepo, DiscountableRepository $discountableRepository)
    {
        parent::__construct();
        $this->couponRepository = $couponRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->articleRepository = $articleRepo;
        $this->businessRepository = $businessRepo;
        $this->articleCategoryRepository = $categoryRepo;
        $this->discountableRepository = $discountableRepository;
    }

    /**
     * Display a listing of the Coupon.
     *
     * @param CouponDataTable $couponDataTable
     * @return Response
     */
    public function index(CouponDataTable $couponDataTable)
    {
        return $couponDataTable->render('coupons.index');
    }

    /**
     * Show the form for creating a new Coupon.
     *
     * @return Application|Factory|Response|View
     * @throws RepositoryException
     */
    public function create()
    {
        $this->articleRepository->pushCriteria(new ArticleOfUserCriteria(auth()->id()));
        $articles = $this->articleRepository->groupedBySalons();

        $this->businessRepository->pushCriteria(new BusinessOfUserCriteria(auth()->id()));
        $this->businessRepository->pushCriteria(new AcceptedCriteria());
        $business = $this->businessRepository->pluck('name', 'id');

        $articleCategory = $this->articleCategoryRepository->pluck('name', 'id');

        $articlesSelected = [];
        $businessesSelected = [];
        $articleCategoriesSelected = [];

        $hasCustomField = in_array($this->couponRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->couponRepository->model());
            $html = generateCustomField($customFields);
        }
        return view('coupons.create')->with("customFields", isset($html) ? $html : false)->with("articles", $articles)->with("business", $business)->with("articleCategory", $articleCategory)->with("articlesSelected", $articlesSelected)->with("businessesSelected", $businessesSelected)->with("articleCategoriesSelected", $articleCategoriesSelected);
    }

    /**
     * Store a newly created Coupon in storage.
     *
     * @param CreateCouponRequest $request
     *
     * @return Application|RedirectResponse|Redirector|Response
     */
    public function store(CreateCouponRequest $request)
    {
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->couponRepository->model());
        try {
            $coupon = $this->couponRepository->create($input);
            $discountables = $this->initDiscountables($input);
            $coupon->discountables()->createMany($discountables);
            $coupon->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));

        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.saved_successfully', ['operator' => __('lang.coupon')]));

        return redirect(route('coupons.index'));
    }

    /**
     * @param array $input
     * @return array
     */
    private function initDiscountables(array $input): array
    {
        $discountables = [];
        if (isset($input['articles'])) {
            foreach ($input['articles'] as $articleId) {
                $discountables[] = ["discountable_type" => "App\Models\Article", "discountable_id" => $articleId];
            }
        }
        if (isset($input['business'])) {
            foreach ($input['business'] as $businessId) {
                $discountables[] = ["discountable_type" => "App\Models\Business", "discountable_id" => $businessId];
            }
        }
        if (isset($input['articleCategories'])) {
            foreach ($input['articleCategories'] as $categoryId) {
                $discountables[] = ["discountable_type" => "App\Models\ArticleCategories", "discountable_id" => $categoryId];
            }
        }
        return $discountables;
    }

    /**
     * Display the specified Coupon.
     *
     * @param int $id
     *
     * @return Application|Factory|Response|View
     */
    public function show(int $id)
    {
        $coupon = $this->couponRepository->findWithoutFail($id);

        if (empty($coupon)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.coupon')]));

            return redirect(route('coupons.index'));
        }

        return view('coupons.show')->with('coupon', $coupon);
    }

    /**
     * Show the form for editing the specified Coupon.
     *
     * @param int $id
     *
     * @return Application|RedirectResponse|Redirector|Response
     * @throws RepositoryException
     */
    public function edit(int $id)
    {
        $this->couponRepository->pushCriteria(new CouponsOfUserCriteria(auth()->id()));

        $coupon = $this->couponRepository->all()->firstWhere('id', '=', $id);

        if (empty($coupon)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.coupon')]));

            return redirect(route('coupons.index'));
        }
        $this->articleRepository->pushCriteria(new ArticleOfUserCriteria(auth()->id()));
        $articles = $this->articleRepository->groupedBySalons();

        $this->businessRepository->pushCriteria(new BusinessOfUserCriteria(auth()->id()));
        $this->businessRepository->pushCriteria(new AcceptedCriteria());
        $business = $this->businessRepository->pluck('name', 'id');

        $articleCategory = $this->articleCategoryRepository->pluck('name', 'id');

        $articlesSelected = $coupon->discountables()->where("discountable_type", "App\Models\Article")->pluck('discountable_id');
        $businessesSelected = $coupon->discountables()->where("discountable_type", "App\Models\Business")->pluck('discountable_id');
        $articleCategoriesSelected = $coupon->discountables()->where("discountable_type", "App\Models\ArticleCategories")->pluck('discountable_id');

        $customFieldsValues = $coupon->customFieldsValues()->with('customField')->get();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->couponRepository->model());
        $hasCustomField = in_array($this->couponRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        return view('coupons.edit')->with('coupon', $coupon)->with("customFields", isset($html) ? $html : false)->with("articles", $articles)->with("business", $business)->with("articleCategory", $articleCategory)->with("articlesSelected", $articlesSelected)->with("businessesSelected", $businessesSelected)->with("articleCategoriesSelected", $articleCategoriesSelected);
    }

    /**
     * Update the specified Coupon in storage.
     *
     * @param int $id
     * @param UpdateCouponRequest $request
     *
     * @return Application|RedirectResponse|Redirector|Response
     * @throws RepositoryException
     */
    public function update(int $id, UpdateCouponRequest $request)
    {
        $this->couponRepository->pushCriteria(new CouponsOfUserCriteria(auth()->id()));

        $coupon = $this->couponRepository->all()->firstWhere('id', '=', $id);

        if (empty($coupon)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.coupon')]));
            return redirect(route('coupons.index'));
        }
        $input = $request->all();
        unset($input['code']);
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->couponRepository->model());
        try {
            $coupon = $this->couponRepository->update($input, $id);
            $discountables = $this->initDiscountables($input);
            $coupon->discountables()->delete();
            $coupon->discountables()->createMany($discountables);


            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $coupon->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.updated_successfully', ['operator' => __('lang.coupon')]));

        return redirect(route('coupons.index'));
    }

    /**
     * Remove the specified Coupon from storage.
     *
     * @param int $id
     *
     * @return Application|RedirectResponse|Redirector|Response
     */
    public function destroy(int $id)
    {
        $coupon = $this->couponRepository->findWithoutFail($id);

        if (empty($coupon)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.coupon')]));

            return redirect(route('coupons.index'));
        }

        $this->couponRepository->delete($id);

        Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.coupon')]));

        return redirect(route('coupons.index'));
    }
}
