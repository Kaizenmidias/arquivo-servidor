<template>
  <Layout>
    <section class="relative flex min-h-[100svh] items-center overflow-hidden pt-[120px] pb-[120px] md:pt-[140px] md:pb-[140px] lg:pt-[150px] lg:pb-[150px]">
      <div class="absolute inset-0">
        <img :src="heroImage" alt="Sobre nós" class="h-full w-full object-cover object-right" />
        <div class="absolute inset-0 bg-slate-950/70"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-slate-950/90 via-slate-950/65 to-transparent"></div>
      </div>

      <div class="relative mx-auto w-full max-w-[1400px] px-4">
        <div class="grid items-center lg:grid-cols-[1.05fr_0.95fr]">
          <div class="max-w-2xl text-white">
            <div class="text-xs font-semibold uppercase tracking-[0.24em] text-white/70">
              {{ heroSubtitle }}
            </div>
            <h1 class="mt-6 text-4xl font-semibold leading-tight md:text-5xl lg:text-6xl">
              {{ heroTitle }}
            </h1>
            <p class="mt-6 max-w-xl text-base leading-8 text-white/80 md:text-lg">
              {{ heroText }}
            </p>
            <div class="mt-8">
              <a :href="heroButtonUrl" class="inline-flex items-center gap-2 rounded-full px-6 py-3.5 text-sm font-semibold text-white shadow-[0_18px_40px_rgba(15,23,42,0.18)] transition hover:opacity-90" :style="{ backgroundColor: 'var(--site-secondary)' }">
                {{ heroButtonLabel }}
                <span aria-hidden="true">→</span>
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="bg-white">
      <div class="mx-auto max-w-[1120px] px-4 py-20 lg:py-24">
        <div class="grid items-center gap-10 lg:grid-cols-[0.95fr_1.05fr]">
          <div class="max-w-xl">
            <div class="text-xs font-semibold uppercase tracking-[0.24em] text-black/60">{{ historySubtitle }}</div>
            <h2 class="mt-4 text-3xl font-semibold leading-tight text-slate-900 md:text-4xl">
              {{ historyTitle }}
            </h2>
            <div class="mt-6 space-y-4 text-base leading-8 text-slate-600 md:text-[17px]">
              <p v-for="(paragraph, index) in historyParagraphs" :key="index">
                {{ paragraph }}
              </p>
            </div>
          </div>
          <div>
            <img :src="historyImage" alt="Nossa história" class="w-full rounded-[2rem] object-cover shadow-[0_24px_70px_rgba(15,23,42,0.12)]" />
          </div>
        </div>
      </div>
    </section>

    <section class="bg-white">
      <div class="mx-auto max-w-[1120px] px-4 py-10 lg:py-14">
        <div class="rounded-[25px] bg-[linear-gradient(90deg,#000000_0%,#171717_100%)] px-6 py-8 text-white shadow-[0_26px_70px_rgba(0,0,0,0.18)] lg:px-10 lg:py-10">
              <div class="grid gap-0 sm:grid-cols-2 xl:grid-cols-4">
            <article v-for="(item, index) in numbers" :key="index" class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm">
              <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10 text-2xl text-white overflow-hidden">
                <img v-if="isImageIcon(item.icon_image || item.icon)" :src="item.icon_image || item.icon" alt="" class="h-6 w-6 object-contain" />
                <span v-else-if="item.icon">{{ item.icon }}</span>
                <svg v-else class="h-6 w-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M12 6v6l4 2" />
                </svg>
              </div>
              <div class="mt-5 text-3xl font-semibold">{{ item.number }}</div>
              <div class="mt-2 text-sm font-medium text-white/70">{{ item.title }}</div>
            </article>
          </div>
        </div>
      </div>
    </section>

    <section class="bg-white">
      <div class="mx-auto max-w-[1120px] px-4 py-20 lg:py-24">
        <div class="grid items-center gap-12 lg:grid-cols-[0.95fr_1.05fr]">
          <div class="order-2 lg:order-1 relative">
            <img :src="specialistImage" alt="Especialista" class="w-full rounded-[2rem] object-cover shadow-[0_24px_70px_rgba(15,23,42,0.14)]" />
            <div class="absolute left-[25px] bottom-[25px] max-w-[320px] rounded-[1.5rem] border border-black/10 bg-white p-5 shadow-[0_18px_50px_rgba(0,0,0,0.10)]">
              <div class="text-lg font-semibold text-slate-900">{{ specialistName }}</div>
              <div class="mt-1 text-sm font-medium text-black/60">{{ specialistRole }}</div>
              <p class="mt-3 text-sm leading-7 text-slate-600">{{ specialistCardText }}</p>
            </div>
          </div>

          <div class="order-1 lg:order-2">
            <div class="text-xs font-semibold uppercase tracking-[0.24em] text-black/60">{{ specialistSubtitle }}</div>
            <h2 class="mt-4 text-3xl font-semibold leading-tight text-slate-900 md:text-4xl">
              {{ specialistTitle }}
            </h2>
            <p class="mt-6 max-w-xl text-base leading-8 text-slate-600 md:text-[17px]">
              {{ specialistText }}
            </p>
            <div class="mt-8">
              <a :href="specialistButtonUrl" class="inline-flex items-center gap-2 rounded-full px-6 py-3.5 text-sm font-semibold text-white shadow-[0_18px_40px_rgba(15,23,42,0.18)] transition hover:opacity-90" :style="{ backgroundColor: 'var(--site-primary)' }">
                {{ specialistButtonLabel }}
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="bg-[#f7f5f1]">
      <div class="mx-auto max-w-[1200px] px-4 py-20 lg:py-24">
        <div class="grid items-start gap-10 lg:grid-cols-[0.85fr_1.15fr]">
          <div class="max-w-xl lg:pr-5">
            <div class="text-xs font-semibold uppercase tracking-[0.24em] text-black/60">{{ valuesSubtitle }}</div>
            <h2 class="mt-4 text-4xl font-semibold leading-tight text-slate-900 md:text-[54px]">
              {{ valuesTitle }}
            </h2>
          </div>

          <div class="grid gap-[15px] sm:grid-cols-2">
            <article v-for="(item, index) in values" :key="index" class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-[0_18px_50px_rgba(15,23,42,0.06)]">
              <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-black/5 text-black overflow-hidden">
                <img v-if="isImageIcon(item.icon_image || item.icon)" :src="item.icon_image || item.icon" alt="" class="h-6 w-6 object-contain" />
                <span v-else-if="item.icon">{{ item.icon }}</span>
                <svg v-else class="h-6 w-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M12 2l3 7h7l-5.5 4.1L18 20l-6-4-6 4 1.5-6.9L2 9h7z" />
                </svg>
              </div>
              <h3 class="mt-5 text-lg font-semibold text-slate-900">{{ item.title }}</h3>
              <p class="mt-3 text-sm leading-7 text-slate-600">{{ item.text }}</p>
            </article>
          </div>
        </div>
      </div>
    </section>

    <section class="relative overflow-hidden">
      <div class="absolute inset-0">
        <img :src="ctaImage" alt="CTA" class="h-full w-full object-cover" />
        <div class="absolute inset-0" :style="{ backgroundColor: ctaOverlayColor, opacity: ctaOverlayOpacity }"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-slate-950/90 via-slate-950/50 to-transparent"></div>
      </div>
      <div class="relative mx-auto max-w-[1400px] px-4 pt-20 pb-[6rem] lg:pt-24 lg:pb-[6rem]">
        <div class="grid items-center gap-8 lg:grid-cols-[1.2fr_0.8fr]">
          <div class="max-w-2xl text-white">
            <h2 class="text-4xl font-semibold leading-tight md:text-5xl">
              {{ ctaTitle }}
            </h2>
            <p class="mt-5 max-w-xl text-base leading-8 text-white/78 md:text-lg">
              {{ ctaText }}
            </p>
          </div>
          <div class="flex flex-col gap-3 sm:flex-row lg:justify-end">
            <a :href="ctaButton1Url" class="inline-flex items-center gap-2 rounded-full px-6 py-3.5 text-sm font-semibold text-slate-900 transition hover:opacity-95" :style="{ backgroundColor: 'var(--site-secondary)' }">
              {{ ctaButton1Label }}
              <span aria-hidden="true">→</span>
            </a>
            <a :href="ctaButton2Url" class="inline-flex items-center gap-2 rounded-full border border-white/20 px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-white/10">
              <svg class="h-4 w-4" viewBox="0 0 32 32" fill="currentColor" aria-hidden="true">
                <path d="M19.11 17.4c-.27-.13-1.6-.79-1.85-.89-.25-.09-.43-.13-.61.13-.18.27-.7.89-.86 1.07-.16.18-.31.2-.58.07-.27-.13-1.14-.42-2.17-1.33-.8-.71-1.34-1.58-1.5-1.85-.16-.27-.02-.42.12-.55.12-.12.27-.31.4-.47.13-.16.18-.27.27-.45.09-.18.04-.34-.02-.47-.07-.13-.61-1.47-.84-2.01-.22-.53-.45-.46-.61-.46h-.52c-.18 0-.47.07-.72.34-.25.27-.97.95-.97 2.3 0 1.35.99 2.65 1.13 2.83.13.18 1.95 2.98 4.73 4.18.66.29 1.17.46 1.57.59.66.21 1.25.18 1.72.11.53-.08 1.6-.66 1.82-1.3.22-.64.22-1.19.16-1.3-.07-.11-.25-.18-.52-.31z" />
                <path d="M16.04 3C9.39 3 4 8.35 4 14.95c0 2.1.55 4.14 1.6 5.94L4 29l8.27-1.53c1.75.95 3.73 1.45 5.76 1.45h.01C24.68 28.92 30 23.6 30 16.96 30 10.35 23.75 3 16.04 3zm.01 23.65h-.01c-1.83 0-3.62-.49-5.17-1.42l-.37-.22-4.91.91.93-4.79-.24-.39a9.63 9.63 0 0 1-1.49-5.09c0-5.35 4.38-9.7 9.76-9.7 5.18 0 9.73 4.48 9.73 9.7 0 5.35-4.38 9.7-9.73 9.7z" />
              </svg>
              {{ ctaButton2Label }}
            </a>
          </div>
        </div>
      </div>
    </section>
  </Layout>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Layout from '@/Shared/Layout.vue';

