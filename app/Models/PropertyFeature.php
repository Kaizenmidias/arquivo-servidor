<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PropertyFeature extends Model
{
    protected $fillable = [
        'nome',
        'slug',
        'icone',
        'nome_xml',
    ];

    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class, 'property_property_feature', 'feature_id', 'property_id');
    }
}
