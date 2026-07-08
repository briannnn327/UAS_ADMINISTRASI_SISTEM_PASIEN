<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDoctorRequest extends FormRequest
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
        // Ambil user_id dari doctor yang sedang diedit (route model binding)
        $doctor = $this->route('doctor');
        $userId = $doctor?->user_id;

        return [
            'username'       => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($userId)],
            'email'          => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone'          => ['nullable', 'string', 'max:20'],
            'new_password'   => ['nullable', 'string', 'min:8'],
            'poly_id'        => ['required', 'exists:polies,id'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'license_number' => ['nullable', 'string', 'max:255'],
            'bio'            => ['nullable', 'string', 'max:1000'],
            'is_available'   => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'username.required'  => 'Username wajib diisi.',
            'username.unique'    => 'Username sudah digunakan.',
            'email.required'     => 'Email wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'email.unique'       => 'Email sudah terdaftar.',
            'new_password.min'   => 'Password baru minimal 8 karakter.',
            'poly_id.required'   => 'Poliklinik wajib dipilih.',
            'poly_id.exists'     => 'Poliklinik yang dipilih tidak valid.',
        ];
    }
}