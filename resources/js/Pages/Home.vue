<template>
  <Layout>
    <!-- Hero Section -->
    <section
      class="relative text-white min-h-[100vh] min-h-[100svh] flex items-center"
      :style="{ background: 'linear-gradient(to right, var(--site-primary), var(--site-secondary))' }"
    >
      <div class="absolute inset-0">
        <img :src="homeHeroImage" alt="Imóvel" class="w-full h-full object-cover opacity-20" />
        <div class="absolute inset-0" :style="{ backgroundColor: homeHeroOverlayColor, opacity: homeHeroOverlayOpacity }"></div>
      </div>
      <div class="container mx-auto px-4 relative z-10 w-full py-12">
        <div class="text-center mb-12">
          <h1 class="site-title font-bold mb-4" :style="{ color: homeHeroTitleColor }">{{ homeHeroTitle }}</h1>
          <p v-if="homeHeroSubtitle" class="text-white/80 max-w-3xl mx-auto" :style="{ color: homeHeroSubtitleColor }">{{ homeHeroSubtitle }}</p>
        </div>
        
        <!-- Search Form -->
        <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-5xl mx-auto text-gray-800">
          <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
              <label class="block text-gray-700 text-sm font-semibold mb-1">Negócio</label>
              <select v-model="search.business_type_id" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Todos</option>
                <option v-for="bt in businessTypes" :key="bt.id" :value="bt.id">{{ bt.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-gray-700 text-sm font-semibold mb-1">Tipo</label>
              <select v-model="search.property_type" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Todos</option>
                <option v-for="groupName in propertyTypeGroupNames" :key="groupName" :value="groupName">{{ groupName }}</option>
              </select>
            </div>
            <div>
              <label class="block text-gray-700 text-sm font-semibold mb-1">Valor mín.</label>
              <input v-model="search.price_min" type="text" placeholder="R$ 0,00" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-transparent" @input="onPriceMinInput" />
            </div>
            <div>
              <label class="block text-gray-700 text-sm font-semibold mb-1">Valor máx.</label>
              <input v-model="search.price_max" type="text" placeholder="R$ ilimitado" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-transparent" @input="onPriceMaxInput" />
            </div>
            <div class="flex items-end">
              <button type="button" class="w-full site-button font-bold py-3 px-6 rounded-lg transition flex items-center justify-center gap-2" @click="goSearch">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                Pesquisar
              </button>
            </div>
          </div>

          <button type="button" class="mt-3 flex items-center justify-center gap-2 text-gray-600 hover:text-gray-900 text-sm font-medium transition-colors mx-auto" @click="toggleAdvanced">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
              <path d="M10 18H14V16H10V18ZM3 6V8H21V6H3ZM6 13H18V11H6V13Z"></path>
            </svg>
            Filtros Avançados
            <svg class="w-4 h-4 transition-transform" fill="currentColor" viewBox="0 0 24 24" :class="showAdvanced ? 'rotate-180' : ''">
              <path d="M11.9999 13.1714L16.9497 8.22168L18.3639 9.63589L11.9999 15.9999L5.63599 9.63589L7.0502 8.22168L11.9999 13.1714Z"></path>
            </svg>
          </button>

          <div v-if="showAdvanced" class="mt-4 bg-white/95 backdrop-blur-xl rounded-2xl p-4 sm:p-6 shadow-xl border border-gray-200">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
              <div class="bg-gray-50 rounded-xl p-3">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Quartos</span>
                <div class="flex items-center justify-between mt-2">
                  <button type="button" class="w-8 h-8 rounded-lg bg-gray-200 flex items-center justify-center hover:bg-gray-300 transition-colors" @click="decrement('bedrooms_min')">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M5 11V13H19V11H5Z"></path></svg>
                  </button>
                  <span class="font-semibold text-gray-900">{{ search.bedrooms_min }}+</span>
                  <button type="button" class="w-8 h-8 rounded-lg bg-gray-200 flex items-center justify-center hover:bg-gray-300 transition-colors" @click="increment('bedrooms_min')">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M11 11V5H13V11H19V13H13V19H11V13H5V11H11Z"></path></svg>
                  </button>
                </div>
              </div>
              <div class="bg-gray-50 rounded-xl p-3">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Suítes</span>
                <div class="flex items-center justify-between mt-2">
                  <button type="button" class="w-8 h-8 rounded-lg bg-gray-200 flex items-center justify-center hover:bg-gray-300 transition-colors" @click="decrement('suites_min')">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M5 11V13H19V11H5Z"></path></svg>
                  </button>
                  <span class="font-semibold text-gray-900">{{ search.suites_min }}+</span>
                  <button type="button" class="w-8 h-8 rounded-lg bg-gray-200 flex items-center justify-center hover:bg-gray-300 transition-colors" @click="increment('suites_min')">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M11 11V5H13V11H19V13H13V19H11V13H5V11H11Z"></path></svg>
                  </button>
                </div>
              </div>
              <div class="bg-gray-50 rounded-xl p-3">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Vagas</span>
                <div class="flex items-center justify-between mt-2">
                  <button type="button" class="w-8 h-8 rounded-lg bg-gray-200 flex items-center justify-center hover:bg-gray-300 transition-colors" @click="decrement('garages_min')">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M5 11V13H19V11H5Z"></path></svg>
                  </button>
                  <span class="font-semibold text-gray-900">{{ search.garages_min }}+</span>
                  <button type="button" class="w-8 h-8 rounded-lg bg-gray-200 flex items-center justify-center hover:bg-gray-300 transition-colors" @click="increment('garages_min')">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M11 11V5H13V11H19V13H13V19H11V13H5V11H11Z"></path></svg>
                  </button>
                </div>
              </div>
              <div class="bg-gray-50 rounded-xl p-3">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Área Mín (m²)</span>
                <input v-model="search.area_min" placeholder="0" class="w-full mt-2 px-3 py-2 rounded-lg bg-white border border-gray-200 text-sm focus:ring-2 focus:ring-blue-500 outline-none" type="number" />
              </div>
              <div class="bg-gray-50 rounded-xl p-3">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Área Máx (m²)</span>
                <input v-model="search.area_max" placeholder="Ilimitado" class="w-full mt-2 px-3 py-2 rounded-lg bg-white border border-gray-200 text-sm focus:ring-2 focus:ring-blue-500 outline-none" type="number" />
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mt-4">
              <div class="bg-gray-50 rounded-xl p-3">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Terreno Mín (m²)</span>
                <input v-model="search.lot_area_min" placeholder="0" class="w-full mt-2 px-3 py-2 rounded-lg bg-white border border-gray-200 text-sm focus:ring-2 focus:ring-blue-500 outline-none" type="number" />
              </div>
              <div class="bg-gray-50 rounded-xl p-3">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Terreno Máx (m²)</span>
                <input v-model="search.lot_area_max" placeholder="Ilimitado" class="w-full mt-2 px-3 py-2 rounded-lg bg-white border border-gray-200 text-sm focus:ring-2 focus:ring-blue-500 outline-none" type="number" />
              </div>
            </div>

            <div v-if="specialCategories.length > 0" class="mt-4 pt-4 border-t border-gray-200">
              <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Diferenciais</span>
              <div class="flex flex-wrap gap-2 mt-2 max-h-[200px] overflow-y-auto">
                <button
                  v-for="sc in specialCategories"
                  :key="sc.id"
                  type="button"
                  class="px-3 py-1.5 rounded-full text-xs font-medium transition-all"
                  :class="search.special_category_ids.includes(sc.id) ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
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

    <!-- Categories Carousel -->
    <CategoryCarousel />

    <!-- Seleção Especial -->
    <section class="py-16 bg-white">
      <div class="max-w-[1400px] mx-auto px-4">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Seleção Especial</h2>
        <div class="overflow-x-auto">
          <div class="flex gap-4 pb-2">
            <a v-for="property in selecaoEspecial" :key="property.id" :href="property.url || ('/imoveis/' + property.slug)" class="group flex-shrink-0 w-80">
              <PropertyCard :property="property" />
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- Mais Procurados -->
    <section class="py-16 bg-gray-50">
      <div class="max-w-[1400px] mx-auto px-4">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Mais Procurados</h2>
        <div class="overflow-x-auto">
          <div class="flex gap-4 pb-2">
            <a v-for="property in maisProcurados" :key="property.id" :href="property.url || ('/imoveis/' + property.slug)" class="group flex-shrink-0 w-80">
              <PropertyCard :property="property" />
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- Visto Recentemente -->
    <section class="py-16 bg-white">
      <div class="max-w-[1400px] mx-auto px-4">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Visto Recentemente</h2>
        <div class="overflow-x-auto">
          <div class="flex gap-4 pb-2">
            <a v-for="property in vistoRecentemente" :key="property.id" :href="property.url || ('/imoveis/' + property.slug)" class="group flex-shrink-0 w-80">
              <PropertyCard :property="property" />
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- Instagram Section -->
    <section class="py-16 bg-gray-50">
      <div class="max-w-[1400px] mx-auto px-4">
        <div class="flex items-center justify-between mb-6">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-gradient-to-r from-orange-500 via-pink-500 to-purple-500 rounded-full">
              <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
              </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-800">@{{ instagramHandle }}</h2>
            <span class="px-4 py-1 bg-orange-100 text-orange-700 rounded-full text-sm font-semibold">Instagram</span>
          </div>
          <a :href="instagramProfileUrl || '#'" target="_blank" rel="noopener" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-full font-semibold hover:bg-gray-100 transition">
            Seguir
          </a>
        </div>
        
        <!-- Instagram Carousel -->
        <div class="relative">
          <!-- Fade Overlays -->
          <div class="absolute left-0 top-0 bottom-0 w-20 z-10 pointer-events-none" style="background: linear-gradient(to right, rgba(249, 250, 251, 1), rgba(249, 250, 251, 0));"></div>
          <div class="absolute right-0 top-0 bottom-0 w-20 z-10 pointer-events-none" style="background: linear-gradient(to left, rgba(249, 250, 251, 1), rgba(249, 250, 251, 0));"></div>
          
          <!-- Carousel Track -->
          <div class="overflow-hidden">
            <div
              class="flex gap-4"
              :style="{ transform: `translateX(${instagramPosition}px)` }"
              ref="instagramTrackRef"
            >
              <template v-for="(item, index) in instagramItems" :key="index">
                <a :href="item.permalink || instagramProfileUrl || '#'" target="_blank" rel="noopener" class="flex-shrink-0 w-64 group">
                  <div class="relative rounded-xl overflow-hidden shadow-md">
                    <img :src="item.image" :alt="item.caption" class="w-full h-80 object-cover group-hover:scale-105 transition-transform duration-500">
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                      <div class="text-white text-center">
                        <div class="text-sm font-semibold">Ver no Instagram</div>
                      </div>
                    </div>
                  </div>
                </a>
              </template>
              <!-- Duplicate items for infinite loop -->
              <template v-for="(item, index) in instagramItems" :key="'dupe-'+index">
                <a :href="item.permalink || instagramProfileUrl || '#'" target="_blank" rel="noopener" class="flex-shrink-0 w-64 group">
                  <div class="relative rounded-xl overflow-hidden shadow-md">
                    <img :src="item.image" :alt="item.caption" class="w-full h-80 object-cover group-hover:scale-105 transition-transform duration-500">
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                      <div class="text-white text-center">
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

    <!-- Últimas Notícias -->
    <section class="py-16 bg-white">
      <div class="max-w-[1400px] mx-auto px-4">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Últimas Notícias</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <a v-for="(item, index) in ultimasNoticias" :key="index" :href="item.link" class="group">
            <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-shadow">
              <img :src="item.image" :alt="item.title" class="w-full h-56 object-cover">
              <div class="p-6">
                <span class="text-blue-900 text-sm font-semibold uppercase">{{ item.category }}</span>
                <h3 class="text-xl font-bold text-gray-800 mt-2 mb-2 group-hover:text-blue-900 transition-colors">{{ item.title }}</h3>
                <p class="text-gray-600 line-clamp-2 mb-4">{{ item.excerpt }}</p>
                <div class="flex items-center justify-between text-sm text-gray-500">
                  <span>{{ item.date }}</span>
                  <span class="flex items-center gap-1 text-blue-900 font-semibold">
                    Ler mais
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
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
import CategoryCarousel from '@/Shared/CategoryCarousel.vue';

const props = defineProps({
  homePage: {
    type: Object,
    default: null,
  },
  selecaoEspecial: {
    type: Array,
    default: () => [],
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
  propertyTypeGroups: {
    type: Object,
    default: () => ({}),
  },
  specialCategories: {
    type: Array,
    default: () => [],
  },
});

const placeholderImage = `data:image/svg+xml,${encodeURIComponent(
  `<svg xmlns="http://www.w3.org/2000/svg" width="1920" height="800" viewBox="0 0 1920 800">
    <defs>
      <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
        <stop offset="0" stop-color="#0f172a"/>
        <stop offset="1" stop-color="#1d4ed8"/>
      </linearGradient>
    </defs>
    <rect width="1920" height="800" fill="url(#g)"/>
    <text x="960" y="420" text-anchor="middle" font-family="Arial, sans-serif" font-size="54" fill="rgba(255,255,255,0.35)">Conecta Imóvel</text>
  </svg>`
)}`;

const homeHeroImage = computed(() => props.homePage?.banner_image || placeholderImage);
const homeHeroTitle = computed(() => props.homePage?.banner_title ?? 'Seja bem vindo! Seu novo lar está aqui.');
const homeHeroSubtitle = computed(() => props.homePage?.banner_subtitle ?? '');
const homeHeroTitleColor = computed(() => props.homePage?.banner_title_color ?? '#ffffff');
const homeHeroSubtitleColor = computed(() => props.homePage?.banner_subtitle_color ?? 'rgba(255,255,255,0.85)');
const homeHeroOverlayColor = computed(() => props.homePage?.banner_overlay_color ?? '#0f172a');
const homeHeroOverlayOpacity = computed(() => {
  const raw = Number(props.homePage?.banner_overlay_opacity ?? 70);
  return Math.max(0, Math.min(100, raw)) / 100;
});

const businessTypes = computed(() => props.businessTypes || []);
const propertyTypeGroups = computed(() => props.propertyTypeGroups || {});
const propertyTypeGroupNames = computed(() => Object.keys(propertyTypeGroups.value || {}));
const specialCategories = computed(() => props.specialCategories || []);

const showAdvanced = ref(false);
const search = reactive({
  business_type_id: '',
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

const selecaoEspecial = computed(() => props.selecaoEspecial || []);
const maisProcurados = computed(() => props.maisProcurados || []);
const vistoRecentemente = computed(() => props.vistoRecentemente || []);

const instagramProfileUrl = computed(() => props.instagramUrl || (props.instagramUsername ? `https://instagram.com/${props.instagramUsername}` : ''));
const instagramHandle = computed(() => props.instagramUsername || 'instagram');

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

// Instagram Auto Scroll
const instagramPosition = ref(0);
const instagramTrackRef = ref(null);
let instagramInterval = null;

onMounted(() => {
  const itemWidth = 272; // 256 + 16 gap
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
