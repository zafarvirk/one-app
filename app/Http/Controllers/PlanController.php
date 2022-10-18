<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Criteria\EServices\ServiceOfUserCriteria;
use App\Criteria\Business\BusinessOfUserCriteria;
use App\DataTables\PlanDataTable;
use App\Repositories\PlanRepository;
use App\Repositories\ServiceRepository;
use App\Repositories\BusinessRepository;
use App\Repositories\CustomFieldRepository;
use Flash;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

class PlanController extends Controller
{
    /** @var  PlanRepository */
    private $PlanRepository;

    /**
      * @var CustomFieldRepository
    */
    private $customFieldRepository;

    /**
      * @var ServiceRepository
    */
    private $ServiceRepository;
    /**
     * @var BusinessRepository
     */
    private $businessRepository;

    public function __construct(PlanRepository $planRepo, CustomFieldRepository $customFieldRepo, ServiceRepository $serviceRepo, BusinessRepository $businessRepo)
    {
        parent::__construct();
        $this->planRepository = $planRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->serviceRepository = $serviceRepo;
        $this->businessRepository = $businessRepo;
    }

    /**
     * Display a listing of the Aminities.
     *
     * @param PlanDataTable $planDataTable
     * @return mixed
    */

    public function index(PlanDataTable $planDataTable)
    {
        return $planDataTable->render('plans.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $hasCustomField = in_array($this->planRepository->model(), setting('custom_field_models', []));
        $business = $this->businessRepository->getByCriteria(new BusinessOfUserCriteria(auth()->id()))->pluck('name', 'id');
        $article = $this->serviceRepository->getByCriteria(new ServiceOfUserCriteria(auth()->id()))->pluck('name', 'id');
        $articleSelected = [];
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->planRepository->model());
            $html = generateCustomField($customFields);
        }
        return view('plans.create')->with("customFields", isset($html) ? $html : false)->with("articleSelected",$articleSelected)->with("article", $article)->with("business", $business);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->planRepository->model());
        try {
            $plans = $this->planRepository->create($input);
            $plans->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.saved_successfully', ['operator' => __('lang.plans')]));

        return redirect(route('plans.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $plans = $this->planRepository->findWithoutFail($id);
        if (empty($plans)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.plans')]));

            return redirect(route('plans.index'));
        }
        $business = $this->businessRepository->getByCriteria(new BusinessOfUserCriteria(auth()->id()))->pluck('name', 'id');
        $article = $this->serviceRepository->getByCriteria(new ServiceOfUserCriteria(auth()->id()))->pluck('name', 'id');
        $articleSelected = $plans->plans_article()->pluck('article_id')->toArray();

        $customFieldsValues = $plans->customFieldsValues()->with('customField')->get();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->planRepository->model());
        $hasCustomField = in_array($this->planRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        return view('plans.edit')->with("plans", $plans)->with("customFields", isset($html) ? $html : false)->with("article",$article)->with("articleSelected",$articleSelected)->with("business", $business);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $plans = $this->planRepository->findWithoutFail($id);

        if (empty($plans)) {
            Flash::error('Plans not found');
            return redirect(route('plans.index'));
        }

        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->planRepository->model());
        try {
            $plans = $this->planRepository->update($input, $id);
            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $plans->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.updated_successfully', ['operator' => __('lang.plans')]));

        return redirect(route('plans.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        $plans = $this->planRepository->findWithoutFail($id);

        if (empty($plans)) {
            Flash::error('Plans not found');

            return redirect(route('plans.index'));
        }

        $this->planRepository->delete($id);

        Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.plans')]));

        return redirect(route('plans.index'));
    }
}
