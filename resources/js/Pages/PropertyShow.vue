<template>
  <Layout>
    <div class="container mx-auto px-4 py-4">
      <a href="/imoveis" class="flex items-center gap-2 text-blue-800 hover:text-blue-600">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Voltar
      </a>
    </div>

    <div class="container mx-auto mb-8 px-4">
      <PropertyGallery :images="photos" :initial-index="0" />
    </div>

    <div class="container mx-auto px-4 pb-12">
      <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <div class="space-y-8 lg:col-span-2">
          <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex flex-wrap items-center gap-3">
              <span class="rounded-full bg-blue-900 px-4 py-1 text-sm font-semibold text-white">{{ property.code }}</span>
              <span v-if="property.isExclusive" class="rounded-full bg-orange-500 px-4 py-1 text-sm font-semibold text-white">EXCLUSIVO</span>
              <span class="rounded-full bg-gray-100 px-4 py-1 text-sm font-semibold text-gray-700">{{ photos.length }} Fotos</span>
              <span v-for="label in businessBadges" :key="label" :class="label === 'ALUGUEL' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-800'" class="rounded-full px-4 py-1 text-sm font-semibold">
                {{ label }}
              </span>
            </div>

            <h1 class="text-3xl font-bold text-gray-900">{{ property.title }}</h1>
            <p class="mt-2 text-gray-600">{{ property.address }}</p>
            <p v-if="property.condominiumName" class="mt-1 text-sm text-gray-500">Condomínio: {{ property.condominiumName }}</p>

            <div v-if="topHighlights.length > 0" class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
              <div v-for="item in topHighlights" :key="item.label" class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-4">
                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">{{ item.label }}</div>
                <div class="mt-2 text-lg font-bold text-gray-900">{{ item.value }}</div>
              </div>
            </div>
          </section>

          <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-xl font-bold text-gray-800">Descrição do Imóvel</h2>
            <div class="space-y-4 leading-relaxed text-gray-700" v-html="property.description"></div>
          </section>

          <section v-for="section in detailSections" :key="section.title" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="mb-5 text-xl font-bold text-gray-800">{{ section.title }}</h2>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
              <div v-for="item in section.items" :key="item.label" class="flex items-start justify-between gap-4 rounded-xl border border-gray-100 px-4 py-3">
                <span class="text-sm font-medium text-gray-500">{{ item.label }}</span>
                <span class="text-right font-semibold text-gray-900">{{ item.value }}</span>
              </div>
            </div>
          </section>
        </div>

        <div class="lg:col-span-1">
          <div class="sticky top-28 space-y-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-lg">
            <div class="flex flex-wrap items-center gap-3">
              <span
                v-for="label in businessBadges"
                :key="label"
                :class="label === 'ALUGUEL' ? 'bg-orange-500' : (label === 'VENDA' ? 'bg-blue-900' : 'bg-gray-700')"
                class="rounded-full px-4 py-1 text-sm font-bold text-white"
              >
                {{ label }}
              </span>
              <span v-if="property.propertyType" class="rounded-full bg-gray-100 px-4 py-1 text-sm font-semibold text-gray-700">{{ property.propertyType }}</span>
            </div>

            <div class="space-y-3">
              <div v-for="row in priceRows" :key="row.key" class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-4">
                <div :class="row.key === 'rent' ? 'text-orange-700' : 'text-blue-900'" class="text-sm font-semibold uppercase tracking-[0.18em]">
                  {{ row.label }}
                </div>
                <div :class="row.key === 'rent' ? 'text-orange-700' : 'text-blue-900'" class="mt-2 text-4xl font-bold">
                  {{ formatCurrencyBRL(row.value, 0) }}<span v-if="row.suffix" class="text-2xl">{{ row.suffix }}</span>
                </div>
              </div>
              <div v-if="priceRows.length === 0" class="rounded-2xl border border-dashed border-gray-200 px-4 py-4 text-gray-400">
                Consulte valores
              </div>
            </div>

            <div v-if="costRows.length > 0" class="space-y-3 border-t border-gray-100 pt-6">
              <div v-for="row in costRows" :key="row.label" class="flex items-center justify-between gap-4">
                <span class="text-gray-500">{{ row.label }}</span>
                <span class="font-medium text-gray-800">{{ row.value }}</span>
              </div>
            </div>

            <div class="border-t border-gray-100 pt-6">
              <form @submit.prevent="submitContact" class="space-y-4">
                <div>
                  <input
                    v-model="contactForm.nome"
                    type="text"
                    placeholder="Seu nome *"
                    class="w-full rounded-full border-0 bg-gray-100 px-4 py-3 focus:ring-2 focus:ring-blue-500"
                    required
                  />
                </div>
                <div class="flex items-center gap-3">
                  <div class="rounded-full bg-gray-100 px-4 py-3 text-gray-600">BR</div>
                  <input
                    v-model="contactForm.telefone"
                    type="tel"
                    placeholder="(00) 00000-0000"
                    class="flex-1 rounded-full border-0 bg-gray-100 px-4 py-3 focus:ring-2 focus:ring-blue-500"
                    required
                  />
                </div>
                <div>
                  <input
                    v-model="contactForm.email"
                    type="email"
                    placeholder="Seu e-mail"
                    class="w-full rounded-full border-0 bg-gray-100 px-4 py-3 focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <textarea
                    v-model="contactForm.mensagem"
                    placeholder="Olá, estou interessado nesse imóvel que encontrei no site."
                    rows="4"
                    class="w-full resize-none rounded-3xl border-0 bg-gray-100 px-4 py-3 focus:ring-2 focus:ring-blue-500"
                  ></textarea>
                </div>
                <button
                  type="submit"
                  class="w-full rounded-full bg-blue-900 px-6 py-4 font-bold text-white transition hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-60"
                  :disabled="contactForm.processing"
                >
                  Enviar mensagem
                </button>
              </form>
            </div>

            <div class="rounded-2xl border border-orange-200 bg-gradient-to-br from-orange-100 to-orange-50 p-6">
              <div class="flex items-start gap-4">
                <div class="rounded-full bg-orange-700/20 p-3">
                  <svg class="w-7 h-7 text-orange-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                </div>
                <div>
                  <h3 class="text-lg font-bold text-gray-800">Agende sua visita</h3>
                  <p class="text-sm text-gray-600">Conheça o imóvel pessoalmente</p>
                </div>
              </div>
              <button class="mt-4 flex w-full items-center justify-center gap-2 rounded-full bg-orange-500 px-6 py-4 font-bold text-white transition hover:bg-orange-600">
                <span>Agendar visita</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import PropertyGallery from '@/Components/PropertyGallery.vue';
