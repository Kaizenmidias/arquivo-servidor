<template>
  <AdminLayout>
    <template #pageTitle>{{ isEdit ? 'Editar Imóvel' : 'Novo Imóvel' }}</template>

    <div v-if="showProcessingBanner" class="mb-6 rounded-xl border border-blue-200 bg-blue-50 px-4 py-4 text-blue-950">
      <div class="flex items-center justify-between gap-4">
        <div>
          <div class="font-semibold">Processando imagens em background</div>
          <div class="mt-1 text-sm text-blue-900/80">
            {{ processingSummaryText }}
          </div>
        </div>
        <div class="whitespace-nowrap text-sm font-semibold">
          {{ processingCounts.completed }}/{{ processingCounts.total }} concluídas
        </div>
      </div>
      <div class="mt-3 h-2 overflow-hidden rounded-full bg-blue-100">
        <div class="h-2 rounded-full bg-blue-700 transition-all duration-300" :style="{ width: `${processingProgress}%` }"></div>
      </div>
    </div>

    <form @submit.prevent="submit" class="grid grid-cols-1 gap-6 xl:grid-cols-3">
      <div class="space-y-6 xl:col-span-2">
        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow">
          <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-900">Dados Principais</h2>
            <p class="mt-1 text-sm text-gray-500">Informações básicas do anúncio, negociação e localização principal.</p>
          </div>

          <div class="space-y-5">
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-700">Título</label>
              <input v-model="form.titulo" type="text" class="w-full rounded-lg border border-gray-300 px-4 py-3" placeholder="Título do imóvel">
              <div v-if="form.errors.titulo" class="mt-1 text-sm text-red-600">{{ form.errors.titulo }}</div>
            </div>

            <div>
              <label class="mb-2 block text-sm font-medium text-gray-700">Código de Referência</label>
              <input v-model="form.codigo_referencia" :disabled="!isEdit" type="text" class="w-full rounded-lg border border-gray-300 px-4 py-3 disabled:bg-gray-100 disabled:text-gray-500" placeholder="Gerado automaticamente">
              <div v-if="form.errors.codigo_referencia" class="mt-1 text-sm text-red-600">{{ form.errors.codigo_referencia }}</div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Tipo de imóvel</label>
                <select v-model="form.tipo_propriedade_id" class="w-full rounded-lg border border-gray-300 px-4 py-3 bg-white">
                  <option v-for="type in propertyTypes" :key="type.id" :value="type.id">
                    {{ type.nome_subtipo ? `${type.nome_tipo} / ${type.nome_subtipo}` : type.nome_tipo }}
                  </option>
                </select>
                <div v-if="form.errors.tipo_propriedade_id" class="mt-1 text-sm text-red-600">{{ form.errors.tipo_propriedade_id }}</div>
              </div>

              <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Condomínio</label>
                <select v-model="form.condominium_id" class="w-full rounded-lg border border-gray-300 px-4 py-3 bg-white">
                  <option :value="null">Selecione um condomínio</option>
                  <option v-for="item in condominiums" :key="item.id" :value="item.id">{{ item.name }}</option>
                </select>
                <div v-if="form.errors.condominium_id" class="mt-1 text-sm text-red-600">{{ form.errors.condominium_id }}</div>
              </div>
            </div>

            <div>
              <label class="mb-2 block text-sm font-medium text-gray-700">Negócio</label>
              <div class="grid grid-cols-1 gap-3 rounded-lg border border-gray-300 px-4 py-4 sm:grid-cols-3">
                <label v-for="bt in businessTypes" :key="bt.id" class="flex items-center gap-3 text-sm text-gray-700">
                  <input v-model="form.business_type_ids" type="checkbox" :value="bt.id" class="rounded border-gray-300">
                  <span>{{ businessTypeLabel(bt.name) }}</span>
                </label>
              </div>
              <div v-if="form.errors.business_type_ids" class="mt-1 text-sm text-red-600">{{ form.errors.business_type_ids }}</div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Valor de venda</label>
                <input v-model="form.valor_venda" type="text" inputmode="numeric" class="w-full rounded-lg border border-gray-300 px-4 py-3" placeholder="R$ 0,00" @input="onSalePriceInput">
                <div v-if="form.errors.valor_venda" class="mt-1 text-sm text-red-600">{{ form.errors.valor_venda }}</div>
              </div>

              <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Valor de locação</label>
                <input v-model="form.valor_locacao" type="text" inputmode="numeric" class="w-full rounded-lg border border-gray-300 px-4 py-3" placeholder="R$ 0,00" @input="onRentPriceInput">
                <div v-if="form.errors.valor_locacao" class="mt-1 text-sm text-red-600">{{ form.errors.valor_locacao }}</div>
              </div>
            </div>

            <div>
              <label class="mb-2 block text-sm font-medium text-gray-700">Endereço</label>
              <input v-model="form.endereco" type="text" class="w-full rounded-lg border border-gray-300 px-4 py-3" placeholder="Rua, Número">
              <div v-if="form.errors.endereco" class="mt-1 text-sm text-red-600">{{ form.errors.endereco }}</div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
              <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Bairro</label>
                <input v-model="form.bairro" type="text" class="w-full rounded-lg border border-gray-300 px-4 py-3" placeholder="Bairro">
                <div v-if="form.errors.bairro" class="mt-1 text-sm text-red-600">{{ form.errors.bairro }}</div>
              </div>

              <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Cidade</label>
                <input v-model="form.cidade" type="text" class="w-full rounded-lg border border-gray-300 px-4 py-3" placeholder="Cidade">
                <div v-if="form.errors.cidade" class="mt-1 text-sm text-red-600">{{ form.errors.cidade }}</div>
              </div>

              <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">UF</label>
                <input v-model="form.estado" type="text" maxlength="2" class="w-full rounded-lg border border-gray-300 px-4 py-3 uppercase" placeholder="SP">
                <div v-if="form.errors.estado" class="mt-1 text-sm text-red-600">{{ form.errors.estado }}</div>
              </div>
            </div>
          </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow">
          <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-900">Características do Imóvel</h2>
            <p class="mt-1 text-sm text-gray-500">Campos estruturados para cadastro completo e futuras integrações com CRM.</p>
          </div>

          <div class="space-y-8">
            <div>
              <div class="mb-3 text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Informações Gerais</div>
              <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <div>
                  <label class="mb-2 block text-sm font-medium text-gray-700">Quartos</label>
                  <input v-model.number="form.quartos" type="number" min="0" class="w-full rounded-lg border border-gray-300 px-4 py-3" placeholder="0">
                  <div v-if="form.errors.quartos" class="mt-1 text-sm text-red-600">{{ form.errors.quartos }}</div>
                </div>

                <div>
                  <label class="mb-2 block text-sm font-medium text-gray-700">Suítes</label>
                  <input v-model.number="form.suites" type="number" min="0" class="w-full rounded-lg border border-gray-300 px-4 py-3" placeholder="0">
                  <div v-if="form.errors.suites" class="mt-1 text-sm text-red-600">{{ form.errors.suites }}</div>
                </div>

                <div>
                  <label class="mb-2 block text-sm font-medium text-gray-700">Banheiros</label>
                  <input v-model.number="form.banheiros" type="number" min="0" class="w-full rounded-lg border border-gray-300 px-4 py-3" placeholder="0">
                  <div v-if="form.errors.banheiros" class="mt-1 text-sm text-red-600">{{ form.errors.banheiros }}</div>
                </div>

                <div>
                  <label class="mb-2 block text-sm font-medium text-gray-700">Lavabos</label>
                  <input v-model.number="form.lavabos" type="number" min="0" class="w-full rounded-lg border border-gray-300 px-4 py-3" placeholder="0">
                  <div v-if="form.errors.lavabos" class="mt-1 text-sm text-red-600">{{ form.errors.lavabos }}</div>
                </div>

                <div>
                  <label class="mb-2 block text-sm font-medium text-gray-700">Garagens</label>
                  <input v-model.number="form.garagens" type="number" min="0" class="w-full rounded-lg border border-gray-300 px-4 py-3" placeholder="0">
                  <div v-if="form.errors.garagens" class="mt-1 text-sm text-red-600">{{ form.errors.garagens }}</div>
                </div>

                <div>
                  <label class="mb-2 block text-sm font-medium text-gray-700">Andar</label>
                  <input v-model.number="form.andar" type="number" min="0" class="w-full rounded-lg border border-gray-300 px-4 py-3" placeholder="0">
                  <div v-if="form.errors.andar" class="mt-1 text-sm text-red-600">{{ form.errors.andar }}</div>
                </div>
              </div>
            </div>

            <div>
              <div class="mb-3 text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Áreas</div>
              <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                  <label class="mb-2 block text-sm font-medium text-gray-700">Área Total (m²)</label>
                  <input v-model.number="form.area_total" type="number" min="0" step="0.01" class="w-full rounded-lg border border-gray-300 px-4 py-3" placeholder="0,00">
                  <div v-if="form.errors.area_total" class="mt-1 text-sm text-red-600">{{ form.errors.area_total }}</div>
                </div>

                <div>
                  <label class="mb-2 block text-sm font-medium text-gray-700">Área Construída (m²)</label>
                  <input v-model.number="form.area_construida" type="number" min="0" step="0.01" class="w-full rounded-lg border border-gray-300 px-4 py-3" placeholder="0,00">
                  <div v-if="form.errors.area_construida" class="mt-1 text-sm text-red-600">{{ form.errors.area_construida }}</div>
                </div>
              </div>
            </div>

            <div>
              <div class="mb-3 text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Financeiro</div>
              <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                  <label class="mb-2 block text-sm font-medium text-gray-700">Valor do Condomínio</label>
                  <input v-model="form.valor_condominio" type="text" inputmode="numeric" class="w-full rounded-lg border border-gray-300 px-4 py-3" placeholder="R$ 0,00" @input="onCondominiumPriceInput">
                  <div v-if="form.errors.valor_condominio" class="mt-1 text-sm text-red-600">{{ form.errors.valor_condominio }}</div>
                </div>

                <div>
                  <label class="mb-2 block text-sm font-medium text-gray-700">Valor do IPTU</label>
                  <input v-model="form.valor_iptu" type="text" inputmode="numeric" class="w-full rounded-lg border border-gray-300 px-4 py-3" placeholder="R$ 0,00" @input="onIptuPriceInput">
                  <div v-if="form.errors.valor_iptu" class="mt-1 text-sm text-red-600">{{ form.errors.valor_iptu }}</div>
                </div>
              </div>
            </div>

            <div>
              <div class="mb-3 text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Comercial</div>
              <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <label class="flex items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 text-sm text-gray-700">
                  <input v-model="form.aceita_permuta" type="checkbox" class="rounded border-gray-300">
                  Aceita Permuta
                </label>
                <label class="flex items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 text-sm text-gray-700">
                  <input v-model="form.aceita_financiamento" type="checkbox" class="rounded border-gray-300">
                  Aceita Financiamento
                </label>
              </div>
            </div>

            <div>
              <div class="mb-3 text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Características Extras</div>
              <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="md:col-span-1">
                  <label class="mb-2 block text-sm font-medium text-gray-700">Mobiliado</label>
                  <label class="flex items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 text-sm text-gray-700">
                    <input v-model="form.mobiliado" type="checkbox" class="rounded border-gray-300">
                    Imóvel mobiliado
                  </label>
                </div>

                <div>
                  <label class="mb-2 block text-sm font-medium text-gray-700">Ano de Construção</label>
                  <input v-model.number="form.ano_construcao" type="number" min="1800" :max="currentYear + 1" class="w-full rounded-lg border border-gray-300 px-4 py-3" placeholder="2021">
                  <div v-if="form.errors.ano_construcao" class="mt-1 text-sm text-red-600">{{ form.errors.ano_construcao }}</div>
                </div>

                <div>
                  <label class="mb-2 block text-sm font-medium text-gray-700">Posição Solar</label>
                  <select v-model="form.posicao_solar" class="w-full rounded-lg border border-gray-300 px-4 py-3 bg-white">
                    <option value="">Selecione</option>
                    <option v-for="option in solarPositionOptions" :key="option" :value="option">{{ option }}</option>
                  </select>
                  <div v-if="form.errors.posicao_solar" class="mt-1 text-sm text-red-600">{{ form.errors.posicao_solar }}</div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow">
          <div class="mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Descrição</h2>
            <p class="mt-1 text-sm text-gray-500">Conteúdo exibido na página pública do imóvel.</p>
          </div>

          <textarea v-model="form.descricao" rows="10" class="w-full rounded-lg border border-gray-300 px-4 py-3" placeholder="Descrição do imóvel..."></textarea>
          <div v-if="form.errors.descricao" class="mt-1 text-sm text-red-600">{{ form.errors.descricao }}</div>
        </section>
      </div>

      <div class="space-y-6">
        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow">
          <div class="mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Configurações</h2>
          </div>

          <div class="space-y-6">
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-700">Flags</label>
              <div class="grid grid-cols-1 gap-3">
                <label class="flex items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 text-sm text-gray-700">
                  <input v-model="form.is_exclusive" type="checkbox" class="rounded border-gray-300">
                  Exclusivo
                </label>
                <label class="flex items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 text-sm text-gray-700">
                  <input v-model="form.is_off_market" type="checkbox" class="rounded border-gray-300">
                  Off Market
                </label>
              </div>
            </div>

            <div v-if="specialCategories.length > 0">
              <label class="mb-2 block text-sm font-medium text-gray-700">Categoria especial</label>
              <select v-model="selectedSpecialCategoryId" class="w-full rounded-lg border border-gray-300 px-4 py-3 bg-white">
                <option value="">Nenhuma</option>
                <option v-for="sc in specialCategories" :key="sc.id" :value="String(sc.id)">{{ sc.name }}</option>
              </select>
              <div v-if="form.errors.special_category_ids" class="mt-1 text-sm text-red-600">{{ form.errors.special_category_ids }}</div>
            </div>

            <div>
              <label class="mb-2 block text-sm font-medium text-gray-700">Exibir na Home</label>
              <div class="grid grid-cols-1 gap-3">
                <label class="flex items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 text-sm text-gray-700">
                  <input v-model="form.show_in_home_selecao_especial" type="checkbox" class="rounded border-gray-300">
                  Seleção especial
                </label>
                <label class="flex items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 text-sm text-gray-700">
                  <input v-model="form.show_in_home_mais_procurados" type="checkbox" class="rounded border-gray-300">
                  Mais procurados
                </label>
                <label class="flex items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 text-sm text-gray-700">
                  <input v-model="form.show_in_home_visto_recentemente" type="checkbox" class="rounded border-gray-300">
                  Visto recentemente
                </label>
              </div>
            </div>
          </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow">
          <div class="mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Imagens</h2>
          </div>

          <PropertyImageUploader
            ref="imageUploaderRef"
            :existing-photos="propertyPhotos"
            :upload-url="`${adminBase}/properties/uploads`"
            :delete-upload-base-url="`${adminBase}/properties/uploads`"
            :max-files="imageUploadConfig?.maxFiles ?? null"
            :max-file-size-bytes="imageUploadConfig?.maxFileSizeBytes || (10 * 1024 * 1024)"
            :parallel-uploads="imageUploadConfig?.parallelUploads || 6"
          />
          <div v-if="form.errors.featured_upload_token" class="mt-3 text-sm text-red-600">{{ form.errors.featured_upload_token }}</div>
          <div v-if="form.errors.gallery_upload_tokens" class="mt-1 text-sm text-red-600">{{ form.errors.gallery_upload_tokens }}</div>
          <div v-if="uploadFormError" class="mt-1 text-sm text-red-600">{{ uploadFormError }}</div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow">
          <div class="mb-4">
            <h2 class="text-lg font-semibold text-gray-900">SEO</h2>
          </div>

          <div class="space-y-4">
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-700">Meta Title</label>
              <input v-model="form.meta_title" type="text" class="w-full rounded-lg border border-gray-300 px-4 py-3" placeholder="Título SEO (opcional)">
              <div v-if="form.errors.meta_title" class="mt-1 text-sm text-red-600">{{ form.errors.meta_title }}</div>
            </div>

            <div>
              <label class="mb-2 block text-sm font-medium text-gray-700">Meta Description</label>
              <textarea v-model="form.meta_description" rows="4" class="w-full rounded-lg border border-gray-300 px-4 py-3" placeholder="Descrição SEO (opcional)"></textarea>
              <div v-if="form.errors.meta_description" class="mt-1 text-sm text-red-600">{{ form.errors.meta_description }}</div>
            </div>
          </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow">
          <button type="submit" :disabled="form.processing" class="w-full rounded-lg bg-blue-900 px-6 py-3 font-semibold text-white transition hover:bg-blue-800 disabled:opacity-60">
            {{ form.processing ? 'Salvando...' : 'Salvar Imóvel' }}
          </button>
        </section>
      </div>
    </form>
  </AdminLayout>
