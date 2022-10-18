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
use App\Criteria\Users\GymCustomersCriteria;
use App\DataTables\GymDataTable;
use App\Events\BusinessChangedEvent;
use App\Http\Requests\CreateGymRequest;
use App\Http\Requests\UpdateGymRequest;
use App\Repositories\AddressRepository;
use App\Repositories\CustomFieldRepository;
use App\Repositories\BusinessCategoryRepository;
use App\Repositories\GymRepository;
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

class GymController extends Controller
{
    /** @var  GymRepository */
    private $gymRepository;

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

    public function __construct(GymRepository $gymRepo, CustomFieldRepository $customFieldRepo, UploadRepository $uploadRepo
        , BusinessCategoryRepository                  $businessCatgoryRepo
        , UserRepository                        $userRepo
        , ModuleRepository                        $moduleRepo
        , AddressRepository                     $addressRepo
        , TaxRepository                         $taxRepo)
    {
        parent::__construct();
        $this->gymRepository = $gymRepo;
        $this->moduleRepository = $moduleRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->uploadRepository = $uploadRepo;
        $this->businessCategoryRepository = $businessCatgoryRepo;
        $this->userRepository = $userRepo;
        $this->addressRepository = $addressRepo;
        $this->taxRepository = $taxRepo;
    }

    /**
     * Display a listing of the Gym.
     *
     * @param GymDataTable $gymDataTable
     * @return mixed
     */
    public function index(GymDataTable $gymDataTable)
    {
        return $gymDataTable->render('gyms.index');
    }

