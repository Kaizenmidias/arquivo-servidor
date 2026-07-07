<?php

namespace App\Http\Requests\Admin;

use App\Models\CustomScript;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomScriptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return $this->baseRules();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'scope' => $this->input('scope', CustomScript::SCOPE_SITEWIDE),
            'location' => $this->input('location', CustomScript::LOCATION_HEAD),
            'page_target' => trim((string) $this->input('page_target', '')),
        ]);
    }

    protected function baseRules(): array
    {
        $locations = array_keys(config('integrations.custom_script_locations', []));

        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'location' => ['required', 'string', Rule::in($locations)],
            'scope' => ['required', 'string', Rule::in(array_keys(config('integrations.custom_script_scopes', [])))],
            'page_target' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^(route|page):[A-Za-z0-9._-]+$/',
                Rule::requiredIf(fn () => $this->input('scope') === CustomScript::SCOPE_PAGE),
            ],
            'code' => ['required', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
