<template>
  <Layout>
    <section class="relative min-h-[92svh] overflow-visible bg-black text-white">
      <div class="absolute inset-0">
        <img :src="homeHeroImage" alt="Imóvel" class="h-full w-full object-cover grayscale" />
        <div class="absolute inset-0 bg-black" :style="{ opacity: homeHeroOverlayOpacity }"></div>
      </div>

      <div class="ui-shell relative z-10 flex min-h-[92svh] items-center pb-32 pt-28 md:pb-36 lg:pt-32">
        <div class="max-w-4xl ui-fade-up">
          <div class="mb-5 flex items-center gap-3 text-[11px] font-semibold uppercase tracking-[0.24em] text-white/80">
            <span class="h-px w-10 bg-white/70"></span>
            <span>Find. Love. Live.</span>
          </div>
          <h1 class="max-w-4xl text-5xl font-semibold leading-[1.02] text-white md:text-7xl lg:text-8xl">{{ homeHeroTitle }}</h1>
          <p v-if="homeHeroSubtitle" class="mt-6 max-w-2xl text-base leading-8 text-white/82 md:text-lg">{{ homeHeroSubtitle }}</p>
          <div class="mt-8 flex flex-wrap gap-3">
            <a href="/imoveis" class="inline-flex items-center gap-3 rounded px-6 py-4 text-xs font-bold uppercase tracking-[0.16em] text-white transition hover:brightness-110" :style="{ backgroundColor: ctaColor }">
              Explorar imóveis
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-6-6 6 6-6 6"></path>
              </svg>
            </a>
            <a href="/quem-somos" class="inline-flex items-center gap-3 rounded border border-white/70 px-6 py-4 text-xs font-bold uppercase tracking-[0.16em] text-white transition hover:bg-white hover:text-black">
              Saiba mais
            </a>
          </div>
        </div>
      </div>

      <div class="ui-shell relative z-20 -mt-24 pb-10">
        <div class="bg-white p-4 text-black shadow-[0_24px_80px_rgba(0,0,0,0.18)] md:p-6">
          <div class="grid grid-cols-1 gap-3 md:grid-cols-5">
            <div>
              <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-500">Negócio</label>
              <select v-model="search.business_type_id" class="home-field">
                <option value="">Todos</option>
                <option v-for="bt in businessTypeOptions" :key="bt.id" :value="bt.id">{{ bt.name }}</option>
              </select>
            </div>
            <div>
              <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-500">Tipo de imóvel</label>
              <select v-model="search.property_type" class="home-field">
                <option value="">Todos</option>
                <option v-for="groupName in propertyTypeGroupNames" :key="groupName" :value="groupName">{{ groupName }}</option>
              </select>
            </div>
            <div>
              <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-500">Valor mín.</label>
              <input v-model="search.price_min" type="text" placeholder="R$ 0,00" class="home-field" @input="onPriceMinInput" />
            </div>
            <div>
              <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-500">Valor máx.</label>
              <input v-model="search.price_max" type="text" placeholder="R$ ilimitado" class="home-field" @input="onPriceMaxInput" />
            </div>
            <div class="flex items-end">
              <button type="button" class="flex w-full items-center justify-center gap-2 rounded px-6 py-3 text-sm font-semibold text-white transition hover:brightness-110" :style="{ backgroundColor: ctaColor }" @click="goSearch">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                Pesquisar
              </button>
            </div>
          </div>

          <button type="button" class="mx-auto mt-4 flex items-center justify-center gap-2 rounded-full border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-black transition hover:border-black" @click="toggleAdvanced">
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
              <path d="M10 18H14V16H10V18ZM3 6V8H21V6H3ZM6 13H18V11H6V13Z"></path>
            </svg>
            Filtros Avançados
            <svg class="h-4 w-4 transition-transform" fill="currentColor" viewBox="0 0 24 24" :class="showAdvanced ? 'rotate-180' : ''">
              <path d="M11.9999 13.1714L16.9497 8.22168L18.3639 9.63589L11.9999 15.9999L5.63599 9.63589L7.0502 8.22168L11.9999 13.1714Z"></path>
            </svg>
          </button>

          <div v-if="showAdvanced" class="mt-4 border border-gray-200 bg-white p-4 sm:p-6">
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
              <div class="home-filter-box">
                <span class="home-filter-label">Quartos</span>
                <div class="mt-2 flex items-center justify-between">
                  <button type="button" class="home-stepper" @click="decrement('bedrooms_min')">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M5 11V13H19V11H5Z"></path></svg>
                  </button>
                  <span class="font-semibold text-black">{{ search.bedrooms_min }}+</span>
                  <button type="button" class="home-stepper" @click="increment('bedrooms_min')">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M11 11V5H13V11H19V13H13V19H11V13H5V11H11Z"></path></svg>
                  </button>
                </div>
              </div>
              <div class="home-filter-box">
                <span class="home-filter-label">Suítes</span>
                <div class="mt-2 flex items-center justify-between">
                  <button type="button" class="home-stepper" @click="decrement('suites_min')">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M5 11V13H19V11H5Z"></path></svg>
                  </button>
                  <span class="font-semibold text-black">{{ search.suites_min }}+</span>
                  <button type="button" class="home-stepper" @click="increment('suites_min')">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M11 11V5H13V11H19V13H13V19H11V13H5V11H11Z"></path></svg>
                  </button>
                </div>
              </div>
              <div class="home-filter-box">
                <span class="home-filter-label">Vagas</span>
                <div class="mt-2 flex items-center justify-between">
                  <button type="button" class="home-stepper" @click="decrement('garages_min')">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M5 11V13H19V11H5Z"></path></svg>
                  </button>
                  <span class="font-semibold text-black">{{ search.garages_min }}+</span>
                  <button type="button" class="home-stepper" @click="increment('garages_min')">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M11 11V5H13V11H19V13H13V19H11V13H5V11H11Z"></path></svg>
                  </button>
                </div>
              </div>
              <div class="home-filter-box">
                <span class="home-filter-label">Área Mín (m²)</span>
                <input v-model="search.area_min" placeholder="0" class="home-small-field" type="number" />
              </div>
              <div class="home-filter-box">
                <span class="home-filter-label">Área Máx (m²)</span>
                <input v-model="search.area_max" placeholder="Ilimitado" class="home-small-field" type="number" />
              </div>
              <div class="home-filter-box lg:col-span-2">
                <span class="home-filter-label">Condomínio</span>
                <select v-model="search.condominium_id" class="home-small-field">
                  <option value="">Todos</option>
                  <option v-for="item in condominiumOptions" :key="item.id" :value="item.id">{{ item.name }}</option>
                </select>
              </div>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-4">
              <div class="home-filter-box">
                <span class="home-filter-label">Terreno Mín (m²)</span>
                <input v-model="search.lot_area_min" placeholder="0" class="home-small-field" type="number" />
              </div>
              <div class="home-filter-box">
                <span class="home-filter-label">Terreno Máx (m²)</span>
                <input v-model="search.lot_area_max" placeholder="Ilimitado" class="home-small-field" type="number" />
              </div>
            </div>

            <div v-if="specialCategoryList.length > 0" class="mt-4 border-t border-gray-200 pt-4">
              <span class="home-filter-label">Diferenciais</span>
              <div class="mt-2 flex max-h-[200px] flex-wrap gap-2 overflow-y-auto">
                <button
                  v-for="sc in specialCategoryList"
                  :key="sc.id"
                  type="button"
                  class="rounded-full px-3 py-1.5 text-xs font-medium transition-all"
                  :class="search.special_category_ids.includes(sc.id) ? 'bg-black text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                  @click="toggleSpecial(sc.id)"
                >
                  {{ sc.name }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="bg-white py-16 md:py-20">
      <div class="ui-shell">
        <div class="mb-8 flex flex-col justify-between gap-5 md:flex-row md:items-end">
          <div>
            <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-gray-500">Melhores Oportunidades</div>
            <h2 class="mt-3 max-w-3xl text-3xl font-semibold leading-tight text-black md:text-5xl">Melhores Oportunidades</h2>
          </div>
          <a href="/imoveis" class="inline-flex items-center gap-3 text-xs font-bold uppercase tracking-[0.16em] text-black transition hover:opacity-60">
            Ver todos os imóveis
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-6-6 6 6-6 6"></path>
            </svg>
          </a>
        </div>
        <div v-if="featuredProperties.length > 0" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <a v-for="property in featuredProperties" :key="property.id" :href="property.url || ('/imoveis/' + property.slug)" class="group w-full">
            <PropertyCard :property="property" :show-photo-controls="false" monochrome />
          </a>
        </div>
        <div v-else class="rounded border border-dashed border-gray-300 px-6 py-10 text-center text-gray-500">
          Nenhum imóvel disponível no momento.
        </div>
        <Pagination v-if="featuredLinks.length > 3" :links="featuredLinks" class="mt-6 flex justify-center" />
      </div>
    </section>

    <section class="bg-black py-8 text-white">
      <div class="ui-shell">
        <div class="grid grid-cols-2 gap-px overflow-hidden rounded bg-white/20 md:grid-cols-4">
          <div v-for="stat in homeStats" :key="stat.label" class="bg-black px-5 py-7 text-center">
            <div class="text-3xl font-semibold md:text-4xl">{{ stat.value }}</div>
            <div class="mt-2 text-xs uppercase tracking-[0.18em] text-white/65">{{ stat.label }}</div>
          </div>
        </div>
      </div>
    </section>

    <section v-if="homeContentHtml" class="bg-white py-14">
      <div class="ui-shell">
        <div class="grid gap-8 md:grid-cols-[0.85fr_1.15fr] md:items-start">
          <div>
            <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-gray-500">Sobre</div>
            <h2 class="mt-3 text-3xl font-semibold leading-tight text-black md:text-5xl">{{ homeHeroTitle }}</h2>
          </div>
          <div class="home-rich-content text-gray-700" v-html="homeContentHtml"></div>
        </div>
      </div>
    </section>

    <section v-if="showInstagramSection" class="bg-gray-50 py-16">
      <div class="mx-auto max-w-[1400px] px-4">
        <div class="mb-6 flex items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <div class="rounded-full bg-black p-2">
              <svg class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
              </svg>
            </div>
            <h2 class="text-2xl font-bold text-black">@{{ instagramHandle }}</h2>
            <span class="rounded-full border border-gray-300 px-4 py-1 text-sm font-semibold text-black">Instagram</span>
          </div>
          <a :href="instagramProfileUrl || '#'" target="_blank" rel="noopener" class="rounded px-6 py-2 font-semibold text-white transition hover:brightness-110" :style="{ backgroundColor: ctaColor }">
            Seguir
          </a>
        </div>

        <div class="relative">
          <div class="pointer-events-none absolute bottom-0 left-0 top-0 z-10 w-20 bg-gradient-to-r from-gray-50 to-transparent"></div>
          <div class="pointer-events-none absolute bottom-0 right-0 top-0 z-10 w-20 bg-gradient-to-l from-gray-50 to-transparent"></div>
          <div class="overflow-hidden">
            <div ref="instagramTrackRef" class="flex gap-4" :style="{ transform: `translateX(${instagramPosition}px)` }">
              <template v-for="(item, index) in instagramItems" :key="index">
                <a :href="item.permalink || instagramProfileUrl || '#'" target="_blank" rel="noopener" class="group w-64 flex-shrink-0">
                  <div class="relative overflow-hidden rounded shadow-md">
                    <img :src="item.image" :alt="item.caption" class="h-80 w-full object-cover grayscale transition-transform duration-500 group-hover:scale-105">
                    <div class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                      <div class="text-center text-white">
                        <div class="text-sm font-semibold">Ver no Instagram</div>
                      </div>
                    </div>
                  </div>
                </a>
              </template>
              <template v-for="(item, index) in instagramItems" :key="'dupe-'+index">
                <a :href="item.permalink || instagramProfileUrl || '#'" target="_blank" rel="noopener" class="group w-64 flex-shrink-0">
                  <div class="relative overflow-hidden rounded shadow-md">
                    <img :src="item.image" :alt="item.caption" class="h-80 w-full object-cover grayscale transition-transform duration-500 group-hover:scale-105">
                    <div class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                      <div class="text-center text-white">
                        <div class="text-sm font-semibold">Ver no Instagram</div>
                      </div>
                    </div>
                  </div>
                </a>
              </template>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="bg-white py-16">
      <div class="mx-auto max-w-[1400px] px-4">
        <div class="mb-8">
          <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-gray-500">Últimas Notícias</div>
          <h2 class="mt-3 text-3xl font-semibold text-black md:text-5xl">Últimas Notícias</h2>
        </div>
        <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
          <a v-for="(item, index) in ultimasNoticias" :key="index" :href="item.link" class="group">
            <div class="overflow-hidden rounded border border-gray-200 bg-white transition-shadow hover:shadow-[0_18px_45px_rgba(0,0,0,0.10)]">
              <img :src="item.image" :alt="item.title" class="h-56 w-full object-cover grayscale">
              <div class="p-6">
                <span class="text-sm font-semibold uppercase text-gray-500">{{ item.category }}</span>
                <h3 class="mb-2 mt-2 text-xl font-bold text-black transition-opacity group-hover:opacity-70">{{ item.title }}</h3>
                <p class="mb-4 line-clamp-2 text-gray-600">{{ item.excerpt }}</p>
                <div class="flex items-center justify-between text-sm text-gray-500">
                  <span>{{ item.date }}</span>
                  <span class="flex items-center gap-1 font-semibold text-black">
                    Ler mais
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                  </span>
                </div>
              </div>
            </div>
          </a>
        </div>
      </div>
    </section>
  </Layout>
</template>

<script setup>
import { computed, reactive, ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import Layout from '@/Shared/Layout.vue';
import PropertyCard from '@/Shared/PropertyCard.vue';
import Pagination from '@/Shared/Pagination.vue';

const props = defineProps({
  homePage: {
    type: Object,
    default: null,
  },
  selecaoEspecial: {
    type: Object,
    default: () => ({
      data: [],
      links: [],
    }),
  },
  maisProcurados: {
    type: Array,
    default: () => [],
  },
  vistoRecentemente: {
    type: Array,
    default: () => [],
  },
  instagramFeed: {
    type: Array,
    default: () => [],
  },
  instagramEnabled: {
    type: Boolean,
    default: true,
  },
  instagramUsername: {
    type: String,
    default: null,
  },
  instagramUrl: {
    type: String,
    default: null,
  },
  businessTypes: {
    type: Array,
    default: () => [],
  },
  condominiums: {
    type: Array,
    default: () => [],
  },
  propertyTypeGroups: {
    type: Object,
    default: () => ({}),
  },
  specialCategories: {
    type: Array,
    default: () => [],
  },
});

const ctaColor = '#18392f';

const placeholderImage = `data:image/svg+xml,${encodeURIComponent(
  `<svg xmlns="http://www.w3.org/2000/svg" width="1920" height="800" viewBox="0 0 1920 800">
    <rect width="1920" height="800" fill="#111111"/>
    <rect x="220" y="150" width="1480" height="500" fill="#2b2b2b"/>
    <path d="M360 560l340-250 180 145 210-185 470 290H360z" fill="#666666"/>
    <circle cx="1350" cy="250" r="80" fill="#999999"/>
    <text x="960" y="705" text-anchor="middle" font-family="Arial, sans-serif" font-size="54" fill="#ffffff">Meteorikah Imobiliária</text>
  </svg>`
)}`;

const homeHeroImage = computed(() => props.homePage?.banner_image || placeholderImage);
const homeHeroTitle = computed(() => props.homePage?.banner_title ?? 'Seja bem vindo! Seu novo lar está aqui.');
const homeHeroSubtitle = computed(() => props.homePage?.banner_subtitle ?? '');
const homeHeroOverlayOpacity = computed(() => {
  const raw = Number(props.homePage?.banner_overlay_opacity ?? 70);
  const safe = Number.isFinite(raw) ? raw : 70;
  return Math.max(35, Math.min(82, safe)) / 100;
});
const homeContentHtml = computed(() => String(props.homePage?.conteudo || '').trim());

const businessTypeOptions = computed(() => (
  Array.isArray(props.businessTypes) ? props.businessTypes.filter((item) => item && item.id != null) : []
));
const condominiumOptions = computed(() => (
  Array.isArray(props.condominiums) ? props.condominiums.filter((item) => item && item.id != null) : []
));
const propertyTypeGroups = computed(() => props.propertyTypeGroups || {});
const propertyTypeGroupNames = computed(() => Object.keys(propertyTypeGroups.value || {}));
const specialCategoryList = computed(() => (
  Array.isArray(props.specialCategories) ? props.specialCategories.filter((item) => item && item.id != null) : []
));

const showAdvanced = ref(false);
const search = reactive({
  business_type_id: '',
  condominium_id: '',
  property_type: '',
  price_min: '',
  price_max: '',
  bedrooms_min: 0,
  suites_min: 0,
  garages_min: 0,
  area_min: '',
  area_max: '',
  lot_area_min: '',
  lot_area_max: '',
  special_category_ids: [],
});

const toggleAdvanced = () => {
  showAdvanced.value = !showAdvanced.value;
};

const formatCurrencyBRL = (value) => {
  const digits = String(value ?? '').replace(/\D/g, '');
  const number = Number(digits) / 100;
  return number.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
};
const normalizePrice = (value) => {
  const digits = String(value ?? '').replace(/\D/g, '');
  const number = Number(digits) / 100;
  if (!Number.isFinite(number) || number <= 0) return '';
  return formatCurrencyBRL(value);
};
const onPriceMinInput = () => {
  search.price_min = normalizePrice(search.price_min);
};
const onPriceMaxInput = () => {
  search.price_max = normalizePrice(search.price_max);
};

const increment = (key) => {
  search[key] = Number(search[key] || 0) + 1;
};
const decrement = (key) => {
  const next = Number(search[key] || 0) - 1;
  search[key] = Math.max(0, next);
};

const toggleSpecial = (id) => {
  const n = Number(id);
  if (search.special_category_ids.includes(n)) {
    search.special_category_ids = search.special_category_ids.filter((x) => x !== n);
    return;
  }
  search.special_category_ids = [...search.special_category_ids, n];
};

const goSearch = () => {
  const params = {
    business_type_id: search.business_type_id || undefined,
    condominium_id: search.condominium_id || undefined,
    property_type: search.property_type || undefined,
    price_min: search.price_min || undefined,
    price_max: search.price_max || undefined,
    bedrooms_min: search.bedrooms_min > 0 ? String(search.bedrooms_min) : undefined,
    suites_min: search.suites_min > 0 ? String(search.suites_min) : undefined,
    garages_min: search.garages_min > 0 ? String(search.garages_min) : undefined,
    area_min: search.area_min !== '' ? String(search.area_min) : undefined,
    area_max: search.area_max !== '' ? String(search.area_max) : undefined,
    lot_area_min: search.lot_area_min !== '' ? String(search.lot_area_min) : undefined,
    lot_area_max: search.lot_area_max !== '' ? String(search.lot_area_max) : undefined,
    special_category_ids: search.special_category_ids.length > 0 ? search.special_category_ids : undefined,
  };

  router.get('/imoveis', params, { preserveScroll: true });
};

const featuredProperties = computed(() => {
  if (Array.isArray(props.selecaoEspecial?.data)) {
    return props.selecaoEspecial.data.filter((item) => item && item.id != null);
  }

  if (Array.isArray(props.selecaoEspecial)) {
    return props.selecaoEspecial.filter((item) => item && item.id != null);
  }

  return [];
});

const featuredLinks = computed(() => (
  Array.isArray(props.selecaoEspecial?.links) ? props.selecaoEspecial.links.filter((item) => item && 'label' in item) : []
));

const homeStats = computed(() => [
  { value: `${featuredProperties.value.length}+`, label: 'Oportunidades' },
  { value: `${businessTypeOptions.value.length}+`, label: 'Negócios' },
  { value: `${condominiumOptions.value.length}+`, label: 'Condomínios' },
  { value: `${specialCategoryList.value.length}+`, label: 'Diferenciais' },
]);

const instagramProfileUrl = computed(() => props.instagramUrl || (props.instagramUsername ? `https://instagram.com/${props.instagramUsername}` : ''));
const instagramHandle = computed(() => props.instagramUsername || 'instagram');
const showInstagramSection = computed(() => !!props.instagramEnabled);

const instagramItems = computed(() => {
  if (Array.isArray(props.instagramFeed) && props.instagramFeed.length > 0) {
    return props.instagramFeed
      .filter((m) => m?.id)
      .map((m) => ({
        id: m.id,
        image: `/instagram/media/${m.id}`,
        caption: m.caption || '',
        permalink: m.permalink || '',
      }));
  }

  return [
    { id: 1, image: placeholderImage, caption: 'Instagram' },
    { id: 2, image: placeholderImage, caption: 'Instagram' },
    { id: 3, image: placeholderImage, caption: 'Instagram' },
    { id: 4, image: placeholderImage, caption: 'Instagram' },
    { id: 5, image: placeholderImage, caption: 'Instagram' },
    { id: 6, image: placeholderImage, caption: 'Instagram' },
    { id: 7, image: placeholderImage, caption: 'Instagram' },
    { id: 8, image: placeholderImage, caption: 'Instagram' },
  ];
});

const ultimasNoticias = ref([
  { id: 1, title: 'Dicas para comprar seu primeiro imóvel', excerpt: 'Confira as melhores dicas para quem está comprando seu primeiro imóvel e quer evitar erros.', category: 'Dicas', date: '01 de Junho, 2026', link: '#', image: placeholderImage },
  { id: 2, title: 'Mercado imobiliário em alta no Brasil', excerpt: 'Veja as tendências do mercado imobiliário para o segundo semestre de 2026.', category: 'Mercado', date: '30 de Maio, 2026', link: '#', image: placeholderImage },
  { id: 3, title: 'Como decorar sua casa sem gastar muito', excerpt: 'Ideias criativas para decorar sua casa com estilo e sem gastar muito dinheiro.', category: 'Decoração', date: '28 de Maio, 2026', link: '#', image: placeholderImage },
]);

const instagramPosition = ref(0);
const instagramTrackRef = ref(null);
let instagramInterval = null;

onMounted(() => {
  const itemWidth = 272;
  instagramInterval = setInterval(() => {
    instagramPosition.value -= 1;
    if (Math.abs(instagramPosition.value) >= itemWidth * instagramItems.value.length) {
      instagramPosition.value = 0;
    }
  }, 30);
});

onUnmounted(() => {
  if (instagramInterval) clearInterval(instagramInterval);
});
</script>

<style scoped>
.home-field {
  width: 100%;
  border-radius: 0.25rem;
  border: 1px solid #e5e7eb;
  background: #ffffff;
  padding: 0.75rem 1rem;
  color: #000000;
  font-size: 0.875rem;
  outline: none;
  transition: border-color 180ms ease, box-shadow 180ms ease;
}

.home-field:focus,
.home-small-field:focus {
  border-color: #000000;
  box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.08);
}

.home-filter-box {
  border-radius: 0.25rem;
  background: #f9fafb;
  padding: 0.75rem;
}

.home-filter-label {
  color: #6b7280;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.home-stepper {
  display: flex;
  height: 2rem;
  width: 2rem;
  align-items: center;
  justify-content: center;
  border-radius: 0.25rem;
  background: #e5e7eb;
  color: #000000;
  transition: background-color 180ms ease;
}

.home-stepper:hover {
  background: #d1d5db;
}

.home-small-field {
  margin-top: 0.5rem;
  width: 100%;
  border-radius: 0.25rem;
  border: 1px solid #e5e7eb;
  background: #ffffff;
  padding: 0.5rem 0.75rem;
  color: #000000;
  font-size: 0.875rem;
  outline: none;
}

.home-rich-content {
  font-size: 1rem;
  line-height: 1.85;
}

.home-rich-content :deep(* + *) {
  margin-top: 1rem;
}

.home-rich-content :deep(h1),
.home-rich-content :deep(h2),
.home-rich-content :deep(h3) {
  color: #000000;
  font-weight: 700;
  line-height: 1.2;
}

.home-rich-content :deep(a) {
  color: #000000;
  font-weight: 700;
  text-decoration: underline;
}
</style>
