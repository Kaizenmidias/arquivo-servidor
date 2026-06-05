<template>
  <Layout>
    <section class="relative text-white">
      <div class="absolute inset-0">
        <img :src="bannerImage" :alt="page?.titulo || 'Página'" class="w-full h-full object-cover" />
        <div class="absolute inset-0" :style="{ backgroundColor: bannerOverlayColor, opacity: bannerOverlayOpacity }"></div>
      </div>
      <div class="relative container mx-auto px-4 py-16">
        <h1 class="text-4xl font-bold text-center" :style="{ color: bannerTitleColor }">{{ bannerTitle }}</h1>
        <p v-if="bannerSubtitle" class="mt-4 text-center max-w-3xl mx-auto" :style="{ color: bannerSubtitleColor }">{{ bannerSubtitle }}</p>
        <div class="flex justify-center mt-4 text-sm">
          <span><a href="/" class="hover:text-blue-200">Início</a></span>
          <span class="mx-2">/</span>
          <span>{{ page?.titulo || 'Página' }}</span>
        </div>
      </div>
    </section>

    <section class="py-16 bg-white">
      <div class="container mx-auto px-4">
        <div class="prose max-w-none" v-html="page?.conteudo || ''"></div>
      </div>
    </section>
  </Layout>
</template>

<script setup>
import { computed } from 'vue';
import Layout from '@/Shared/Layout.vue';

const props = defineProps({
  page: {
    type: Object,
    default: () => null,
  },
});

const placeholderImage = `data:image/svg+xml,${encodeURIComponent(
  `<svg xmlns="http://www.w3.org/2000/svg" width="1600" height="600" viewBox="0 0 1600 600">
    <defs>
      <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
        <stop offset="0" stop-color="#0f172a"/>
        <stop offset="1" stop-color="#1e3a8a"/>
      </linearGradient>
    </defs>
    <rect width="1600" height="600" fill="url(#g)"/>
    <text x="800" y="330" text-anchor="middle" font-family="Arial, sans-serif" font-size="44" fill="rgba(255,255,255,0.35)">Página</text>
  </svg>`
)}`;

const bannerImage = computed(() => props.page?.banner_image || placeholderImage);
const bannerTitle = computed(() => props.page?.banner_title || props.page?.titulo || 'Página');
const bannerSubtitle = computed(() => props.page?.banner_subtitle || '');
const bannerTitleColor = computed(() => props.page?.banner_title_color || '#ffffff');
const bannerSubtitleColor = computed(() => props.page?.banner_subtitle_color || 'rgba(255,255,255,0.85)');
const bannerOverlayColor = computed(() => props.page?.banner_overlay_color || '#0f172a');
const bannerOverlayOpacity = computed(() => {
  const raw = Number(props.page?.banner_overlay_opacity ?? 70);
  return Math.max(0, Math.min(100, raw)) / 100;
});
</script>
