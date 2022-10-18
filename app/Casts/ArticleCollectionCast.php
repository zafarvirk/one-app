<?php
/*
 * File name: ArticleCollectionCast.php
 * Last modified: 2022.02.15 at 13:49:23
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Casts;

use App\Models\Article;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * Class ArticleCollectionCast
 * @package App\Casts
 */
class ArticleCollectionCast implements CastsAttributes
{

    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes): array
    {
        if (!empty($value)) {
            $decodedValue = json_decode($value, true);
            return array_map(function ($value) {
                $article = Article::find($value['id']);
                if (!empty($article)) {
                    return $article;
                }
                $article = new Article($value);
                array_push($article->fillable, 'id');
                $article->id = $value['id'];
                return $article;
            }, $decodedValue);
        }
        return [];
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes): array
    {
//        if (!$value instanceof Collection) {
//            throw new InvalidArgumentException('The given value is not an Collection instance.');
//        }
        return [
            'article' => json_encode($value->map->only(['id', 'name', 'price', 'discount_price']))
        ];
    }
}