const props = defineProps({
  page: {
    type: Object,
    default: () => null,
  },
});

const inertiaPage = usePage();
const data = computed(() => props.page?.data || {});

const fallbackImage = (label, width = 1600, height = 900) => `data:image/svg+xml,${encodeURIComponent(
  `<svg xmlns="http://www.w3.org/2000/svg" width="${width}" height="${height}" viewBox="0 0 ${width} ${height}">
    <defs>
      <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
        <stop offset="0" stop-color="#000000"/>
        <stop offset="1" stop-color="#57534e"/>
      </linearGradient>
    </defs>
    <rect width="${width}" height="${height}" fill="url(#g)"/>
    <text x="50%" y="50%" text-anchor="middle" dominant-baseline="middle" font-family="Arial, sans-serif" font-size="44" fill="rgba(255,255,255,0.35)">${label}</text>
  </svg>`
)}`;

const normalizeList = (value) => Array.isArray(value) ? value : [];
const legacyText = (...values) => values.map((value) => String(value || '').trim()).filter(Boolean).join(' ');
const isImageIcon = (value) => {
  const icon = String(value || '');
  return /^https?:\/\//.test(icon) || icon.startsWith('data:image') || icon.startsWith('/');
};

const heroImage = computed(() => data.value.hero?.image || data.value.hero_image || data.value.hero_background_image || fallbackImage('Sobre Nós'));
const heroSubtitle = computed(() => data.value.hero?.subtitle || data.value.hero_subtitle || 'Sobre Nós');
const heroTitle = computed(() => data.value.hero?.title || data.value.hero_title || legacyText(data.value.hero_title_primary, data.value.hero_title_secondary) || 'Conexão que transforma imóveis em histórias.');
const heroText = computed(() => data.value.hero?.text || data.value.hero_text || data.value.hero_subtitle || 'Atendimento personalizado, curadoria refinada e visão estratégica para decisões imobiliárias com segurança.');
const heroButtonLabel = computed(() => data.value.hero?.button_label || data.value.hero_button_label || 'Ver imóveis');
const heroButtonUrl = computed(() => data.value.hero?.button_url || data.value.hero_button_url || '/imoveis');

