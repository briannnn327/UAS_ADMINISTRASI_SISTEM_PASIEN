<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreScheduleRequest extends FormRequest
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
            'doctor_id'    => ['required', 'exists:doctors,id'],
            'day_of_week'  => ['required', Rule::in([0, 1, 2, 3, 4, 5, 6])],
            'start_time'   => ['required', 'date_format:H:i'],
            'end_time'     => ['required', 'date_format:H:i', 'after:start_time'],
            'quota'        => ['nullable', 'integer', 'min:1'],
            'is_available' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'doctor_id.required'   => 'Dokter wajib dipilih.',
            'doctor_id.exists'     => 'Dokter yang dipilih tidak valid.',
            'day_of_week.required' => 'Hari praktik wajib dipilih.',
            'day_of_week.in'       => 'Hari praktik tidak valid.',
            'start_time.required'  => 'Jam mulai wajib diisi.',
            'start_time.date_format' => 'Format jam mulai tidak valid (HH:MM).',
            'end_time.required'    => 'Jam selesai wajib diisi.',
            'end_time.date_format' => 'Format jam selesai tidak valid (HH:MM).',
            'end_time.after'       => 'Jam selesai harus setelah jam mulai.',
            'quota.integer'        => 'Kuota harus berupa angka.',
            'quota.min'            => 'Kuota minimal 1.',
        ];
    }
}