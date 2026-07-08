<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
        // Ambil ID user yang sedang diedit dari route model binding
        $userId = $this->route('user')?->id;

        return [
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($userId)],
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone'    => ['nullable', 'string', 'max:20'],
            'role'     => ['required', Rule::in(['admin', 'doctor', 'patient'])],

            // Password baru bersifat opsional saat update
            'new_password' => ['nullable', 'string', 'min:8'],

            // Hanya relevan kalau role = doctor
            'poly_id'         => ['nullable', 'required_if:role,doctor', 'exists:polies,id'],
            'specialization'  => ['nullable', 'string', 'max:255'],
            'license_number'  => ['nullable', 'string', 'max:255'],
            'is_available'    => ['nullable', 'boolean'],
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
            'new_password.min'  => 'Password baru minimal 8 karakter.',
            'role.required'     => 'Role wajib dipilih.',
            'role.in'           => 'Role tidak valid.',
            'poly_id.required_if' => 'Poli wajib dipilih untuk role dokter.',
            'poly_id.exists'    => 'Poli yang dipilih tidak valid.',
        ];
    }
}