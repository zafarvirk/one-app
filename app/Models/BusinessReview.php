<?php
/*
 * File name: BusinessReview.php
 * Last modified: 2022.02.15 at 16:13:21
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Models;

use App\Casts\EServiceCollectionCast;
use Eloquent as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

/**
 * Class BusinessReview
 * @package App\Models
 * @version January 23, 2021, 7:42 pm UTC
 *
 * @property User user
 * @property Salon salon
 * @property EService[] e_services
 * @property string review
 * @property double rate
 * @property integer user_id
 * @property integer employee_id
 * @property integer salon_id
 * @method hasMedia(mixed $collection)
 * @method getFirstMedia(mixed $collection)
 */
class BusinessReview extends Model
{

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'rate' => 'required|numeric|max:5|min:0',
        'booking_id' => 'nullable|exists:bookings,id',
        'order_id' => 'nullable|exists:orders,id'
    ];
    public $table = 'business_reviews';
    public $fillable = [
        'review',
        'rate',
        'booking_id',
        'user_id',
        'business_id',
    ];
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'review' => 'string',
        'rate' => 'double',
        'booking_id' => 'integer'
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
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'id');
    }

    /**
     * @return BelongsTo
     **/
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}
