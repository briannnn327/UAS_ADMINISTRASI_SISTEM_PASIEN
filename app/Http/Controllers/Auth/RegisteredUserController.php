<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Tampilkan halaman registrasi.
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * Proses registrasi user baru.
     */
    public function store(Request $request)
    {
        // Validasi input (pakai 'username' dan 'phone' sesuai view)
        $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Buat user baru
        $user = User::create([
            'username' => $request->username,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'password' => Hash::make($request->password),
            'role'     => 'patient',   // Default role pasien
            'is_active' => true,
        ]);

        // Trigger event registered
        event(new Registered($user));

        // Login otomatis setelah registrasi
        Auth::login($user);

        // Redirect ke dashboard pasien
        return redirect()->route('patient.dashboard');
    }
}