<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Carbon\Carbon;
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

class ArticleSchedule extends Model
{
    /**
     * Validation rules
     *
     * @var array
    */
    public static $rules = [
        'article_id' => 'required|exists:article,id',
        'start_date' => 'required',
        'start_time' => 'required',
        'duration' => 'required',
        'repeat' => 'required|in:never,weekly',
        'days' => 'required_if:repeat,=,weekly|array',
        'days.*' => 'string',
    ];

    public $translatable = [
    ];

    public $table = 'article_schedule';
    public $fillable = [
        'article_id',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'duration',
        'repeat',
        'days',
        'recurrence_rules'
    ];
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
    */
    protected $casts = [
        'article_id' => 'integer',
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
        'start_time' => 'string',
        'end_time' => 'string',
        'repeat' => 'string',
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

    public function getDaysAttribute($days)
    {
        $days = (array) json_decode($days);
        $selectedDays = [];

        foreach ($days as $day => $selected) {
            if ($selected) {
                $selectedDays[] = $day;
            }
        }

        return $selectedDays;
    }

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
    public function class()
    {
        return $this->belongsTo(Article::class, 'article_id', 'id');
    }


}
