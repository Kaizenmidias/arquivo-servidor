<template>
  <Layout>
    <section class="relative overflow-hidden">
      <div class="absolute inset-0">
        <img :src="heroImage" alt="Sobre nós" class="h-full w-full object-cover" />
        <div class="absolute inset-0 bg-slate-950/60"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-slate-950/90 via-slate-950/70 to-transparent"></div>
      </div>

      <div class="relative mx-auto max-w-[1400px] px-4 py-20 lg:py-28">
        <div class="grid items-center gap-12 lg:grid-cols-2">
          <div class="max-w-2xl text-white">
            <div class="inline-flex items-center rounded-full border border-white/15 bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-white/80">
              {{ heroSubtitle }}
            </div>
            <h1 class="mt-6 text-4xl font-semibold leading-tight md:text-5xl lg:text-6xl">
              {{ heroTitle }}
            </h1>
            <p class="mt-6 max-w-xl text-base leading-8 text-white/78 md:text-lg">
              {{ heroText }}
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
              <a :href="heroButtonUrl" class="inline-flex items-center gap-2 rounded-full px-6 py-3.5 text-sm font-semibold text-white shadow-[0_18px_40px_rgba(15,23,42,0.18)] transition hover:opacity-90" :style="{ backgroundColor: 'var(--site-secondary)' }">
                {{ heroButtonLabel }}
                <span aria-hidden="true">→</span>
              </a>
            </div>
          </div>

          <div class="relative">
            <img :src="heroImage" alt="Sobre nós" class="min-h-[360px] w-full rounded-[2rem] object-cover shadow-[0_28px_80px_rgba(15,23,42,0.35)] lg:min-h-[560px]" />
          </div>
        </div>
      </div>
    </section>

    <section class="bg-white">
      <div class="mx-auto max-w-[1400px] px-4 py-20 lg:py-24">
        <div class="grid items-center gap-12 lg:grid-cols-2">
          <div>
            <div class="text-xs font-semibold uppercase tracking-[0.24em]" :style="{ color: 'var(--site-secondary)' }">{{ historySubtitle }}</div>
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

    <section class="bg-[#f7f4ef]">
      <div class="mx-auto max-w-[1400px] px-4 py-20">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
          <article v-for="(item, index) in numbers" :key="index" class="rounded-[1.5rem] border border-white/70 bg-white p-6 shadow-[0_18px_50px_rgba(15,23,42,0.06)]">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-900/5 text-2xl" :style="{ color: 'var(--site-secondary)' }">
              <span v-if="item.icon">{{ item.icon }}</span>
              <svg v-else class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M12 6v6l4 2" />
              </svg>
            </div>
            <div class="mt-5 text-3xl font-semibold text-slate-900">{{ item.number }}</div>
            <div class="mt-2 text-sm font-medium text-slate-500">{{ item.title }}</div>
          </article>
        </div>
      </div>
    </section>

    <section class="bg-white">
      <div class="mx-auto max-w-[1400px] px-4 py-20 lg:py-24">
        <div class="grid items-center gap-12 lg:grid-cols-2">
          <div class="order-2 lg:order-1 relative">
            <img :src="specialistImage" alt="Especialista" class="w-full rounded-[2rem] object-cover shadow-[0_24px_70px_rgba(15,23,42,0.14)]" />
            <div class="absolute -bottom-6 left-6 max-w-[320px] rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-[0_18px_50px_rgba(15,23,42,0.10)]">
              <div class="text-lg font-semibold text-slate-900">{{ specialistName }}</div>
              <div class="mt-1 text-sm font-medium" :style="{ color: 'var(--site-secondary)' }">{{ specialistRole }}</div>
              <p class="mt-3 text-sm leading-7 text-slate-600">{{ specialistCardText }}</p>
            </div>
          </div>

          <div class="order-1 lg:order-2">
            <div class="text-xs font-semibold uppercase tracking-[0.24em]" :style="{ color: 'var(--site-secondary)' }">{{ specialistSubtitle }}</div>
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

    <section class="bg-[#f7f4ef]">
      <div class="mx-auto max-w-[1400px] px-4 py-20 lg:py-24">
        <div class="grid items-center gap-12 lg:grid-cols-2">
          <div>
            <div class="text-xs font-semibold uppercase tracking-[0.24em]" :style="{ color: 'var(--site-secondary)' }">{{ regionSubtitle }}</div>
            <h2 class="mt-4 text-3xl font-semibold leading-tight text-slate-900 md:text-4xl">
              {{ regionTitle }}
            </h2>
            <div class="mt-6 space-y-4 text-base leading-8 text-slate-600 md:text-[17px]">
              <p v-for="(paragraph, index) in regionParagraphs" :key="index">
                {{ paragraph }}
              </p>
            </div>
            <div v-if="regions.length" class="mt-8 flex flex-wrap gap-3">
              <span v-for="region in regions" :key="region" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700">
                {{ region }}
              </span>
            </div>
          </div>
          <div>
            <img :src="regionImage" alt="Nossa região" class="w-full rounded-[2rem] object-cover shadow-[0_24px_70px_rgba(15,23,42,0.12)]" />
          </div>
        </div>
      </div>
    </section>

    <section class="bg-white">
      <div class="mx-auto max-w-[1400px] px-4 py-20 lg:py-24">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
          <article v-for="(item, index) in values" :key="index" class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-[0_18px_50px_rgba(15,23,42,0.06)]">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-900/5 text-slate-900" :style="{ color: 'var(--site-secondary)' }">
              <span v-if="item.icon">{{ item.icon }}</span>
              <svg v-else class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M12 2l3 7h7l-5.5 4.1L18 20l-6-4-6 4 1.5-6.9L2 9h7z" />
              </svg>
            </div>
            <h3 class="mt-5 text-lg font-semibold text-slate-900">{{ item.title }}</h3>
            <p class="mt-3 text-sm leading-7 text-slate-600">{{ item.text }}</p>
          </article>
        </div>
      </div>
    </section>

    <section class="relative overflow-hidden">
      <div class="absolute inset-0">
        <img :src="ctaImage" alt="CTA" class="h-full w-full object-cover" />
        <div class="absolute inset-0 bg-slate-950/78"></div>
      </div>
      <div class="relative mx-auto max-w-[1400px] px-4 py-20 lg:py-24">
        <div class="grid items-center gap-8 lg:grid-cols-[1.2fr_0.8fr]">
          <div class="max-w-2xl text-white">
            <h2 class="text-3xl font-semibold leading-tight md:text-4xl">
              {{ ctaTitle }}
            </h2>
            <p class="mt-5 max-w-xl text-base leading-8 text-white/78 md:text-lg">
              {{ ctaText }}
            </p>
          </div>
          <div class="flex flex-col gap-3 sm:flex-row lg:justify-end">
            <a :href="ctaButton1Url" class="inline-flex items-center justify-center rounded-full px-6 py-3.5 text-sm font-semibold text-slate-900 transition hover:opacity-95" :style="{ backgroundColor: 'var(--site-secondary)' }">
              {{ ctaButton1Label }}
            </a>
            <a :href="ctaButton2Url" class="inline-flex items-center justify-center rounded-full border border-white/20 px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-white/10">
              {{ ctaButton2Label }}
            </a>
            <a href="/admin/pages/2" class="inline-flex items-center justify-center rounded-full border border-white/20 px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-white/10">
              Dashboard
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
const settings = computed(() => inertiaPage.props.settings || {});
const data = computed(() => props.page?.data || {});

