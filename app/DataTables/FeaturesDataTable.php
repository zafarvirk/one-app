<?php
/*
 * File name: FeaturesDataTable.php
 * Last modified: 2021.03.21 at 12:22:10
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */

namespace App\DataTables;

use App\Models\Features;
use App\Models\CustomField;
use Barryvdh\DomPDF\Facade as PDF;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Services\DataTable;

class FeaturesDataTable extends DataTable
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
            ->editColumn('image', function ($features) {
                return getMediaColumn($features, 'image', '', '');
            })
            ->editColumn('name', function ($features) {
                return $features->name;
            })
            ->editColumn('description', function ($features) {
                return $features->description;
            })
            ->editColumn('features_businesses', function ($features) {
                return getLinksColumnByRouteName($features->features_businesses, 'businesses.edit', 'id', 'name');
            })
            ->editColumn('updated_at', function ($features) {
                return getDateColumn($features, 'updated_at');
            })
            ->addColumn('action', 'features.datatables_actions')
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
                'data' => 'image',
                'title' => trans('lang.features_image'),
                'searchable' => false, 'orderable' => false, 'exportable' => false, 'printable' => false,
            ],
            [
                'data' => 'name',
                'title' => trans('lang.features_name'),

            ],
            [
                'data' => 'description',
                'title' => trans('lang.features_description'),

            ],
            [
                'data' => 'features_businesses',
                'title' => trans('lang.features_business'),
                'searchable' => false, 'orderable' => false,
            ],
            [
                'data' => 'updated_at',
                'title' => trans('lang.features_updated_at'),
                'searchable' => false,
            ]
        ];
        $columns = array_filter($columns);
        $hasCustomField = in_array(Highlight::class, setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFieldsCollection = CustomField::where('custom_field_model', Highlight::class)->where('in_table', '=', true)->get();
            foreach ($customFieldsCollection as $key => $field) {
                array_splice($columns, $field->order - 1, 0, [[
                    'data' => 'custom_fields.' . $field->name . '.view',
                    'title' => trans('lang.features_' . $field->name),
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
    public function query(Features $model)
    {
      
        return $model->newQuery()->orderby('id','DESC')->with('features_businesses');
        
        
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
        return 'featuresdatatable_' . time();
    }
}
