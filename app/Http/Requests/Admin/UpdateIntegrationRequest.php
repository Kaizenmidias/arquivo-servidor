<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIntegrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        $provider = (string) $this->route('provider');
        $provider = in_array($provider, ['gtm', 'meta_pixel'], true) ? $provider : 'gtm';

        $pattern = $provider === 'gtm'
            ? '/^GTM-[A-Z0-9]+$/'
            : '/^[0-9]{5,}$/';

        return [
            'external_id' => ['nullable', 'string', 'max:100', Rule::when($this->boolean('is_active'), ['required']), 'regex:' . $pattern],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'external_id' => trim((string) $this->input('external_id', '')),
        ]);
    }
}
