<?php
/*
 * File name: Coupon.php
 * Last modified: 2022.02.12 at 02:17:43
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Models;

use App\Casts\CouponCast;
use App\Traits\HasTranslations;
use DateTime;
use Eloquent as Model;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\CastsInboundAttributes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;

/**
 * Class Coupon
 * @package App\Models
 *
 * @property integer id
 * @property string code
 * @property double discount
 * @property string discount_type
 * @property string description
 * @property DateTime expires_at
 * @property boolean enabled
 * @property Collection[] eServices
 * @property Collection[] salons
 * @property Collection[] categories
 * @property float|int $value
 */
class Coupon extends Model implements Castable
{

    use HasTranslations;

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'code' => 'required|unique:coupons|max:50',
        'discount' => 'required|numeric|min:0',
        'discount_type' => 'required',
        'expires_at' => 'required|date|after_or_equal:tomorrow'
    ];
    public $translatable = [
        'description',
    ];
    public $table = 'coupons';
    public $fillable = [
        'code',
        'discount',
        'discount_type',
        'description',
        'expires_at',
        'enabled'
    ];
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'code' => 'string',
        'discount' => 'double',
        'value' => 'double',
        'discount_type' => 'string',
        'description' => 'string',
        'expires_at' => 'datetime',
        'enabled' => 'boolean'
    ];
    /**
     * New Attributes
     *
     * @var array
     */
    protected $appends = [
        'custom_fields',

    ];

    /**
     * @return CastsAttributes|CastsInboundAttributes|string
     */
    public static function castUsing()
    {
        return CouponCast::class;
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

    public function discountables(): HasMany
    {
        return $this->hasMany(Discountable::class, 'coupon_id');
    }

    public function article(): MorphToMany
    {
        return $this->morphedByMany(Article::class, 'discountable');
    }

    public function business_categories(): MorphToMany
    {
        return $this->morphedByMany(BusinessCategory::class, 'discountable');
    }

    public function business(): MorphToMany
    {
        return $this->morphedByMany(Business::class, 'discountable');
    }

    public function getValue($article): Coupon
    {
        $couponValue = 0;
        $articleOfCategories = $this->categories->pluck('article')->flatten()->toArray();
        $articleOfBusiness = $this->business->pluck('article')->flatten()->toArray();
        $couponArticle = $this->article->concat($articleOfCategories)->concat($articleOfBusiness);
        $couponArticleIds = $couponArticle->pluck('id')->toArray();
        foreach ($article as $articl) {
            if (in_array($articl->id, $couponArticleIds)) {
                if ($this->discount_type == 'percent') {
                    $couponValue += $articl->getPrice() * $this->discount / 100;
                } else {
                    $couponValue += $this->discount;
                }
            }
        }
        $this->value = $couponValue;
        unset($this['article'], $this['business'], $this['categories']);
        return $this;
    }

}
