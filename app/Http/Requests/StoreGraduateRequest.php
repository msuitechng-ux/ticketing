<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGraduateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create graduates');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'ceremony_id' => 'required|exists:ceremonies,id',
            'student_id' => 'required|string|unique:graduates,student_id|max:50',
            'student_name' => 'required|string|max:255',
            'degree_level' => ['required', Rule::in(['Undergraduate', 'Masters', 'PhD'])],
            'faculty' => 'required|string|max:255',
            'department' => 'required|string|max:255',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'student_id.unique' => 'A graduate with this student ID already exists.',
            'degree_level.in' => 'Invalid degree level. Must be Undergraduate, Masters, or PhD.',
        ];
    }
}
