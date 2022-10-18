<?php
/*
 * File name: ArticleCategories.php
 * Last modified: 2022.03.08 at 14:33:13
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Models;

use App\Traits\HasTranslations;
use Eloquent as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Image\Exceptions\InvalidManipulation;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\Models\Media;


class PostComment extends Model implements HasMedia
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
        'text' => 'required',
        'post_id' => 'required|exists:posts,id',
        'post_comment_id' => 'nullable|exists:post_comment,id'
    ];
    public $translatable = [
        'text',
    ];
    public $table = 'post_comment';
    public $fillable = [
        'text',
        'is_deleted',
        'user_id',
        'post_id',
        'post_comment_id'
    ];
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'text' => 'string',
        'is_deleted' => 'boolean',
        'user_id' => 'integer',
        'post_id' => 'integer',
        'post_comment_id' => 'integer'
    ];
    /**
     * New Attributes
     *
     * @var array
     */
    protected $appends = [
        'custom_fields',
        'has_media'
    ];

    protected $hidden = [
        "created_at",
        "updated_at",
    ];

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
     * Add Media to api results
     * @return bool
     */
    public function getHasMediaAttribute()
    {
        return $this->hasMedia('image');
    }

    /**
     * @return BelongsTo
     **/
    public function parentComment()
    {
        return $this->belongsTo(PostComment::class, 'post_comment_id', 'id');
    }

    /**
     * @return HasMany
     **/
    public function subComment()
    {
        return $this->hasMany(PostComment::class, 'post_comment_id')->orderBy('order');
    }

    /**
     * @return BelongsTo
     **/
    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id', 'id');
    }
    /**
     * @return BelongsTo
     **/
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * @return HasMany
     **/
    public function postReaction()
    {
        return $this->hasMany(PostReaction::class, 'post_comment_id' , 'id');
    }

}
