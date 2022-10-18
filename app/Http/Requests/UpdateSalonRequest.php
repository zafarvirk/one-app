<?php
/*
 * File name: UpdateSalonRequest.php
 * Last modified: 2022.02.02 at 21:20:43
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Http\Requests;

use App\Models\Salon;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSalonRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [  
            'name' => 'required|max:127',
            'business_category_id' => 'required|exists:business_categories,id',
            'address_id' => 'required|exists:addresses,id',
            'phone_number' => 'max:50',
            'mobile_number' => 'max:50',
            'availability_range' => 'required|max:9999999,99|min:0'
        ];
    }

    /**
     * @return array
     */
    public function validationData(): array
    {
        if (!auth()->user()->hasRole('admin')) {
            $this->offsetUnset('accepted');
        }
        return parent::validationData();
    }


}
