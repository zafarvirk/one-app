<?php
/*
 * File name: GymDataTable.php
 * Last modified: 2022.02.13 at 23:05:09
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\DataTables;

use App\Models\CustomField;
use App\Models\Business;
use Barryvdh\DomPDF\Facade as PDF;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Services\DataTable;

class GymDataTable extends DataTable
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
            ->editColumn('image', function ($gym) {
                return getMediaColumn($gym, 'image');
            })
            ->editColumn('name', function ($gym) {
                if ($gym['featured']) {
                    return $gym->name . "<span class='badge bg-" . setting('theme_color') . " p-1 m-2'>" . trans('lang.article_featured') . "</span>";
                }
                return $gym->name;
            })
            ->editColumn('business_category.name', function ($gym) {
                return getLinksColumnByRouteName([$gym->business_category], "businessCategories.edit", 'id', 'name');
            })
            ->editColumn('users', function ($gym) {
                return getLinksColumnByRouteName($gym->users, 'users.edit', 'id', 'name');
            })->editColumn('address.address', function ($gym) {
                return getLinksColumnByRouteName([$gym->address], 'addresses.edit', 'id', 'address');
            })->editColumn('taxes', function ($gym) {
                return getLinksColumnByRouteName($gym->taxes, 'taxes.edit', 'id', 'name');
            })
            ->editColumn('available', function ($gym) {
                return getBooleanColumn($gym, 'available');
            })
            ->editColumn('closed', function ($gym) {
                return getNotBooleanColumn($gym, 'closed',trans('lang.gym_closed'),trans('lang.gym_open'));
            })
            ->editColumn('accepted', function ($gym) {
                return getBooleanColumn($gym, 'accepted');
            })
            ->editColumn('is_populer', function ($gym) {
                return getBooleanColumn($gym, 'is_populer');
            })
            ->editColumn('updated_at', function ($gym) {
                return getDateColumn($gym);
            })
            ->addColumn('action', 'gyms.datatables_actions')
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
                'title' => trans('lang.gym_image'),
                'searchable' => false, 'orderable' => false, 'exportable' => false, 'printable' => false,
            ],
            [
                'data' => 'name',
                'title' => trans('lang.gym_name'),

            ],
            [
                'data' => 'business_category.name',
                'name' => 'business_category.name',
                'title' => trans('lang.gym_business_category_id'),

            ],
            [
                'data' => 'users',
                'title' => trans('lang.gym_users'),
                'searchable' => false,
                'orderable' => false
            ],
            [
                'data' => 'phone_number',
                'title' => trans('lang.gym_phone_number'),

            ],
            [
                'data' => 'mobile_number',
                'title' => trans('lang.gym_mobile_number'),

            ],
            [
                'data' => 'address.address',
                'title' => trans('lang.gym_address'),
                'searchable' => false,
                'orderable' => false
            ],
            [
                'data' => 'availability_range',
                'title' => trans('lang.gym_availability_range'),

            ],
            [
                'data' => 'taxes',
                'title' => trans('lang.gym_taxes'),
                'searchable' => false,
                'orderable' => false
            ],
            [
                'data' => 'available',
                'title' => trans('lang.gym_available'),

            ],
            [
                'data' => 'closed',
                'title' => trans('lang.gym_closed'),
                'searchable' => false,

            ],
            [
                'data' => 'accepted',
                'title' => trans('lang.gym_accepted'),

            ],
            [
                'data' => 'is_populer',
                'title' => trans('lang.gym_populer'),

            ],
            [
                'data' => 'updated_at',
                'title' => trans('lang.address_updated_at'),
                'searchable' => false,
            ]
        ];

        $hasCustomField = in_array(Business::class, setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFieldsCollection = CustomField::where('custom_field_model', Business::class)->where('in_table', '=', true)->get();
            foreach ($customFieldsCollection as $key => $field) {
                array_splice($columns, $field->order - 1, 0, [[
                    'data' => 'custom_fields.' . $field->name . '.view',
                    'title' => trans('lang.salon_' . $field->name),
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
     * @param Salon $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Business $model): \Illuminate\Database\Eloquent\Builder
    {
        if (auth()->user()->hasRole('admin')) {
            return $model->newQuery()->with("business_category")
            ->with("address")
            ->join("business_modules", "business_modules.business_id", "=", "businesses.id")
            ->where('business_modules.module_id', 3)
            ->where('businesses.accepted', '1')->select("businesses.*");
        } else if (auth()->user()->hasRole('salon owner')) {
            return $model->newQuery()
                ->with("business_category")
                ->with("address")
                ->join("business_users", "business_users.business_id", "=", "businesses.id")
                ->join("business_modules", "business_modules.business_id", "=", "businesses.id")
                ->where('business_modules.module_id', 3)
                ->where('business_users.user_id', auth()->id())
                ->groupBy("businesses.id")
                ->where('businesses.accepted', '1')
                ->select("businesses.*");
        } else if (auth()->user()->hasRole('class_manager')) {
            return $model->newQuery()
                ->with("business_category")
                ->with("address")
                ->join("business_users", "business_users.business_id", "=", "businesses.id")
                ->join("business_modules", "business_modules.business_id", "=", "businesses.id")
                ->where('business_modules.module_id', 3)
                ->where('business_users.user_id', auth()->id())
                ->groupBy("businesses.id")
                ->where('businesses.accepted', '1')
                ->select("businesses.*");
        } else {
            return $model->newQuery()->with("business_category")
            ->with("address")
            ->join("business_modules", "business_modules.business_id", "=", "businesses.id")
            ->where('business_modules.module_id', 3)
            ->where('businesses.accepted', '1')->select("businesses.*");
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
                        ), true),
                    'fixedColumns' => [],
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
        return 'gymsdatatable_' . time();
    }
}
