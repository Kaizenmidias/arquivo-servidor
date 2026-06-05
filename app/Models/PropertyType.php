<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
}
