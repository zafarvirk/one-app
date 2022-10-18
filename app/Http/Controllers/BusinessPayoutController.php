<?php
/*
 * File name: BusinessPayoutController.php
 * Last modified: 2022.02.02 at 21:22:03
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Http\Controllers;

use App\Criteria\Business\BusinessOfUserCriteria;
use App\DataTables\BusinessPayoutDataTable;
use App\Http\Requests\CreateBusinessPayoutRequest;
use App\Repositories\CustomFieldRepository;
use App\Repositories\EarningRepository;
use App\Repositories\BusinessPayoutRepository;
use App\Repositories\SalonRepository;
use Carbon\Carbon;
use Flash;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

class BusinessPayoutController extends Controller
{
    /** @var  BusinessPayoutRepository */
    private $businessPayoutRepository;

    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;

    /**
     * @var SalonRepository
     */
    private $SalonRepository;
    /**
     * @var EarningRepository
     */
    private $earningRepository;

    public function __construct(BusinessPayoutRepository $salonPayoutRepo, CustomFieldRepository $customFieldRepo, SalonRepository $salonRepo, EarningRepository $earningRepository)
    {
        parent::__construct();
        $this->businessPayoutRepository = $salonPayoutRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->SalonRepository = $salonRepo;
        $this->earningRepository = $earningRepository;
    }

    /**
     * Display a listing of the BusinessPayout.
     *
     * @param BusinessPayoutDataTable $salonPayoutDataTable
     * @return Response
     */
    public function index(BusinessPayoutDataTable $salonPayoutDataTable)
    {
        return $salonPayoutDataTable->render('salon_payouts.index');
    }

    /**
     * Show the form for creating a new BusinessPayout.
     *
     * @param int $id
     * @return Application|Factory|Response|View
     * @throws RepositoryException
     */
    public function create(int $id)
    {
        $this->SalonRepository->pushCriteria(new BusinessOfUserCriteria(auth()->id()));
        $salon = $this->SalonRepository->findWithoutFail($id);
        if (empty($salon)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.salon')]));
            return redirect(route('salonPayouts.index'));
        }
        $earning = $this->earningRepository->findByField('salon_id', $id)->first();
        $totalPayout = $this->businessPayoutRepository->findByField('salon_id', $id)->sum("amount");

        $hasCustomField = in_array($this->businessPayoutRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->businessPayoutRepository->model());
            $html = generateCustomField($customFields);
        }
        return view('salon_payouts.create')->with("customFields", isset($html) ? $html : false)->with("salon", $salon)->with("amount", $earning->salon_earning - $totalPayout);
    }

    /**
     * Store a newly created BusinessPayout in storage.
     *
     * @param CreateBusinessPayoutRequest $request
     *
     * @return Application|RedirectResponse|Redirector|Response
     */
    public function store(CreateBusinessPayoutRequest $request)
    {
        $input = $request->all();
        $earning = $this->earningRepository->findByField('salon_id', $input['salon_id'])->first();
        $totalPayout = $this->businessPayoutRepository->findByField('salon_id', $input['salon_id'])->sum("amount");
        $input['amount'] = $earning->salon_earning - $totalPayout;
        if ($input['amount'] <= 0) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.earning')]));
            return redirect(route('salonPayouts.index'));
        }
        $input['paid_date'] = Carbon::now();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->businessPayoutRepository->model());
        try {
            $salonPayout = $this->businessPayoutRepository->create($input);
            $salonPayout->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));

        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.saved_successfully', ['operator' => __('lang.salon_payout')]));

        return redirect(route('salonPayouts.index'));
    }
}
