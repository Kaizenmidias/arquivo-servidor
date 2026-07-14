<template>
  <Layout>
    <section class="relative overflow-hidden text-white">
      <div class="absolute inset-0">
        <img :src="propertiesBannerImage" alt="Imóveis" class="w-full h-full object-cover" />
        <div class="absolute inset-0" :style="{ backgroundColor: propertiesBannerOverlayColor, opacity: propertiesBannerOverlayOpacity }"></div>
      </div>
      <div class="ui-shell relative py-16 lg:py-20">
        <div class="max-w-3xl ui-fade-up">
          <span class="inline-flex rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-white/85 backdrop-blur-sm">Curadoria de imóveis</span>
          <h1 class="mt-4 text-4xl font-bold tracking-tight" :style="{ color: propertiesBannerTitleColor }">{{ propertiesBannerTitle }}</h1>
          <p v-if="propertiesBannerSubtitle" class="mt-4 max-w-2xl text-base text-white/85 sm:text-lg" :style="{ color: propertiesBannerSubtitleColor }">{{ propertiesBannerSubtitle }}</p>
        </div>
      </div>
    </section>

    <section class="bg-transparent py-8 lg:-mt-10 lg:pb-12">
      <div class="ui-shell">
        <div class="mb-4 flex items-center justify-between gap-3 lg:hidden">
          <button
            type="button"
            class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 shadow-sm transition hover:border-slate-300 hover:bg-slate-50"
            @click="openMobileFilters"
          >
            <svg class="h-4 w-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L15 12.414V19a1 1 0 01-.553.894l-4 2A1 1 0 019 21v-8.586L3.293 6.707A1 1 0 013 6V4z"></path>
            </svg>
            <span>{{ mobileFilterButtonLabel }}</span>
          </button>
          <div class="text-sm font-medium text-gray-700">
            {{ totalLabel }}
          </div>
        </div>

        <transition name="fade">
          <div v-if="isMobileFiltersOpen" class="fixed inset-0 z-[65] bg-slate-950/55 backdrop-blur-sm lg:hidden" @click="closeMobileFilters"></div>
        </transition>

        <div class="grid grid-cols-1 lg:grid-cols-[320px_1fr] gap-6">
          <aside :class="filterPanelClass">
            <div class="flex h-full flex-col">
              <div class="flex items-center justify-between gap-3">
                <div class="inline-flex items-center gap-2 font-semibold text-gray-900">
                  <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L15 12.414V19a1 1 0 01-.553.894l-4 2A1 1 0 019 21v-8.586L3.293 6.707A1 1 0 013 6V4z"></path>
                  </svg>
                  <span>Filtros</span>
                </div>
                <div class="flex items-center gap-3">
                  <button type="button" class="text-sm text-gray-500 hover:text-gray-900" @click="clearAll">Limpar</button>
                  <button type="button" class="inline-flex rounded-full border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 lg:hidden" @click="closeMobileFilters">
                    Fechar
                  </button>
                </div>
              </div>

              <div class="flex min-h-0 flex-1 flex-col">
                <div class="mt-4">
                  <div class="relative">
                    <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input v-model="form.q" type="text" class="w-full rounded-2xl border border-slate-200 bg-white pl-10 pr-4 py-3 text-sm transition focus:border-slate-300 focus:ring-2 focus:ring-slate-900/10" placeholder="Código, endereço..." />
                  </div>
                </div>

                <div class="mt-6 flex-1 space-y-6 overflow-y-auto pr-1 lg:pr-0">
                  <div>
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Negócio</div>
                    <div class="flex flex-wrap gap-2">
                      <button
                        v-for="bt in businessTypes"
                        :key="bt.id"
                        type="button"
                        class="rounded-full border px-4 py-2 text-sm transition"
                        :class="form.business_type_id === String(bt.id) ? 'bg-slate-900 text-white border-slate-900 shadow-sm' : 'bg-white text-gray-700 border-slate-200 hover:border-slate-300 hover:bg-slate-50'"
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
                        class="rounded-full border px-4 py-2 text-sm transition"
                        :class="form.property_type === t.value ? 'bg-slate-900 text-white border-slate-900 shadow-sm' : 'bg-white text-gray-700 border-slate-200 hover:border-slate-300 hover:bg-slate-50'"
                        @click="togglePropertyType(t.value)"
                      >
                        {{ t.label }}
                      </button>
                    </div>
                  </div>

                  <div>
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Condomínio</div>
                    <select v-model="form.condominium_id" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm transition focus:border-slate-300 focus:ring-2 focus:ring-slate-900/10" @change="apply">
                      <option value="">Todos</option>
                      <option v-for="item in condominiums" :key="item.id" :value="String(item.id)">{{ item.name }}</option>
                    </select>
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
                          class="w-full rounded-2xl border border-slate-200 pl-9 pr-3 py-3 text-sm transition focus:border-slate-300 focus:ring-2 focus:ring-slate-900/10"
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
                          class="w-full rounded-2xl border border-slate-200 pl-9 pr-3 py-3 text-sm transition focus:border-slate-300 focus:ring-2 focus:ring-slate-900/10"
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
                        class="rounded-full border px-4 py-2 text-sm transition"
                        :class="form.special_category_ids.includes(String(sc.id)) ? 'bg-slate-900 text-white border-slate-900 shadow-sm' : 'bg-white text-gray-700 border-slate-200 hover:border-slate-300 hover:bg-slate-50'"
                        @click="toggleSpecialCategory(sc.id)"
                      >
                        {{ sc.name }}
                      </button>
                    </div>
                  </div>

                  <div class="grid grid-cols-1 gap-5">
                    <div>
                      <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Dormitórios</div>
                      <select v-model="form.bedrooms_min" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm transition focus:border-slate-300 focus:ring-2 focus:ring-slate-900/10">
                        <option value="">Qualquer</option>
                        <option v-for="n in [1,2,3,4,5]" :key="n" :value="String(n)">Maior ou igual {{ n }}</option>
                      </select>
                    </div>
                    <div>
                      <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Sendo suítes</div>
                      <select v-model="form.suites_min" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm transition focus:border-slate-300 focus:ring-2 focus:ring-slate-900/10">
                        <option value="">Qualquer</option>
                        <option v-for="n in [1,2,3,4,5]" :key="n" :value="String(n)">Maior ou igual {{ n }}</option>
                      </select>
                    </div>
                    <div>
                      <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Vagas</div>
                      <select v-model="form.garages_min" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm transition focus:border-slate-300 focus:ring-2 focus:ring-slate-900/10">
                        <option value="">Qualquer</option>
                        <option v-for="n in [1,2,3,4,5]" :key="n" :value="String(n)">{{ n }}+</option>
                      </select>
                    </div>
                  </div>

                  <div>
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Área privativa (m²)</div>
                    <div class="grid grid-cols-2 gap-3">
                      <input v-model="form.area_min" type="text" inputmode="numeric" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm transition focus:border-slate-300 focus:ring-2 focus:ring-slate-900/10" placeholder="Mínimo" />
                      <input v-model="form.area_max" type="text" inputmode="numeric" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm transition focus:border-slate-300 focus:ring-2 focus:ring-slate-900/10" placeholder="Máximo" />
                    </div>
                  </div>

                  <div>
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Área do terreno (m²)</div>
                    <div class="grid grid-cols-2 gap-3">
                      <input v-model="form.lot_area_min" type="text" inputmode="numeric" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm transition focus:border-slate-300 focus:ring-2 focus:ring-slate-900/10" placeholder="Mínimo" />
                      <input v-model="form.lot_area_max" type="text" inputmode="numeric" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm transition focus:border-slate-300 focus:ring-2 focus:ring-slate-900/10" placeholder="Máximo" />
                    </div>
                  </div>

                  <button type="button" class="w-full rounded-2xl bg-slate-900 py-3 text-sm font-semibold text-white transition hover:bg-slate-800" @click="apply">
                    Aplicar filtros
                  </button>
                </div>
              </div>
            </div>
          </aside>

          <main>
            <div class="mb-6 flex flex-col gap-4 rounded-[24px] border border-white/50 bg-white/80 p-4 shadow-sm backdrop-blur-xl sm:flex-row sm:items-center sm:justify-between">
              <div class="text-gray-900 font-semibold">
                {{ totalLabel }}
              </div>
              <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2">
                  <span class="text-sm text-gray-500">Exibir</span>
                  <select v-model="form.per_page" class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm transition focus:border-slate-300 focus:ring-2 focus:ring-slate-900/10" @change="apply">
                    <option v-for="option in perPageOptions" :key="option" :value="String(option)">{{ option }}</option>
                  </select>
                </div>
                <div class="flex items-center gap-2">
                  <span class="text-sm text-gray-500">Ordenar</span>
                <select v-model="form.sort" class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm transition focus:border-slate-300 focus:ring-2 focus:ring-slate-900/10" @change="apply">
                  <option value="newest">Mais recentes</option>
                  <option value="price_asc">Menor preço</option>
                  <option value="price_desc">Maior preço</option>
                </select>
                </div>
              </div>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
              <a v-for="property in items" :key="property.id" :href="property.url" class="group ui-fade-up">
                <PropertyCard :property="property" />
              </a>
            </div>

            <div v-if="items.length === 0" class="mt-6 rounded-[24px] border border-dashed border-slate-200 bg-white/80 py-16 text-center text-gray-600 backdrop-blur-sm">
              Nenhum imóvel encontrado com os filtros selecionados.
            </div>

            <div v-if="paginationLinks.length > 0" class="flex justify-center mt-10">
              <nav class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-white px-2 py-1 shadow-sm">
                <Link
                  v-for="(link, idx) in paginationLinks"
                  :key="idx"
                  :href="link.url || '#'"
                  class="px-3 py-2 text-sm rounded-full"
                  :class="link.active ? 'bg-slate-900 text-white' : (link.url ? 'text-gray-700 hover:bg-gray-100' : 'text-gray-300 cursor-not-allowed')"
                  v-html="translatePaginationLabel(link.label)"
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
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import Layout from '@/Shared/Layout.vue';
import PropertyCard from '@/Shared/PropertyCard.vue';
import { Link, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
  properties: {
    type: Object,
    default: () => ({ data: [] }),
  },
  sitePropertiesCount: {
    type: Number,
    default: 0,
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
  condominiums: {
    type: Array,
    default: () => [],
  },
  specialCategories: {
    type: Array,
    default: () => [],
  },
});

