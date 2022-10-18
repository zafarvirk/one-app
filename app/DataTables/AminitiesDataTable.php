<?php
/*
 * File name: AddressDataTable.php
 * Last modified: 2021.03.21 at 12:22:10
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */

namespace App\DataTables;

use App\Models\Aminities;
use App\Models\CustomField;
use Barryvdh\DomPDF\Facade as PDF;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Services\DataTable;

class AminitiesDataTable extends DataTable
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
            ->editColumn('image', function ($aminities) {
                return getMediaColumn($aminities, 'image', '', '');
            })
            ->editColumn('name', function ($aminities) {
                return $aminities->name;
            })
            ->editColumn('description', function ($aminities) {
                return $aminities->description;
            })
            ->editColumn('aminities_business', function ($aminities) {
                return getLinksColumnByRouteName($aminities->aminities_business, 'businesses.edit', 'id', 'name');
            })
            ->editColumn('updated_at', function ($aminities) {
                return getDateColumn($aminities, 'updated_at');
            })
            ->addColumn('action', 'aminities.datatables_actions')
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
                'title' => trans('lang.aminities_image'),
                'searchable' => false, 'orderable' => false, 'exportable' => false, 'printable' => false,
            ],
            [
                'data' => 'name',
                'title' => trans('lang.aminities_name'),

            ],
            [
                'data' => 'description',
                'title' => trans('lang.aminities_description'),

            ],
            [
                'data' => 'aminities_business',
                'title' => trans('lang.aminities_business'),
                'searchable' => false, 'orderable' => false,
            ],
            [
                'data' => 'updated_at',
                'title' => trans('lang.aminities_updated_at'),
                'searchable' => false,
            ]
        ];
        $columns = array_filter($columns);
        $hasCustomField = in_array(Aminities::class, setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFieldsCollection = CustomField::where('custom_field_model', Aminities::class)->where('in_table', '=', true)->get();
            foreach ($customFieldsCollection as $key => $field) {
                array_splice($columns, $field->order - 1, 0, [[
                    'data' => 'custom_fields.' . $field->name . '.view',
                    'title' => trans('lang.aminities_' . $field->name),
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
    public function query(Aminities $model)
    {
      
        return $model->newQuery()->orderby('id','DESC')->with('aminities_business');
        
        
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
        return 'aminitiesdatatable_' . time();
    }
}
