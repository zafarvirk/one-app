<?php
/**
 * File name: MarketDataTable.php
 * Last modified: 2020.04.30 at 08:21:09
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2020
 *
 */

namespace App\DataTables;

use App\Models\CustomField;
use App\Models\Business;
use Barryvdh\DomPDF\Facade as PDF;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;

class MarketDataTable extends DataTable
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
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        $dataTable = new EloquentDataTable($query);
        $columns = array_column($this->getColumns(), 'data');
        $dataTable = $dataTable
            ->editColumn('image', function ($market) {
                return getMediaColumn($market, 'image');
            })
            ->editColumn('name', function ($market) {
                if ($market['featured']) {
                    return $market->name . "<span class='badge bg-" . setting('theme_color') . " p-1 m-2'>" . trans('lang.article_featured') . "</span>";
                }
                return $market->name;
            })
            ->editColumn('address.address', function ($market) {
                return getLinksColumnByRouteName([$market->address], 'addresses.edit', 'id', 'address');
            })
            ->editColumn('updated_at', function ($market) {
                return getDateColumn($market, 'updated_at');
            })
            ->editColumn('closed', function ($product) {
                return getNotBooleanColumn($product, 'closed');
            })
            ->editColumn('available_for_delivery', function ($product) {
                return getBooleanColumn($product, 'available_for_delivery');
            })
            ->editColumn('active', function ($market) {
                return getBooleanColumn($market, 'active');
            })
            ->editColumn('is_populer', function ($market) {
                return getBooleanColumn($market, 'is_populer');
            })
            ->addColumn('action', 'markets.datatables_actions')
            ->rawColumns(array_merge($columns, ['action']));

        return $dataTable;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Post $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Business $model)
    {
        if (auth()->user()->hasRole('admin')) {
            return $model->newQuery()->with("business_category")
            ->with("address")
            ->join("business_modules", "business_modules.business_id", "=", "businesses.id")
            ->where('business_modules.module_id', 2)
            ->where('businesses.accepted', '1')->select("businesses.*");
        } else if (auth()->user()->hasRole('salon owner')){
            return $model->newQuery()
                ->with("business_category")
                ->with("address")
                ->join("business_users", "business_users.business_id", "=", "businesses.id")
                ->join("business_modules", "business_modules.business_id", "=", "businesses.id")
                ->where('business_modules.module_id', 2)
                ->where('business_users.user_id', auth()->id())
                ->groupBy("businesses.id")
                ->where('businesses.accepted', '1')
                ->select("businesses.*");
        }else if (auth()->user()->hasRole('class_manager')){
            return $model->newQuery()
                ->with("business_category")
                ->with("address")
                ->join("business_users", "business_users.business_id", "=", "businesses.id")
                ->join("business_modules", "business_modules.business_id", "=", "businesses.id")
                ->where('business_modules.module_id', 2)
                ->where('business_users.user_id', auth()->id())
                ->groupBy("businesses.id")
                ->where('businesses.accepted', '1')
                ->select("businesses.*");
        } else if(auth()->user()->hasRole('driver')){
            return $model->newQuery()
                ->with("business_category")
                ->with("address")
                ->join("driver_markets", "driver_markets.business_id", "=", "businesses.id")
                ->join("business_modules", "business_modules.business_id", "=", "businesses.id")
                ->where('business_modules.module_id', 2)
                ->where('driver_markets.user_id', auth()->id())
                ->groupBy("businesses.id")
                ->where('businesses.accepted', '1')
                ->select("businesses.*");
        } else if (auth()->user()->hasRole('client')) {
            return $model->newQuery()
                ->with("business_category")
                ->with("address")
                ->join("products", "products.business_id", "=", "businesses.id")
                ->join("product_orders", "products.id", "=", "product_orders.product_id")
                ->join("orders", "orders.id", "=", "product_orders.order_id")
                ->join("business_modules", "business_modules.business_id", "=", "businesses.id")
                ->where('business_modules.module_id', 2)
                ->where('orders.user_id', auth()->id())
                ->groupBy("businesses.id")
                ->where('businesses.accepted', '1')
                ->select("businesses.*");
        } else {
            return $model->newQuery()->with("business_category")
            ->with("address")
            ->join("business_modules", "business_modules.business_id", "=", "businesses.id")
            ->where('business_modules.module_id', 2)
            ->where('businesses.accepted', '1')->select("businesses.*");
        }
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->addAction(['title'=>trans('lang.actions'),'width' => '80px', 'printable' => false, 'responsivePriority' => '100'])
            ->parameters(array_merge(
                config('datatables-buttons.parameters'), [
                    'language' => json_decode(
                        file_get_contents(base_path('resources/lang/' . app()->getLocale() . '/datatable.json')
                        ), true)
                ]
            ));
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
                'title' => trans('lang.market_image'),
                'searchable' => false, 'orderable' => false, 'exportable' => false, 'printable' => false,
            ],
            [
                'data' => 'name',
                'title' => trans('lang.market_name'),

            ],
            [
                'data' => 'address.address',
                'title' => trans('lang.market_address'),
                'searchable' => false,
                'orderable' => false

            ],
            [
                'data' => 'phone_number',
                'title' => trans('lang.market_phone'),

            ],
            [
                'data' => 'mobile_number',
                'title' => trans('lang.market_mobile'),

            ],
            [
                'data' => 'available_for_delivery',
                'title' => trans('lang.market_available_for_delivery'),

            ],
            [
                'data' => 'closed',
                'title' => trans('lang.market_closed'),

            ],
            [
                'data' => 'active',
                'title' => trans('lang.market_active'),

            ],
            [
                'data' => 'is_populer',
                'title' => trans('lang.salon_populer'),

            ],
            [
                'data' => 'updated_at',
                'title' => trans('lang.market_updated_at'),
                'searchable' => false,
            ]
        ];

        $hasCustomField = in_array(Business::class, setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFieldsCollection = CustomField::where('custom_field_model', Business::class)->where('in_table', '=', true)->get();
            foreach ($customFieldsCollection as $key => $field) {
                array_splice($columns, $field->order - 1, 0, [[
                    'data' => 'custom_fields.' . $field->name . '.view',
                    'title' => trans('lang.market_' . $field->name),
                    'orderable' => false,
                    'searchable' => false,
                ]]);
            }
        }
        return $columns;
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'marketsdatatable_' . time();
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
}