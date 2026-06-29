<?php

namespace App\Http\Requests;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'status'                 => ['sometimes', Rule::enum(OrderStatus::class)],
            'items'                  => ['sometimes', 'array', 'min:1'],
            'items.*.product_name'   => ['required_with:items', 'string', 'max:255'],
            'items.*.quantity'       => ['required_with:items', 'integer', 'min:1'],
            'items.*.price'          => ['required_with:items', 'numeric', 'min:0.01'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'status.Illuminate\Validation\Rules\Enum' => 'Status must be one of: ' . OrderStatus::commaSeparated() . '.',
            'items.min'                               => 'At least one item is required when updating items.',
            'items.*.product_name.required_with'      => 'Each item must have a product name.',
            'items.*.quantity.required_with'          => 'Each item must have a quantity.',
            'items.*.quantity.min'                    => 'Item quantity must be at least 1.',
            'items.*.price.required_with'             => 'Each item must have a price.',
            'items.*.price.min'                       => 'Item price must be at least 0.01.',
        ];
    }
}
