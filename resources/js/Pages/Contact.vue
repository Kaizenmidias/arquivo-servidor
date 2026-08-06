<template>
  <Layout>
    <section class="relative overflow-hidden bg-black text-white">
      <div class="absolute inset-0">
        <img :src="bannerImage" :alt="bannerTitle" class="h-full w-full object-cover grayscale" />
        <div class="absolute inset-0 bg-black/72"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_78%_18%,rgba(255,255,255,0.18),transparent_24%),linear-gradient(90deg,#000_0%,rgba(0,0,0,0.86)_48%,rgba(0,0,0,0.38)_100%)]"></div>
      </div>
      <div class="relative mx-auto max-w-[1180px] px-4 py-20 lg:py-28">
        <h1 class="text-5xl font-semibold leading-[0.98] tracking-tight text-white md:text-6xl lg:text-7xl">{{ bannerTitle }}</h1>
        <p v-if="bannerSubtitle" class="mt-6 max-w-2xl text-lg leading-8 text-white/82 md:text-xl">{{ bannerSubtitle }}</p>
        <div class="mt-8 flex text-sm text-white/70">
          <span><a href="/" class="hover:text-white">Início</a></span>
          <span class="mx-2">/</span>
          <span>{{ bannerTitle }}</span>
        </div>
      </div>
    </section>

    <!-- Contact Section -->
    <section class="bg-[#f7f5f1] py-16 lg:py-20">
      <div class="mx-auto max-w-[1180px] px-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
          <!-- Contact Info -->
          <div class="space-y-8">
            <h2 class="text-4xl font-semibold text-black md:text-5xl">Entre em Contato</h2>
            <p class="text-lg leading-8 text-black/68">
              Estamos aqui para ajudar! Entre em contato conosco através dos canais abaixo ou envie uma mensagem.
            </p>

            <div v-if="page?.conteudo" class="prose max-w-none" v-html="page.conteudo"></div>
            
            <div class="space-y-6">
              <div class="flex items-start space-x-4">
                <div class="border border-black/10 bg-white p-3">
                  <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                  </svg>
                </div>
                <div>
                  <h3 class="text-lg font-semibold text-gray-800">Telefone</h3>
                  <p class="text-gray-600">{{ settings?.telefone || '(11) 99999-9999' }}</p>
                </div>
              </div>
              
              <div class="flex items-start space-x-4">
                <div class="border border-black/10 bg-white p-3">
                  <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                  </svg>
                </div>
                <div>
                  <h3 class="text-lg font-semibold text-gray-800">E-mail</h3>
                  <p class="text-gray-600">{{ settings?.email_contato || 'contato@imobiliaria.com.br' }}</p>
                </div>
              </div>
              
              <div class="flex items-start space-x-4">
                <div class="border border-black/10 bg-white p-3">
                  <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  </svg>
                </div>
                <div>
                  <h3 class="text-lg font-semibold text-gray-800">Endereço</h3>
                  <p class="text-gray-600">{{ settings?.endereco || 'Rua Exemplo, 123 - Bairro Centro, São Paulo - SP' }}</p>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Contact Form -->
          <div class="border border-black/10 bg-white p-6 shadow-[0_24px_70px_rgba(0,0,0,0.12)] md:p-8">
            <form @submit.prevent="submitForm" class="space-y-6">
              <div>
                <label class="block text-gray-700 font-medium mb-2">Nome</label>
                <input 
                  type="text" 
                  v-model="form.nome" 
                  placeholder="Seu nome completo" 
                  class="w-full border border-black/15 bg-white px-4 py-3 text-black outline-none transition focus:border-black"
                  required
                />
              </div>
              <div>
                <label class="block text-gray-700 font-medium mb-2">Telefone</label>
                <input 
                  type="tel" 
                  v-model="form.telefone" 
                  placeholder="(11) 99999-9999" 
                  class="w-full border border-black/15 bg-white px-4 py-3 text-black outline-none transition focus:border-black"
                  required
                />
              </div>
              <div>
                <label class="block text-gray-700 font-medium mb-2">E-mail</label>
                <input 
                  type="email" 
                  v-model="form.email" 
                  placeholder="seuemail@exemplo.com" 
                  class="w-full border border-black/15 bg-white px-4 py-3 text-black outline-none transition focus:border-black"
                  required
                />
              </div>
              <div>
                <label class="block text-gray-700 font-medium mb-2">Mensagem</label>
                <textarea 
                  v-model="form.mensagem" 
                  rows="5" 
                  placeholder="Sua mensagem..." 
                  class="w-full border border-black/15 bg-white px-4 py-3 text-black outline-none transition focus:border-black"
                  required
                ></textarea>
              </div>
              <RecaptchaField
                v-if="captchaEnabled"
                ref="recaptchaRef"
                v-model="form.recaptcha_token"
                :site-key="recaptchaSiteKey"
                :error="form.errors.recaptcha_token"
              />
              <button 
                type="submit" 
                class="w-full bg-[#173b2f] py-4 px-6 text-sm font-bold uppercase tracking-[0.18em] text-white transition hover:bg-[#10291f] disabled:cursor-not-allowed disabled:opacity-60"
                :disabled="form.processing"
              >
                Enviar Mensagem
              </button>
            </form>
          </div>
        </div>
      </div>
    </section>
  </Layout>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import Layout from '@/Shared/Layout.vue';
import RecaptchaField from '@/Shared/RecaptchaField.vue';

const props = defineProps({
  page: {
    type: Object,
    default: () => null,
  },
  settings: {
    type: Object,
    default: () => ({}),
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
    <text x="800" y="330" text-anchor="middle" font-family="Arial, sans-serif" font-size="44" fill="rgba(255,255,255,0.35)">Contato</text>
  </svg>`
)}`;

const bannerImage = computed(() => props.page?.banner_image || placeholderImage);
const bannerTitle = computed(() => props.page?.banner_title || 'Contato');
const bannerSubtitle = computed(() => props.page?.banner_subtitle || '');
const bannerTitleColor = computed(() => props.page?.banner_title_color || '#ffffff');
const bannerSubtitleColor = computed(() => props.page?.banner_subtitle_color || 'rgba(255,255,255,0.85)');
const bannerOverlayColor = computed(() => props.page?.banner_overlay_color || '#000000');
const bannerOverlayOpacity = computed(() => {
  const raw = Number(props.page?.banner_overlay_opacity ?? 70);
  return Math.max(0, Math.min(100, raw)) / 100;
});

const page = usePage();
const recaptchaRef = ref(null);
const recaptchaSiteKey = computed(() => String(page.props.settings?.recaptcha_site_key || '').trim());
const captchaEnabled = computed(() => recaptchaSiteKey.value !== '');

const form = useForm({
  nome: '',
  telefone: '',
  email: '',
  mensagem: '',
  origem: 'Site - Contato',
  recaptcha_token: '',
});

async function submitForm() {
  if (captchaEnabled.value && !form.recaptcha_token) {
    form.setError('recaptcha_token', 'Confirme o captcha para continuar.');
    return;
  }

  form.post('/contato/send', {
    preserveScroll: true,
    onSuccess: () => {
      alert('Mensagem enviada com sucesso! Entraremos em contato em breve.');
      form.reset('nome', 'telefone', 'email', 'mensagem');
      form.clearErrors();
      recaptchaRef.value?.reset?.();
    },
    onError: () => {
      if (captchaEnabled.value) {
        recaptchaRef.value?.reset?.();
      }
    },
  });
}
</script>
