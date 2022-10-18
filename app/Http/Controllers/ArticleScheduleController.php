<?php


namespace App\Http\Controllers;

use App\DataTables\ArticleScheduleDataTable;
use App\Http\Requests\CreateArticleScheduleRequest;
use App\Http\Requests\UpdateArticleScheduleRequest;
use App\Repositories\ArticleScheduleRepository;
use App\Repositories\ArticleRepository;
use App\Repositories\CustomFieldRepository;
use Carbon\Carbon;
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

class ArticleScheduleController extends Controller
{
    /** @var  ArticleScheduleRepository */
    private $articleScheduleRepository;

    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;

    /**
     * @var ArticleRepository
     */
    private $articleRepository;

    protected $days = [
        'Sunday' => 'Sunday',
        'Monday' => 'Monday',
        'Tuesday' => 'Tuesday',
        'Wednesday' => 'Wednesday',
        'Thursday' => 'Thursday',
        'Friday' => 'Friday',
        'Saturday' => 'Saturday',
    ];

    public function __construct(ArticleScheduleRepository $articleScheduleRepo, CustomFieldRepository $customFieldRepo, ArticleRepository $articleRepo)
    {
        parent::__construct();
        $this->articleScheduleRepository = $articleScheduleRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->articleRepository = $articleRepo;
    }

    /**
     * Display a listing of the article.
     *
     * @param ArticleScheduleDataTable $articleScheduleDataTable
     * @return Response
     */
    public function index(ArticleScheduleDataTable $articleScheduleDataTable)
    {
        return $articleScheduleDataTable->render('article_schedule.index');
    }

    /**
     * Show the form for creating a new article.
     *
     * @return Application|Factory|Response|View
     */
    public function create()
    {
        $article = $this->articleRepository->where('type' , 'class')->pluck('name', 'id');
        
        $selectedDyas = [];
        $hasCustomField = in_array($this->articleRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->articleRepository->model());
            $html = generateCustomField($customFields);
        }
        return view('article_schedule.create')->with("customFields", isset($html) ? $html : false)->with("article", $article)->with("days", $this->days)->with("selectedDyas", $selectedDyas);
    }

    /**
     * Store a newly created article in storage.
     *
     * @param CreateArticleScheduleRequest $request
     *
     * @return Application|RedirectResponse|Redirector|Response
     */
    public function store(CreateArticleScheduleRequest $request)
    {
        $input = $request->all();

        if ($input['repeat'] == 'weekly' && isset($input['days']) && count($input['days'])) {
            $days = [];
            for ($i=0; $i < 7; $i++) { 
                $day = (new Carbon())->addDays($i)->dayName;

                if (in_array($day, $input['days'])) {
                    $days[$day] = 1;
                }
                else {
                    $days[$day] = 0;
                }
            }

            $input['days'] = $days;
        }
        else {
            $input['days'] = null;
            $input['recurrence_rules'] = null;
        }

        $input['end_time'] = $this->articleScheduleRepository->calculateEndTime($input['start_time'], $input['duration'])->format('H:i:s');
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->articleScheduleRepository->model());

        try {
            $articleSchedule = $this->articleScheduleRepository->create($input);
            $articleSchedule->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));
            
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.saved_successfully', ['operator' => __('lang.article_schedule')]));

        return redirect(route('article_schedule.index'));
    }

    /**
     * Display the specified article.
     *
     * @param int $id
     *
     * @return Application|RedirectResponse|Redirector|Response
     * @throws RepositoryException
     */
    public function show(int $id)
    {
        $articleSchedule = $this->articleScheduleRepository->findWithoutFail($id);

        if (empty($articleSchedule)) {
            Flash::error('E Service not found');

            return redirect(route('article_schedule.index'));
        }

        return view('article_schedule.show')->with('classSchedule', $articleSchedule);
    }

    /**
     * Show the form for editing the specified article.
     *
     * @param int $id
     *
     * @return Application|RedirectResponse|Redirector|Response
     * @throws RepositoryException
     */
    public function edit(int $id)
    {
        $articleSchedule = $this->articleScheduleRepository->findWithoutFail($id);
        if (empty($articleSchedule)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.article_schedule')]));

            return redirect(route('article_schedule.index'));
        }
        $article = $this->articleRepository->where('type' , 'class')->pluck('name', 'id');

        $selectedDyas = $articleSchedule->days;

        $customFieldsValues = $articleSchedule->customFieldsValues()->with('customField')->get();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->articleScheduleRepository->model());
        $hasCustomField = in_array($this->articleScheduleRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }
        return view('article_schedule.edit')->with('articleSchedule', $articleSchedule)->with("customFields", isset($html) ? $html : false)->with("article", $article)->with("days", $this->days)->with("selectedDyas", $selectedDyas);
    }

    /**
     * Update the specified article in storage.
     *
     * @param int $id
     * @param UpdateArticleScheduleRequest $request
     *
     * @return Application|RedirectResponse|Redirector|Response
     * @throws RepositoryException
     */
    public function update(int $id, UpdateArticleScheduleRequest $request)
    {
        $articleSchedule = $this->articleScheduleRepository->findWithoutFail($id);

        if (empty($articleSchedule)) {
            Flash::error('E Service not found');
            return redirect(route('article_schedule.index'));
        }

        $input = $request->all();
        
        if ($input['repeat'] == 'weekly' && isset($input['days']) && count($input['days'])) {
            $days = [];
            for ($i=0; $i < 7; $i++) { 
                $day = (new Carbon())->addDays($i)->dayName;

                if (in_array($day, $input['days'])) {
                    $days[$day] = 1;
                }
                else {
                    $days[$day] = 0;
                }
            }

            $input['days'] = $days;
        }
        else {
            $input['days'] = null;
            $input['recurrence_rules'] = null;
        }

        $input['end_time'] = $this->articleScheduleRepository->calculateEndTime($input['start_time'], $input['duration'])->format('H:i:s');
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->articleScheduleRepository->model());
        
        try {
            
            $articleSchedule = $this->articleScheduleRepository->update($input, $id);
            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $articleSchedule->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.updated_successfully', ['operator' => __('lang.article_schedule')]));

        return redirect(route('article_schedule.index'));
    }

    /**
     * Remove the specified article from storage.
     *
     * @param int $id
     *
     * @return Application|RedirectResponse|Redirector|Response
     * @throws RepositoryException
     */
    public function destroy(int $id)
    {
        $articleSchedule = $this->articleScheduleRepository->findWithoutFail($id);

        if (empty($articleSchedule)) {
            Flash::error('E Service not found');

            return redirect(route('article_schedule.index'));
        }

        $this->articleScheduleRepository->delete($id);

        Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.article_schedule')]));

        return redirect(route('article_schedule.index'));
    }


}
