<?php
/*
 * File name: ArticleRequestDataTable.php
 * Last modified: 2022.03.11 at 00:39:10
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\DataTables;

use App\Models\ArticleRequest;
use App\Models\CustomField;
use App\Models\Post;
use Barryvdh\DomPDF\Facade as PDF;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Services\DataTable;

class ArticleRequestDataTable extends DataTable
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
            ->editColumn('image', function ($request) {
                return getMediaColumn($request, 'image', '', '');
            })
            ->editColumn('name', function ($request) {
                return $request->name;
            })
            ->editColumn('user.name', function ($request) {
                return getLinksColumnByRouteName([$request->user], 'users.edit', 'id', 'name');
            })
            ->editColumn('business_category.name', function ($request) {
                return getLinksColumnByRouteName([$request->business_category], 'businessCategories.edit', 'id', 'name');
            })
            ->editColumn('transaction_status.status', function ($request) {
                return getLinksColumnByRouteName([$request->transaction_status], 'transaction_statuses.edit', 'id', 'status');
            })
            ->editColumn('address.address', function ($request) {
                return getLinksColumnByRouteName([$request->address], 'addresses.edit', 'id', 'address');
            })
            ->editColumn('updated_at', function ($request) {
                return getDateColumn($request, 'updated_at');
            })
            ->addColumn('action', 'requests.datatables_actions')
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
                'title' => trans('lang.request_image'),
                'searchable' => false, 'orderable' => false, 'exportable' => false, 'printable' => false,
            ],
            [
                'data' => 'name',
                'title' => trans('lang.request_name'),

            ],
            [
                'data' => 'type',
                'title' => trans('lang.request_type'),

            ],
            [
                'data' => 'required_datetime',
                'title' => trans('lang.request_required_datetime'),

            ],
            [
                'data' => 'user.name',
                'name' => 'user.name',
                'title' => trans('lang.request_user'),
                'searchable' => false, 'orderable' => false,
            ],
            [
                'data' => 'business_category.name',
                'name' => 'business_category.name',
                'title' => trans('lang.request_business_category'),
                'searchable' => false, 'orderable' => false,
            ],
            [
                'data' => 'transaction_status.status',
                'name' => 'transaction_status.status',
                'title' => trans('lang.request_transaction_status'),
                'searchable' => false, 'orderable' => false,
            ],
            [
                'data' => 'address.address',
                'name' => 'address.address',
                'title' => trans('lang.request_address'),
                'searchable' => false, 'orderable' => false,
            ],
            [
                'data' => 'updated_at',
                'title' => trans('lang.request_updated_at'),
                'searchable' => false,
            ]
        ];

        $hasCustomField = in_array(ArticleRequest::class, setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFieldsCollection = CustomField::where('custom_field_model', ArticleRequest::class)->where('in_table', '=', true)->get();
            foreach ($customFieldsCollection as $key => $field) {
                array_splice($columns, $field->order - 1, 0, [[
                    'data' => 'custom_fields.' . $field->name . '.view',
                    'title' => trans('lang.category_' . $field->name),
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
     * @param ArticleRequest $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(ArticleRequest $model)
    {
        return $model->newQuery()->with("business_category")->with("transaction_status")->with("address")->select("article_requests.*");
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
        return 'articlerequestsdatatable_' . time();
    }
}