    /**
     * Show the form for creating a new Salon.
     *
     * @return Application|Factory|Response|View
     */
    public function create()
    {
        $businessCatgory = $this->businessCategoryRepository->getByCriteria(new EnabledCriteria())->pluck('name', 'id');
        $user = $this->userRepository->getByCriteria(new GymCustomersCriteria())->pluck('name', 'id');
        $address = $this->addressRepository->getByCriteria(new AddressesOfUserCriteria(auth()->id()))->pluck('address', 'id');
        $modules = $this->moduleRepository->pluck('name', 'id');
        $modulesSelected = [];
        $tax = $this->taxRepository->pluck('name', 'id');
        $usersSelected = [];
        $taxesSelected = [];
        $hasCustomField = in_array($this->gymRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->gymRepository->model());
            $html = generateCustomField($customFields);
        }
        return view('gyms.create')->with("customFields", isset($html) ? $html : false)->with("businessCatgory", $businessCatgory)->with("user", $user)->with("usersSelected", $usersSelected)->with("address", $address)->with("tax", $tax)->with("taxesSelected", $taxesSelected)->with("modules", $modules)->with("modulesSelected",$modulesSelected);
    }

    /**
     * Store a newly created Salon in storage.
     *
     * @param CreateGymRequest $request
     *
     * @return Application|RedirectResponse|Redirector|Response
     */
    public function store(CreateGymRequest $request)
    {
        $input = $request->all();
        if (auth()->user()->hasRole(['provider', 'customer'])) {
            $input['users'] = [auth()->id()];
        }
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->gymRepository->model());
        try {
            $gym = $this->gymRepository->create($input);
            $gym->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));
            if (isset($input['image']) && $input['image'] && is_array($input['image'])) {
                foreach ($input['image'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                    $mediaItem = $cacheUpload->getMedia('image')->first();
                    $mediaItem->copy($gym, 'image');
                }
            }
            // event(new BusinessChangedEvent($gym, $gym));
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.saved_successfully', ['operator' => __('lang.gym')]));

        return redirect(route('gyms.index'));
    }

    /**
     * Display the specified gym.
     *
     * @param int $id
     *
     * @return Application|RedirectResponse|Redirector|Response
     * @throws RepositoryException
     */
    public function show(int $id)
    {
        $this->gymRepository->pushCriteria(new BusinessOfUserCriteria(auth()->id()));
        $gym = $this->gymRepository->findWithoutFail($id);

        if (empty($gym)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.gym')]));

            return redirect(route('gyms.index'));
        }

        return view('gyms.show')->with('gym', $gym);
    }

    /**
     * Show the form for editing the specified gym.
     *
     * @param int $id
     *
     * @return Application|RedirectResponse|Redirector|Response
     * @throws RepositoryException
     */
    public function edit(int $id)
    {
        $this->gymRepository->pushCriteria(new BusinessOfUserCriteria(auth()->id()));
        $gym = $this->gymRepository->findWithoutFail($id);
        if (empty($gym)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.gym')]));
            return redirect(route('gyms.index'));
        }
        $businessCatgory = $this->businessCategoryRepository->getByCriteria(new EnabledCriteria())->pluck('name', 'id');
        $user = $this->userRepository->getByCriteria(new GymCustomersCriteria())->pluck('name', 'id');
        $address = $this->addressRepository->getByCriteria(new AddressesOfUserCriteria(auth()->id()))->pluck('address', 'id');
        $tax = $this->taxRepository->pluck('name', 'id');
        $usersSelected = $gym->users()->pluck('users.id')->toArray();
        $taxesSelected = $gym->taxes()->pluck('taxes.id')->toArray();

        $modules = $this->moduleRepository->pluck('name', 'id');
        $modulesSelected = $gym->business_modules()->pluck('module_id')->toArray();

        $customFieldsValues = $gym->customFieldsValues()->with('customField')->get();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->gymRepository->model());
        $hasCustomField = in_array($this->gymRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        return view('gyms.edit')->with('gym', $gym)->with("customFields", isset($html) ? $html : false)->with("businessCatgory", $businessCatgory)->with("user", $user)->with("usersSelected", $usersSelected)->with("address", $address)->with("tax", $tax)->with("taxesSelected", $taxesSelected)->with("modules",$modules)->with("modulesSelected",$modulesSelected);
    }

    /**
     * Update the specified gym in storage.
     *
     * @param int $id
     * @param UpdateGymRequest $request
     *
     * @return Application|RedirectResponse|Redirector|Response
     * @throws RepositoryException
     */
    public function update(int $id, UpdateGymRequest $request)
    {
        $this->gymRepository->pushCriteria(new BusinessOfUserCriteria(auth()->id()));
        $oldgym = $this->gymRepository->findWithoutFail($id);

        if (empty($oldgym)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.gym')]));
            return redirect(route('gyms.index'));
        }
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->gymRepository->model());
        try {
            $input['users'] = isset($input['users']) ? $input['users'] : [];
            $input['taxes'] = isset($input['taxes']) ? $input['taxes'] : [];
            $gym = $this->gymRepository->update($input, $id);
            if (isset($input['image']) && $input['image'] && is_array($input['image'])) {
                foreach ($input['image'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                    $mediaItem = $cacheUpload->getMedia('image')->first();
                    $mediaItem->copy($gym, 'image');
                }
            }
            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $gym->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }
            // event(new BusinessChangedEvent($gym, $oldgym));
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.updated_successfully', ['operator' => __('lang.gym')]));

        return redirect(route('gyms.index'));
    }

    /**
     * Remove the specified gym from storage.
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
            return redirect(route('gyms.index'));
        }
        $this->gymRepository->pushCriteria(new BusinessOfUserCriteria(auth()->id()));
        $gym = $this->gymRepository->findWithoutFail($id);

        if (empty($gym)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.gym')]));

            return redirect(route('gyms.index'));
        }

        $this->gymRepository->delete($id);

        Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.gym')]));

        return redirect(route('gyms.index'));
    }

    /**
     * Remove Media of gym
     * @param Request $request
     */
    public function removeMedia(Request $request)
    {
        $input = $request->all();
        $gym = $this->gymRepository->findWithoutFail($input['id']);
        try {
            if ($gym->hasMedia($input['collection'])) {
                $gym->getFirstMedia($input['collection'])->delete();
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
