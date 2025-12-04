<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('request extra tickets');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'requested_quantity' => 'required|integer|min:1|max:10',
            'reason' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'requested_quantity.min' => 'You must request at least 1 ticket.',
            'requested_quantity.max' => 'You cannot request more than 10 extra tickets.',
        ];
    }
}
