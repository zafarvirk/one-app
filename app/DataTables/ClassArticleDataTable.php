<?php
/*
 * File name: ArticleDataTable.php
 * Last modified: 2022.02.03 at 15:29:18
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\DataTables;

use App\Models\CustomField;
use App\Models\Article;
use App\Models\Post;
use Barryvdh\DomPDF\Facade as PDF;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Services\DataTable;

class ClassArticleDataTable extends DataTable
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
            ->editColumn('image', function ($article) {
                return getMediaColumn($article, 'image');
            })
            ->editColumn('name', function ($article) {
                if ($article['featured']) {
                    return $article['name'] . "<span class='badge bg-" . setting('theme_color') . " p-1 m-2'>" . trans('lang.article_featured') . "</span>";
                }
                return $article['name'];
            })
            ->editColumn('price', function ($article) {
                return getPriceColumn($article);
            })
            ->editColumn('discount_price', function ($article) {
                if (empty($article['discount_price'])) {
                    return '-';
                } else {
                    return getPriceColumn($article, 'discount_price');
                }
            })
            ->editColumn('updated_at', function ($article) {
                return getDateColumn($article, 'updated_at');
            })
            ->editColumn('article_categories', function ($article) {
                return getLinksColumnByRouteName($article->article_categories, 'article_categories.edit', 'id', 'name');
            })
            ->editColumn('business.name', function ($article) {
                return getLinksColumnByRouteName([$article->business], 'businesses.edit', 'id', 'name');
            })
            ->editColumn('available', function ($article) {
                return getBooleanColumn($article, 'available');
            })
            ->addColumn('action', 'class_article.datatables_actions')
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
                'title' => trans('lang.article_image'),
                'searchable' => false, 'orderable' => false, 'exportable' => false, 'printable' => false,
            ],
            [
                'data' => 'name',
                'title' => trans('lang.article_name'),

            ],
            [
                'data' => 'business.name',
                'name' => 'business.name',
                'title' => trans('lang.article_business_id'),

            ],
            [
                'data' => 'price',
                'title' => trans('lang.article_price'),

            ],
            [
                'data' => 'discount_price',
                'title' => trans('lang.article_discount_price'),

            ],
            [
                'data' => 'article_categories',
                'title' => trans('lang.article_categories'),
                'searchable' => false,
                'orderable' => false
            ],
            [
                'data' => 'available',
                'title' => trans('lang.article_available'),

            ],
            [
                'data' => 'updated_at',
                'title' => trans('lang.article_updated_at'),
                'searchable' => false,
            ]
        ];

        $hasCustomField = in_array(Article::class, setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFieldsCollection = CustomField::where('custom_field_model', Article::class)->where('in_table', '=', true)->get();
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
     * @param Article $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Article $model)
    {
        if (auth()->user()->hasRole('salon owner')) {
            return $model->newQuery()->with("business")->join('business_users', 'business_users.business_id', '=', 'article.business_id')
                ->groupBy('article.id')
                ->where('business_users.user_id', auth()->id())
                ->where('article.type', 'class')
                ->select('article.*');
        } 
        else if (auth()->user()->hasRole('class_manager')) {
            return $model->newQuery()->with("business")->join('business_users', 'business_users.business_id', '=', 'article.business_id')
                ->groupBy('article.id')
                ->where('business_users.user_id', auth()->id())
                ->where('article.type', 'class')
                ->select('article.*');
        }
        return $model->newQuery()->with("business")->with('article_categories')
        ->where('type', 'class')->select("$model->table.*");
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
        return 'articlesdatatable_' . time();
    }
}