</template>

<script setup>
import axios from 'axios';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Shared/AdminLayout.vue';
import PropertyImageUploader from '@/Shared/PropertyImageUploader.vue';

const page = usePage();
const adminBase = computed(() => page.props?.paths?.admin || '/admin');

const props = defineProps({
  propertyTypes: {
    type: Array,
    default: () => [],
  },
  businessTypes: {
    type: Array,
    default: () => [],
  },
  condominiums: {
    type: Array,
    default: () => [],
  },
  specialCategories: {
    type: Array,
    default: () => [],
  },
  property: {
    type: Object,
    default: null,
  },
  selectedSpecialCategoryIds: {
    type: Array,
    default: () => [],
  },
  generatedReferenceCode: {
    type: String,
    default: '',
  },
  imageUploadConfig: {
    type: Object,
    default: () => ({ maxFiles: null, maxFileSizeBytes: 10 * 1024 * 1024, parallelUploads: 6, pollIntervalMs: 4000 }),
  },
});

const isEdit = computed(() => !!props.property?.id);
const currentYear = new Date().getFullYear();
const solarPositionOptions = ['Norte', 'Sul', 'Leste', 'Oeste', 'Nordeste', 'Noroeste', 'Sudeste', 'Sudoeste'];

const defaultPropertyTypeId = computed(() => props.propertyTypes[0]?.id ?? null);
const defaultBusinessTypeIds = computed(() => {
  const ids = getInitialBusinessTypeIds(props.property, props.businessTypes);
  return ids.length > 0 ? ids : (props.businessTypes[0]?.id ? [props.businessTypes[0].id] : []);
});

