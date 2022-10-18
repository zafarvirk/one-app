<?php
/*
 * File name: BusinessReviewDataTable.php
 * Last modified: 2022.02.12 at 02:17:42
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\DataTables;

use App\Models\CustomField;
use App\Models\BusinessReview;
use Barryvdh\DomPDF\Facade as PDF;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Services\DataTable;

class BusinessReviewDataTable extends DataTable
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
            ->editColumn('updated_at', function ($BusinessReview) {
                return getDateColumn($BusinessReview, 'updated_at');
            })
            ->editColumn('booking.user.name', function ($BusinessReview) {
                return getLinksColumnByRouteName([$BusinessReview->booking->user], 'users.edit', 'id', 'name');
            })
            ->editColumn('booking.salon.name', function ($BusinessReview) {
                return getLinksColumnByRouteName([$BusinessReview->booking->salon], 'salons.edit', 'id', 'name');
            })
            ->addColumn('action', 'business_reviews.datatables_actions')
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
                'data' => 'review',
                'title' => trans('lang.business_review_review'),

            ],
            [
                'data' => 'rate',
                'title' => trans('lang.business_review_rate'),

            ],
            [
                'data' => 'booking.user.name',
                'title' => trans('lang.business_review_user_id'),

            ],
            [
                'data' => 'booking.salon.name',
                'title' => trans('lang.business_review_salon_id'),
            ],
            [
                'data' => 'updated_at',
                'title' => trans('lang.business_review_updated_at'),
                'searchable' => false,
            ]
        ];

        $hasCustomField = in_array(BusinessReview::class, setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFieldsCollection = CustomField::where('custom_field_model', BusinessReview::class)->where('in_table', '=', true)->get();
            foreach ($customFieldsCollection as $key => $field) {
                array_splice($columns, $field->order - 1, 0, [[
                    'data' => 'custom_fields.' . $field->name . '.view',
                    'title' => trans('lang.business_review_' . $field->name),
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
     * @param BusinessReview $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(BusinessReview $model): \Illuminate\Database\Eloquent\Builder
    {
        if (auth()->user()->hasRole('admin')) {
            return $model->newQuery()->with("booking")->select("business_reviews.*");
        } else if (auth()->user()->hasRole('salon owner')) {
            return $model->newQuery()->with("booking")->join("bookings", "bookings.id", "=", "business_reviews.booking_id")
                ->join("business_users", "business_users.business_id", "=", "bookings.business->id")
                ->where('business_users.user_id', auth()->id())
                ->groupBy('business_reviews.id')
                ->select('business_reviews.*');
        } else if (auth()->user()->hasRole('class_manager')) {
            return $model->newQuery()->with("booking")->join("bookings", "bookings.id", "=", "business_reviews.booking_id")
                ->join("business_users", "business_users.business_id", "=", "bookings.business->id")
                ->where('business_users.user_id', auth()->id())
                ->groupBy('business_reviews.id')
                ->select('business_reviews.*');
        } else if (auth()->user()->hasRole('customer')) {
            return $model->newQuery()->join("bookings", "bookings.id", "=", "business_reviews.booking_id")
                ->where('bookings.user_id', auth()->id())
                ->groupBy('business_reviews.id')
                ->select('business_reviews.*');
        } else {
            return $model->newQuery()->with("user")->with("salon")->select("$model->table.*");
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
        return 'business_reviewsdatatable_' . time();
    }
}
