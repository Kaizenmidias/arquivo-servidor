<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class SpecialCategory extends Model
{
    protected $appends = [
        'cover_url',
    ];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'cover_path',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class, 'property_special_category', 'special_category_id', 'property_id');
    }

    public function propertyTypes(): BelongsToMany
    {
        return $this->belongsToMany(PropertyType::class, 'property_type_special_category', 'special_category_id', 'property_type_id');
    }

    public function getCoverUrlAttribute(): ?string
    {
        if (empty($this->cover_path)) {
            return null;
        }

        return Storage::disk('public')->url($this->cover_path);
    }
}
