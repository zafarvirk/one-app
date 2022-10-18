<?php
/*
 * File name: TransactionStatusController.php
 * Last modified: 2021.01.25 at 22:00:21
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */

namespace App\Http\Controllers;

use App\DataTables\TransactionStatusDataTable;
use App\Http\Requests\CreateTransactionStatusRequest;
use App\Http\Requests\UpdateTransactionStatusRequest;
use App\Repositories\TransactionStatusRepository;
use App\Repositories\CustomFieldRepository;
use Exception;
use Flash;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Prettus\Validator\Exceptions\ValidatorException;

class TransactionStatusController extends Controller
{
    /** @var  TransactionStatusRepository */
    private $transactionStatusRepository;

    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;



    public function __construct(TransactionStatusRepository $transactionStatusRepo, CustomFieldRepository $customFieldRepo )
    {
        parent::__construct();
        $this->transactionStatusRepository = $transactionStatusRepo;
        $this->customFieldRepository = $customFieldRepo;

    }

    /**
     * Display a listing of the TransactionStatus.
     *
     * @param TransactionStatusDataTable $transactionStatusDataTable
     * @return Response
     */
    public function index(TransactionStatusDataTable $transactionStatusDataTable)
    {
        return $transactionStatusDataTable->render('transaction_statuses.index');
    }

    /**
     * Show the form for creating a new TransactionStatus.
     *
     * @return Application|Factory|Response|View
     */
    public function create()
    {


        $hasCustomField = in_array($this->transactionStatusRepository->model(),setting('custom_field_models',[]));
            if($hasCustomField){
                $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->transactionStatusRepository->model());
                $html = generateCustomField($customFields);
            }
        return view('transaction_statuses.create')->with("customFields", isset($html) ? $html : false);
    }

    /**
     * Store a newly created TransactionStatus in storage.
     *
     * @param CreateTransactionStatusRequest $request
     *
     * @return Application|RedirectResponse|Redirector|Response
     */
    public function store(CreateTransactionStatusRequest $request)
    {
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->transactionStatusRepository->model());
        try {
            $transactionStatus = $this->transactionStatusRepository->create($input);
            $transactionStatus->customFieldsValues()->createMany(getCustomFieldsValues($customFields,$request));

        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.saved_successfully',['operator' => __('lang.transaction_status')]));

        return redirect(route('transactionStatuses.index'));
    }

    /**
     * Display the specified TransactionStatus.
     *
     * @param int $id
     *
     * @return Application|Factory|Response|View
     */
    public function show($id)
    {
        $transactionStatus = $this->transactionStatusRepository->findWithoutFail($id);

        if (empty($transactionStatus)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.transaction_status')]));
            return redirect(route('TransactionStatuses.index'));
        }
        return view('transaction_statuses.show')->with('TransactionStatus', $transactionStatus);
    }

    /**
     * Show the form for editing the specified TransactionStatus.
     *
     * @param int $id
     *
     * @return Application|RedirectResponse|Redirector|Response
     */
    public function edit($id)
    {
        $transactionStatus = $this->transactionStatusRepository->findWithoutFail($id);


        if (empty($transactionStatus)) {
            Flash::error(__('lang.not_found',['operator' => __('lang.transaction_status')]));

            return redirect(route('transactionStatuses.index'));
        }
        $customFieldsValues = $transactionStatus->customFieldsValues()->with('customField')->get();
        $customFields =  $this->customFieldRepository->findByField('custom_field_model', $this->transactionStatusRepository->model());
        $hasCustomField = in_array($this->transactionStatusRepository->model(),setting('custom_field_models',[]));
        if($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }
        return view('transaction_statuses.edit')->with('TransactionStatus', $transactionStatus)->with("customFields", isset($html) ? $html : false);
    }

    /**
     * Update the specified TransactionStatus in storage.
     *
     * @param int $id
     * @param UpdateTransactionStatusRequest $request
     *
     * @return Application|RedirectResponse|Redirector|Response
     */
    public function update($id, UpdateTransactionStatusRequest $request)
    {
        $transactionStatus = $this->transactionStatusRepository->findWithoutFail($id);

        if (empty($transactionStatus)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.transaction_status')]));
            return redirect(route('transactionStatuses.index'));
        }
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->transactionStatusRepository->model());
        try {
            $transactionStatus = $this->transactionStatusRepository->update($input, $id);


            foreach (getCustomFieldsValues($customFields, $request) as $value){
                $transactionStatus->customFieldsValues()
                    ->updateOrCreate(['custom_field_id'=>$value['custom_field_id']],$value);
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }
        Flash::success(__('lang.updated_successfully',['operator' => __('lang.transaction_status')]));
        return redirect(route('transactionStatuses.index'));
    }

    /**
     * Remove the specified TransactionStatus from storage.
     *
     * @param int $id
     *
     * @return Application|RedirectResponse|Redirector|Response
     */
    public function destroy($id)
    {
        $transactionStatus = $this->transactionStatusRepository->findWithoutFail($id);

        if (empty($transactionStatus)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.transaction_status')]));

            return redirect(route('transactionStatuses.index'));
        }

        $this->transactionStatusRepository->delete($id);

        Flash::success(__('lang.deleted_successfully',['operator' => __('lang.transaction_status')]));
        return redirect(route('transactionStatuses.index'));
    }

        /**
     * Remove Media of TransactionStatus
     * @param Request $request
     */
    public function removeMedia(Request $request)
    {
        $input = $request->all();
        $transactionStatus = $this->transactionStatusRepository->findWithoutFail($input['id']);
        try {
            if ($transactionStatus->hasMedia($input['collection'])) {
                $transactionStatus->getFirstMedia($input['collection'])->delete();
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

}
