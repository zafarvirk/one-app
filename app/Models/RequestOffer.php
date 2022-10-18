<?php


namespace App\Models;

use App\Traits\HasTranslations;
use Eloquent as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;


class RequestOffer extends Model
{

    use HasTranslations;

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'article_request_id' => 'required|exists:article_requests,id',
        'business_id' => 'required|exists:businesses,id'
    ];

    public $timestamps = false;
    public $table = 'request_offers';
    public $fillable = [
        'article_request_id',
        'quote_amount',
        'status',
        'user_id',
        'business_id'
    ];
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'article_request_id' => 'integer',
        'quote_amount' => 'string',
        'status' => 'string',
        'user_id' => 'integer',
        'business_id' => 'integer'
    ];
    public $translatable = [
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

    /**
     * @return array
     */
    public function getCustomFieldsAttribute(): array
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

    /**
     * @return MorphMany
     */
    public function customFieldsValues(): MorphMany
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

    /**
     * @return BelongsTo
     **/
    public function request(): BelongsTo
    {
        return $this->belongsTo(ArticleRequest::class, 'article_request_id', 'id');
    }

    /**
     * @return BelongsTo
     **/
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}
