<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Criteria\Business\BusinessOfUserCriteria;
use App\DataTables\HighlightDataTable;
use App\Http\Requests\CreateHighlightRequest;
use App\Http\Requests\UpdateHighlightRequest;
use App\Repositories\HighlightRepository;
use App\Repositories\BusinessRepository;
use App\Repositories\CustomFieldRepository;
use App\Repositories\UploadRepository;
use Flash;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

class HighlightController extends Controller
{
    /** @var  HighlightRepository */
    private $highlightRepository;

    /**
      * @var CustomFieldRepository
    */
    private $customFieldRepository;

    /**
      * @var BusinessRepository
    */
    private $businessRepository;

    /**
     * @var UploadRepository
     */
    private $uploadRepository;

    public function __construct(HighlightRepository $highlightRepo, CustomFieldRepository $customFieldRepo, BusinessRepository $businessRepo, UploadRepository $uploadRepo)
    {
        parent::__construct();
        $this->highlightRepository = $highlightRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->businessRepository = $businessRepo;
        $this->uploadRepository = $uploadRepo;
    }

    /**
     * Display a listing of the Aminities.
     *
     * @param HighlightDataTable $highlightDataTable
     * @return mixed
    */
 
    public function index(HighlightDataTable $highlightDataTable)
    {
        return $highlightDataTable->render('highlights.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $hasCustomField = in_array($this->highlightRepository->model(), setting('custom_field_models', []));
        $business = $this->businessRepository->getByCriteria(new BusinessOfUserCriteria(auth()->id()))->pluck('name', 'id');
        $businessSelected = [];
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->highlightRepository->model());
            $html = generateCustomField($customFields);
        }
        return view('highlights.create')->with("customFields", isset($html) ? $html : false)->with("businessSelected",$businessSelected)->with("business", $business);
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
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->highlightRepository->model());
        try {
            $highlights = $this->highlightRepository->create($input);
            $highlights->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));
            if (isset($input['image']) && $input['image'] && is_array($input['image'])) {
                foreach ($input['image'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                    $mediaItem = $cacheUpload->getMedia('image')->first();
                    $mediaItem->copy($highlights, 'image');
                }
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.saved_successfully', ['operator' => __('lang.highlights')]));

        return redirect(route('highlights.index'));
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
        $highlights = $this->highlightRepository->findWithoutFail($id);
        if (empty($highlights)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.highlights')]));

            return redirect(route('highlights.index'));
        }

        $business = $this->businessRepository->getByCriteria(new BusinessOfUserCriteria(auth()->id()))->pluck('name', 'id');
        $businessSelected = $highlights->highlight_businesses()->pluck('business_id')->toArray();

        $customFieldsValues = $highlights->customFieldsValues()->with('customField')->get();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->highlightRepository->model());
        $hasCustomField = in_array($this->highlightRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        return view('highlights.edit')->with("highlights", $highlights)->with("customFields", isset($html) ? $html : false)->with("business",$business)->with("businessSelected",$businessSelected);
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
        $highlights = $this->highlightRepository->findWithoutFail($id);

        if (empty($highlights)) {
            Flash::error('Highlights not found');
            return redirect(route('highlights.index'));
        }

        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->highlightRepository->model());
        try {
            $highlights = $this->highlightRepository->update($input, $id);
            if (isset($input['image']) && $input['image'] && is_array($input['image'])) {
                foreach ($input['image'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                    $mediaItem = $cacheUpload->getMedia('image')->first();
                    $mediaItem->copy($highlights, 'image');
                }
            }
            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $highlights->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.updated_successfully', ['operator' => __('lang.highlights')]));

        return redirect(route('highlights.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        $highlights = $this->highlightRepository->findWithoutFail($id);

        if (empty($highlights)) {
            Flash::error('Highlights not found');

            return redirect(route('highlights.index'));
        }

        $this->highlightRepository->delete($id);

        Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.highlights')]));

        return redirect(route('highlights.index'));
    }

    public function removeMedia(Request $request)
    {
        $input = $request->all();
        $highlights = $this->highlightRepository->findWithoutFail($input['id']);
        try {
            if ($highlights->hasMedia($input['collection'])) {
                $highlights->getFirstMedia($input['collection'])->delete();
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
