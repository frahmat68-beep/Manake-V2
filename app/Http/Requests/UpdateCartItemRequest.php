<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
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
            'qty' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * Get the validation error messages.
     */
    public function messages(): array
    {
        return [
            'qty.required' => 'Jumlah sewa alat harus ditentukan.',
            'qty.integer' => 'Jumlah sewa alat harus berupa angka.',
            'qty.min' => 'Jumlah sewa alat minimal 1 unit.',
        ];
    }
}
