<?php
/*
 * File name: Salon.php
 * Last modified: 2022.05.18 at 20:18:02
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Models;

use App\Casts\BusinessCast;
use App\Traits\HasTranslations;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Eloquent as Model;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Database\Eloquent\Collection;
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
use Spatie\OpeningHours\OpeningHours;

/**
 * Class Salon
 * @package App\Models
 * @version January 13, 2021, 11:11 am UTC
 *
 * @property SalonLevel salonLevel
 * @property Collection[] users
 * @property Collection[] taxes
 * @property Address address
 * @property Collection[] awards
 * @property Collection[] experiences
 * @property Collection[] availabilityHours
 * @property Collection[] eServices
 * @property Collection[] galleries
 * @property integer id
 * @property string name
 * @property integer salon_level_id
 * @property string description
 * @property string phone_number
 * @property string mobile_number
 * @property double availability_range
 * @property boolean available
 * @property boolean featured
 * @property boolean accepted
 */
class Business extends Model implements HasMedia, Castable
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
        'business_category_id' => 'required|exists:business_categories,id',
        'address_id' => 'nullable|exists:addresses,id',
        'phone_number' => 'max:50',
        'mobile_number' => 'max:50',
        'availability_range' => 'required|max:9999999,99|min:0'
    ];
        /**
     * Validation rules
     *
     * @var array
     */
    public static $adminRules = [
        'name' => 'required',
        'description' => 'required',
        'delivery_fee' => 'nullable|numeric|min:0',
        'address_id' => 'nullable',
        'admin_commission' => 'required|numeric|min:0',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $managerRules = [
        'name' => 'required',
        'description' => 'required',
        'delivery_fee' => 'nullable|numeric|min:0',
        'address_id' => 'nullable',
    ];

    public $translatable = [
        'name',
        'description',
        'modules'
    ];
    public $table = 'businesses';
    public $fillable = [
        'name',
        'business_category_id',
        'address_id',
        'description',
        'type',
        'modules',
        'phone_number',
        'mobile_number',
        'availability_range',
        'admin_commission',
        'delivery_fee',
        'default_tax',
        'delivery_range',
        'available_for_delivery',
        'closed',
        'information',
        'active',
        'available',
        'featured',
        'accepted',
        'is_populer',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'image' => 'string',
        'name' => 'string',
        'business_category_id' => 'integer',
        'address_id' => 'integer',
        'description' => 'string',
        'modules' => 'string',
        'phone_number' => 'string',
        'mobile_number' => 'string',
        'availability_range' => 'double',
        'available' => 'boolean',
        'featured' => 'boolean',
        'accepted' => 'boolean',
        'is_populer' => 'boolean',
    ];
    /**
     * New Attributes
     *
     * @var array
     */
    protected $appends = [
        'custom_fields',
        'has_media',
        'rate',
        'closed',
        'total_reviews'
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
        return BusinessCast::class;
    }

    public function discountables(): MorphMany
    {
        return $this->morphMany('App\Models\Discountable', 'discountable');
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

    public function customFieldsValues(): MorphMany
    {
        return $this->morphMany('App\Models\CustomFieldValue', 'customizable');
    }

    public function scopeNear($query, $latitude, $longitude, $areaLatitude, $areaLongitude)
    {
        // Calculate the distant in mile
        $distance = "SQRT(
                    POW(69.1 * (addresses.latitude - $latitude), 2) +
                    POW(69.1 * ($longitude - addresses.longitude) * COS(addresses.latitude / 57.3), 2))";

        // Calculate the distant in mile
        $area = "SQRT(
                    POW(69.1 * (addresses.latitude - $areaLatitude), 2) +
                    POW(69.1 * ($areaLongitude - addresses.longitude) * COS(addresses.latitude / 57.3), 2))";

        // convert the distance to KM if the distance unit is KM
        if (setting('distance_unit') == 'km') {
            $distance .= " * 1.60934"; // 1 Mile = 1.60934 KM
            $area .= " * 1.60934"; // 1 Mile = 1.60934 KM
        }

        return $query
            ->join('addresses', 'businesses.address_id', '=', 'addresses.id')
            ->whereRaw("$distance < businesses.availability_range")
            ->select(DB::raw($distance . " AS distance"), DB::raw($area . " AS area"), "businesses.*")
            ->orderBy('area');
    }

    /**
     * Provider ready when he is accepted by admin and marked as available
     * and is open now
     */
    public function getClosedAttribute(): bool
    {
        return !$this->accepted || !$this->attributes['available'] || $this->openingHours()->isClosed();
    }

    public function openingHours(): OpeningHours
    {
        $openingHoursArray = [];
        foreach ($this->availabilityHours as $element) {
            $openingHoursArray[$element['day']][] = $element['start_at'] . '-' . $element['end_at'];
        }
        return OpeningHours::createAndMergeOverlappingRanges($openingHoursArray);
    }

    /**
     * get each range of 30 min with open/close salon
     */
    public function weekCalendarRange(Carbon $date, int $employeeId): array
    {
        $period = CarbonPeriod::since($date->subDay()->ceilDay())->minutes(30)->until($date->addDay()->ceilDay()->subMinutes(30));
        $dates = [];
        // Iterate over the period
        foreach ($period as $key => $d) {
            $firstDate = $d->locale('en')->toDateTime();
            $isOpen = $firstDate > new \DateTime("now");
            if($isOpen){
                $isOpen = $this->openingHours()->isOpenAt($firstDate);
                if ($isOpen && $employeeId != 0) {
                    $isOpen = !($this->bookings()->where('booking_at', '=', $firstDate)
                        ->where('cancel', '<>', '1')
                        ->whereNotIn('transaction_status_id', ['6', '7'])
                        ->where('employee_id', '=', $employeeId)
                        ->count());
                }
            }
            $times = $d->locale('en')->toIso8601String();
            $dates[] = [$times, $isOpen];
        }
        return $dates;
    }

    /**
     * @return HasMany
     **/
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'business->id')->orderBy('booking_at');
    }

    public function getRateAttribute(): float
    {
        return (float)$this->BusinessReviews()->avg('rate');
    }

    /**
     * @return HasMany
     **/
    public function BusinessReviews(): HasMany
    {
        return $this->hasMany(BusinessReview::class, 'business_id');
    }

    public function getTotalReviewsAttribute(): float
    {
        return $this->BusinessReviews()->count();
    }

    /**
     * @return BelongsTo
     **/
    public function business_category(): BelongsTo
    {
        return $this->belongsTo(BusinessCategory::class, 'business_category_id', 'id');
    }

    /**
     * @return HasMany
     **/
    public function awards(): HasMany
    {
        return $this->hasMany(Award::class, 'business_id');
    }

    /**
     * @return HasMany
     **/
    public function experiences(): HasMany
    {
        return $this->hasMany(Experience::class, 'business_id');
    }

    /**
     * @return HasMany
     **/
    public function availabilityHours(): HasMany
    {
        return $this->hasMany(AvailabilityHour::class, 'business_id')->orderBy('start_at');
    }

    /**
     * @return HasMany
     **/
    public function article(): HasMany
    {
        return $this->hasMany(Article::class, 'business_id');
    }

    /**
     * @return HasMany
     **/
    public function galleries(): HasMany
    {
        return $this->hasMany(Gallery::class, 'business_id');
    }

    /**
     * @return BelongsToMany
     **/
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'business_users');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function drivers()
    {
        return $this->belongsToMany(\App\Models\User::class, 'driver_businesses');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function fields()
    {
        return $this->belongsToMany(\App\Models\Field::class, 'business_fields');
    }

    /**
     * @return BelongsTo
     **/
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    /**
     * @return BelongsToMany
     **/
    public function taxes(): BelongsToMany
    {
        return $this->belongsToMany(Tax::class, 'business_taxes');
    }

    /**
     * Add Media to api results
     * @return bool
     */
    public function getHasMediaAttribute(): bool
    {
        return $this->hasMedia('image');
    }

    /**
     * @return BelongsToMany
     **/
    public function aminities()
    {
        return $this->belongsToMany(Aminities::class, 'aminities_business');
    }
    public function highlights()
    {
        return $this->belongsToMany(Highlight::class, 'highlight_businesses');
    }
    public function features()
    {
        return $this->belongsToMany(Features::class, 'features_businesses');
    }    
    /**
     * @return HasMany
     **/
    public function posts()
    {
        return $this->hasMany(Post::class, 'business_id');
    }

    public function business_modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'business_modules');
    }    
    /**
     * @return HasMany
     **/
    public function plans()
    {
        return $this->hasMany(Plans::class, 'plan_business_id');
    }



}