const formatNullableCurrencyNumberBRL = (value) => {
  if (value === null || value === undefined || value === '') {
    return '';
  }

  const number = Number(value);
  if (!Number.isFinite(number) || number <= 0) {
    return '';
  }

  return number.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
};

const form = useForm({
  titulo: props.property?.titulo || '',
  codigo_referencia: props.property?.codigo_referencia || (isEdit.value ? '' : props.generatedReferenceCode),
  meta_title: props.property?.meta_title || '',
  meta_description: props.property?.meta_description || '',
  descricao: props.property?.descricao || '',
  tipo_propriedade_id: props.property?.tipo_propriedade_id ?? defaultPropertyTypeId.value,
  condominium_id: props.property?.condominium_id ?? null,
  business_type_ids: defaultBusinessTypeIds.value,
  valor_venda: formatNullableCurrencyNumberBRL(props.property?.valor_venda),
  valor_locacao: formatNullableCurrencyNumberBRL(props.property?.valor_locacao),
  endereco: props.property?.endereco || '',
  bairro: props.property?.bairro || '',
  cidade: props.property?.cidade || '',
  estado: props.property?.estado || 'SP',
  area_total: props.property?.area_total ?? null,
  area_construida: props.property?.area_construida ?? props.property?.area_util ?? null,
  quartos: props.property?.quartos ?? null,
  suites: props.property?.suites ?? null,
  banheiros: props.property?.banheiros ?? null,
  lavabos: props.property?.lavabos ?? null,
  garagens: props.property?.garagens ?? null,
  andar: props.property?.andar ?? null,
  valor_condominio: formatNullableCurrencyNumberBRL(props.property?.valor_condominio ?? props.property?.condominio),
  valor_iptu: formatNullableCurrencyNumberBRL(props.property?.valor_iptu ?? props.property?.iptu),
  aceita_permuta: !!props.property?.aceita_permuta,
  aceita_financiamento: !!props.property?.aceita_financiamento,
  mobiliado: !!props.property?.mobiliado,
  ano_construcao: props.property?.ano_construcao ?? null,
  posicao_solar: props.property?.posicao_solar || '',
  is_exclusive: !!props.property?.is_exclusive,
  is_off_market: !!props.property?.is_off_market,
  show_in_home_selecao_especial: !!props.property?.show_in_home_selecao_especial,
  show_in_home_mais_procurados: !!props.property?.show_in_home_mais_procurados,
  show_in_home_visto_recentemente: !!props.property?.show_in_home_visto_recentemente,
  featured_upload_token: null,
  gallery_upload_tokens: [],
  remove_photo_ids: [],
  photo_order_ids: [],
  special_category_ids: props.selectedSpecialCategoryIds || [],
});

