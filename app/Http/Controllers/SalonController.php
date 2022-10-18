<?php
/*
 * File name: SalonController.php
 * Last modified: 2022.02.12 at 02:17:42
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Http\Controllers;

use App\Criteria\Addresses\AddressesOfUserCriteria;
use App\Criteria\SalonLevels\EnabledCriteria;
use App\Criteria\Business\BusinessOfUserCriteria;
use App\Criteria\Users\SalonsCustomersCriteria;
use App\DataTables\RequestedSalonDataTable;
use App\DataTables\SalonDataTable;
use App\Events\BusinessChangedEvent;
use App\Http\Requests\CreateSalonRequest;
use App\Http\Requests\UpdateSalonRequest;
use App\Repositories\AddressRepository;
use App\Repositories\CustomFieldRepository;
use App\Repositories\BusinessCategoryRepository;
use App\Repositories\SalonRepository;
use App\Repositories\TaxRepository;
use App\Repositories\UploadRepository;
use App\Repositories\UserRepository;
use App\Repositories\ModuleRepository;
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
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

class SalonController extends Controller
{
    /** @var  SalonRepository */
    private $SalonRepository;

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
     * @var BusinessCategoryRepository
     */
    private $BusinessCategoryRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var AddressRepository
     */
    private $addressRepository;
    /**
     * @var TaxRepository
     */
    private $taxRepository;

    public function __construct(SalonRepository $salonRepo, CustomFieldRepository $customFieldRepo, UploadRepository $uploadRepo
        , BusinessCategoryRepository                  $businessCatgoryRepo
        , UserRepository                        $userRepo
        , ModuleRepository                        $moduleRepo
        , AddressRepository                     $addressRepo
        , TaxRepository                         $taxRepo)
    {
        parent::__construct();
        $this->SalonRepository = $salonRepo;
        $this->moduleRepository = $moduleRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->uploadRepository = $uploadRepo;
        $this->businessCategoryRepository = $businessCatgoryRepo;
        $this->userRepository = $userRepo;
        $this->addressRepository = $addressRepo;
        $this->taxRepository = $taxRepo;
    }

    /**
     * Display a listing of the Salon.
     *
     * @param SalonDataTable $SalonDataTable
     * @return mixed
     */
    public function index(SalonDataTable $SalonDataTable)
    {
        return $SalonDataTable->render('salons.index');
    }

    /**
     * Display a listing of the Salon.
     *
     * @param SalonDataTable $SalonDataTable
     * @return mixed
     */
    public function requestedSalons(RequestedSalonDataTable $requestedSalonDataTable)
    {
        return $requestedSalonDataTable->render('salons.requested');
    }

    /**
     * Show the form for creating a new Salon.
     *
     * @return Application|Factory|Response|View
     */
    public function create()
    {
        $businessCatgory = $this->businessCategoryRepository->getByCriteria(new EnabledCriteria())->pluck('name', 'id');
        $user = $this->userRepository->getByCriteria(new SalonsCustomersCriteria())->pluck('name', 'id');
        $address = $this->addressRepository->getByCriteria(new AddressesOfUserCriteria(auth()->id()))->pluck('address', 'id');
        $modules = $this->moduleRepository->pluck('name', 'id');
        $modulesSelected = [];
        $tax = $this->taxRepository->pluck('name', 'id');
        $usersSelected = [];
        $taxesSelected = [];
        $hasCustomField = in_array($this->SalonRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->SalonRepository->model());
            $html = generateCustomField($customFields);
        }
        return view('salons.create')->with("customFields", isset($html) ? $html : false)->with("businessCatgory", $businessCatgory)->with("user", $user)->with("usersSelected", $usersSelected)->with("address", $address)->with("tax", $tax)->with("taxesSelected", $taxesSelected)->with("modules", $modules)->with("modulesSelected",$modulesSelected);
    }

    /**
     * Store a newly created Salon in storage.
     *
     * @param CreateSalonRequest $request
     *
     * @return Application|RedirectResponse|Redirector|Response
     */
    public function store(CreateSalonRequest $request)
    {
        $input = $request->all();
        if (auth()->user()->hasRole(['provider', 'customer'])) {
            $input['users'] = [auth()->id()];
        }
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->SalonRepository->model());
        try {
            $salon = $this->SalonRepository->create($input);
            $salon->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));
            if (isset($input['image']) && $input['image'] && is_array($input['image'])) {
                foreach ($input['image'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                    $mediaItem = $cacheUpload->getMedia('image')->first();
                    $mediaItem->copy($salon, 'image');
                }
            }
            // event(new BusinessChangedEvent($salon, $salon));
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.saved_successfully', ['operator' => __('lang.salon')]));

        return redirect(route('salons.index'));
    }

    /**
     * Display the specified Salon.
     *
     * @param int $id
     *
     * @return Application|RedirectResponse|Redirector|Response
     * @throws RepositoryException
     */
    public function show(int $id)
    {
        $this->SalonRepository->pushCriteria(new BusinessOfUserCriteria(auth()->id()));
        $salon = $this->SalonRepository->findWithoutFail($id);

        if (empty($salon)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.salon')]));

            return redirect(route('salons.index'));
        }

        return view('salons.show')->with('salon', $salon);
    }

    /**
     * Show the form for editing the specified Salon.
     *
     * @param int $id
     *
     * @return Application|RedirectResponse|Redirector|Response
     * @throws RepositoryException
     */
    public function edit(int $id)
    {
        $this->SalonRepository->pushCriteria(new BusinessOfUserCriteria(auth()->id()));
        $salon = $this->SalonRepository->findWithoutFail($id);
        if (empty($salon)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.salon')]));
            return redirect(route('salons.index'));
        }
        $businessCatgory = $this->businessCategoryRepository->getByCriteria(new EnabledCriteria())->pluck('name', 'id');
        $user = $this->userRepository->getByCriteria(new SalonsCustomersCriteria())->pluck('name', 'id');
        $address = $this->addressRepository->getByCriteria(new AddressesOfUserCriteria(auth()->id()))->pluck('address', 'id');
        $tax = $this->taxRepository->pluck('name', 'id');
        $usersSelected = $salon->users()->pluck('users.id')->toArray();
        $taxesSelected = $salon->taxes()->pluck('taxes.id')->toArray();

        $modules = $this->moduleRepository->pluck('name', 'id');
        $modulesSelected = $salon->business_modules()->pluck('module_id')->toArray();

        $customFieldsValues = $salon->customFieldsValues()->with('customField')->get();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->SalonRepository->model());
        $hasCustomField = in_array($this->SalonRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        return view('salons.edit')->with('salon', $salon)->with("customFields", isset($html) ? $html : false)->with("businessCatgory", $businessCatgory)->with("user", $user)->with("usersSelected", $usersSelected)->with("address", $address)->with("tax", $tax)->with("taxesSelected", $taxesSelected)->with("modules",$modules)->with("modulesSelected",$modulesSelected);
    }

    /**
     * Update the specified Salon in storage.
     *
     * @param int $id
     * @param UpdateSalonRequest $request
     *
     * @return Application|RedirectResponse|Redirector|Response
     * @throws RepositoryException
     */
    public function update(int $id, UpdateSalonRequest $request)
    {
        $this->SalonRepository->pushCriteria(new BusinessOfUserCriteria(auth()->id()));
        $oldSalon = $this->SalonRepository->findWithoutFail($id);

        if (empty($oldSalon)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.salon')]));
            return redirect(route('salons.index'));
        }
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->SalonRepository->model());
        try {
            $input['users'] = isset($input['users']) ? $input['users'] : [];
            $input['taxes'] = isset($input['taxes']) ? $input['taxes'] : [];
            $salon = $this->SalonRepository->update($input, $id);
            if (isset($input['image']) && $input['image'] && is_array($input['image'])) {
                foreach ($input['image'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                    $mediaItem = $cacheUpload->getMedia('image')->first();
                    $mediaItem->copy($salon, 'image');
                }
            }
            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $salon->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }
            // event(new BusinessChangedEvent($salon, $oldSalon));
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.updated_successfully', ['operator' => __('lang.salon')]));

        return redirect(route('salons.index'));
    }

    /**
     * Remove the specified Salon from storage.
     *
     * @param int $id
     *
     * @return Application|RedirectResponse|Redirector|Response
     * @throws RepositoryException
     */
    public function destroy(int $id)
    {
        if (config('installer.demo_app')) {
            Flash::warning('This is only demo app you can\'t change this section ');
            return redirect(route('salons.index'));
        }
        $this->SalonRepository->pushCriteria(new BusinessOfUserCriteria(auth()->id()));
        $salon = $this->SalonRepository->findWithoutFail($id);

        if (empty($salon)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.salon')]));

            return redirect(route('salons.index'));
        }

        $this->SalonRepository->delete($id);

        Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.salon')]));

        return redirect(route('salons.index'));
    }

    /**
     * Remove Media of Salon
     * @param Request $request
     */
    public function removeMedia(Request $request)
    {
        $input = $request->all();
        $salon = $this->SalonRepository->findWithoutFail($input['id']);
        try {
            if ($salon->hasMedia($input['collection'])) {
                $salon->getFirstMedia($input['collection'])->delete();
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
