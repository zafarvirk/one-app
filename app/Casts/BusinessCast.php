<?php
/*
 * File name: SalonCast.php
 * Last modified: 2022.02.15 at 13:33:42
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Casts;

use App\Models\Business;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

/**
 * Class SalonCast
 * @package App\Casts
 */
class BusinessCast implements CastsAttributes
{

    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes): Business
    {
        $decodedValue = json_decode($value, true);
        $business = Business::find($decodedValue['id']);
        // business exist in database
        if (!empty($business)) {
            return $business;
        }
        // if not exist the clone will loaded
        // create new business based on values stored on database
        $business = new Business($decodedValue);
        // push id attribute fillable array
        array_push($business->fillable, 'id');
        // assign the id to business object
        $business->id = $decodedValue['id'];
        return $business;
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes): array
    {
//        if (!$value instanceof \Eloquent) {
//            throw new InvalidArgumentException('The given value is not an Salon instance.');
//        } 
        return [
            'business' => json_encode([
                'id' => $value['id'],
                'name' => $value['name'],
                'phone_number' => $value['phone_number'],
                'mobile_number' => $value['mobile_number'],
            ])
        ];
    }
}