const selectedSpecialCategoryId = computed({
  get: () => {
    const firstId = Array.isArray(form.special_category_ids) ? form.special_category_ids[0] : null;
    return firstId ? String(firstId) : '';
  },
  set: (value) => {
    form.special_category_ids = value ? [Number(value)] : [];
  },
});

const imageUploaderRef = ref(null);
const uploadFormError = ref('');
const propertyPhotos = ref(Array.isArray(props.property?.photos) ? props.property.photos : []);
const processingCounts = ref({
  total: propertyPhotos.value.length,
  pending: propertyPhotos.value.filter((photo) => photo?.processing_status === 'pending').length,
  processing: propertyPhotos.value.filter((photo) => photo?.processing_status === 'processing').length,
  completed: propertyPhotos.value.filter((photo) => photo?.processing_status === 'completed').length,
  failed: propertyPhotos.value.filter((photo) => photo?.processing_status === 'failed').length,
});
let processingTimer = null;

const formatCurrencyBRL = (value) => {
  const digits = String(value ?? '').replace(/\D/g, '');
  if (!digits) return '';
  const number = Number(digits) / 100;
  return number.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
};

function getInitialBusinessTypeIds(property, businessTypes) {
  const ids = [];
  const findTypeId = (targetName) => businessTypes.find((type) => String(type?.name || '').toLowerCase() === targetName)?.id;

  if (property?.aceita_venda) {
    const saleId = findTypeId('comprar');
    if (saleId) ids.push(saleId);
  }

  if (property?.aceita_locacao) {
    const rentId = findTypeId('alugar');
    if (rentId) ids.push(rentId);
  }

  if (property?.aceita_temporada) {
    const seasonId = findTypeId('temporada');
    if (seasonId) ids.push(seasonId);
  }

  if (ids.length === 0 && property?.business_type_id) {
    ids.push(property.business_type_id);
  }

  return [...new Set(ids)];
}

