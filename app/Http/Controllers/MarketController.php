<?php
/**
 * File name: MarketController.php
 * Last modified: 2020.04.30 at 08:21:08
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2020
 *
 */

namespace App\Http\Controllers;

use App\Criteria\Business\BusinessOfUserCriteria;
use App\Criteria\Addresses\AddressesOfUserCriteria;
use App\Criteria\SalonLevels\EnabledCriteria;
use App\Criteria\Users\AdminsCriteria;
use App\Criteria\Users\ClientsCriteria;
use App\Criteria\Users\DriversCriteria;
use App\Criteria\Users\ManagersClientsCriteria;
use App\Criteria\Users\ManagersCriteria;
use App\DataTables\MarketDataTable;
use App\Events\BusinessChangedEvent;
use App\Http\Requests\CreateMarketRequest;
use App\Http\Requests\UpdateMarketRequest;
use App\Repositories\CustomFieldRepository;
use App\Repositories\FieldRepository;
use App\Repositories\MarketRepository;
use App\Repositories\AddressRepository;
use App\Repositories\ModuleRepository;
use App\Repositories\BusinessCategoryRepository;
use App\Repositories\TaxRepository;
use App\Repositories\UploadRepository;
use App\Repositories\UserRepository;
use Flash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Prettus\Validator\Exceptions\ValidatorException;

class MarketController extends Controller
{
    /** @var  MarketRepository */
    private $marketRepository;

    /** @var  ModuleRepository */
    private $moduleRepository;

    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;

    /**
     * @var UploadRepository
     */
    private $uploadRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var FieldRepository
     */
    private $fieldRepository;
    /**
     * @var BusinessCategoryRepository
     */
    private $BusinessCategoryRepository;
    /**
     * @var AddressRepository
     */
    private $addressRepository;
    /**
     * @var TaxRepository
     */
    private $taxRepository;

    public function __construct(MarketRepository $marketRepo, CustomFieldRepository $customFieldRepo, UploadRepository $uploadRepo, UserRepository $userRepo, FieldRepository $fieldRepository
        , BusinessCategoryRepository                  $businessCatgoryRepo
        , AddressRepository                     $addressRepo
        , ModuleRepository                        $moduleRepo
        , TaxRepository                         $taxRepo)
    {
        parent::__construct();
        $this->marketRepository = $marketRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->moduleRepository = $moduleRepo;
        $this->uploadRepository = $uploadRepo;
        $this->userRepository = $userRepo;
        $this->fieldRepository = $fieldRepository;
        $this->businessCategoryRepository = $businessCatgoryRepo;
        $this->addressRepository = $addressRepo;
        $this->taxRepository = $taxRepo;
    }

    /**
     * Display a listing of the Market.
     *
     * @param MarketDataTable $marketDataTable
     * @return Response
     */
    public function index(MarketDataTable $marketDataTable)
    {
        return $marketDataTable->render('markets.index');
    }

