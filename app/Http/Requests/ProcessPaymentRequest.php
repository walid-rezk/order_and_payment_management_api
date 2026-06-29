<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use App\Services\PaymentManager;
use Illuminate\Foundation\Http\FormRequest;

class ProcessPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $availableGateways = app(PaymentManager::class)->getAvailableGateways();

        return [
            'payment_method' => ['required', 'string', Rule::in($availableGateways)],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        $availableGateways = app(PaymentManager::class)->getAvailableGateways();

        $gatewaysString = implode(', ', $availableGateways);

        return [
            'payment_method.required' => 'A payment method is required.',
            'payment_method.in'       => "Payment method must be one of: {$gatewaysString}.",
            'amount.required'         => 'A payment amount is required.',
            'amount.min'              => 'Payment amount must be at least 0.01.',
        ];
    }
}