const businessTypeLabel = (name) => {
  if (name === 'Comprar') return 'Compra';
  if (name === 'Alugar') return 'Aluguel';
  return name;
};

const showProcessingBanner = computed(() => isEdit.value && processingCounts.value.total > 0 && (processingCounts.value.pending > 0 || processingCounts.value.processing > 0 || processingCounts.value.failed > 0));
const processingProgress = computed(() => {
  const total = Number(processingCounts.value.total || 0);
  if (!total) return 0;
  return Math.max(0, Math.min(100, Math.round((Number(processingCounts.value.completed || 0) / total) * 100)));
});
const processingSummaryText = computed(() => {
  const { pending, processing, failed } = processingCounts.value;
  if (failed > 0) {
    return `${processingCounts.value.completed} concluídas, ${failed} com falha e ${pending + processing} ainda em processamento.`;
  }

  return `${pending} pendentes e ${processing} em processamento na fila.`;
});

const onSalePriceInput = () => {
  form.valor_venda = formatCurrencyBRL(form.valor_venda);
};

const onRentPriceInput = () => {
  form.valor_locacao = formatCurrencyBRL(form.valor_locacao);
};

const onCondominiumPriceInput = () => {
  form.valor_condominio = formatCurrencyBRL(form.valor_condominio);
};

