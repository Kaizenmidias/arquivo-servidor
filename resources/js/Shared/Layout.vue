<template>
  <div class="min-h-screen flex flex-col bg-white text-gray-900">
    <Header />
    <main :class="['flex-grow', isHome ? '' : 'pt-20']">
      <slot />
    </main>
    <Footer />

    <div v-if="showCookieBanner" class="fixed bottom-6 right-6 z-50 w-[420px] max-w-[calc(100vw-3rem)]">
      <div class="bg-white border border-gray-200 shadow-2xl rounded-2xl p-5">
        <div class="flex items-start gap-4">
          <div class="flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center" style="background: color-mix(in oklab, var(--site-primary) 12%, transparent)">
            <svg class="w-6 h-6" style="color: var(--site-primary)" fill="currentColor" viewBox="0 0 24 24">
              <path d="M8 6V10H11V6H13V10H16V6H18V10L19 10C20.5977 10 21.9037 11.2489 21.9949 12.8237L22 13V14C22 15.0139 21.6227 15.9397 21.001 16.6447L21 21C21 21.5523 20.5523 22 20 22H4C3.44772 22 3 21.5523 3 21L2.99917 16.6401C2.47812 16.0464 2.12795 15.2943 2.02793 14.4584L2.00551 14.2052L2 14V13C2 11.4023 3.24892 10.0963 4.82373 10.0051L5 10L6 10V6H8ZM9.002 16.641L8.94768 16.7041C8.30742 17.4013 7.42357 17.8697 6.43424 17.9767L6.20413 17.9949L6 18C5.65524 18 5.32032 17.9563 5.00061 17.8738L5 20H19V17.8738C18.6809 17.9562 18.3456 18 18 18C16.9378 18 15.9724 17.586 15.2561 16.9106L15.1065 16.7619L15 16.644L14.8935 16.7619C14.2607 17.4246 13.4024 17.8703 12.4418 17.9759L12.1996 17.9951L12 18C10.914 18 9.92563 17.5661 9.20287 16.8557L9.05211 16.6993L9.002 16.641ZM19 12H5C4.48716 12 4.06449 12.386 4.00673 12.8834L4 13V14C4 15.1046 4.89543 16 6 16C7.10457 16 8 15.1046 8 14C8 12.6667 10 12.6667 10 14C10 15.1046 10.8954 16 12 16C13.1046 16 14 15.1046 14 14C14 12.6667 16 12.6667 16 14C16 15.1046 16.8954 16 18 16C19.1046 16 20 15.1046 20 14V13C20 12.4477 19.5523 12 19 12Z"></path>
            </svg>
          </div>
          <div class="flex-1">
            <h3 class="font-semibold text-neutral-900 mb-1">Nós usamos cookies</h3>
            <p class="text-sm text-neutral-600 mb-4">
              Utilizamos cookies para melhorar sua experiência, analisar o tráfego e personalizar conteúdo.
              <a href="/politicas-de-privacidade" class="hover:underline" style="color: var(--site-primary)">Saiba mais</a>
            </p>
            <div class="flex flex-wrap gap-2">
              <button type="button" class="h-9 rounded-lg px-4 text-xs font-medium text-white shadow-md hover:shadow-lg transition" style="background: var(--site-primary)" @click="acceptAll">
                Aceitar todos
              </button>
              <button type="button" class="h-9 rounded-lg px-4 text-xs font-medium border-2 transition" style="border-color: var(--site-primary); color: var(--site-primary)" @click="rejectAll">
                Rejeitar
              </button>
              <button type="button" class="h-9 rounded-lg px-4 text-xs font-medium hover:bg-gray-100 transition" @click="openCookieModal">
                Configurar
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showCookieModal" class="fixed inset-0 z-[60] flex items-center justify-center px-4" style="background: rgba(0,0,0,0.45)">
      <div class="w-full max-w-2xl bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden max-h-[80vh] overflow-y-auto">
        <div class="sticky top-0 flex items-center justify-between p-4 border-b border-gray-200 bg-white">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: color-mix(in oklab, var(--site-primary) 12%, transparent)">
              <svg class="w-5 h-5" style="color: var(--site-primary)" fill="currentColor" viewBox="0 0 24 24">
                <path d="M2 12C2 11.1355 2.1097 10.2967 2.31595 9.49655C3.40622 9.55308 4.48848 9.0104 5.0718 8.00006C5.65467 6.99049 5.58406 5.78295 4.99121 4.86726C6.18354 3.69553 7.66832 2.82046 9.32603 2.36158C9.8222 3.33409 10.8333 4.00006 12 4.00006C13.1667 4.00006 14.1778 3.33409 14.674 2.36158C16.3317 2.82046 17.8165 3.69553 19.0088 4.86726C18.4159 5.78295 18.3453 6.99049 18.9282 8.00006C19.5115 9.0104 20.5938 9.55308 21.6841 9.49655C21.8903 10.2967 22 11.1355 22 12C22 12.8645 21.8903 13.7034 21.6841 14.5035C20.5938 14.4469 19.5115 14.9896 18.9282 15.9999C18.3453 17.0096 18.4159 18.2171 19.0088 19.1328C17.8165 20.3046 16.3317 21.1796 14.674 21.6385C14.1778 20.666 13.1667 20 12 20C10.8333 20 9.8222 20.666 9.32603 21.6385C7.66832 21.1796 6.18354 20.3046 4.99121 19.1328C5.58406 18.2171 5.65467 17.0096 5.0718 15.9999C4.48848 14.9896 3.40622 14.4469 2.31595 14.5035C2.1097 13.7034 2 12.8645 2 12Z"></path>
              </svg>
            </div>
            <div>
              <h3 class="font-semibold text-neutral-900">Configurar cookies</h3>
              <p class="text-xs text-neutral-500">Personalize suas preferências</p>
            </div>
          </div>
          <button type="button" class="p-2 rounded-lg hover:bg-gray-100 transition-colors" @click="closeCookieModal">
            <svg class="w-5 h-5 text-neutral-500" fill="currentColor" viewBox="0 0 24 24">
              <path d="M11.9997 10.5865L16.9495 5.63672L18.3637 7.05093L13.4139 12.0007L18.3637 16.9504L16.9495 18.3646L11.9997 13.4149L7.04996 18.3646L5.63574 16.9504L10.5855 12.0007L5.63574 7.05093L7.04996 5.63672L11.9997 10.5865Z"></path>
            </svg>
          </button>
        </div>

        <div class="p-4 space-y-3">
          <div class="p-4 rounded-xl border" style="border-color: color-mix(in oklab, var(--site-primary) 30%, transparent); background: color-mix(in oklab, var(--site-primary) 6%, transparent)">
            <div class="flex items-start justify-between gap-3">
              <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                  <span class="font-medium text-neutral-900">Essenciais</span>
                  <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full" style="color: var(--site-primary); background: color-mix(in oklab, var(--site-primary) 12%, transparent)">
                    Obrigatório
                  </span>
                </div>
                <p class="text-sm text-neutral-500">Necessários para o funcionamento do site. Não podem ser desativados.</p>
              </div>
              <button disabled class="relative w-12 h-7 rounded-full opacity-60 cursor-not-allowed" style="background: var(--site-primary)">
                <div class="absolute top-1 left-1 w-5 h-5 rounded-full bg-white shadow-md" style="transform: translateX(22px)"></div>
              </button>
            </div>
          </div>

          <div class="p-4 rounded-xl border" :style="optionCardStyle(consentDraft.analytics)">
            <div class="flex items-start justify-between gap-3">
              <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                  <span class="font-medium text-neutral-900">Analíticos</span>
                </div>
                <p class="text-sm text-neutral-500">Nos ajudam a entender como você usa o site para melhorar sua experiência.</p>
              </div>
              <button type="button" class="relative w-12 h-7 rounded-full transition-colors" :style="toggleStyle(consentDraft.analytics)" @click="consentDraft.analytics = !consentDraft.analytics">
                <div class="absolute top-1 w-5 h-5 rounded-full bg-white shadow-md transition-transform" :style="{ transform: consentDraft.analytics ? 'translateX(22px)' : 'translateX(2px)' }"></div>
              </button>
            </div>
          </div>

          <div class="p-4 rounded-xl border" :style="optionCardStyle(consentDraft.marketing)">
            <div class="flex items-start justify-between gap-3">
              <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                  <span class="font-medium text-neutral-900">Marketing</span>
                </div>
                <p class="text-sm text-neutral-500">Usados para mostrar anúncios relevantes baseados nos seus interesses.</p>
              </div>
              <button type="button" class="relative w-12 h-7 rounded-full transition-colors" :style="toggleStyle(consentDraft.marketing)" @click="consentDraft.marketing = !consentDraft.marketing">
                <div class="absolute top-1 w-5 h-5 rounded-full bg-white shadow-md transition-transform" :style="{ transform: consentDraft.marketing ? 'translateX(22px)' : 'translateX(2px)' }"></div>
              </button>
            </div>
          </div>

          <div class="p-4 rounded-xl border" :style="optionCardStyle(consentDraft.functional)">
            <div class="flex items-start justify-between gap-3">
              <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                  <span class="font-medium text-neutral-900">Funcionais</span>
                </div>
                <p class="text-sm text-neutral-500">Permitem funcionalidades extras como chat ao vivo e vídeos incorporados.</p>
              </div>
              <button type="button" class="relative w-12 h-7 rounded-full transition-colors" :style="toggleStyle(consentDraft.functional)" @click="consentDraft.functional = !consentDraft.functional">
                <div class="absolute top-1 w-5 h-5 rounded-full bg-white shadow-md transition-transform" :style="{ transform: consentDraft.functional ? 'translateX(22px)' : 'translateX(2px)' }"></div>
              </button>
            </div>
          </div>
        </div>

        <div class="sticky bottom-0 flex gap-3 p-4 border-t border-gray-200 bg-white">
          <button type="button" class="h-11 px-6 rounded-xl text-sm font-medium border-2 flex-1 transition" style="border-color: var(--site-primary); color: var(--site-primary)" @click="rejectAll">
            Rejeitar opcionais
          </button>
          <button type="button" class="h-11 px-6 rounded-xl text-sm font-medium text-white shadow-md flex-1 transition" style="background: var(--site-primary)" @click="saveDraft">
            Salvar preferências
          </button>
        </div>
      </div>
    </div>

    <!-- Floating WhatsApp Button -->
    <a
      href="https://wa.me/5511999999999?text=Olá! Vim do site e tenho interesse em um imóvel"
      target="_blank"
      rel="noopener noreferrer"
      class="fixed bottom-6 right-6 z-50 bg-green-500 hover:bg-green-600 text-white rounded-full p-4 shadow-lg transition-transform hover:scale-110"
    >
      <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.488-.494-.67-.503-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.037 1.01-1.037 2.466s1.061 2.85 1.208 3.048c.148.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .162 5.332.162 11.887c0 2.096.547 4.14 1.588 5.94L0 24l6.307-1.654a11.88 11.88 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.332 11.89-11.886a11.821 11.821 0 0 0-3.483-8.433" />
      </svg>
    </a>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Header from './Header.vue';
