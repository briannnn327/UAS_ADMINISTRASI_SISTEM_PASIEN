<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan membuat request ini.
     */
    public function authorize(): bool
    {
        return true; // Ubah dari false menjadi true agar bisa dipakai
    }

    /**
     * Dapatkan aturan validasi yang berlaku untuk request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'phone'    => ['required', 'string', 'max:20'], // bisa diubah jadi nullable jika tidak wajib
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * (Opsional) Pesan kustom untuk validasi.
     */
    public function messages(): array
    {
        return [
            'username.required' => 'Username wajib diisi.',
            'username.unique'   => 'Username sudah digunakan.',
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email sudah terdaftar.',
            'phone.required'    => 'Nomor HP wajib diisi.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 8 karakter.',
            'password.confirmed'=> 'Konfirmasi password tidak cocok.',
        ];
    }
}