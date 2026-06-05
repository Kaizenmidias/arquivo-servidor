<template>
  <Layout>
    <section class="bg-gray-50 py-8">
      <div class="max-w-[1400px] mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-[320px_1fr] gap-6">
          <aside class="bg-white rounded-2xl shadow border border-gray-100 p-5 h-fit sticky top-6">
            <div class="flex items-center justify-between">
              <div class="inline-flex items-center gap-2 font-semibold text-gray-900">
                <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L15 12.414V19a1 1 0 01-.553.894l-4 2A1 1 0 019 21v-8.586L3.293 6.707A1 1 0 013 6V4z"></path>
                </svg>
                <span>Filtros</span>
              </div>
              <button type="button" class="text-sm text-gray-500 hover:text-gray-900" @click="clearAll">Limpar</button>
            </div>

            <div class="mt-4">
              <div class="relative">
                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input
                  v-model="form.q"
                  type="text"
                  class="w-full border border-gray-200 rounded-full pl-10 pr-4 py-2.5 text-sm"
                  placeholder="Código, endereço..."
                />
              </div>
            </div>

            <div class="mt-6 space-y-6">
              <div>
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Negócio</div>
                <div class="flex flex-wrap gap-2">
                  <button
                    v-for="bt in businessTypes"
                    :key="bt.id"
                    type="button"
                    class="px-4 py-2 rounded-full text-sm border transition"
                    :class="form.business_type_id === String(bt.id) ? 'bg-blue-900 text-white border-blue-900' : 'bg-white text-gray-700 border-gray-200 hover:border-gray-300'"
                    @click="toggleBusinessType(bt.id)"
                  >
                    {{ bt.name }}
                  </button>
                </div>
              </div>

              <div>
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Tipo de imóvel</div>
                <div class="flex flex-wrap gap-2">
                  <button
                    v-for="t in propertyTypeGroups"
                    :key="t.value"
                    type="button"
                    class="px-4 py-2 rounded-full text-sm border transition"
                    :class="form.property_type === t.value ? 'bg-blue-900 text-white border-blue-900' : 'bg-white text-gray-700 border-gray-200 hover:border-gray-300'"
                    @click="togglePropertyType(t.value)"
                  >
                    {{ t.label }}
                  </button>
                </div>
              </div>

              <div>
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Preço</div>
                <div class="grid grid-cols-2 gap-3">
                  <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-500">R$</span>
                    <input
                      v-model="form.price_min"
                      type="text"
                      inputmode="numeric"
                      class="w-full border border-gray-200 rounded-full pl-9 pr-3 py-2.5 text-sm"
                      placeholder="Mínimo"
                      @input="onPriceMinInput"
                    />
                  </div>
                  <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-500">R$</span>
                    <input
                      v-model="form.price_max"
                      type="text"
                      inputmode="numeric"
                      class="w-full border border-gray-200 rounded-full pl-9 pr-3 py-2.5 text-sm"
                      placeholder="Máximo"
                      @input="onPriceMaxInput"
                    />
                  </div>
                </div>
              </div>

              <div v-if="specialCategories.length > 0">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Categorias especiais</div>
                <div class="flex flex-wrap gap-2">
                  <button
                    v-for="sc in specialCategories"
                    :key="sc.id"
                    type="button"
                    class="px-4 py-2 rounded-full text-sm border transition"
                    :class="form.special_category_ids.includes(String(sc.id)) ? 'bg-blue-900 text-white border-blue-900' : 'bg-white text-gray-700 border-gray-200 hover:border-gray-300'"
                    @click="toggleSpecialCategory(sc.id)"
                  >
                    {{ sc.name }}
                  </button>
                </div>
              </div>

              <div class="grid grid-cols-1 gap-5">
                <div>
                  <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Dormitórios</div>
                  <select v-model="form.bedrooms_min" class="w-full border border-gray-200 rounded-full px-4 py-2.5 text-sm">
                    <option value="">Qualquer</option>
                    <option v-for="n in [1,2,3,4,5]" :key="n" :value="String(n)">Maior ou igual {{ n }}</option>
                  </select>
                </div>
                <div>
                  <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Sendo suítes</div>
                  <select v-model="form.suites_min" class="w-full border border-gray-200 rounded-full px-4 py-2.5 text-sm">
                    <option value="">Qualquer</option>
                    <option v-for="n in [1,2,3,4,5]" :key="n" :value="String(n)">Maior ou igual {{ n }}</option>
                  </select>
                </div>
                <div>
                  <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Vagas</div>
                  <select v-model="form.garages_min" class="w-full border border-gray-200 rounded-full px-4 py-2.5 text-sm">
                    <option value="">Qualquer</option>
                    <option v-for="n in [1,2,3,4,5]" :key="n" :value="String(n)">{{ n }}+</option>
                  </select>
                </div>
              </div>

              <div>
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Área privativa (m²)</div>
                <div class="grid grid-cols-2 gap-3">
                  <input v-model="form.area_min" type="text" inputmode="numeric" class="w-full border border-gray-200 rounded-full px-4 py-2.5 text-sm" placeholder="Mínimo" />
                  <input v-model="form.area_max" type="text" inputmode="numeric" class="w-full border border-gray-200 rounded-full px-4 py-2.5 text-sm" placeholder="Máximo" />
                </div>
              </div>

              <div>
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Área do terreno (m²)</div>
                <div class="grid grid-cols-2 gap-3">
                  <input v-model="form.lot_area_min" type="text" inputmode="numeric" class="w-full border border-gray-200 rounded-full px-4 py-2.5 text-sm" placeholder="Mínimo" />
                  <input v-model="form.lot_area_max" type="text" inputmode="numeric" class="w-full border border-gray-200 rounded-full px-4 py-2.5 text-sm" placeholder="Máximo" />
                </div>
              </div>

              <button type="button" class="w-full bg-blue-900 hover:bg-blue-800 text-white font-semibold py-3 rounded-full transition" @click="apply">
                Aplicar filtros
              </button>
            </div>
          </aside>

          <main>
            <a href="/off-market" class="block bg-gradient-to-r from-slate-800 to-blue-900 text-white rounded-2xl p-5 mb-6 border border-white/10">
              <div class="flex items-center justify-between gap-4">
                <div>
                  <div class="flex items-center gap-2 font-semibold">
                    <svg class="w-5 h-5 text-white/90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Acesse imóveis Off-Market</span>
                  </div>
                  <div class="text-sm text-white/70">Lista exclusiva de imóveis fora do mercado aberto</div>
                </div>
                <div class="w-10 h-10 rounded-full bg-white/15 flex items-center justify-center">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                  </svg>
                </div>
              </div>
            </a>

            <div class="flex items-center justify-between gap-4 mb-6">
              <div class="text-gray-900 font-semibold">
                {{ totalLabel }}
              </div>
              <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500">Ordenar</span>
                <select v-model="form.sort" class="border border-gray-200 rounded-full px-4 py-2 text-sm bg-white" @change="apply">
                  <option value="newest">Mais recentes</option>
                  <option value="price_asc">Menor preço</option>
                  <option value="price_desc">Maior preço</option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
              <a v-for="property in items" :key="property.id" :href="property.url" class="group">
                <PropertyCard :property="property" />
              </a>
            </div>

            <div v-if="items.length === 0" class="py-16 text-center text-gray-600 bg-white rounded-2xl border border-gray-100 mt-6">
              Nenhum imóvel encontrado com os filtros selecionados.
            </div>

            <div v-if="paginationLinks.length > 0" class="flex justify-center mt-10">
              <nav class="inline-flex items-center gap-1 bg-white border border-gray-200 rounded-full px-2 py-1 shadow-sm">
                <Link
                  v-for="(link, idx) in paginationLinks"
                  :key="idx"
                  :href="link.url || '#'"
                  class="px-3 py-2 text-sm rounded-full"
                  :class="link.active ? 'bg-blue-900 text-white' : (link.url ? 'text-gray-700 hover:bg-gray-100' : 'text-gray-300 cursor-not-allowed')"
                  v-html="link.label"
                  preserve-scroll
                  preserve-state
                />
              </nav>
            </div>
          </main>
        </div>
      </div>
    </section>
  </Layout>
