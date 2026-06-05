<template>
  <Layout>
    <section class="relative text-white">
      <div class="absolute inset-0">
        <img :src="aboutHeroBg" alt="Quem somos" class="w-full h-full object-cover" />
        <div class="absolute inset-0" :style="{ backgroundColor: 'var(--site-primary)', opacity: 0.7 }"></div>
      </div>
      <div class="relative max-w-[1400px] mx-auto px-4 py-24">
        <div class="max-w-3xl mx-auto text-center">
          <div class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-full bg-white/10 border border-white/20 text-sm tracking-wide">
            <span class="opacity-90">{{ settings.nome_empresa || 'Imobiliária' }}</span>
          </div>
          <h1 class="mt-6 text-4xl md:text-6xl font-semibold leading-tight">
            <span class="block">{{ aboutHeroTitlePrimary }}</span>
            <span class="block" :style="{ color: 'var(--site-secondary)' }">{{ aboutHeroTitleSecondary }}</span>
          </h1>
          <p class="mt-6 text-white/80 max-w-2xl mx-auto">
            {{ aboutHeroSubtitle }}
          </p>
          <div class="mt-8 flex justify-center">
            <a :href="aboutHeroButtonUrl" class="inline-flex items-center gap-2 text-white font-semibold px-6 py-3 rounded-full transition" :style="{ backgroundColor: 'var(--site-secondary)' }">
              {{ aboutHeroButtonLabel }}
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </a>
          </div>
        </div>
      </div>
    </section>

    <section class="text-white" :style="{ backgroundColor: 'var(--site-primary)' }">
      <div class="max-w-[1400px] mx-auto px-4 py-10">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
          <div v-for="(s, idx) in aboutStats" :key="idx">
            <div class="text-3xl md:text-4xl font-bold" :style="{ color: 'var(--site-secondary)' }">{{ s.value }}</div>
            <div class="mt-1 text-xs md:text-sm text-white/75">{{ s.label }}</div>
          </div>
        </div>
      </div>
    </section>

    <section class="bg-[#F5F0EB]">
      <div class="max-w-[1400px] mx-auto px-4 py-16">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
          <div>
            <div class="text-xs tracking-[0.25em] uppercase font-semibold flex items-center gap-4" :style="{ color: 'var(--site-secondary)' }">
              <span class="inline-block w-10 h-[1px]" :style="{ backgroundColor: 'var(--site-secondary)' }"></span>
              <span>{{ aboutEssenceKicker }}</span>
            </div>
            <h2 class="mt-4 text-4xl font-semibold" :style="{ color: 'var(--site-primary)' }">
              <span>{{ aboutEssenceTitlePrimary }}</span>
              <span :style="{ color: 'var(--site-secondary)' }"> {{ aboutEssenceTitleHighlight }}</span>
            </h2>
            <p class="mt-6 text-neutral-600">{{ aboutEssenceText1 }}</p>
            <p class="mt-4 text-neutral-600">{{ aboutEssenceText2 }}</p>

            <div class="mt-8 flex flex-wrap gap-6 text-sm" :style="{ color: 'var(--site-secondary)' }">
              <div v-for="(b, idx) in aboutEssenceBullets" :key="idx" class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: 'var(--site-secondary)' }"></span>
                <span>{{ b }}</span>
              </div>
            </div>
          </div>

          <div class="relative">
            <img :src="aboutEssenceImage" alt="Quem somos" class="w-full rounded-2xl shadow-lg object-cover max-h-[520px]" />
            <div class="absolute -bottom-6 left-6 text-white rounded-xl px-6 py-4 shadow-xl" :style="{ backgroundColor: 'var(--site-primary)' }">
              <div class="text-3xl font-bold">{{ aboutEssenceBadgeValue }}</div>
              <div class="text-xs tracking-[0.25em] text-white/70">{{ aboutEssenceBadgeLabel }}</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="bg-[#F5F0EB]">
      <div class="max-w-[1400px] mx-auto px-4 py-16">
        <div class="text-center max-w-2xl mx-auto">
          <div class="text-xs tracking-[0.25em] uppercase font-semibold" :style="{ color: 'var(--site-secondary)' }">{{ aboutTeamKicker }}</div>
          <h2 class="mt-3 text-4xl font-semibold" :style="{ color: 'var(--site-primary)' }">{{ aboutTeamTitle }}</h2>
          <p class="mt-2 text-neutral-600">{{ aboutTeamSubtitle }}</p>
        </div>

        <div class="mt-12 grid grid-cols-1 sm:grid-cols-2 gap-10 justify-items-center">
          <div v-for="(m, idx) in aboutTeamMembers" :key="idx" class="text-center">
            <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-white shadow-md mx-auto bg-white">
              <img :src="m.photo" :alt="m.name" class="w-full h-full object-cover" />
            </div>
            <div class="mt-4 font-semibold" :style="{ color: 'var(--site-primary)' }">{{ m.name }}</div>
            <div class="text-sm" :style="{ color: 'var(--site-secondary)' }">{{ m.role }}</div>
          </div>
        </div>
      </div>
    </section>

    <section v-if="quoteText" class="bg-white">
      <div class="max-w-[1400px] mx-auto px-4 py-16">
        <div class="max-w-3xl mx-auto text-center">
          <div class="text-3xl leading-relaxed italic text-neutral-700">“{{ quoteText }}”</div>
          <div v-if="quoteAuthor" class="mt-6 text-sm font-semibold" :style="{ color: 'var(--site-primary)' }">
            {{ quoteAuthor }}<span v-if="quoteAuthorRole" class="font-normal text-neutral-600"> — {{ quoteAuthorRole }}</span>
          </div>
        </div>
      </div>
    </section>

    <section class="text-white" :style="{ backgroundColor: 'var(--site-primary)' }">
      <div class="max-w-[1400px] mx-auto px-4 py-16">
        <div class="text-center max-w-2xl mx-auto">
          <div class="text-xs tracking-[0.25em] uppercase font-semibold" :style="{ color: 'var(--site-secondary)' }">Nossos pilares</div>
          <h2 class="mt-3 text-3xl md:text-4xl font-semibold">O que guia nosso trabalho</h2>
        </div>

        <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div v-for="(p, idx) in pillars" :key="idx" class="bg-white/10 border border-white/15 rounded-2xl p-6">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-4 overflow-hidden" :style="{ backgroundColor: 'rgba(255,255,255,0.10)' }">
              <img v-if="p.icon" :src="p.icon" alt="" class="w-7 h-7 object-contain" />
              <svg v-else class="w-6 h-6" :style="{ color: 'var(--site-secondary)' }" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2Zm1 15h-2v-2h2v2Zm0-4h-2V7h2v6Z"></path>
              </svg>
            </div>
            <div class="font-semibold text-lg">{{ p.title }}</div>
            <div class="mt-2 text-sm text-white/75 leading-relaxed">{{ p.description }}</div>
          </div>
        </div>
      </div>
    </section>

    <section class="py-24 md:py-36 bg-white">
      <div class="container mx-auto px-4 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-16 lg:gap-24 items-center">
          <div class="relative">
            <div class="grid grid-cols-12 gap-4">
              <div class="col-span-7 space-y-4">
                <div class="aspect-[3/4] rounded-2xl overflow-hidden shadow-lg relative">
                  <img :src="territoryMainImage" alt="Território" class="w-full h-full object-cover" />
                </div>
              </div>
              <div class="col-span-5 space-y-4 pt-12">
                <div class="aspect-square rounded-2xl overflow-hidden shadow-lg relative">
                  <img :src="territorySquareImage" alt="Território" class="w-full h-full object-cover" />
                </div>
                <div class="aspect-[4/3] rounded-2xl overflow-hidden shadow-lg relative">
                  <img :src="territoryWideImage" alt="Território" class="w-full h-full object-cover" />
                </div>
              </div>
            </div>
            <div class="absolute -bottom-4 -right-4 w-32 h-32 border-2 rounded-2xl -z-10" :style="{ borderColor: 'color-mix(in oklab, var(--site-secondary) 15%, transparent)' }"></div>
          </div>

          <div>
            <div class="inline-flex items-center gap-2 mb-6">
              <div class="h-px w-10" :style="{ backgroundColor: 'var(--site-secondary)' }"></div>
              <span class="text-sm tracking-[0.15em] uppercase font-medium" :style="{ color: 'var(--site-secondary)' }">{{ territoryKicker }}</span>
            </div>

            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold mb-8 leading-tight" :style="{ color: 'var(--site-primary)' }">
              {{ territoryTitle }}
              <span :style="{ color: 'var(--site-secondary)' }">{{ territoryTitleHighlight }}</span>
            </h2>

            <div class="space-y-6 text-base md:text-lg text-neutral-600 leading-relaxed">
              <p v-if="territoryText1">{{ territoryText1 }}</p>
              <p v-if="territoryText2">{{ territoryText2 }}</p>
            </div>

            <div v-if="territoryRegions.length > 0" class="mt-10 p-6 rounded-2xl border" :style="{ backgroundColor: '#F5F0EB', borderColor: 'color-mix(in oklab, var(--site-secondary) 10%, transparent)' }">
              <div class="flex items-center gap-3 mb-3">
                <svg class="w-5 h-5" :style="{ color: 'var(--site-secondary)' }" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 20.8995L16.9497 15.9497C19.6834 13.2161 19.6834 8.78392 16.9497 6.05025C14.2161 3.31658 9.78392 3.31658 7.05025 6.05025C4.31658 8.78392 4.31658 13.2161 7.05025 15.9497L12 20.8995ZM12 23.7279L5.63604 17.364C2.12132 13.8492 2.12132 8.15076 5.63604 4.63604C9.15076 1.12132 14.8492 1.12132 18.364 4.63604C21.8787 8.15076 21.8787 13.8492 18.364 17.364L12 23.7279Z"></path>
                </svg>
                <span class="font-semibold" :style="{ color: 'var(--site-primary)' }">Regiões de atuação</span>
              </div>
              <div class="flex flex-wrap gap-2">
                <span v-for="r in territoryRegions" :key="r" class="px-3 py-1.5 text-sm rounded-lg bg-white text-neutral-600 border border-neutral-200">
                  {{ r }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section v-if="page?.conteudo" class="bg-white">
      <div class="max-w-[1400px] mx-auto px-4 py-16">
        <div class="prose max-w-none" v-html="page?.conteudo || ''"></div>
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

const placeholderImage = `data:image/svg+xml,${encodeURIComponent(
  `<svg xmlns="http://www.w3.org/2000/svg" width="1600" height="900" viewBox="0 0 1600 900">
    <defs>
      <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
        <stop offset="0" stop-color="#0f172a"/>
        <stop offset="1" stop-color="#1e3a8a"/>
      </linearGradient>
    </defs>
    <rect width="1600" height="900" fill="url(#g)"/>
    <text x="800" y="470" text-anchor="middle" font-family="Arial, sans-serif" font-size="48" fill="rgba(255,255,255,0.4)">Quem Somos</text>
  </svg>`
)}`;

const aboutHeroBg = computed(() => data.value.hero_background_image || placeholderImage);
const aboutHeroTitlePrimary = computed(() => data.value.hero_title_primary || 'Conexão patrimonial.');
const aboutHeroTitleSecondary = computed(() => data.value.hero_title_secondary || 'Valor que permanece.');
const aboutHeroSubtitle = computed(
  () => data.value.hero_subtitle || 'Há mais de 12 anos conectando pessoas aos melhores endereços com expertise, confiança e sofisticação.'
);
const aboutHeroButtonLabel = computed(() => data.value.hero_button_label || 'Explorar imóveis');
const aboutHeroButtonUrl = computed(() => data.value.hero_button_url || '/imoveis');

const aboutStats = computed(() => {
  const stats = Array.isArray(data.value.stats) ? data.value.stats : [];
  const normalized = stats
    .filter((x) => x && typeof x === 'object')
    .slice(0, 4)
    .map((x) => ({ value: x.value || '', label: x.label || '' }));

  while (normalized.length < 4) {
    normalized.push({ value: '', label: '' });
  }
  return normalized;
});

const aboutEssenceKicker = computed(() => data.value.essence?.kicker || 'Nossa essência');
const aboutEssenceTitlePrimary = computed(() => data.value.essence?.title_primary || 'Construímos relações,');
const aboutEssenceTitleHighlight = computed(() => data.value.essence?.title_highlight || 'não apenas negócios');
const aboutEssenceText1 = computed(
  () =>
    data.value.essence?.text_1 ||
    'A identidade da imobiliária nasce do conceito de conexão: a relação contínua entre pessoas, imóveis e decisões estratégicas.'
);
const aboutEssenceText2 = computed(
  () => data.value.essence?.text_2 || 'Tratamos cada negociação com atenção e cuidado, respeitando a história de quem escolhe um novo lar.'
);
const aboutEssenceBullets = computed(() => {
  const bullets = Array.isArray(data.value.essence?.bullets) ? data.value.essence.bullets : [];
  return bullets.filter(Boolean).slice(0, 3);
});
const aboutEssenceImage = computed(() => data.value.essence?.image || placeholderImage);
const aboutEssenceBadgeValue = computed(() => data.value.essence?.badge_value || '12');
const aboutEssenceBadgeLabel = computed(() => data.value.essence?.badge_label || 'ANOS');

const aboutTeamKicker = computed(() => data.value.team?.kicker || 'Nosso time');
const aboutTeamTitle = computed(() => data.value.team?.title || 'Quem faz acontecer');
const aboutTeamSubtitle = computed(() => data.value.team?.subtitle || 'Profissionais apaixonados por conectar pessoas aos melhores endereços');
const aboutTeamMembers = computed(() => {
  const members = Array.isArray(data.value.team?.members) ? data.value.team.members : [];
  const normalized = members
    .filter((x) => x && typeof x === 'object')
    .slice(0, 2)
    .map((x) => ({ name: x.name || '', role: x.role || '', photo: x.photo || placeholderImage }));

  while (normalized.length < 2) {
    normalized.push({ name: '', role: '', photo: placeholderImage });
  }

  return normalized.filter((m) => (m.name || '').trim().length > 0);
});

const quoteText = computed(() => (data.value.quote?.text || '').trim());
const quoteAuthor = computed(() => (data.value.quote?.author || '').trim());
const quoteAuthorRole = computed(() => (data.value.quote?.author_role || '').trim());

const pillars = computed(() => {
  const list = Array.isArray(data.value.pillars) ? data.value.pillars : [];
  return list
    .filter((x) => x && typeof x === 'object')
    .slice(0, 4)
    .map((x) => ({ title: x.title || '', description: x.description || '', icon: x.icon || '' }))
    .filter((x) => (x.title || '').trim().length > 0);
});

const territoryKicker = computed(() => data.value.territory?.kicker || 'Nosso território');
const territoryTitle = computed(() => data.value.territory?.title || 'Alphaville é');
const territoryTitleHighlight = computed(() => data.value.territory?.title_highlight || 'nossa casa');
const territoryText1 = computed(() => data.value.territory?.text_1 || '');
const territoryText2 = computed(() => data.value.territory?.text_2 || '');
const territoryRegions = computed(() => {
  const regs = Array.isArray(data.value.territory?.regions) ? data.value.territory.regions : [];
  return regs.map((x) => String(x || '').trim()).filter(Boolean).slice(0, 10);
});
const placeholderTerritoryMain = `data:image/svg+xml,${encodeURIComponent(
  `<svg xmlns="http://www.w3.org/2000/svg" width="900" height="1200" viewBox="0 0 900 1200">
    <defs>
      <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
        <stop offset="0" stop-color="#0f172a"/>
        <stop offset="1" stop-color="#1e293b"/>
      </linearGradient>
    </defs>
    <rect width="900" height="1200" fill="url(#g)"/>
    <text x="450" y="620" text-anchor="middle" font-family="Arial, sans-serif" font-size="40" fill="rgba(255,255,255,0.35)">Imagem vertical</text>
  </svg>`
)}`;
const placeholderTerritorySquare = `data:image/svg+xml,${encodeURIComponent(
  `<svg xmlns="http://www.w3.org/2000/svg" width="900" height="900" viewBox="0 0 900 900">
    <defs>
      <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
        <stop offset="0" stop-color="#111827"/>
        <stop offset="1" stop-color="#334155"/>
      </linearGradient>
    </defs>
    <rect width="900" height="900" fill="url(#g)"/>
    <text x="450" y="470" text-anchor="middle" font-family="Arial, sans-serif" font-size="38" fill="rgba(255,255,255,0.35)">Imagem quadrada</text>
  </svg>`
)}`;
const placeholderTerritoryWide = `data:image/svg+xml,${encodeURIComponent(
  `<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="900" viewBox="0 0 1200 900">
    <defs>
      <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
        <stop offset="0" stop-color="#0b1220"/>
        <stop offset="1" stop-color="#1e3a8a"/>
      </linearGradient>
    </defs>
    <rect width="1200" height="900" fill="url(#g)"/>
    <text x="600" y="470" text-anchor="middle" font-family="Arial, sans-serif" font-size="38" fill="rgba(255,255,255,0.35)">Imagem horizontal</text>
  </svg>`
)}`;

const territoryMainImage = computed(() => data.value.territory?.images?.main || placeholderTerritoryMain);
const territorySquareImage = computed(() => data.value.territory?.images?.square || placeholderTerritorySquare);
const territoryWideImage = computed(() => data.value.territory?.images?.wide || placeholderTerritoryWide);
</script>