const inertiaPage = usePage();
const settings = computed(() => inertiaPage.props.settings || {});

const placeholderImage = `data:image/svg+xml,${encodeURIComponent(
  `<svg xmlns="http://www.w3.org/2000/svg" width="1600" height="600" viewBox="0 0 1600 600">
    <defs>
      <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
        <stop offset="0" stop-color="#0f172a"/>
        <stop offset="1" stop-color="#1e3a8a"/>
      </linearGradient>
    </defs>
    <rect width="1600" height="600" fill="url(#g)"/>
    <text x="800" y="330" text-anchor="middle" font-family="Arial, sans-serif" font-size="44" fill="rgba(255,255,255,0.35)">Imóveis</text>
  </svg>`
)}`;

const propertiesBannerImage = computed(() => settings.value.properties_banner_image_url || placeholderImage);
const propertiesBannerTitle = computed(() => settings.value.properties_banner_title || 'Imóveis');
const propertiesBannerSubtitle = computed(() => settings.value.properties_banner_subtitle || '');
const propertiesBannerTitleColor = computed(() => settings.value.properties_banner_title_color || '#ffffff');
const propertiesBannerSubtitleColor = computed(() => settings.value.properties_banner_subtitle_color || 'rgba(255,255,255,0.85)');
const propertiesBannerOverlayColor = computed(() => settings.value.properties_banner_overlay_color || '#0f172a');
const propertiesBannerOverlayOpacity = computed(() => {
  const raw = Number(settings.value.properties_banner_overlay_opacity ?? 70);
  return Math.max(0, Math.min(100, raw)) / 100;
});

