<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class ArticleOrder
 * @package App\Models
 * @version August 31, 2019, 11:18 am UTC
 *
 * @property \App\Models\Product product
 * @property \App\Models\Option[] options
 * @property \App\Models\Order order
 * @property double price
 * @property integer quantity
 * @property integer article_id
 * @property integer order_id
 */
class ArticleOrder extends Model
{

    public $table = 'article_orders';
    


    public $fillable = [
        'price',
        'quantity',
        'article_id',
        'order_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'double',
        'quantity' => 'integer',
        'article_id' => 'integer',
        'order_id' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'price' => 'required',
        'article_id' => 'required|exists:articles,id',
        'order_id' => 'required|exists:orders,id'
    ];

    /**
     * New Attributes
     *
     * @var array
     */
    protected $appends = [
        'custom_fields',
        // 'options'
    ];

    public function customFieldsValues()
    {
        return $this->morphMany('App\Models\CustomFieldValue', 'customizable');
    }

    public function getCustomFieldsAttribute()
    {
        $hasCustomField = in_array(static::class,setting('custom_field_models',[]));
        if (!$hasCustomField){
            return [];
        }
        $array = $this->customFieldsValues()
            ->join('custom_fields','custom_fields.id','=','custom_field_values.custom_field_id')
            ->where('custom_fields.in_table','=',true)
            ->get()->toArray();

        return convertToAssoc($array,'name');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function article()
    {
        return $this->belongsTo(\App\Models\Article::class, 'article_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function options()
    {
        return $this->belongsToMany(\App\Models\Option::class, 'article_order_options');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function order()
    {
        return $this->belongsTo(\App\Models\Order::class, 'order_id', 'id');
    }
//        /**
//    * @return \Illuminate\Database\Eloquent\Collection
//    */
//    public function getOptionsAttribute()
//    {
//        return $this->options()->get(['options.id', 'options.name']);
//    }
}
