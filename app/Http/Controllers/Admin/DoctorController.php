<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDoctorRequest;
use App\Http\Requests\UpdateDoctorRequest;
use App\Models\Doctor;
use App\Models\Poly;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DoctorController extends Controller
{
    /**
     * Daftar dokter.
     */
    public function index()
    {
        $doctors = Doctor::with(['user', 'poly'])->orderBy('poly_id')->get();
        $polies = Poly::where('is_active', true)->get();
        return view('admin.doctors.index', compact('doctors', 'polies'));
    }

    /**
     * Form tambah dokter.
     */
    public function create()
    {
        $polies = Poly::where('is_active', true)->get();
        return view('admin.doctors.create', compact('polies'));
    }

    /**
     * Simpan dokter baru.
     */
    public function store(StoreDoctorRequest $request)
    {
        $validated = $request->validated();

        // Buat user dengan role doctor
        $user = User::create([
            'username' => $validated['username'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role'     => 'doctor',
        ]);

        // Buat record doctor
        $user->doctor()->create([
            'poly_id'         => $validated['poly_id'],
            'specialization'  => $validated['specialization'] ?? 'Dokter Umum',
            'license_number'  => $validated['license_number'] ?? '',
            'bio'             => $validated['bio'] ?? '',
            'is_available'    => true,
        ]);

        return redirect()->route('admin.doctors.index')
            ->with('success', "Dokter dr. {$validated['username']} berhasil ditambahkan.");
    }

    /**
     * Edit dokter.
     */
    public function edit(Doctor $doctor)
    {
        $polies = Poly::where('is_active', true)->get();
        return view('admin.doctors.edit', compact('doctor', 'polies'));
    }

    /**
     * Update dokter.
     */
    public function update(UpdateDoctorRequest $request, Doctor $doctor)
    {
        $validated = $request->validated();

        // Update user
        $doctor->user->update([
            'username' => $validated['username'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'] ?? null,
        ]);

        if (!empty($validated['new_password'])) {
            $doctor->user->update(['password' => Hash::make($validated['new_password'])]);
        }

        // Update doctor
        $doctor->update([
            'poly_id'         => $validated['poly_id'],
            'specialization'  => $validated['specialization'] ?? $doctor->specialization,
            'license_number'  => $validated['license_number'] ?? $doctor->license_number,
            'bio'             => $validated['bio'] ?? $doctor->bio,
            'is_available'    => $validated['is_available'] ?? $doctor->is_available,
        ]);

        return redirect()->route('admin.doctors.index')
            ->with('success', 'Data dokter berhasil diperbarui.');
    }

    /**
     * Toggle ketersediaan dokter.
     */
    public function toggleAvailability(Doctor $doctor)
    {
        $doctor->update(['is_available' => !$doctor->is_available]);
        $status = $doctor->is_available ? 'Tersedia' : 'Tidak Tersedia';
        return back()->with('success', "Status dokter: {$status}");
    }

    /**
     * Hapus dokter.
     */
    public function destroy(Doctor $doctor)
    {
        // Hapus user (cascade akan menghapus doctor)
        $doctor->user->delete();
        return redirect()->route('admin.doctors.index')
            ->with('success', 'Dokter berhasil dihapus.');
    }
}