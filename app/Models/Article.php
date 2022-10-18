<?php
/*
 * File name: Article.php
 * Last modified: 2022.03.11 at 22:24:56
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Models;

use App\Casts\ArticleCast;
use App\Traits\HasTranslations;
use Eloquent as Model;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;
use Spatie\Image\Exceptions\InvalidManipulation;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\Models\Media;

/**
 * Class Article
 * @package App\Models
 * @version January 19, 2021, 1:59 pm UTC
 *
 * @property Collection category
 * @property Salon salon
 * @property Collection Option
 * @property string name
 * @property integer id
 * @property double price
 * @property double discount_price
 * @property string duration
 * @property string description
 * @property boolean featured
 * @property boolean enable_booking
 * @property boolean enable_at_salon
 * @property boolean enable_at_customer_address
 * @property boolean available
 * @property integer business_id
 */
class Article extends Model implements HasMedia, Castable
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
        'name' => 'required|max:127',
        'price' => 'required|numeric|min:0|max:99999999,99',
        'discount_price' => 'nullable|numeric|min:0|max:99999999,99',
        // 'duration' => 'nullable|max:16',
        'description' => 'required',
        'business_id' => 'required|exists:businesses,id'
    ];
    public $translatable = [
        'name',
        'description',
    ];
    public $table = 'article';
    public $fillable = [
        'name',
        'type',
        'price',
        'discount_price',
        // 'duration',
        'description',
        'capacity',
        'package_items_count',
        'unit',
        'featured',
        'enable_booking',
        'enable_at_business',
        'enable_at_customer_address',
        'available',
        'deliverable',
        'business_id',
        'max_appoiintments',
        'is_staff_required'
    ];
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'image' => 'string',
        'price' => 'double',
        'discount_price' => 'double',
        // 'duration' => 'string',
        'description' => 'string',
        'capacity' => 'double',
        'package_items_count' => 'integer',
        'unit' => 'string',
        'featured' => 'boolean',
        'deliverable' => 'boolean',
        'enable_booking' => 'boolean',
        'enable_at_business' => 'boolean',
        'enable_at_customer_address' => 'boolean',
        'available' => 'boolean',
        'max_appoiintments' => 'integer',
        'is_staff_required' => 'boolean',
        'business_id' => 'integer',
    ];
    /**
     * New Attributes
     *
     * @var array
     */
    protected $appends = [
        'custom_fields',
        'has_media',
        'is_favorite',
    ];

    protected $hidden = [
        "created_at",
        "updated_at",
    ];

    /**
     * @return string
     */
    public static function castUsing(): string
    {
        return ArticleCast::class;
    }

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
    public function getFirstMediaUrl($collectionName = 'default', string $conversion = '')
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
     * Add Media to api results
     * @return bool
     */
    public function getHasMediaAttribute(): bool
    {
        return $this->hasMedia('image');
    }

    public function scopeNear($query, $latitude, $longitude)
    {
        // Calculate the distant in mile
        $distance = "SQRT(
                    POW(69.1 * (addresses.latitude - $latitude), 2) +
                    POW(69.1 * ($longitude - addresses.longitude) * COS(addresses.latitude / 57.3), 2))";

        // convert the distance to KM if the distance unit is KM
        if (setting('distance_unit') == 'km') {
            $distance .= " * 1.60934"; // 1 Mile = 1.60934 KM
        }

        return $query
            ->join('businesses', 'businesses.id', '=', 'article.business_id')
            ->join('addresses', 'businesses.address_id', '=', 'addresses.id')
            ->whereRaw("$distance < businesses.availability_range")
            ->select(DB::raw($distance . " AS distance"), "article.*")
            ->orderBy('distance');
    }

    /**
     * Check if is a favorite for current user
     * @return bool
     */
    public function getIsFavoriteAttribute(): bool
    {
        return $this->favorites()->count() > 0;
    }

    /**
     * @return HasMany
     **/
    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'article_id')->where('favorites.user_id', auth()->id());
    }

    /**
     * @return BelongsTo
     **/
    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id', 'id');
    }

    /**
     * @return HasMany
     **/
    public function options()
    {
        return $this->hasMany(Option::class, 'article_id');
    }

    /**
     * @return BelongsToMany
     **/
    public function optionGroups(): BelongsToMany
    {
        return $this->belongsToMany(OptionGroup::class, 'options')->distinct();
    }

    /**
     * @return BelongsToMany
     **/
    public function article_categories(): BelongsToMany
    {
        return $this->belongsToMany(ArticleCategories::class, 'e_service_categories');
    }
    
    /**
     * @return BelongsToMany
     **/
    public function article_staff(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'article_staff');
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->discount_price > 0 ? $this->discount_price : $this->price;
    }

    /**
     * @return bool
     */
    public function hasDiscount(): bool
    {
        return $this->discount_price > 0;
    }

    public function discountables(): MorphMany
    {
        return $this->morphMany('App\Models\Discountable', 'discountable');
    }
    
    /**
     * @return HasMany
     **/
    public function posts()
    {
        return $this->hasMany(Post::class, 'article_id');
    }

    /**
     * @return HasMany
     **/
    public function schedule()
    {
        return $this->hasMany(ArticleSchedule::class, 'article_id');
    }

    public function plans()
    {
        return $this->belongsToMany(Plan::class, 'plan_articles', 'article_id', 'plan_id');
    }
}