const historyImage = computed(() => data.value.history?.image || data.value.history_image || data.value.essence?.image || fallbackImage('Nossa História'));
const historySubtitle = computed(() => data.value.history?.subtitle || data.value.history_subtitle || 'Nossa História');
const historyTitle = computed(() => data.value.history?.title || data.value.history_title || data.value.essence?.title_primary || 'Mais que imóveis, construímos relações.');
const historyParagraphs = computed(() => {
  const text = normalizeList(data.value.history?.text ? [data.value.history.text] : data.value.history_text || []);
  const fallback = normalizeList([data.value.essence?.text_1, data.value.essence?.text_2]);
  return (text.length ? text : fallback).slice(0, 2).length ? (text.length ? text : fallback).slice(0, 2) : [
    'A Meteorikah atua com foco em sofisticação, confiança e atendimento sob medida.',
  ];
});

const numbers = computed(() => {
  const list = normalizeList(data.value.numbers || data.value.stats);
  const normalized = list.map((item) => ({
    number: item?.number || item?.value || '',
    title: item?.title || item?.label || '',
    icon: item?.icon || '',
  }));
  return normalized.filter((item) => item.number || item.title).slice(0, 4);
});

const specialistImage = computed(() => data.value.specialist?.image || data.value.specialist_image || data.value.team?.members?.[0]?.photo || fallbackImage('Especialista'));
const specialistSubtitle = computed(() => data.value.specialist?.subtitle || 'Nosso Especialista');
const specialistTitle = computed(() => data.value.specialist?.title || 'Consultoria dedicada a cada decisão.');
const specialistText = computed(() => data.value.specialist?.text || 'Um atendimento direto, humano e altamente personalizado para orientar cada etapa com clareza.');
const specialistName = computed(() => data.value.specialist?.name || data.value.team?.members?.[0]?.name || 'Consultor Meteorikah');
const specialistRole = computed(() => data.value.specialist?.role || data.value.team?.members?.[0]?.role || 'Especialista Imobiliário');
const specialistCardText = computed(() => data.value.specialist?.card_description || 'Curadoria refinada, leitura de mercado e suporte próximo em imóveis de alto padrão.');
const specialistButtonLabel = computed(() => data.value.specialist?.button_label || 'Fale com o especialista');
const specialistButtonUrl = computed(() => data.value.specialist?.button_url || '/contato');