const items = computed(() => props.properties?.data || []);
const meta = computed(() => props.properties?.meta || null);
const paginationLinks = computed(() => props.properties?.links || []);
const perPageOptions = [12, 18, 24, 36];
const totalLabel = computed(() => {
  const total = Number(props.sitePropertiesCount ?? 0);
  const fallback = meta.value?.total;
  const value = Number.isFinite(total) && total > 0
    ? total
    : (typeof fallback === 'number' ? fallback : items.value.length);
  return `${value} ${value === 1 ? 'imóvel' : 'imóveis'}`;
});
const isMobileFiltersOpen = ref(false);
const mobileFilterButtonLabel = computed(() => {
  const activeCount = Object.keys(cleanFilters(form)).filter((key) => key !== 'sort').length;
  return activeCount > 0 ? `Filtros (${activeCount})` : 'Filtros';
});
const filterPanelClass = computed(() => {
  const desktopClasses = 'lg:sticky lg:top-28 lg:z-40 lg:block lg:max-h-[calc(100vh-8rem)] lg:overflow-hidden';
  const mobileClasses = isMobileFiltersOpen.value
    ? 'fixed inset-x-4 bottom-4 top-24 z-[70] block overflow-hidden rounded-[28px] shadow-[0_28px_80px_rgba(15,23,42,0.28)]'
    : 'hidden';

  return `ui-surface-panel p-5 ${mobileClasses} ${desktopClasses}`;
});