const onIptuPriceInput = () => {
  form.valor_iptu = formatCurrencyBRL(form.valor_iptu);
};

async function refreshProcessingStatus() {
  if (!isEdit.value || !props.property?.id) return;

  try {
    const response = await axios.get(`${adminBase.value}/properties/${props.property.id}/image-processing-status`);
    const counts = response.data?.counts || {};
    processingCounts.value = {
      total: Number(counts.total || 0),
      pending: Number(counts.pending || 0),
      processing: Number(counts.processing || 0),
      completed: Number(counts.completed || 0),
      failed: Number(counts.failed || 0),
    };

    if (Array.isArray(response.data?.photos)) {
      propertyPhotos.value = response.data.photos;
    }

    if ((processingCounts.value.pending + processingCounts.value.processing) === 0 && processingTimer) {
      clearInterval(processingTimer);
      processingTimer = null;
    }
  } catch {
  }
}

const submit = () => {
  uploadFormError.value = '';

  const payload = imageUploaderRef.value?.getSubmissionPayload?.();
  if (!payload) {
    uploadFormError.value = 'Nao foi possivel preparar o envio das imagens.';
    return;
  }

  if (payload.hasPendingUploads) {
    uploadFormError.value = `Aguarde o termino dos uploads antes de salvar o imovel. ${payload.uploaded_count} de ${payload.selected_count} imagens foram enviadas e ${payload.pending_count} ainda estao pendentes.`;
    return;
  }

  if (payload.hasUploadErrors) {
    uploadFormError.value = `Existem ${payload.failed_count} imagens com falha de envio. Reenvie ou remova antes de salvar.`;
    return;
  }

  form.featured_upload_token = payload.featured_upload_token;
  form.gallery_upload_tokens = payload.gallery_upload_tokens;
  form.remove_photo_ids = payload.remove_photo_ids;
  form.photo_order_ids = payload.photo_order_ids;

  if (isEdit.value) {
    form.transform((data) => ({ ...data, _method: 'put' })).post(`${adminBase.value}/properties/${props.property.id}`, { forceFormData: true });
    return;
  }

  form.post(`${adminBase.value}/properties`, { forceFormData: true });
};

onMounted(() => {
  if (!isEdit.value || !props.property?.id) return;

  if ((processingCounts.value.pending + processingCounts.value.processing + processingCounts.value.failed) > 0) {
    refreshProcessingStatus();
  }

  processingTimer = setInterval(refreshProcessingStatus, props.imageUploadConfig?.pollIntervalMs || 4000);
});

onBeforeUnmount(() => {
  if (processingTimer) {
    clearInterval(processingTimer);
    processingTimer = null;
  }
});
</script>