const fallbackImage = (label, width = 1600, height = 900) => `data:image/svg+xml,${encodeURIComponent(
  `<svg xmlns="http://www.w3.org/2000/svg" width="${width}" height="${height}" viewBox="0 0 ${width} ${height}">
    <defs>
      <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
        <stop offset="0" stop-color="#0f172a"/>
        <stop offset="1" stop-color="#1e3a8a"/>
      </linearGradient>
    </defs>
    <rect width="${width}" height="${height}" fill="url(#g)"/>
    <text x="50%" y="50%" text-anchor="middle" dominant-baseline="middle" font-family="Arial, sans-serif" font-size="44" fill="rgba(255,255,255,0.35)">${label}</text>
  </svg>`
)}`;

const normalizeList = (value) => Array.isArray(value) ? value : [];
const legacyText = (...values) => values.map((value) => String(value || '').trim()).filter(Boolean).join(' ');

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
  const fallback = normalizeList([
    data.value.essence?.text_1,
    data.value.essence?.text_2,
  ]);
  return text.length ? text : (fallback.length ? fallback : ['A Meteorikah atua com foco em sofisticação, confiança e atendimento sob medida.']);
});

const numbers = computed(() => {
  const list = normalizeList(data.value.numbers || data.value.stats);
  const normalized = list.map((item) => ({
    number: item?.number || item?.value || '',
    title: item?.title || item?.label || '',
    icon: item?.icon || '',
  }));
  return normalized.filter((item) => item.number || item.title);
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

const regionImage = computed(() => data.value.region?.image || data.value.territory?.images?.wide || data.value.territory_image || fallbackImage('Nossa Região'));
const regionSubtitle = computed(() => data.value.region?.subtitle || 'Nossa Região');
const regionTitle = computed(() => data.value.region?.title || 'Atuação estratégica em regiões nobres.');
const regionParagraphs = computed(() => {
  const list = normalizeList(data.value.region?.text ? [data.value.region.text] : data.value.region_text || []);
  const fallback = normalizeList([
    data.value.territory?.text_1,
    data.value.territory?.text_2,
  ]);
  return list.length ? list : (fallback.length ? fallback : ['Atendemos com atenção especial às áreas mais valorizadas e estratégicas da região.']);
});
const regions = computed(() => normalizeList(data.value.region?.regions || data.value.territory?.regions).map((item) => String(item || '').trim()).filter(Boolean));

const values = computed(() => {
  const list = normalizeList(data.value.values || data.value.pillars);
  return list.map((item) => ({
    icon: item?.icon || '',
    title: item?.title || '',
    text: item?.text || item?.description || '',
  })).filter((item) => item.title || item.text);
});

const ctaImage = computed(() => data.value.cta?.background_image || data.value.cta_background_image || data.value.hero?.image || heroImage.value);
const ctaTitle = computed(() => data.value.cta?.title || 'Vamos encontrar o imóvel ideal para você?');
const ctaText = computed(() => data.value.cta?.text || 'Nossa equipe está pronta para apresentar oportunidades exclusivas e conduzir sua busca com precisão.');
const ctaButton1Label = computed(() => data.value.cta?.button_1_label || 'Ver imóveis');
const ctaButton1Url = computed(() => data.value.cta?.button_1_url || '/imoveis');
const ctaButton2Label = computed(() => data.value.cta?.button_2_label || 'WhatsApp');
const ctaButton2Url = computed(() => data.value.cta?.button_2_url || '/contato');
</script>