import Layout from '@/Shared/Layout.vue';

const props = defineProps({
  property: {
    type: Object,
    required: true,
  },
});

const placeholderImage = `data:image/svg+xml,${encodeURIComponent(
  `<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="800" viewBox="0 0 1200 800">
    <defs>
      <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
        <stop offset="0" stop-color="#0f172a"/>
        <stop offset="1" stop-color="#1e3a8a"/>
      </linearGradient>
    </defs>
    <rect width="1200" height="800" fill="url(#g)"/>
    <rect x="130" y="140" width="940" height="520" rx="28" fill="rgba(255,255,255,0.10)"/>
    <path d="M320 590l260-230 120 115 160-150 320 330H320z" fill="rgba(255,255,255,0.26)"/>
    <circle cx="420" cy="340" r="58" fill="rgba(255,255,255,0.22)"/>
    <text x="600" y="740" text-anchor="middle" font-family="Arial, sans-serif" font-size="28" fill="rgba(255,255,255,0.72)">Imagem indisponível</text>
  </svg>`
)}`;

const photos = computed(() => {
  const list = props.property?.photos ?? [];
  if (Array.isArray(list) && list.length > 0) return list;
  return [{ full: placeholderImage, medium: placeholderImage, thumb: placeholderImage }];
});

const businessBadges = computed(() => {
  const labels = Array.isArray(props.property?.businessLabels) ? props.property.businessLabels : [];

  if (labels.length > 0) {
    return labels.map((label) => {
      if (label === 'Comprar') return 'VENDA';
      if (label === 'Alugar' || label === 'Aluguel') return 'ALUGUEL';
      return String(label || '').toUpperCase();
    });
  }

  if (props.property?.type) {
    return [String(props.property.type).toUpperCase()];
  }

  return [];
});

