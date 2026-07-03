<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Poly;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Menampilkan daftar user.
     */
    public function index()
    {
        $users = User::orderBy('role')->orderBy('username')->get();
        $polies = Poly::where('is_active', true)->get();
        return view('admin.users.index', compact('users', 'polies'));
    }

    /**
     * Menampilkan form tambah user.
     */
    public function create()
    {
        $polies = Poly::where('is_active', true)->get();
        return view('admin.users.create', compact('polies'));
    }

    /**
     * Menyimpan user baru.
     */
    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'username' => $validated['username'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'] ?? 'patient',
        ]);

        // Jika role doctor, buat record doctor
        if ($user->role === 'doctor' && !empty($validated['poly_id'])) {
            $user->doctor()->create([
                'poly_id'         => $validated['poly_id'],
                'specialization'  => $validated['specialization'] ?? 'Dokter Umum',
                'license_number'  => $validated['license_number'] ?? '',
                'is_available'    => true,
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    /**
     * Menampilkan form edit user.
     */
    public function edit(User $user)
    {
        $polies = Poly::where('is_active', true)->get();
        $doctor = $user->doctor;
        return view('admin.users.edit', compact('user', 'polies', 'doctor'));
    }

    /**
     * Update user.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        $data = [
            'username' => $validated['username'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'] ?? null,
            'role'     => $validated['role'],
        ];

        if (!empty($validated['new_password'])) {
            $data['password'] = Hash::make($validated['new_password']);
        }

        $user->update($data);

        // Update doctor jika role doctor
        if ($user->role === 'doctor' && $user->doctor) {
            $user->doctor->update([
                'poly_id'         => $validated['poly_id'] ?? $user->doctor->poly_id,
                'specialization'  => $validated['specialization'] ?? $user->doctor->specialization,
                'license_number'  => $validated['license_number'] ?? $user->doctor->license_number,
                'is_available'    => $validated['is_available'] ?? $user->doctor->is_available,
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    /**
     * Toggle aktif/nonaktif user.
     */
    public function toggleActive(User $user)
    {
        if ($user->id === 1) {
            return back()->with('error', 'Akun admin utama tidak dapat dinonaktifkan.');
        }
        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Pengguna berhasil {$status}.");
    }

    /**
     * Hapus user.
     */
    public function destroy(User $user)
    {
        if ($user->id === 1) {
            return back()->with('error', 'Akun admin utama tidak dapat dihapus.');
        }
        $user->delete();
        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }
}