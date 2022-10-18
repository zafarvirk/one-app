<?php


namespace App\DataTables;

use App\Models\CustomField;
use App\Models\ArticleSchedule;
use Barryvdh\DomPDF\Facade as PDF;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Services\DataTable;

class ArticleScheduleDataTable extends DataTable
{
    /**
     * custom fields columns
     * @var array
     */
    public static $customFields = [];

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return DataTableAbstract
     */
    public function dataTable($query)
    {
        $dataTable = new EloquentDataTable($query);
        $columns = array_column($this->getColumns(), 'data');
        $dataTable = $dataTable
            // ->editColumn('start_date', function ($article_schedule) {
            //     return getDateColumn($article_schedule, 'start_date');
            // })
            ->editColumn('days', function ($article_schedule) {
                return getDaysColumn($article_schedule);
            })
            ->editColumn('class.name', function ($article_schedule) {
                return getLinksColumnByRouteName([$article_schedule->class], 'article.edit', 'id', 'name');
            })
            ->addColumn('action', 'article_schedule.datatables_actions')
            ->rawColumns(array_merge($columns, ['action']));

        return $dataTable;
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        $columns = [
            [
                'data' => 'class.name',
                'name' => 'class.name',
                'title' => trans('lang.class_article'),

            ],
            [
                'data' => 'start_date',
                'title' => trans('lang.article_schedule_start_date'),

            ],
            [
                'data' => 'end_date',
                'title' => trans('lang.article_schedule_end_date'),
            ],
            [
                'data' => 'start_time',
                'title' => trans('lang.article_schedule_start_time'),

            ],
            [
                'data' => 'end_time',
                'title' => trans('lang.article_schedule_end_time'),

            ],
            [
                'data' => 'duration',
                'title' => trans('lang.article_schedule_duration'),
            ],
            [
                'data' => 'repeat',
                'title' => trans('lang.article_schedule_repeat'),

            ],
            [
                'data' => 'days',
                'title' => trans('lang.article_schedule_days'),

            ],
        ];

        $hasCustomField = in_array(ArticleSchedule::class, setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFieldsCollection = CustomField::where('custom_field_model', ArticleSchedule::class)->where('in_table', '=', true)->get();
            foreach ($customFieldsCollection as $key => $field) {
                array_splice($columns, $field->order - 1, 0, [[
                    'data' => 'custom_fields.' . $field->name . '.view',
                    'title' => trans('lang.article_' . $field->name),
                    'orderable' => false,
                    'searchable' => false,
                ]]);
            }
        }
        return $columns;
    }

    /**
     * Get query source of dataTable.
     *
     * @param ArticleSchedule $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(ArticleSchedule $model)
    {
        return $model->newQuery()->with("class")->select("$model->table.*");
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return Builder
     */
    public function html()
    {
        return $this->builder()
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->addAction(['width' => '80px', 'printable' => false, 'responsivePriority' => '100'])
            ->parameters(array_merge(
                config('datatables-buttons.parameters'), [
                    'language' => json_decode(
                        file_get_contents(base_path('resources/lang/' . app()->getLocale() . '/datatable.json')
                        ), true)
                ]
            ));
    }

    /**
     * Export PDF using DOMPDF
     * @return mixed
     */
    public function pdf()
    {
        $data = $this->getDataForPrint();
        $pdf = PDF::loadView($this->printPreview, compact('data'));
        return $pdf->download($this->filename() . '.pdf');
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'scheduledatatable_' . time();
    }
}