const normalizeString = (value) => (value === null || value === undefined ? '' : String(value));
const isDesktopViewport = () => typeof window !== 'undefined' && window.innerWidth >= 1024;
const setBodyScrollLock = (locked) => {
  if (typeof document === 'undefined') {
    return;
  }

  document.body.classList.toggle('overflow-hidden', locked);
};

const form = reactive({
  q: normalizeString(props.filters?.q),
  business_type_id: normalizeString(props.filters?.business_type_id),
  condominium_id: normalizeString(props.filters?.condominium_id),
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
  per_page: normalizeString(props.filters?.per_page || '18'),
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
  closeMobileFiltersIfNeeded();
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
  form.condominium_id = '';
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
  form.per_page = '18';
  apply();
};

function translatePaginationLabel(label) {
  return String(label || '')
    .replace(/&laquo;\s*Previous/gi, '&laquo; Anterior')
    .replace(/Next\s*&raquo;/gi, 'Próximo &raquo;')
    .replace(/Previous/gi, 'Anterior')
    .replace(/Next/gi, 'Próximo');
}

const openMobileFilters = () => {
  if (isDesktopViewport()) {
    return;
  }

  isMobileFiltersOpen.value = true;
};

const closeMobileFilters = () => {
  isMobileFiltersOpen.value = false;
};

const closeMobileFiltersIfNeeded = () => {
  if (!isDesktopViewport()) {
    closeMobileFilters();
  }
};

const onResize = () => {
  if (isDesktopViewport()) {
    closeMobileFilters();
  }
};

watch(isMobileFiltersOpen, (open) => {
  setBodyScrollLock(open);
});

onMounted(() => {
  window.addEventListener('resize', onResize, { passive: true });
});

onBeforeUnmount(() => {
  window.removeEventListener('resize', onResize);
  setBodyScrollLock(false);
});
</script>
