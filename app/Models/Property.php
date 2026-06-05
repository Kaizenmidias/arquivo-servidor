<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'codigo_anuncio',
        'codigo_referencia',
        'titulo',
        'slug',
        'meta_title',
        'meta_description',
        'descricao',
        'tipo_propriedade_id',
        'operacao',
        'valor',
        'moeda',
        'endereco',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'cep',
        'id_localidade_xml',
        'localidade_xml',
        'latitud',
        'longitud',
        'area_util',
        'area_total',
        'quartos',
        'suites',
        'banheiros',
        'garagens',
        'condominio',
        'iptu',
        'destaque',
        'is_exclusive',
        'is_off_market',
        'show_in_home_selecao_especial',
        'show_in_home_mais_procurados',
        'show_in_home_visto_recentemente',
        'ativo',
        'data_modificacao_xml',
        'business_type_id',
    ];

    protected function casts(): array
    {
        return [
            'destaque' => 'boolean',
            'ativo' => 'boolean',
            'is_exclusive' => 'boolean',
            'is_off_market' => 'boolean',
            'show_in_home_selecao_especial' => 'boolean',
            'show_in_home_mais_procurados' => 'boolean',
            'show_in_home_visto_recentemente' => 'boolean',
        ];
    }

    public function businessType(): BelongsTo
    {
        return $this->belongsTo(BusinessType::class);
    }

    public function propertyType(): BelongsTo
    {
        return $this->belongsTo(PropertyType::class, 'tipo_propriedade_id');
    }

    public function specialCategories(): BelongsToMany
    {
        return $this->belongsToMany(SpecialCategory::class, 'property_special_category', 'property_id', 'special_category_id');
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(PropertyFeature::class, 'property_property_feature', 'property_id', 'feature_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(PropertyPhoto::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }
}
