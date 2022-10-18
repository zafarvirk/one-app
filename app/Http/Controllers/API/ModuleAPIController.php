<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\ModuleRepository;
use App\Repositories\CustomFieldRepository;
use Illuminate\Http\JsonResponse;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;

class ModuleAPIController extends Controller
{
    /** @var  ModuleRepository */
    private $moduleRepository;

    /**
    * @var CustomFieldRepository
    */
    private $customFieldRepository;

    public function __construct(ModuleRepository $moduleRepo, CustomFieldRepository $customFieldRepo)
    {
        parent::__construct();
        $this->moduleRepository = $moduleRepo;
        $this->customFieldRepository = $customFieldRepo;
    }

    /**
     * Display a listing of the Salon.
     * GET|HEAD /salons
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $modules = $this->moduleRepository->all();
        $this->filterCollection($request, $modules);

        return $this->sendResponse($modules->toArray(), 'Modules retrieved successfully');

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $input = $request->all();
            $modules = $this->moduleRepository->create($input);
        }catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($modules->toArray(), __('lang.saved_successfully', ['operator' => __('lang.modules')]));
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
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        $modules = $this->moduleRepository->findWithoutFail($id);
        if (empty($modules)) {
            return $this->sendError('Modules not found');
        }
        
        try {
            $input = $request->all();
            $modules = $this->moduleRepository->update($input, $id);
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        return $this->sendResponse($modules->toArray(), __('lang.updated_successfully', ['operator' => __('lang.modules')]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        $modules = $this->moduleRepository->findWithoutFail($id);

        if (empty($modules)) {
            return $this->sendError('Modules not found');
        }

        $modules = $this->moduleRepository->delete($id);

        return $this->sendResponse($modules, __('lang.deleted_successfully', ['operator' => __('lang.modules')]));
    }
}
