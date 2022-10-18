<?php
/*
 * File name: BusinessCategoryDataTable.php
 * Last modified: 2022.02.03 at 14:23:26
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\DataTables;

use App\Models\CustomField;
use App\Models\Post;
use App\Models\BusinessCategory;
use Barryvdh\DomPDF\Facade as PDF;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Services\DataTable;

class BusinessCategoryDataTable extends DataTable
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
            ->editColumn('image', function ($businessCategory) {
                return getMediaColumn($businessCategory, 'image', '', '');
            })
            ->editColumn('description', function ($businessCategory) {
                return getStripedHtmlColumn($businessCategory, 'description');
            })
            ->editColumn('name', function ($businessCategory) {
                return $businessCategory->name;
            })
            ->editColumn('color', function ($businessCategory) {
                return getColorColumn($businessCategory, 'color');
            })
            ->editColumn('featured', function ($businessCategory) {
                return getBooleanColumn($businessCategory, 'featured');
            })
            ->editColumn('parent_category.name', function ($businessCategory) {
                return getLinksColumnByRouteName([$businessCategory->parentCategory], 'businessCategories.edit', 'id', 'name');
            })
            ->editColumn('updated_at', function ($businessCategory) {
                return getDateColumn($businessCategory, 'updated_at');
            })
            ->editColumn('disabled', function ($businessCategory) {
                return getNotBooleanColumn($businessCategory, 'disabled');
            })
            ->editColumn('default', function ($businessCategory) {
                return getBooleanColumn($businessCategory, 'default');
            })
            ->addColumn('action', 'business_categories.datatables_actions')
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
                'data' => 'image',
                'title' => trans('lang.category_image'),
                'searchable' => false, 'orderable' => false, 'exportable' => false, 'printable' => false,
            ],
            [
                'data' => 'name',
                'title' => trans('lang.business_category_name'),

            ],
            [
                'data' => 'color',
                'title' => trans('lang.category_color'),

            ],
            [
                'data' => 'description',
                'title' => trans('lang.category_description'),

            ],
            [
                'data' => 'featured',
                'title' => trans('lang.category_featured'),
            ],
            [
                'data' => 'order',
                'title' => trans('lang.category_order'),
            ],
            [
                'data' => 'parent_category.name',
                'name' => 'parentCategory.name',
                'title' => trans('lang.category_parent_id'),
                'searchable' => false, 'orderable' => false,
            ],
            [
                'data' => 'commission',
                'title' => trans('lang.business_category_commission'),

            ],
            [
                'data' => 'disabled',
                'title' => trans('lang.business_category_disabled'),

            ],
            [
                'data' => 'default',
                'title' => trans('lang.business_category_default'),

            ],
            [
                'data' => 'updated_at',
                'title' => trans('lang.business_category_updated_at'),
                'searchable' => false,
            ]
        ];

        $hasCustomField = in_array(BusinessCategory::class, setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFieldsCollection = CustomField::where('custom_field_model', BusinessCategory::class)->where('in_table', '=', true)->get();
            foreach ($customFieldsCollection as $key => $field) {
                array_splice($columns, $field->order - 1, 0, [[
                    'data' => 'custom_fields.' . $field->name . '.view',
                    'title' => trans('lang.business_category_' . $field->name),
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
     * @param Post $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(BusinessCategory $model)
    {
        return $model->newQuery()->with("parentCategory")->select("$model->table.*");
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
        return 'business_categoriesdatatable_' . time();
    }
}
