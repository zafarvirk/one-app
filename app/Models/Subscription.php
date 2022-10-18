<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Eloquent as Model;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\CastsInboundAttributes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;
use Spatie\Image\Exceptions\InvalidManipulation;
use Spatie\Image\Manipulations;

class Subscription extends Model
{
    /**
     * Validation rules
     *
     * @var array
    */
    public static $rules = [
        // 'user_id' => 'required|exists:users,id',
        'plan_id' => 'required|exists:plans,id'
    ];

    public $translatable = [
    ];

    public $table = 'subscriptions';
    public $fillable = [
        'user_id',
        'plan_id',
        'expiry_date',
        'available_sessions',
        'payment_id',
        'is_active',
    ];
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
    */
    protected $casts = [
        'user_id' => 'integer',
        'plan_id' => 'integer',
        'expiry_date' => 'string',
        'available_sessions' => 'string',
        'payment_id' => 'integer',
        'is_active' => 'integer',
    ];
    /**
     * New Attributes
     *
     * @var array
    */
    protected $appends = [
        'custom_fields',
    ];

    protected $hidden = [
        "created_at",
        "updated_at",
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
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * @return BelongsTo
     **/
    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'id');
    }

}
