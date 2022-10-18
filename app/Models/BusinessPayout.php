<?php
/*
 * File name: BusinessPayout.php
 * Last modified: 2022.02.02 at 21:21:33
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Date;

/**
 * Class BusinessPayout
 * @package App\Models
 * @version January 30, 2021, 11:17 am UTC
 *
 * @property Salon salon
 * @property integer business_id
 * @property string method
 * @property double amount
 * @property Date paid_date
 * @property string note
 */
class BusinessPayout extends Model
{

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'business_id' => 'required|exists:salons,id',
        'method' => 'required',
        'amount' => 'required|numeric|min:0.01|max:99999999,99'
    ];
    public $table = 'business_payouts';
    public $fillable = [
        'business_id',
        'method',
        'amount',
        'paid_date',
        'note'
    ];
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'business_id' => 'integer',
        'method' => 'string',
        'amount' => 'double',
        'paid_date' => 'datetime',
        'note' => 'string'
    ];
    /**
     * New Attributes
     *
     * @var array
     */
    protected $appends = [
        'custom_fields',
    ];

    public function getCustomFieldsAttribute()
    {
        $hasCustomField = in_array(static::class, setting('custom_field_models', []));
        if (!$hasCustomField) {
            return [];
        }
        $array = $this->customFieldsValues()
            ->join('custom_fields', 'custom_fields.id', '=', 'custom_field_values.custom_field_id')
            ->where('custom_fields.in_table', '=', true)
            ->get()->toArray();

        return convertToAssoc($array, 'name');
    }

    public function customFieldsValues()
    {
        return $this->morphMany('App\Models\CustomFieldValue', 'customizable');
    }

    /**
     * @return BelongsTo
     **/
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id', 'id');
    }

}
