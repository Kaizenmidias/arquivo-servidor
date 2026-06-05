<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyPhoto extends Model
{
    protected $fillable = [
        'property_id',
        'arquivo',
        'url',
        'principal',
        'ordem',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