const valuesSubtitle = computed(() => data.value.values?.subtitle || data.value.pillars?.subtitle || 'Nossos Valores');
const valuesTitle = computed(() => data.value.values?.title || data.value.pillars?.title || 'O que nos guia todos os dias.');

const values = computed(() => {
  const list = normalizeList(data.value.values || data.value.pillars);
  return list.map((item) => ({
    icon: item?.icon || '',
    icon_image: item?.icon_image || '',
    title: item?.title || '',
    text: item?.text || item?.description || '',
  })).filter((item) => item.title || item.text).slice(0, 4);
});

const ctaImage = computed(() => data.value.cta?.background_image || data.value.cta_background_image || data.value.hero?.image || heroImage.value);
const ctaOverlayColor = computed(() => data.value.cta?.overlay_color || '#000000');
const ctaOverlayOpacity = computed(() => {
  const opacity = Number(data.value.cta?.overlay_opacity ?? 78);
  return Number.isFinite(opacity) ? Math.min(Math.max(opacity, 0), 100) / 100 : 0.78;
});
const ctaTitle = computed(() => data.value.cta?.title || 'Vamos encontrar o imóvel ideal para você?');
const ctaText = computed(() => data.value.cta?.text || 'Nossa equipe está pronta para apresentar oportunidades exclusivas e conduzir sua busca com precisão.');
const ctaButton1Label = computed(() => data.value.cta?.button_1_label || 'Ver imóveis disponíveis');
const ctaButton1Url = computed(() => data.value.cta?.button_1_url || '/imoveis');
const ctaButton2Label = computed(() => data.value.cta?.button_2_label || 'Falar no WhatsApp');
const ctaButton2Url = computed(() => data.value.cta?.button_2_url || '/contato');
</script>
