<?php
/*
 * File name: ArticleRequest.php
 * Last modified: 2022.02.02 at 19:14:31
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
*/

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
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\Models\Media;

class ArticleRequest extends Model implements HasMedia
{

    use HasMediaTrait {
        getFirstMediaUrl as protected getFirstMediaUrlTrait;
    }
    use HasTranslations;

    /**
     * Validation rules
     *
     * @var array
    */
    public static $rules = [
        'name' => 'required',
    ];

    public $table = 'article_requests';
    public $fillable = [
        'name',
        'required_datetime',
        'type',
        'user_id',
        'business_category_id',
        'transaction_status_id',
        'address_id',
        'scope',
        'no_of_offers',
        'merchants_informed',
        'order_id',
        'address_from_text',
        'address_from_coordinates',
        'request_type',
        'price_type',
        'price',
        'price_from',
        'offer_accepted_by_business_id'
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
        'required_datetime' => 'string',
        'type' => 'string',
        'user_id' => 'integer',
        'business_category_id' => 'integer',
        'transaction_status_id' => 'integer',
        'address_id' => 'integer',
        'scope' => 'string',
        'no_of_offers' => 'integer',
        'merchants_informed' => 'integer',
        'order_id' => 'integer',
        'address_from_text' => 'string',
        'address_from_coordinates' => 'string',
        'request_type' => 'string',
        'price_type' => 'string',
        'price' => 'string',
        'price_from' => 'string',
        'offer_accepted_by_business_id' => 'integer'
    ];

    protected $hidden = [
        "created_at",
        "updated_at",
    ];


    

    /**
     * @param Media|null $media
     * @throws InvalidManipulation
     */
    public function registerMediaConversions(Media $media = null)
    {
        $this->addMediaConversion('thumb')
            ->fit(Manipulations::FIT_CROP, 200, 200)
            ->sharpen(10);

        $this->addMediaConversion('icon')
            ->fit(Manipulations::FIT_CROP, 100, 100)
            ->sharpen(10);
    }

    /**
     * to generate media url in case of fallback will
     * return the file type icon
     * @param string $conversion
     * @return string url
     */
    public function getFirstMediaUrl($collectionName = 'default', $conversion = '')
    {
        $url = $this->getFirstMediaUrlTrait($collectionName);
        $array = explode('.', $url);
        $extension = strtolower(end($array));
        if (in_array($extension, config('medialibrary.extensions_has_thumb'))) {
            return asset($this->getFirstMediaUrlTrait($collectionName, $conversion));
        } else {
            return asset(config('medialibrary.icons_folder') . '/' . $extension . '.png');
        }
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
    public function business_category(): BelongsTo
    {
        return $this->belongsTo(BusinessCategory::class, 'business_category_id' , 'id');
    }

    /**
     * @return BelongsTo
     **/
    public function transaction_status(): BelongsTo
    {
        return $this->belongsTo(TransactionStatus::class, 'transaction_status_id' , 'id');
    }

    /**
     * @return BelongsTo
     **/
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'address_id' , 'id');
    }

    /**
     * @return BelongsTo
     **/
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id' , 'id');
    }

}
