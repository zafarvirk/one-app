<?php
/*
 * File name: AddressAPIController.php
 * Last modified: 2021.02.18 at 12:08:19
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */

namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use App\Criteria\Addresses\AddressesOfUserCriteria;
use App\Models\Address;
use App\Repositories\AddressRepository;
use App\Repositories\CustomFieldRepository;
use App\Http\Requests\CreateAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class AddressController
 * @package App\Http\Controllers\API
 */
class AddressAPIController extends Controller
{
    /** @var  AddressRepository */
    private $addressRepository;

    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;

    public function __construct(AddressRepository $addressRepo, CustomFieldRepository $customFieldRepo)
    {
        $this->addressRepository = $addressRepo;
        $this->customFieldRepository = $customFieldRepo;
    }

    /**
     * Display a listing of the Address.
     * GET|HEAD /addresses
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $this->addressRepository->pushCriteria(new RequestCriteria($request));
            $this->addressRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $addresses = $this->addressRepository->all();
        $this->filterCollection($request, $addresses);

        return $this->sendResponse($addresses->toArray(), __('lang.saved_successfully', ['operator' => __('lang.address')]));
    }

    /**
     * Display the specified Address.
     * GET|HEAD /addresses/{id}
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function show($id)
    {
        /** @var Address $address */
        if (!empty($this->addressRepository)) {
            $address = $this->addressRepository->findWithoutFail($id);
        }

        if (empty($address)) {
            return $this->sendError('Address not found');
        }

        return $this->sendResponse($address->toArray(), 'Address retrieved successfully');
    }

    /**
     * Store a newly created Address in storage.
     *
     * @param CreateAddressRequest $request
     *
     * @return Application|RedirectResponse|Redirector|Response
     */
    public function store(CreateAddressRequest $request)
    {
        $input = $request->all();
        $input['user_id'] = auth()->user()->id;
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->addressRepository->model());
        try {
            $address = $this->addressRepository->create($input);
            $address->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));

        } catch (ValidatorException $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($address, 'Address created successfully');
    }

       /**
     * Update the specified Address in storage.
     *
     * @param int $id
     * @param UpdateAddressRequest $request
     *
     * @return Application|RedirectResponse|Redirector|Response
     * @throws RepositoryException
     */
    public function update(int $id, UpdateAddressRequest $request)
    {
        $this->addressRepository->pushCriteria(new AddressesOfUserCriteria(auth()->id()));
        $address = $this->addressRepository->findWithoutFail($id);

        if (empty($address)) {
            Flash::error('Address not found');
            return redirect(route('addresses.index'));
        }
        $input = $request->all();
        $input['user_id'] = $address->user->id;
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->addressRepository->model());
        try {
            $address = $this->addressRepository->update($input, $id);


            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $address->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }
        } catch (ValidatorException $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($address, 'Address updated successfully');
    }

     /**
     * Remove the specified Address from storage.
     *
     * @param int $id
     *
     * @return Application|RedirectResponse|Redirector|Response
     * @throws RepositoryException
     */
    public function destroy(int $id)
    {
        $this->addressRepository->pushCriteria(new AddressesOfUserCriteria(auth()->id()));
        $address = $this->addressRepository->findWithoutFail($id);

        if (empty($address)) {
            return $this->sendError('Address not found');
        }

        $this->addressRepository->delete($id);

        return $this->sendResponse('' , 'Address deleted successfully');
    }

}
