<template>
  <Layout>
    <section class="relative overflow-hidden bg-black text-white">
      <div class="absolute inset-0">
        <img :src="bannerImage" :alt="page?.titulo || 'Página'" class="w-full h-full object-cover" />
        <div class="absolute inset-0" :style="{ backgroundColor: bannerOverlayColor, opacity: bannerOverlayOpacity }"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_78%_18%,rgba(255,255,255,0.18),transparent_24%),linear-gradient(90deg,#000_0%,rgba(0,0,0,0.86)_48%,rgba(0,0,0,0.38)_100%)]"></div>
      </div>
      <div class="relative mx-auto max-w-[1180px] px-4 py-20 lg:py-28">
        <h1 class="text-5xl font-semibold leading-[0.98] tracking-tight text-white md:text-6xl lg:text-7xl" :style="{ color: bannerTitleColor }">{{ bannerTitle }}</h1>
        <p v-if="bannerSubtitle" class="mt-6 max-w-2xl text-lg leading-8 md:text-xl" :style="{ color: bannerSubtitleColor }">{{ bannerSubtitle }}</p>
        <div class="mt-8 flex text-sm text-white/70">
          <span><a href="/" class="hover:text-white">Início</a></span>
          <span class="mx-2">/</span>
          <span>{{ page?.titulo || 'Página' }}</span>
        </div>
      </div>
    </section>

    <section class="bg-[#f7f5f1] py-16 lg:py-20">
      <div class="mx-auto max-w-[980px] px-4">
        <div class="prose max-w-none border border-black/10 bg-white p-6 text-black shadow-[0_24px_70px_rgba(0,0,0,0.08)] md:p-10" v-html="page?.conteudo || ''"></div>
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
        <stop offset="0" stop-color="#000000"/>
        <stop offset="1" stop-color="#57534e"/>
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
const bannerOverlayColor = computed(() => props.page?.banner_overlay_color || '#000000');
const bannerOverlayOpacity = computed(() => {
  const raw = Number(props.page?.banner_overlay_opacity ?? 70);
  return Math.max(0, Math.min(100, raw)) / 100;
});
</script>
