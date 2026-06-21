<template>
  <div class="bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100 hover:shadow-xl transition-shadow">
    <div class="relative">
      <img :src="activePhoto" :alt="property.title" class="w-full h-56 object-cover" loading="lazy" />
      <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>
      <div class="absolute top-3 left-3 flex items-center gap-2">
        <span v-if="property.code" class="bg-black/70 text-white text-xs font-semibold px-2 py-1 rounded">
          {{ property.code }}
        </span>
        <span
          v-for="label in businessBadges"
          :key="label"
          :class="badgeClass(label)"
          class="text-xs font-semibold px-2 py-1 rounded"
        >
          {{ label }}
        </span>
      </div>
      <button type="button" class="absolute top-3 right-3 w-9 h-9 rounded-full bg-white/90 hover:bg-white flex items-center justify-center">
        <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
        </svg>
      </button>

      <button
        v-if="photoList.length > 1"
        type="button"
        class="absolute left-2 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-white/90 hover:bg-white flex items-center justify-center"
        @click.stop.prevent="prevPhoto"
      >
        <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
      </button>
      <button
        v-if="photoList.length > 1"
        type="button"
        class="absolute right-2 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-white/90 hover:bg-white flex items-center justify-center"
        @click.stop.prevent="nextPhoto"
      >
        <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
      </button>

      <div v-if="photoList.length > 1" class="absolute bottom-3 left-0 right-0 flex justify-center gap-2">
        <button
          v-for="(_, idx) in photoList"
          :key="idx"
          type="button"
          class="w-2.5 h-2.5 rounded-full"
          :class="idx === activePhotoIndex ? 'bg-white' : 'bg-white/50'"
          @click.stop.prevent="setPhoto(idx)"
        ></button>
      </div>
    </div>
    <div class="p-4">
      <div class="flex items-center gap-2 text-xs text-gray-500">
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11.5a2.5 2.5 0 10-2.5-2.5 2.5 2.5 0 002.5 2.5zm0 9.5s7-4.35 7-11A7 7 0 105 10c0 6.65 7 11 7 11z"></path>
        </svg>
        <span>{{ property.location || property.address }}</span>
      </div>
      <h3 class="mt-2 text-lg font-semibold text-gray-900 leading-snug">{{ property.title || 'Imóvel' }}</h3>

      <div class="mt-3 flex items-center gap-4 text-xs text-gray-600 flex-wrap">
        <span class="inline-flex items-center gap-1">
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
          </svg>
          <span>{{ property.bedrooms ?? 0 }}</span>
        </span>
        <span class="inline-flex items-center gap-1">
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 10V7a2 2 0 012-2h6a2 2 0 012 2v3m3 0v9a2 2 0 01-2 2H6a2 2 0 01-2-2v-9"></path>
          </svg>
          <span>{{ property.bathrooms ?? 0 }}</span>
        </span>
        <span class="inline-flex items-center gap-1">
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 18h16M6 4v16M18 4v16"></path>
          </svg>
          <span>{{ formatArea(property.area) }} m²</span>
        </span>
        <span v-if="property.lotArea" class="inline-flex items-center gap-1">
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 18h16M6 4v16M18 4v16"></path>
          </svg>
          <span>{{ formatArea(property.lotArea) }} m²</span>
        </span>
      </div>

      <div class="mt-4 space-y-2 border-t border-gray-100 pt-4">
        <div v-for="row in priceRows" :key="row.key" class="flex items-center justify-between gap-3">
          <span :class="row.key === 'rent' ? 'bg-orange-50 text-orange-700' : 'bg-blue-50 text-blue-700'" class="rounded-md px-2 py-1 text-xs font-semibold uppercase tracking-wide">
            {{ row.label }}
          </span>
          <span :class="row.key === 'rent' ? 'text-orange-700' : 'text-blue-900'" class="text-xl font-bold text-right">
            {{ formatCurrencyBRL(row.value) }}<span v-if="row.suffix">{{ row.suffix }}</span>
          </span>
        </div>
        <div v-if="priceRows.length === 0" class="text-sm font-medium text-gray-400">
          Consulte valores
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';

const placeholderImage = `data:image/svg+xml,${encodeURIComponent(
  `<svg xmlns="http://www.w3.org/2000/svg" width="800" height="600" viewBox="0 0 800 600">
    <defs>
      <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
        <stop offset="0" stop-color="#0f172a"/>
        <stop offset="1" stop-color="#1e3a8a"/>
      </linearGradient>
    </defs>
    <rect width="800" height="600" fill="url(#g)"/>
    <rect x="80" y="120" width="640" height="360" rx="24" fill="rgba(255,255,255,0.10)"/>
    <path d="M250 380l150-140 70 65 80-75 150 150H250z" fill="rgba(255,255,255,0.25)"/>
    <circle cx="310" cy="240" r="36" fill="rgba(255,255,255,0.22)"/>
    <text x="400" y="520" text-anchor="middle" font-family="Arial, sans-serif" font-size="22" fill="rgba(255,255,255,0.72)">Imagem indisponível</text>
  </svg>`
)}`;

const props = defineProps({ property: { type: Object, required: true } });

const activePhotoIndex = ref(0);

const photoList = computed(() => {
  const list = props.property?.photos;
  if (Array.isArray(list) && list.length > 0) return list;
  if (props.property?.photo) return [props.property.photo];
  return [placeholderImage];
});

const activePhoto = computed(() => photoList.value[activePhotoIndex.value] || placeholderImage);

const setPhoto = (idx) => {
  if (idx < 0 || idx >= photoList.value.length) return;
  activePhotoIndex.value = idx;
};
const prevPhoto = () => {
  const n = photoList.value.length;
  activePhotoIndex.value = (activePhotoIndex.value - 1 + n) % n;
};
const nextPhoto = () => {
  const n = photoList.value.length;
  activePhotoIndex.value = (activePhotoIndex.value + 1) % n;
};

const businessBadges = computed(() => {
  const labels = Array.isArray(props.property?.businessLabels) ? [...props.property.businessLabels] : [];
  if (!labels.length && props.property?.type) {
    labels.push(props.property.type);
  }

  return labels.map((label) => {
    if (label === 'Comprar') return 'VENDA';
    if (label === 'Alugar') return 'ALUGUEL';
    if (label === 'Aluguel') return 'ALUGUEL';
    return String(label || '').toUpperCase();
  });
});

const priceRows = computed(() => {
  const rows = Array.isArray(props.property?.prices) ? props.property.prices.filter((row) => Number(row?.value || 0) > 0) : [];

  if (rows.length) {
    return rows;
  }

  if (Number(props.property?.price || 0) > 0) {
    return [{
      key: 'default',
      label: String(props.property?.type || 'Valor'),
      value: props.property.price,
      suffix: String(props.property?.type || '').toLowerCase().includes('alugu') ? '/mês' : '',
    }];
  }

  return [];
});

const badgeClass = (label) => {
  if (label === 'ALUGUEL') return 'bg-white text-black';
  if (label === 'VENDA') return 'bg-blue-700 text-white';
  return 'bg-gray-700 text-white';
};

const formatCurrencyBRL = (price) => {
  const value = Number(price || 0);
  return value.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
};

const formatArea = (value) => {
  const n = Number(value || 0);
  if (!n) return '0';
  return n.toLocaleString('pt-BR', { maximumFractionDigits: 2 });
};
</script>
