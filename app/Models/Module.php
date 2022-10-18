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


class Module extends Model
{
    use HasTranslations;

    /**
     * Validation rules
     *
     * @var array
    */
    public static $rules = [
        'name' => 'required',
        'status' => 'required',
    ];

    public $table = 'modules';
    public $fillable = [
        'name',
        'status'
    ];

    public $translatable = [
        'name',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'status' => 'boolean',
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

    



    
}
