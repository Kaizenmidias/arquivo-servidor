<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * @property int $id
 */
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
        'condominium_id',
        'operacao',
        'valor',
        'valor_venda',
        'valor_locacao',
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
        'area_construida',
        'quartos',
        'suites',
        'banheiros',
        'lavabos',
        'garagens',
        'andar',
        'condominio',
        'valor_condominio',
        'iptu',
        'valor_iptu',
        'destaque',
        'is_exclusive',
        'show_in_home_selecao_especial',
        'show_in_home_mais_procurados',
        'show_in_home_visto_recentemente',
        'ativo',
        'data_modificacao_xml',
        'business_type_id',
        'aceita_venda',
        'aceita_locacao',
        'aceita_temporada',
        'aceita_permuta',
        'mobiliado',
        'aceita_financiamento',
        'ano_construcao',
        'posicao_solar',
    ];

    protected function casts(): array
    {
        return [
            'destaque' => 'boolean',
            'ativo' => 'boolean',
            'is_exclusive' => 'boolean',
            'show_in_home_selecao_especial' => 'boolean',
            'show_in_home_mais_procurados' => 'boolean',
            'show_in_home_visto_recentemente' => 'boolean',
            'aceita_venda' => 'boolean',
            'aceita_locacao' => 'boolean',
            'aceita_temporada' => 'boolean',
            'aceita_permuta' => 'boolean',
            'mobiliado' => 'boolean',
            'aceita_financiamento' => 'boolean',
            'valor' => 'float',
            'valor_venda' => 'float',
            'valor_locacao' => 'float',
            'condominio' => 'float',
            'valor_condominio' => 'float',
            'iptu' => 'float',
            'valor_iptu' => 'float',
            'area_util' => 'float',
            'area_total' => 'float',
            'area_construida' => 'float',
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

    public function condominium(): BelongsTo
    {
        return $this->belongsTo(Condominium::class);
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

    public function businessLabels(): array
    {
        $labels = [];

        if ($this->aceita_venda) {
            $labels[] = 'Venda';
        }

        if ($this->aceita_locacao) {
            $labels[] = 'Aluguel';
        }

        if ($this->aceita_temporada) {
            $labels[] = 'Temporada';
        }

        if ($labels === [] && !empty($this->operacao)) {
            $labels[] = (string) $this->operacao;
        }

        return $labels;
    }

    public function primaryBusinessLabel(): string
    {
        return $this->businessLabels()[0] ?? 'Venda';
    }

    public function publicPrices(): Collection
    {
        $prices = collect();

        if ((float) ($this->valor_venda ?? 0) > 0) {
            $prices->push([
                'key' => 'sale',
                'label' => 'Venda',
                'value' => (float) $this->valor_venda,
                'suffix' => '',
            ]);
        }

        if ((float) ($this->valor_locacao ?? 0) > 0) {
            $prices->push([
                'key' => 'rent',
                'label' => 'Locação',
                'value' => (float) $this->valor_locacao,
                'suffix' => '/mês',
            ]);
        }

        if ($prices->isEmpty() && (float) ($this->valor ?? 0) > 0) {
            $prices->push([
                'key' => 'default',
                'label' => $this->primaryBusinessLabel(),
                'value' => (float) $this->valor,
                'suffix' => $this->primaryBusinessLabel() === 'Aluguel' ? '/mês' : '',
            ]);
        }

        return $prices;
    }
}
