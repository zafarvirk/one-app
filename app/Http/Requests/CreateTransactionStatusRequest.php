<?php
/*
 * File name: CreateTransactionStatusRequest.php
 * Last modified: 2021.01.25 at 22:00:21
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */

namespace App\Http\Requests;

use App\Models\TransactionStatus;
use Illuminate\Foundation\Http\FormRequest;

class CreateTransactionStatusRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return TransactionStatus::$rules;
    }
}
