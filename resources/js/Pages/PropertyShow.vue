<template>
  <Layout>
    <!-- Back Button -->
    <div class="container mx-auto px-4 py-4">
      <a href="/imoveis" class="text-blue-800 hover:text-blue-600 flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Voltar
      </a>
    </div>

    <!-- Photo Gallery -->
    <div class="container mx-auto px-4 mb-8">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Main Photo -->
        <div class="md:col-span-2 relative rounded-xl overflow-hidden shadow-lg">
          <img
            :src="photos[activePhotoIndex]"
            :alt="property.title"
            class="w-full h-96 object-cover"
          />
        </div>
        <!-- Thumbnails -->
        <div class="grid grid-cols-2 gap-4">
          <div v-for="(photo, index) in photos.slice(0, 4)" :key="index" class="relative rounded-xl overflow-hidden shadow-md cursor-pointer hover:opacity-80 transition">
            <img :src="photo" :alt="`Foto ${index + 1}`" class="w-full h-44 object-cover" @click="activePhotoIndex = index" />
            <div v-if="index === 3 && photos.length > 4" class="absolute inset-0 bg-black/70 flex items-center justify-center text-white text-2xl font-bold">
              +{{ photos.length - 4 }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Property Content -->
    <div class="container mx-auto px-4 pb-12">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column -->
        <div class="lg:col-span-2">
          <!-- Title & Quick Info -->
          <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div>
              <div class="flex items-center gap-3 mb-2">
                <span class="bg-blue-900 text-white px-4 py-1 rounded-full text-sm font-semibold">{{ property.code }}</span>
                <span class="bg-orange-500 text-white px-4 py-1 rounded-full text-sm font-semibold">EXCLUSIVO</span>
                <span class="bg-gray-200 text-gray-700 px-4 py-1 rounded-full text-sm font-semibold">{{ photos.length }} Fotos</span>
              </div>
              <h1 class="text-2xl font-bold text-gray-800 mb-1">{{ property.title }}</h1>
              <p class="text-gray-600">{{ property.address }}</p>
            </div>
            <div class="flex items-center gap-3">
              <button class="p-2 border border-gray-300 rounded-full hover:bg-gray-50">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
              </button>
              <button class="p-2 border border-gray-300 rounded-full hover:bg-gray-50 flex items-center gap-2 px-4">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                </svg>
                Compartilhar
              </button>
            </div>
          </div>

          <!-- Features -->
          <div class="flex flex-wrap gap-4 mb-6">
            <div class="flex items-center gap-2 text-gray-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
              </svg>
              {{ property.bedrooms }} quartos (1 suíte)
            </div>
            <div class="flex items-center gap-2 text-gray-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
              </svg>
              {{ property.bathrooms }} vaga(s)
            </div>
            <div class="flex items-center gap-2 text-gray-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5a2 2 0 012-2z" />
              </svg>
              {{ property.garages }} m² Área Privativa
            </div>
            <div class="flex items-center gap-2 text-gray-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h4a2 2 0 012 2v12a2 2 0 01-2 2h-4a2 2 0 01-2-2V6zM19 12a2 2 0 012-2h4a2 2 0 012 2v4a2 2 0 01-2 2h-4a2 2 0 01-2-2v-4zM15 14a2 2 0 01-2 2H7a2 2 0 01-2-2v-6a2 2 0 012-2h6a2 2 0 012 2v6z" />
              </svg>
              {{ property.lotArea }} m² Área do Terreno
            </div>
          </div>

          <!-- Description -->
          <div class="mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4">DESCRIÇÃO DO IMÓVEL</h2>
            <div class="text-gray-700 leading-relaxed space-y-4" v-html="property.description"></div>
          </div>
        </div>

        <!-- Right Column - Sidebar -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-2xl shadow-lg p-6 sticky top-28 border border-gray-200">
            <!-- Price & Type -->
            <div class="flex items-center gap-3 mb-4">
              <span class="bg-blue-900 text-white px-4 py-1 rounded-full text-sm font-bold">{{ property.type }}</span>
              <span class="bg-gray-100 text-gray-700 px-4 py-1 rounded-full text-sm font-semibold">{{ property.propertyType }}</span>
            </div>

            <div class="text-sm text-gray-500 mb-2">VALOR DE VENDA</div>
            <div class="text-4xl font-bold text-blue-900 mb-6">
              R$ {{ formatPrice(property.price) }}
            </div>

            <!-- Costs -->
            <div class="space-y-3 mb-8">
              <div class="flex items-center justify-between">
                <span class="text-gray-500">Condomínio</span>
                <span class="text-gray-800 font-medium">R$ {{ formatPrice(property.condominium) }}</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-gray-500">IPTU Mensal</span>
                <span class="text-gray-800 font-medium">R$ {{ formatPrice(property.iptu) }}</span>
              </div>
            </div>

            <!-- Contact Form -->
            <div class="border-t border-gray-100 pt-6 mb-6">
              <form @submit.prevent="submitContact" class="space-y-4">
                <div>
                  <input
                    v-model="contactForm.nome"
                    type="text"
                    placeholder="Seu nome *"
                    class="w-full px-4 py-3 bg-gray-100 rounded-full border-0 focus:ring-2 focus:ring-blue-500"
                    required
                  />
                </div>
                <div class="flex items-center gap-3">
                  <div class="px-4 py-3 bg-gray-100 rounded-full text-gray-600">BR</div>
                  <input
                    v-model="contactForm.telefone"
                    type="tel"
                    placeholder="(00) 00000-0000"
                    class="flex-1 px-4 py-3 bg-gray-100 rounded-full border-0 focus:ring-2 focus:ring-blue-500"
                    required
                  />
                </div>
                <div>
                  <input
                    v-model="contactForm.email"
                    type="email"
                    placeholder="Seu e-mail"
                    class="w-full px-4 py-3 bg-gray-100 rounded-full border-0 focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <textarea
                    v-model="contactForm.mensagem"
                    placeholder="Olá, estou interessado nesse imóvel que encontrei no site."
                    rows="4"
                    class="w-full px-4 py-3 bg-gray-100 rounded-3xl border-0 focus:ring-2 focus:ring-blue-500 resize-none"
                  ></textarea>
                </div>
                <button
                  type="submit"
                  class="w-full bg-blue-900 hover:bg-blue-800 text-white font-bold py-4 px-6 rounded-full transition disabled:opacity-60 disabled:cursor-not-allowed"
                  :disabled="contactForm.processing"
                >
                  Enviar mensagem
                </button>
              </form>
            </div>

            <!-- Schedule Visit -->
            <div class="bg-gradient-to-br from-orange-100 to-orange-50 rounded-2xl p-6 border border-orange-200 mb-6">
              <div class="flex items-start gap-4">
                <div class="p-3 bg-orange-700/20 rounded-full">
                  <svg class="w-7 h-7 text-orange-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                </div>
                <div>
                  <h3 class="text-lg font-bold text-gray-800">Agende sua visita</h3>
                  <p class="text-gray-600 text-sm">Conheça o imóvel pessoalmente</p>
                </div>
              </div>
              <button class="w-full mt-4 bg-orange-500 hover:bg-orange-600 text-white font-bold py-4 px-6 rounded-full transition flex items-center justify-center gap-2">
                <span>Agendar visita</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
              </button>
            </div>

            <!-- Stats -->
            <div class="flex items-center justify-between text-gray-500">
              <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <span>385 visualizações</span>
              </div>
              <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
                <span>1 salvos</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Layout from '@/Shared/Layout.vue';

const props = defineProps({
  property: {
    type: Object,
    required: true,
  },
});

const activePhotoIndex = ref(0);

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
  return [placeholderImage];
});

const contactForm = useForm({
  property_id: props.property?.id ?? null,
  nome: '',
  telefone: '',
  email: '',
  mensagem: 'Olá, estou interessado nesse imóvel que encontrei no site.',
  origem: 'Site - Interesse no Imóvel',
});

function formatPrice(value) {
  if (!value) return '0';
  return value.toLocaleString('pt-BR', { minimumFractionDigits: 0 });
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
