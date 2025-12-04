<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProcessTicketRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('approve ticket requests') || $this->user()->can('deny ticket requests');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in(['approve', 'deny', 'waitlist'])],
            'approved_quantity' => 'required_if:action,approve|integer|min:0',
            'admin_notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'action.in' => 'Invalid action. Must be approve, deny, or waitlist.',
            'approved_quantity.required_if' => 'Approved quantity is required when approving a request.',
        ];
    }
}