    /**
     * Show the form for creating a new Market.
     *
     * @return Response
     */
    public function create()
    {
        $user = $this->userRepository->getByCriteria(new ManagersCriteria())->pluck('name', 'id');
        $drivers = $this->userRepository->getByCriteria(new DriversCriteria())->pluck('name', 'id');
        // $field = $this->fieldRepository->pluck('name', 'id');
        $businessCatgory = $this->businessCategoryRepository->getByCriteria(new EnabledCriteria())->pluck('name', 'id');
        $address = $this->addressRepository->getByCriteria(new AddressesOfUserCriteria(auth()->id()))->pluck('address', 'id');
        $tax = $this->taxRepository->pluck('name', 'id');
        $usersSelected = [];
        $taxesSelected = [];
        $driversSelected = [];
        $modules = $this->moduleRepository->pluck('name', 'id');
        $modulesSelected = [];
        // $fieldsSelected = [];
        $hasCustomField = in_array($this->marketRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->marketRepository->model());
            $html = generateCustomField($customFields);
        }
        return view('markets.create')->with("customFields", isset($html) ? $html : false)
        ->with("user", $user)->with("drivers", $drivers)
        ->with("usersSelected", $usersSelected)->with("modules",$modules)->with("modulesSelected",$modulesSelected)
        ->with("driversSelected", $driversSelected)->with("businessCatgory", $businessCatgory)->with("address", $address)->with("tax", $tax)->with("taxesSelected", $taxesSelected);
    }

    /**
     * Store a newly created Market in storage.
     *
     * @param CreateMarketRequest $request
     *
     * @return Response
     */
    public function store(CreateMarketRequest $request)
    {
        $input = $request->all();
        if (auth()->user()->hasRole(['manager','client'])) {
            $input['users'] = [auth()->id()];
        }
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->marketRepository->model());
        try {
            $market = $this->marketRepository->create($input);
            $market->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));
            if (isset($input['image']) && $input['image']) {
                $cacheUpload = $this->uploadRepository->getByUuid($input['image']);
                $mediaItem = $cacheUpload->getMedia('image')->first();
                $mediaItem->copy($market, 'image');
            }
            // event(new BusinessChangedEvent($market, $market));
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.saved_successfully', ['operator' => __('lang.market')]));

        return redirect(route('markets.index'));
    }

    /**
     * Display the specified Market.
     *
     * @param int $id
     *
     * @return Response
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function show($id)
    {
        $this->marketRepository->pushCriteria(new BusinessOfUserCriteria(auth()->id()));
        $market = $this->marketRepository->findWithoutFail($id);

        if (empty($market)) {
            Flash::error('Market not found');

            return redirect(route('markets.index'));
        }

        return view('markets.show')->with('market', $market);
    }

    /**
     * Show the form for editing the specified Market.
     *
     * @param int $id
     *
     * @return Response
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function edit($id)
    {
        $this->marketRepository->pushCriteria(new BusinessOfUserCriteria(auth()->id()));
        $market = $this->marketRepository->findWithoutFail($id);

        if (empty($market)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.market')]));
            return redirect(route('markets.index'));
        }
        if($market['active'] == 0){
            $user = $this->userRepository->getByCriteria(new ManagersClientsCriteria())->pluck('name', 'id');
        } else {
            $user = $this->userRepository->getByCriteria(new ManagersCriteria())->pluck('name', 'id');
        }
        //$user = $market->users();
        $businessCatgory = $this->businessCategoryRepository->getByCriteria(new EnabledCriteria())->pluck('name', 'id');
        $drivers = $this->userRepository->getByCriteria(new DriversCriteria())->pluck('name', 'id');
        // $field = $this->fieldRepository->pluck('name', 'id');
        $address = $this->addressRepository->getByCriteria(new AddressesOfUserCriteria(auth()->id()))->pluck('address', 'id');
        $tax = $this->taxRepository->pluck('name', 'id');
        $taxesSelected = $market->taxes()->pluck('taxes.id')->toArray();
        $usersSelected = $market->users()->pluck('users.id')->toArray();
        $driversSelected = $market->drivers()->pluck('users.id')->toArray();
        // $fieldsSelected = $market->fields()->pluck('fields.id')->toArray();

        $modules = $this->moduleRepository->pluck('name', 'id');
        $modulesSelected = $market->business_modules()->pluck('module_id')->toArray();

        $customFieldsValues = $market->customFieldsValues()->with('customField')->get();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->marketRepository->model());
        $hasCustomField = in_array($this->marketRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        return view('markets.edit')->with('market', $market)->with("customFields", isset($html) ? $html : false)->with("user", $user)->with("drivers", $drivers)
        ->with("usersSelected", $usersSelected)->with("driversSelected", $driversSelected)
        ->with("businessCatgory", $businessCatgory)->with("modules",$modules)->with("modulesSelected",$modulesSelected)
        ->with("address", $address)->with("tax", $tax)->with("taxesSelected", $taxesSelected);
    }

    /**
     * Update the specified Market in storage.
     *
     * @param int $id
     * @param UpdateMarketRequest $request
     *
     * @return Response
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function update($id, UpdateMarketRequest $request)
    {
        $this->marketRepository->pushCriteria(new BusinessOfUserCriteria(auth()->id()));
        $oldMarket = $this->marketRepository->findWithoutFail($id);

        if (empty($oldMarket)) {
            Flash::error('Market not found');
            return redirect(route('markets.index'));
        }
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->marketRepository->model());
        try {

            $market = $this->marketRepository->update($input, $id);
            if (isset($input['image']) && $input['image']) {
                $cacheUpload = $this->uploadRepository->getByUuid($input['image']);
                $mediaItem = $cacheUpload->getMedia('image')->first();
                $mediaItem->copy($market, 'image');
            }
            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $market->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }
            // event(new BusinessChangedEvent($market, $oldMarket));
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.updated_successfully', ['operator' => __('lang.market')]));

        return redirect(route('markets.index'));
    }

    /**
     * Remove the specified Market from storage.
     *
     * @param int $id
     *
     * @return Response
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function destroy($id)
    {
        if (!env('APP_DEMO', false)) {
            $this->marketRepository->pushCriteria(new BusinessOfUserCriteria(auth()->id()));
            $market = $this->marketRepository->findWithoutFail($id);

            if (empty($market)) {
                Flash::error('Market not found');

                return redirect(route('markets.index'));
            }

            $this->marketRepository->delete($id);

            Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.market')]));
        } else {
            Flash::warning('This is only demo app you can\'t change this section ');
        }
        return redirect(route('markets.index'));
    }

    /**
     * Remove Media of Market
     * @param Request $request
     */
    public function removeMedia(Request $request)
    {
        $input = $request->all();
        $market = $this->marketRepository->findWithoutFail($input['id']);
        try {
            if ($market->hasMedia($input['collection'])) {
                $market->getFirstMedia($input['collection'])->delete();
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
