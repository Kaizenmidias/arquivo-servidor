<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    protected $fillable = [
        'property_id',
        'nome',
        'telefone',
        'email',
        'mensagem',
        'origem',
        'categoria',
        'status',
        'proximo_contato_em',
    ];

    protected function casts(): array
    {
        return [
            'proximo_contato_em' => 'datetime',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
