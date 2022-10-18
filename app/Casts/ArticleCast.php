<?php
/*
 * File name: ArticleCast.php
 * Last modified: 2022.02.15 at 13:41:44
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Casts;

use App\Models\Article;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

/**
 * Class ArticleCast
 * @package App\Casts
 */
class ArticleCast implements CastsAttributes
{

    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes): Article
    {
        $decodedValue = json_decode($value, true);
        $article = Article::find($decodedValue['id']);
        // service exist in database
        if (!empty($article)) {
            return $article;
        }
        // if not exist the clone will load
        // create new service based on values stored on database
        $article = new Article($decodedValue);
        // push id attribute fillable array
        array_push($article->fillable, 'id');
        // assign the id to service object
        $article->id = $decodedValue['id'];
        return $article;
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes): array
    {
//        if (!$value instanceof Article) {
//            throw new InvalidArgumentException('The given value is not an Article instance.');
//        }

        return [
            'article' => json_encode(
                [
                    'id' => $value['id'],
                    'name' => $value['name'],
                    'price' => $value['price'],
                    'discount_price' => $value['discount_price'],
                    'duration' => $value['duration'],
                    'enable_booking' => $value['enable_booking'],
                ]
            )
        ];
    }
}
