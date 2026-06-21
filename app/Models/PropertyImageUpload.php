<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $disk
 * @property string $temp_path
 */
class PropertyImageUpload extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'disk',
        'temp_path',
        'original_name',
        'sanitized_name',
        'extension',
        'mime_type',
        'size',
        'sha256',
        'status',
        'validation_error',
        'expires_at',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
