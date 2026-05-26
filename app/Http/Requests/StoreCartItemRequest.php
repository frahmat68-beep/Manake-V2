<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCartItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Breeze auth handles controller access
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'equipment_id' => ['required', 'exists:equipments,id'],
            'rental_start_date' => ['required', 'date', 'after_or_equal:today'],
            'rental_end_date' => ['required', 'date', 'after_or_equal:rental_start_date'],
            'qty' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * Get the validation error messages.
     */
    public function messages(): array
    {
        return [
            'equipment_id.required' => 'Peralatan media harus dipilih.',
            'equipment_id.exists' => 'Peralatan media tidak ditemukan.',
            'rental_start_date.required' => 'Tanggal mulai sewa harus diisi.',
            'rental_start_date.date' => 'Format tanggal mulai sewa tidak valid.',
            'rental_start_date.after_or_equal' => 'Tanggal mulai sewa tidak boleh sebelum hari ini.',
            'rental_end_date.required' => 'Tanggal akhir sewa harus diisi.',
            'rental_end_date.date' => 'Format tanggal akhir sewa tidak valid.',
            'rental_end_date.after_or_equal' => 'Tanggal akhir sewa tidak boleh sebelum tanggal mulai.',
            'qty.required' => 'Jumlah sewa alat harus ditentukan.',
            'qty.integer' => 'Jumlah sewa alat harus berupa angka.',
            'qty.min' => 'Jumlah sewa alat minimal 1 unit.',
        ];
    }
}