const priceRows = computed(() => {
  const rows = Array.isArray(props.property?.prices) ? props.property.prices.filter((row) => Number(row?.value || 0) > 0) : [];

  if (rows.length > 0) {
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

const topHighlights = computed(() => {
  const items = [];

  if (Number(props.property?.bedrooms || 0) > 0) {
    items.push({ label: 'Quartos', value: `${formatInteger(props.property.bedrooms)} Quartos` });
  }
  if (Number(props.property?.suites || 0) > 0) {
    items.push({ label: 'Suítes', value: `${formatInteger(props.property.suites)} Suítes` });
  }
  if (Number(props.property?.bathrooms || 0) > 0) {
    items.push({ label: 'Banheiros', value: `${formatInteger(props.property.bathrooms)} Banheiros` });
  }
  if (Number(props.property?.lavabos || 0) > 0) {
    items.push({ label: 'Lavabos', value: `${formatInteger(props.property.lavabos)} Lavabos` });
  }
  if (Number(props.property?.garages || 0) > 0) {
    items.push({ label: 'Vagas', value: `${formatInteger(props.property.garages)} Vagas` });
  }
  if (Number(props.property?.floor || 0) > 0) {
    items.push({ label: 'Andar', value: `${formatInteger(props.property.floor)}º Andar` });
  }
  if (Number(props.property?.areaTotal || 0) > 0) {
    items.push({ label: 'Área Total', value: `${formatArea(props.property.areaTotal)} m²` });
  }
  if (Number(props.property?.areaBuilt || 0) > 0) {
    items.push({ label: 'Área Construída', value: `${formatArea(props.property.areaBuilt)} m²` });
  }

  return items;
});

const costRows = computed(() => {
  const rows = [];

  if (Number(props.property?.valorCondominio || 0) > 0) {
    rows.push({ label: 'Condomínio', value: formatCurrencyBRL(props.property.valorCondominio, 2) });
  }

  if (Number(props.property?.valorIptu || 0) > 0) {
    rows.push({ label: 'IPTU', value: formatCurrencyBRL(props.property.valorIptu, 2) });
  }

  return rows;
});

const detailSections = computed(() => {
  const sections = [];

  const commercial = [];
  if (props.property?.aceitaPermuta) {
    commercial.push({ label: 'Aceita Permuta', value: 'Sim' });
  }
  if (props.property?.aceitaFinanciamento) {
    commercial.push({ label: 'Aceita Financiamento', value: 'Sim' });
  }
  if (commercial.length > 0) {
    sections.push({ title: 'Condições Comerciais', items: commercial });
  }

  const areas = [];
  if (Number(props.property?.areaTotal || 0) > 0) {
    areas.push({ label: 'Área Total', value: `${formatArea(props.property.areaTotal)} m²` });
  }
  if (Number(props.property?.areaBuilt || 0) > 0) {
    areas.push({ label: 'Área Construída', value: `${formatArea(props.property.areaBuilt)} m²` });
  }
  if (areas.length > 0) {
    sections.push({ title: 'Áreas', items: areas });
  }

  const extras = [];
  if (props.property?.mobiliado) {
    extras.push({ label: 'Mobiliado', value: 'Sim' });
  }
  if (props.property?.posicaoSolar) {
    extras.push({ label: 'Posição Solar', value: props.property.posicaoSolar });
  }
  if (props.property?.anoConstrucao) {
    extras.push({ label: 'Ano de Construção', value: String(props.property.anoConstrucao) });
  }
  if (extras.length > 0) {
    sections.push({ title: 'Características Extras', items: extras });
  }

  return sections;
});

const contactForm = useForm({
  property_id: props.property?.id ?? null,
  nome: '',
  telefone: '',
  email: '',
  mensagem: 'Olá, estou interessado nesse imóvel que encontrei no site.',
  origem: 'Site - Interesse no Imóvel',
});

function formatInteger(value) {
  return Number(value || 0).toLocaleString('pt-BR', { maximumFractionDigits: 0 });
}

function formatArea(value) {
  return Number(value || 0).toLocaleString('pt-BR', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
}

function formatCurrencyBRL(value, digits = 2) {
  return Number(value || 0).toLocaleString('pt-BR', {
    style: 'currency',
    currency: 'BRL',
    minimumFractionDigits: digits,
    maximumFractionDigits: digits,
  });
}

function submitContact() {
  contactForm.post('/contato/send', {
    preserveScroll: true,
    onSuccess: () => {
      alert('Mensagem enviada com sucesso! Entraremos em contato em breve.');
      contactForm.reset('nome', 'telefone', 'email');
      contactForm.mensagem = 'Olá, estou interessado nesse imóvel que encontrei no site.';
      contactForm.clearErrors();
    },
  });
}
</script>
