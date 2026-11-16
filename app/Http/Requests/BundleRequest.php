<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BundleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'need' => ['required', Rule::in(array_keys(config('frozemate.needs')))],
            'people' => ['required', 'integer', Rule::in(array_keys(config('frozemate.people')))],
            'budget' => ['required', Rule::in(array_keys(config('frozemate.budgets')))],
            'meals' => ['required', 'integer', Rule::in(config('frozemate.meals'))],
        ];
    }

    public function attributes(): array
    {
        return [
            'need' => 'kebutuhan',
            'people' => 'jumlah orang',
            'budget' => 'budget',
            'meals' => 'jumlah makan',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Pilih :attribute dulu, ya.',
            'in' => 'Pilihan :attribute tidak dikenali.',
        ];
    }

    /** Kalau validasi gagal (mis. URL diubah manual), kembali ke form bundle. */
    protected function getRedirectUrl(): string
    {
        return route('bundle.index');
    }

    /** @return array{need: string, people: int, budget: string, meals: int} */
    public function params(): array
    {
        $data = $this->validated();

        return [
            'need' => $data['need'],
            'people' => (int) $data['people'],
            'budget' => $data['budget'],
            'meals' => (int) $data['meals'],
        ];
    }
}
