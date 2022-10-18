<?php
/**
 * File name: ProductDataTable.php
 * Last modified: 2020.05.04 at 09:04:18
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2020
 *
 */

namespace App\DataTables;

use App\Models\CustomField;
use App\Models\Article;
use Barryvdh\DomPDF\Facade as PDF;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;

class ProductDataTable extends DataTable
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
            ->editColumn('name', function ($product) {
                if ($product['featured']) {
                    return $product['name'] . "<span class='badge bg-" . setting('theme_color') . " p-1 m-2'>" . trans('lang.article_featured') . "</span>";
                }
                return $product['name'];
            })
            ->editColumn('image', function ($product) {
                return getMediaColumn($product, 'image');
            })
            ->editColumn('price', function ($product) {
                return getPriceColumn($product);
            })
            ->editColumn('discount_price', function ($product) {
                return getPriceColumn($product,'discount_price');
            })
            ->editColumn('business.name', function ($product) {
                return getLinksColumnByRouteName([$product->business], 'businesses.edit', 'id', 'name');
            })
            ->editColumn('capacity', function ($product) {
                return $product['capacity']." ".$product['unit'];
            })
            ->editColumn('article_categories', function ($product) {
                return getLinksColumnByRouteName($product->article_categories, 'article_categories.edit', 'id', 'name');
            })
            ->editColumn('updated_at', function ($product) {
                return getDateColumn($product, 'updated_at');
            })
            ->editColumn('featured', function ($product) {
                return getBooleanColumn($product, 'featured');
            })
            ->addColumn('action', 'products.datatables_actions')
            ->rawColumns(array_merge($columns, ['action']));

        return $dataTable;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Post $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Article $model)
    {

        if (auth()->user()->hasRole('admin')) {
            return $model->newQuery()->with("business")->with("article_categories")->where('type', 'product')->select('article.*')->orderBy('article.updated_at','desc');
        } else if (auth()->user()->hasRole('salon owner')) {
            return $model->newQuery()->with("business")->with("article_categories")
                ->join("business_users", "business_users.business_id", "=", "article.business_id")
                ->where('business_users.user_id', auth()->id())
                ->groupBy('article.id')
                ->where('article.type', 'product')
                ->select('article.*')->orderBy('article.updated_at', 'desc');
        } else if (auth()->user()->hasRole('class_manager')) {
            return $model->newQuery()->with("business")->with("article_categories")
                ->join("business_users", "business_users.business_id", "=", "article.business_id")
                ->where('business_users.user_id', auth()->id())
                ->groupBy('article.id')
                ->where('article.type', 'product')
                ->select('article.*')->orderBy('article.updated_at', 'desc');
        } else if (auth()->user()->hasRole('driver')) {
            return $model->newQuery()->with("business")->with("article_categories")
                ->join("driver_markets", "driver_markets.market_id", "=", "article.market_id")
                ->where('driver_markets.user_id', auth()->id())
                ->groupBy('article.id')
                ->where('article.type', 'product')
                ->select('article.*')->orderBy('article.updated_at', 'desc');
        } else if (auth()->user()->hasRole('client')) {
            return $model->newQuery()->with("business")->with("article_categories")
                ->join("product_orders", "product_orders.article_id", "=", "article.id")
                ->join("orders", "product_orders.order_id", "=", "orders.id")
                ->where('orders.user_id', auth()->id())
                ->groupBy('article.id')
                ->where('article.type', 'product')
                ->select('article.*')->orderBy('article.updated_at', 'desc');
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
                'data' => 'name',
                'title' => trans('lang.product_name'),

            ],
            [
                'data' => 'image',
                'title' => trans('lang.product_image'),
                'searchable' => false, 'orderable' => false, 'exportable' => false, 'printable' => false,
            ],
            [
                'data' => 'price',
                'title' => trans('lang.product_price'),

            ],
            [
                'data' => 'discount_price',
                'title' => trans('lang.product_discount_price'),

            ],
            [
                'data' => 'capacity',
                'title' => trans('lang.product_capacity'),

            ],
            [
                'data' => 'featured',
                'title' => trans('lang.product_featured'),

            ],
            [
                'data' => 'business.name',
                'title' => trans('lang.product_market_id'),

            ],
            [
                'data' => 'article_categories',
                'title' => trans('lang.product_category_id'),
                'searchable' => false,
                'orderable' => false

            ],
            [
                'data' => 'updated_at',
                'title' => trans('lang.product_updated_at'),
                'searchable' => false,
            ]
        ];

        $hasCustomField = in_array(Article::class, setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFieldsCollection = CustomField::where('custom_field_model', Article::class)->where('in_table', '=', true)->get();
            foreach ($customFieldsCollection as $key => $field) {
                array_splice($columns, $field->order - 1, 0, [[
                    'data' => 'custom_fields.' . $field->name . '.view',
                    'title' => trans('lang.product_' . $field->name),
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
        return 'productsdatatable_' . time();
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