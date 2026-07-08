<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDoctorRequest extends FormRequest
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
            'username'       => ['required', 'string', 'max:255', 'unique:users,username'],
            'email'          => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone'          => ['nullable', 'string', 'max:20'],
            'password'       => ['required', 'string', 'min:8'],
            'poly_id'        => ['required', 'exists:polies,id'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'license_number' => ['nullable', 'string', 'max:255'],
            'bio'            => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'username.required' => 'Username wajib diisi.',
            'username.unique'   => 'Username sudah digunakan.',
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 8 karakter.',
            'poly_id.required'  => 'Poliklinik wajib dipilih.',
            'poly_id.exists'    => 'Poliklinik yang dipilih tidak valid.',
        ];
    }
}