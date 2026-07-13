<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return $this->baseRules();
    }

    protected function prepareForValidation(): void
    {
        $showInHome = $this->input('show_in_home');
        $showInHomeEnabled = $showInHome !== null
            ? filter_var($showInHome, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : (
                $this->boolean('show_in_home_selecao_especial')
                || $this->boolean('show_in_home_mais_procurados')
                || $this->boolean('show_in_home_visto_recentemente')
            );

        $this->merge([
            'is_exclusive' => $this->boolean('is_exclusive'),
            'show_in_home_selecao_especial' => (bool) $showInHomeEnabled,
            'show_in_home_mais_procurados' => false,
            'show_in_home_visto_recentemente' => false,
            'aceita_permuta' => $this->boolean('aceita_permuta'),
            'mobiliado' => $this->boolean('mobiliado'),
            'aceita_financiamento' => $this->boolean('aceita_financiamento'),
            'business_type_ids' => array_values(array_filter((array) $this->input('business_type_ids', []))),
            'gallery_upload_tokens' => array_values(array_filter((array) $this->input('gallery_upload_tokens', []))),
            'special_category_ids' => array_values(array_filter((array) $this->input('special_category_ids', []))),
        ]);
    }

    protected function baseRules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'descricao' => ['required', 'string'],
            'tipo_propriedade_id' => ['required', 'integer', 'exists:property_types,id'],
            'condominium_id' => ['nullable', 'integer', 'exists:condominiums,id'],
            'business_type_ids' => ['required', 'array', 'min:1'],
            'business_type_ids.*' => ['integer', 'exists:business_types,id'],
            'valor_venda' => ['nullable', 'string'],
            'valor_locacao' => ['nullable', 'string'],
            'endereco' => ['required', 'string', 'max:255'],
            'bairro' => ['required', 'string', 'max:255'],
            'cidade' => ['required', 'string', 'max:255'],
            'estado' => ['required', 'string', 'size:2'],
            'quartos' => ['nullable', 'integer', 'min:0'],
            'suites' => ['nullable', 'integer', 'min:0'],
            'banheiros' => ['nullable', 'integer', 'min:0'],
            'lavabos' => ['nullable', 'integer', 'min:0'],
            'garagens' => ['nullable', 'integer', 'min:0'],
            'andar' => ['nullable', 'integer', 'min:0'],
            'area_total' => ['nullable', 'numeric', 'min:0'],
            'area_construida' => ['nullable', 'numeric', 'min:0'],
            'valor_condominio' => ['nullable', 'string', 'max:50'],
            'valor_iptu' => ['nullable', 'string', 'max:50'],
            'aceita_permuta' => ['nullable', 'boolean'],
            'mobiliado' => ['nullable', 'boolean'],
            'aceita_financiamento' => ['nullable', 'boolean'],
            'ano_construcao' => ['nullable', 'integer', 'min:1800', 'max:' . (date('Y') + 1)],
            'posicao_solar' => ['nullable', 'string', Rule::in($this->solarPositions())],
            'is_exclusive' => ['nullable', 'boolean'],
            'show_in_home_selecao_especial' => ['nullable', 'boolean'],
            'show_in_home_mais_procurados' => ['nullable', 'boolean'],
            'show_in_home_visto_recentemente' => ['nullable', 'boolean'],
            'featured_upload_token' => ['nullable', 'uuid'],
            'featured_existing_photo_id' => ['nullable', 'integer'],
            'gallery_upload_tokens' => ['nullable', 'array'],
            'gallery_upload_tokens.*' => ['uuid'],
            'special_category_ids' => ['nullable', 'array'],
            'special_category_ids.*' => ['integer', 'exists:special_categories,id'],
        ];
    }

    protected function solarPositions(): array
    {
        return [
            'Norte',
            'Sul',
            'Leste',
            'Oeste',
            'Nordeste',
            'Noroeste',
            'Sudeste',
            'Sudoeste',
        ];
    }
}