</template>

<script setup>
import { computed, reactive, watch } from 'vue';
import Layout from '@/Shared/Layout.vue';
import PropertyCard from '@/Shared/PropertyCard.vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
  properties: {
    type: Object,
    default: () => ({ data: [] }),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  businessTypes: {
    type: Array,
    default: () => [],
  },
  propertyTypeGroups: {
    type: Array,
    default: () => [],
  },
  specialCategories: {
    type: Array,
    default: () => [],
  },
});

const items = computed(() => props.properties?.data || []);
const meta = computed(() => props.properties?.meta || null);
const paginationLinks = computed(() => props.properties?.links || []);
const totalLabel = computed(() => {
  const total = meta.value?.total;
  const value = typeof total === 'number' ? total : items.value.length;
  return `${value} imóvel${value === 1 ? '' : 'is'}`;
});

const normalizeString = (value) => (value === null || value === undefined ? '' : String(value));

const form = reactive({
  q: normalizeString(props.filters?.q),
  business_type_id: normalizeString(props.filters?.business_type_id),
  property_type: normalizeString(props.filters?.property_type),
  special_category_ids: Array.isArray(props.filters?.special_category_ids) ? props.filters.special_category_ids.map((x) => String(x)) : [],
  price_min: normalizeString(props.filters?.price_min),
  price_max: normalizeString(props.filters?.price_max),
  bedrooms_min: normalizeString(props.filters?.bedrooms_min),
  suites_min: normalizeString(props.filters?.suites_min),
  bathrooms_min: normalizeString(props.filters?.bathrooms_min),
  garages_min: normalizeString(props.filters?.garages_min),
  area_min: normalizeString(props.filters?.area_min),
  area_max: normalizeString(props.filters?.area_max),
  lot_area_min: normalizeString(props.filters?.lot_area_min),
  lot_area_max: normalizeString(props.filters?.lot_area_max),
  sort: normalizeString(props.filters?.sort || 'newest'),
});

