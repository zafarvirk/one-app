<?php
/*
 * File name: PlanDataTable.php
 * Last modified: 2021.03.21 at 12:22:10
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */

namespace App\DataTables;

use App\Models\Plan;
use App\Models\CustomField;
use Barryvdh\DomPDF\Facade as PDF;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Services\DataTable;

class PlanDataTable extends DataTable
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
    public function dataTable($query): DataTableAbstract
    {
        $dataTable = new EloquentDataTable($query);
        $columns = array_column($this->getColumns(), 'data');
        $dataTable = $dataTable
            ->editColumn('name', function ($plans) {
                return $plans->name;
            })
            ->editColumn('price_type', function ($plans) {
                return $plans->price_type;
            })
            ->editColumn('price_frequency', function ($plans) {
                return $plans->price_frequency;
            })
            ->editColumn('price', function ($plans) {
                return $plans->price;
            })
            ->editColumn('description', function ($plans) {
                return $plans->description;
            })
            ->editColumn('business.name', function ($plans) {
                return getLinksColumnByRouteName([$plans->business], 'businesses.edit', 'id', 'name');
            })
            ->editColumn('plans_article', function ($plans) {
                return getLinksColumnByRouteName($plans->plans_article, 'articles.edit', 'id', 'name');
            })
            ->editColumn('updated_at', function ($plans) {
                return getDateColumn($plans, 'updated_at');
            })
            ->addColumn('action', 'plans.datatables_actions')
            ->rawColumns(array_merge($columns, ['action']));

        return $dataTable;
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns(): array
    {
        $columns = [
            [
                'data' => 'name',
                'title' => trans('lang.plans_name'),

            ],
            [
                'data' => 'price_type',
                'title' => trans('lang.plans_price_type'),

            ],
            [
                'data' => 'price_frequency',
                'title' => trans('lang.plans_price_frequency'),

            ],
            [
                'data' => 'price',
                'title' => trans('lang.plans_price'),

            ],
            [
                'data' => 'business.name',
                'title' => trans('lang.business'),

            ],
            [
                'data' => 'plans_article',
                'title' => trans('lang.plans_article'),

            ],
            [
                'data' => 'description',
                'title' => trans('lang.plans_description'),

            ],
            [
                'data' => 'updated_at',
                'title' => trans('lang.plans_updated_at'),
                'searchable' => false,
            ]
        ];
        $columns = array_filter($columns);
        $hasCustomField = in_array(Plan::class, setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFieldsCollection = CustomField::where('custom_field_model', Plan::class)->where('in_table', '=', true)->get();
            foreach ($customFieldsCollection as $key => $field) {
                array_splice($columns, $field->order - 1, 0, [[
                    'data' => 'custom_fields.' . $field->name . '.view',
                    'title' => trans('lang.plans_' . $field->name),
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
     * @param Address $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Plan $model)
    {
        if (auth()->user()->hasRole('salon owner')) {
            return $model->newQuery()
                ->with("business")
                ->with('plans_article')
                ->join("business_users", "business_users.business_id", "=", "plans.plan_business_id")
                ->where('business_users.user_id', auth()->id())
                ->orderby('plans.id','DESC');
        } else if (auth()->user()->hasRole('class_manager')) {  
            return $model->newQuery()
                ->with("business")
                ->with('plans_article')
                ->join("business_users", "plans.plan_business_id", "=", "business_users.business_id")
                ->where('business_users.user_id', auth()->id())
                ->orderby('plans.id','DESC');
        } else {
            return $model->newQuery()->orderby('id','DESC')->with('plans_article')->with('business');
        }
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
        return 'plandatatable_' . time();
    }
}
