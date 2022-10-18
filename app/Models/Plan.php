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

class Plan extends Model
{
    /**
     * Validation rules
     *
     * @var array
    */
    public static $rules = [
        'name' => 'required|min:50',
        'price' => 'required|numeric|min:0|max:99999999,99',
        'description' => 'required|max:255',
        'no_of_sessions' => 'required',
        'plan_duration' => 'required',
        'custom_start_date' => 'required',
        'allow_canceltion' => 'required',
        'article_id' => 'required|exists:article,id',
        'plan_business_id' => 'required|exists:businesses,id'
    ];

    public $translatable = [
        'name',
        'description',
    ];

    public $table = 'plans';
    public $fillable = [
        'name',
        'price_type',
        'price_frequency',
        'price',
        'description',
        'type',
        'no_of_sessions',
        'plan_duration',
        'custom_start_date',
        'allow_canceltion',
        'plan_business_id'
    ];
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
    */
    protected $casts = [
        'name' => 'string',
        'price' => 'double',
        'plan_duration' => 'string',
        'description' => 'string',
        'plan_business_id' => 'integer',
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

    public function plans_article(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'plan_articles');
    }
    
    /**
     * @return BelongsTo
     **/
    public function business()
    {
        return $this->belongsTo(Business::class, 'plan_business_id', 'id');
    }    
    /**
     * @return HasMany
     **/
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }

}
