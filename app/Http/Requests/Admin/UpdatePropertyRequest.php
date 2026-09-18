<?php

namespace App\Http\Requests\Admin;

class UpdatePropertyRequest extends StorePropertyRequest
{
    public function rules(): array
    {
        return array_merge($this->baseRules(), [
            'remove_photo_ids' => ['nullable', 'array'],
            'remove_photo_ids.*' => ['integer'],
            'photo_order_ids' => ['nullable', 'array'],
            'photo_order_ids.*' => ['integer'],
            'remove_video_ids' => ['nullable', 'array'],
            'remove_video_ids.*' => ['integer'],
        ]);
    }

    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        $this->merge([
            'remove_photo_ids' => array_values(array_filter((array) $this->input('remove_photo_ids', []))),
            'photo_order_ids' => array_values(array_filter((array) $this->input('photo_order_ids', []))),
            'remove_video_ids' => array_values(array_filter((array) $this->input('remove_video_ids', []))),
        ]);
    }
}
