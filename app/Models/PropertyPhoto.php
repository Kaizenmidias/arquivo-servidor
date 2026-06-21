<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $property_id
 * @property string|null $thumb_small_path
 * @property string|null $thumb_medium_path
 * @property string|null $original_path
 * @property int|null $source_size
 * @property string|null $source_mime_type
 */
class PropertyPhoto extends Model
{
    protected $fillable = [
        'property_id',
        'arquivo',
        'url',
        'original_path',
        'width',
        'height',
        'size',
        'source_size',
        'source_mime_type',
        'mime_type',
        'thumb_small_path',
        'thumb_medium_path',
        'optimized',
        'processed_at',
        'processing_status',
        'processing_error',
        'principal',
        'ordem',
    ];

    protected $appends = [
        'original_url',
        'thumb_small_url',
        'medium_url',
        'thumb_medium_url',
    ];

    protected function casts(): array
    {
        return [
            'principal' => 'boolean',
            'optimized' => 'boolean',
            'processed_at' => 'datetime',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function getThumbSmallUrlAttribute(): ?string
    {
        if (empty($this->thumb_small_path)) {
            return null;
        }

        return Storage::disk('public')->url($this->thumb_small_path);
    }

    public function getOriginalUrlAttribute(): ?string
    {
        if (empty($this->original_path)) {
            return null;
        }

        return Storage::disk('public')->url($this->original_path);
    }

    public function getMediumUrlAttribute(): ?string
    {
        if (empty($this->thumb_medium_path)) {
            return null;
        }

        return Storage::disk('public')->url($this->thumb_medium_path);
    }

    public function getThumbMediumUrlAttribute(): ?string
    {
        return $this->getMediumUrlAttribute();
    }
}
