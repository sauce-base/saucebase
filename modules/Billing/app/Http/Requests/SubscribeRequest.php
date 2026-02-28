<?php

namespace Modules\Billing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubscribeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'plan_price_id' => 'required|exists:subscription_plan_prices,id',
            'payment_method_id' => 'required|string',
        ];
    }
}