const cleanFilters = (raw) => {
  const out = {};
  Object.entries(raw).forEach(([key, value]) => {
    if (Array.isArray(value)) {
      const filtered = value.filter((v) => normalizeString(v).trim() !== '');
      if (filtered.length > 0) out[key] = filtered;
      return;
    }
    const v = normalizeString(value).trim();
    if (v !== '') out[key] = v;
  });
  return out;
};

const apply = () => {
  router.get('/imoveis', cleanFilters(form), { preserveState: true, preserveScroll: true, replace: true });
};

let searchTimeout = null;
watch(
  () => form.q,
  () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => apply(), 350);
  }
);

const formatCurrencyBRLInput = (value) => {
  const digits = String(value ?? '').replace(/\D/g, '');
  if (!digits) return '';
  const number = Number(digits) / 100;
  return number.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const onPriceMinInput = () => {
  form.price_min = formatCurrencyBRLInput(form.price_min);
};

const onPriceMaxInput = () => {
  form.price_max = formatCurrencyBRLInput(form.price_max);
};

const toggleBusinessType = (id) => {
  const current = normalizeString(form.business_type_id);
  const next = String(id);
  form.business_type_id = current === next ? '' : next;
  apply();
};

const togglePropertyType = (value) => {
  const current = normalizeString(form.property_type);
  form.property_type = current === value ? '' : value;
  apply();
};

const toggleSpecialCategory = (id) => {
  const value = String(id);
  const idx = form.special_category_ids.indexOf(value);
  if (idx >= 0) form.special_category_ids.splice(idx, 1);
  else form.special_category_ids.push(value);
  apply();
};

const clearAll = () => {
  form.q = '';
  form.business_type_id = '';
  form.property_type = '';
  form.special_category_ids = [];
  form.price_min = '';
  form.price_max = '';
  form.bedrooms_min = '';
  form.suites_min = '';
  form.bathrooms_min = '';
  form.garages_min = '';
  form.area_min = '';
  form.area_max = '';
  form.lot_area_min = '';
  form.lot_area_max = '';
  form.sort = 'newest';
  apply();
};
</script>
