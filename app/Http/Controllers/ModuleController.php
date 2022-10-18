<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataTables\ModuleDataTable;
use App\Http\Requests\CreateModuleRequest;
use App\Http\Requests\UpdateModuleRequest;
use App\Repositories\ModuleRepository;
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

class ModuleController extends Controller
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
     * Display a listing of the Aminities.
     *
     * @param ModuleDataTable $moduleDataTable
     * @return mixed
    */
 
    public function index(ModuleDataTable $moduleDataTable)
    {
        return $moduleDataTable->render('modules.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $hasCustomField = in_array($this->moduleRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->moduleRepository->model());
            $html = generateCustomField($customFields);
        }
        return view('modules.create')->with("customFields", isset($html) ? $html : false);

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
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->moduleRepository->model());
        try {
            $modules = $this->moduleRepository->create($input);
            $modules->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.saved_successfully', ['operator' => __('lang.modules')]));

        return redirect(route('modules.index'));
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
        $modules = $this->moduleRepository->findWithoutFail($id);
        if (empty($modules)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.modules')]));

            return redirect(route('modules.index'));
        }

        $customFieldsValues = $modules->customFieldsValues()->with('customField')->get();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->moduleRepository->model());
        $hasCustomField = in_array($this->moduleRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        return view('modules.edit')->with("modules", $modules)->with("customFields", isset($html) ? $html : false);
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
        $modules = $this->moduleRepository->findWithoutFail($id);

        if (empty($modules)) {
            Flash::error('Modules not found');
            return redirect(route('modules.index'));
        }

        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->moduleRepository->model());
        try {
            $modules = $this->moduleRepository->update($input, $id);
            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $highlights->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.updated_successfully', ['operator' => __('lang.modules')]));

        return redirect(route('modules.index'));
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
            Flash::error('Modules not found');

            return redirect(route('modules.index'));
        }

        $this->moduleRepository->delete($id);

        Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.modules')]));

        return redirect(route('modules.index'));
    }
}
