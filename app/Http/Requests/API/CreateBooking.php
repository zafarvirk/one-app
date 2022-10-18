<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class CreateBooking extends FormRequest
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
        return [
            'business_id' => 'required|integer',
            'article_id' => 'required|integer',
            'schedule_id' => 'required|integer',
            'date' => 'required|date|after_or_equal:' .date('Y-m-d'),
            'plan_id' => 'nullable|integer',
            'payment_id' => 'required_without:plan_id',
            'quantity' => 'required|integer',
        ];
    }
}