import Footer from './Footer.vue';

const page = usePage();
const isHome = computed(() => page.component === 'Home' || page.url === '/');

const STORAGE_KEY = 'cookie_consent_v1';

const showCookieBanner = ref(false);
const showCookieModal = ref(false);
const consentDraft = reactive({
  essential: true,
  analytics: false,
  marketing: false,
  functional: false,
});

const readConsent = () => {
  try {
    const raw = window.localStorage.getItem(STORAGE_KEY);
    if (!raw) return null;
    const parsed = JSON.parse(raw);
    if (!parsed || typeof parsed !== 'object') return null;
    return parsed;
  } catch {
    return null;
  }
};

const writeConsent = (value) => {
  window.localStorage.setItem(STORAGE_KEY, JSON.stringify({ ...value, essential: true, savedAt: new Date().toISOString() }));
};

const applyConsent = (value) => {
  consentDraft.analytics = !!value.analytics;
  consentDraft.marketing = !!value.marketing;
  consentDraft.functional = !!value.functional;
};

onMounted(() => {
  const existing = readConsent();
  if (!existing) {
    showCookieBanner.value = true;
    return;
  }
  applyConsent(existing);
});

const acceptAll = () => {
  const value = { essential: true, analytics: true, marketing: true, functional: true };
  applyConsent(value);
  writeConsent(value);
  showCookieBanner.value = false;
  showCookieModal.value = false;
};

const rejectAll = () => {
  const value = { essential: true, analytics: false, marketing: false, functional: false };
  applyConsent(value);
  writeConsent(value);
  showCookieBanner.value = false;
  showCookieModal.value = false;
};

const openCookieModal = () => {
  showCookieModal.value = true;
};
const closeCookieModal = () => {
  showCookieModal.value = false;
};

const saveDraft = () => {
  const value = {
    essential: true,
    analytics: consentDraft.analytics,
    marketing: consentDraft.marketing,
    functional: consentDraft.functional,
  };
  writeConsent(value);
  showCookieBanner.value = false;
  showCookieModal.value = false;
};

const optionCardStyle = (enabled) => {
  if (enabled) {
    return {
      borderColor: `color-mix(in oklab, var(--site-primary) 30%, transparent)`,
      background: `color-mix(in oklab, var(--site-primary) 6%, transparent)`,
    };
  }
  return { borderColor: '#e5e7eb', background: '#ffffff' };
};

const toggleStyle = (enabled) => {
  return { background: enabled ? 'var(--site-primary)' : '#e5e7eb' };
};
</script>
