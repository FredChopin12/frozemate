<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'min:3', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^(\+62|62|0)8[0-9\-\s]{7,14}$/'],
            'address' => ['required', 'string', 'min:10', 'max:500'],
            'payment_method' => ['required', Rule::in(array_keys(config('frozemate.payments')))],
            'notes' => ['nullable', 'string', 'max:255'],
            'save_profile' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'customer_name' => 'nama',
            'phone' => 'nomor telepon',
            'address' => 'alamat',
            'payment_method' => 'metode pembayaran',
            'notes' => 'catatan',
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Nomor telepon harus nomor HP Indonesia, contoh: 0812-3456-7890.',
            'address.min' => 'Tulis alamat lengkap (jalan, nomor rumah, patokan) supaya kurir tidak nyasar.',
        ];
    }
}
