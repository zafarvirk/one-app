<?php
/*
 * File name: BookingDataTable.php
 * Last modified: 2022.02.12 at 02:17:43
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\DataTables;

use App\Models\Booking;
use App\Models\CustomField;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Services\DataTable;

class BookingDataTable extends DataTable
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
            ->editColumn('id', function ($booking) {
                return "#" . $booking->id;
            })
            ->editColumn('booking_at', function ($booking) {
                return getDateColumn($booking, 'booking_at');
            })
            ->editColumn('user.name', function ($booking) {
                return getLinksColumnByRouteName([$booking->user], 'users.edit', 'id', 'name');
            })
            ->editColumn('article', function ($booking) {
                return getLinksColumnByRouteName($booking->article, 'articles.edit', 'id', 'name');
            })
            ->editColumn('business.name', function ($booking) {
                return getLinksColumnByRouteName([$booking->business], 'businesses.edit', 'id', 'name');
            })
            ->editColumn('total', function ($booking) {
                return "<span class='text-bold text-success'>" . getPrice($booking->getTotal()) . "</span>";
            })
            ->editColumn('address', function ($booking) {
                return $booking->address->address;
            })
            ->editColumn('taxes', function ($booking) {
                return "<span class='text-bold'>" . getPrice($booking->getTaxesValue()) . "</span>";
            })
            ->editColumn('coupon', function ($booking) {
                return $booking->coupon->code . " <span class='text-bold'>(" . getPrice(-$booking->getCouponValue()) . ")</span>";
            })
            ->editColumn('transaction_status.status', function ($booking) {
                if (isset($booking->transactionStatus))
                    return "<span class='badge px-2 py-1 bg-" . setting('theme_color') . "'>" . $booking->transactionStatus->status . "</span>";
                else
                    return "";
            })
            ->editColumn('payment.payment_status.status', function ($booking) {
                if (isset($booking->payment)) {
                    return "<span class='badge px-2 py-1 bg-" . setting('theme_color') . "'>" . $booking->payment->paymentStatus->status . "</span>";
                } else {
                    return '-';
                }
            })
            ->setRowClass(function ($booking) {
                return $booking->cancel ? 'booking-cancel' : '';
            })
            ->addColumn('action', 'bookings.datatables_actions')
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
                'data' => 'id',
                'title' => trans('lang.booking_id'),
            ],
            [
                'data' => 'article',
                'title' => trans('lang.booking_article'),

            ],
            [
                'data' => 'business.name',
                'name' => 'business',
                'title' => trans('lang.business'),

            ],
            [
                'data' => 'user.name',
                'title' => trans('lang.booking_user_id'),
            ],
            [
                'data' => 'address',
                'name' => 'address',
                'title' => trans('lang.booking_address'),
            ],
            [
                'data' => 'transaction_status.status',
                'name' => 'transactionStatus.status',
                'title' => trans('lang.booking_transaction_status_id'),
            ],
            [
                'data' => 'payment.payment_status.status',
                'name' => 'payment.paymentStatus.status',
                'title' => trans('lang.payment_payment_status_id'),
            ],
            [
                'data' => 'taxes',
                'title' => trans('lang.booking_taxes'),
                'searchable' => false,
                'orderable' => false,

            ],
            [
                'data' => 'coupon',
                'title' => trans('lang.booking_coupon'),
                'searchable' => false,
                'orderable' => false,

            ],
            [
                'data' => 'total',
                'title' => trans('lang.booking_total'),
                'searchable' => false,
                'orderable' => false,

            ],
            [
                'data' => 'booking_at',
                'title' => trans('lang.booking_booking_at'),

            ],
        ];

        $hasCustomField = in_array(Booking::class, setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFieldsCollection = CustomField::where('custom_field_model', Booking::class)->where('in_table', '=', true)->get();
            foreach ($customFieldsCollection as $key => $field) {
                array_splice($columns, $field->order - 1, 0, [[
                    'data' => 'custom_fields.' . $field->name . '.view',
                    'title' => trans('lang.booking_' . $field->name),
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
     * @param Booking $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Booking $model)
    {
        if (auth()->user()->hasRole('admin')) {
            return $model->newQuery()->with("user")->with("transactionStatus")->with("payment")->with("payment.paymentStatus")->select('bookings.*');
        } else if (auth()->user()->hasRole('salon owner')) {
            $businessId = DB::raw("json_extract(business, '$.id')");
            return $model->newQuery()->with("user")->with("transactionStatus")->with("payment")->with("payment.paymentStatus")->join("business_users", "business_users.business_id", "=", $businessId)
                ->where('business_users.user_id', auth()->id())
                ->groupBy('bookings.id')
                ->select('bookings.*');

        } else if (auth()->user()->hasRole('class_manager')) {
            $businessId = DB::raw("json_extract(business, '$.id')");
            return $model->newQuery()->with("user")->with("transactionStatus")->with("payment")->with("payment.paymentStatus")->join("business_users", "business_users.business_id", "=", $businessId)
                ->where('business_users.user_id', auth()->id())
                ->groupBy('bookings.id')
                ->select('bookings.*');

        } else if (auth()->user()->hasRole('customer')) {
            return $model->newQuery()->with("user")->with("transactionStatus")->with("payment")->with("payment.paymentStatus")->where('bookings.user_id', auth()->id())
                ->select('bookings.*')
                ->groupBy('bookings.id');
        } else {
            return $model->newQuery()->with("user")->with("transactionStatus")->with("payment")->with("payment.paymentStatus")->select('bookings.*');
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
        return 'bookingsdatatable_' . time();
    }
}
