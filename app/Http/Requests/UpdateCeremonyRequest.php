<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCeremonyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('edit ceremonies');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'ceremony_date' => 'required|date',
            'venue' => 'required|string|max:255',
            'venue_address' => 'nullable|string|max:500',
            'total_capacity' => 'required|integer|min:1',
            'base_tickets_per_graduate' => 'required|integer|min:1|max:10',
            'ticket_request_deadline' => 'nullable|date|before:ceremony_date',
            'redistribution_deadline' => 'nullable|date|before:ceremony_date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'ticket_request_deadline.before' => 'The ticket request deadline must be before the ceremony date.',
            'redistribution_deadline.before' => 'The redistribution deadline must be before the ceremony date.',
            'base_tickets_per_graduate.max' => 'Base tickets per graduate cannot exceed 10.',
        ];
    }
}
