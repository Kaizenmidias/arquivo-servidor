<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PropertyType extends Model
{
    protected $fillable = [
        'id_tipo_xml',
        'nome_tipo',
        'id_subtipo_xml',
        'nome_subtipo',
        'slug',
    ];

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class, 'tipo_propriedade_id');
    }

    public function specialCategories(): BelongsToMany
    {
        return $this->belongsToMany(SpecialCategory::class, 'property_type_special_category', 'property_type_id', 'special_category_id');
    }
}
