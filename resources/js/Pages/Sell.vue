<template>
  <Layout>
    <section class="relative overflow-hidden bg-black text-white">
      <div class="absolute inset-0">
        <img :src="heroImage" :alt="heroTitle" class="h-full w-full object-cover grayscale" />
        <div class="absolute inset-0" :style="{ backgroundColor: heroOverlayColor, opacity: heroOverlayOpacity }"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_78%_18%,rgba(255,255,255,0.18),transparent_24%),linear-gradient(90deg,#000_0%,rgba(0,0,0,0.86)_48%,rgba(0,0,0,0.38)_100%)]"></div>
      </div>

      <div class="relative mx-auto flex min-h-[78svh] max-w-[1180px] items-center px-4 py-20 lg:min-h-[84svh] lg:py-28">
        <div class="max-w-3xl">
          <div class="text-xs font-semibold uppercase tracking-[0.24em] text-white/72">
            {{ heroKicker }}
          </div>
          <h1 class="mt-6 text-5xl font-semibold leading-[0.98] tracking-tight text-white md:text-6xl lg:text-7xl" :style="{ color: heroTitleColor }">
            {{ heroTitle }}
          </h1>
          <p class="mt-6 max-w-2xl text-lg leading-8 md:text-xl" :style="{ color: heroSubtitleColor }">
            {{ heroSubtitle }}
          </p>
          <div class="mt-8 flex flex-wrap gap-3">
            <a href="#formulario" class="inline-flex items-center gap-2 rounded-full px-6 py-3.5 text-sm font-semibold text-white shadow-[0_18px_40px_rgba(15,23,42,0.18)] transition hover:opacity-90" :style="{ backgroundColor: 'var(--site-primary)' }">
              {{ heroButtonLabel }}
              <span aria-hidden="true">→</span>
            </a>
            <a href="/contato" class="inline-flex items-center gap-2 rounded-full border border-white/24 px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-white/10">
              Fale conosco
            </a>
          </div>
        </div>
      </div>
    </section>

    <section v-if="pageContent" class="bg-white">
      <div class="mx-auto max-w-[980px] px-4 py-14 lg:py-18">
        <div class="prose max-w-none text-slate-700" v-html="pageContent"></div>
      </div>
    </section>

    <section id="formulario" class="bg-[#f7f5f1] py-16 lg:py-20">
      <div class="mx-auto max-w-[1180px] px-4">
        <div class="-mt-24 mx-auto max-w-4xl border border-black/10 bg-white p-6 shadow-[0_28px_80px_rgba(0,0,0,0.16)] md:p-10">
          <h2 class="mb-8 text-center text-2xl font-semibold text-gray-800">Cadastre Seu Imóvel</h2>

          <form @submit.prevent="submitForm" class="space-y-6">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
              <div>
                <label class="mb-2 block font-medium text-gray-700">Nome Completo</label>
                <input type="text" v-model="form.nome" placeholder="Seu nome" class="w-full rounded-lg border border-gray-300 px-4 py-3" required />
              </div>
              <div>
                <label class="mb-2 block font-medium text-gray-700">Telefone</label>
                <input type="tel" v-model="form.telefone" placeholder="(11) 99999-9999" class="w-full rounded-lg border border-gray-300 px-4 py-3" required />
              </div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
              <div>
                <label class="mb-2 block font-medium text-gray-700">E-mail</label>
                <input type="email" v-model="form.email" placeholder="seu@email.com" class="w-full rounded-lg border border-gray-300 px-4 py-3" required />
              </div>
              <div>
                <label class="mb-2 block font-medium text-gray-700">Tipo do Imóvel</label>
                <select v-model="form.tipo_imovel" class="w-full rounded-lg border border-gray-300 px-4 py-3" required>
                  <option value="">Selecione</option>
                  <option value="casa">Casa</option>
                  <option value="apartamento">Apartamento</option>
                  <option value="terreno">Terreno</option>
                  <option value="comercial">Comercial</option>
                  <option value="outro">Outro</option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
              <div>
                <label class="mb-2 block font-medium text-gray-700">Tipo de Negócio</label>
                <select v-model="form.tipo_negocio" class="w-full rounded-lg border border-gray-300 px-4 py-3" required>
                  <option value="">Selecione</option>
                  <option value="venda">Venda</option>
                  <option value="locacao">Locação</option>
                </select>
              </div>
              <div>
                <label class="mb-2 block font-medium text-gray-700">Região / Condomínio</label>
                <input type="text" v-model="form.regiao_condominio" placeholder="Ex: Centro, Alphaville" class="w-full rounded-lg border border-gray-300 px-4 py-3" />
              </div>
              <div>
                <label class="mb-2 block font-medium text-gray-700">Valor do Imóvel (R$)</label>
                <input type="text" v-model="form.valor_imovel" placeholder="Ex: 500.000" class="w-full rounded-lg border border-gray-300 px-4 py-3" />
              </div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
              <div>
                <label class="mb-2 block font-medium text-gray-700">Quartos</label>
                <input type="number" v-model="form.quartos" placeholder="3" class="w-full rounded-lg border border-gray-300 px-4 py-3" />
              </div>
              <div>
                <label class="mb-2 block font-medium text-gray-700">Banheiros</label>
                <input type="number" v-model="form.banheiros" placeholder="2" class="w-full rounded-lg border border-gray-300 px-4 py-3" />
              </div>
              <div>
                <label class="mb-2 block font-medium text-gray-700">Área (m²)</label>
                <input type="number" v-model="form.area" placeholder="150" class="w-full rounded-lg border border-gray-300 px-4 py-3" />
              </div>
            </div>

            <div>
              <label class="mb-2 block font-medium text-gray-700">Mensagem</label>
              <textarea v-model="form.mensagem" rows="4" placeholder="Descreva seu imóvel..." class="w-full rounded-lg border border-gray-300 px-4 py-3"></textarea>
            </div>

            <RecaptchaField
              v-if="captchaEnabled"
              ref="recaptchaRef"
              v-model="form.recaptcha_token"
              :site-key="recaptchaSiteKey"
              :error="form.errors.recaptcha_token"
            />

            <button type="submit" class="w-full bg-[#173b2f] px-6 py-4 text-sm font-bold uppercase tracking-[0.18em] text-white transition hover:bg-[#10291f] disabled:cursor-not-allowed disabled:opacity-60" :disabled="form.processing">
              Enviar
            </button>
          </form>
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
});

const pageData = computed(() => props.page?.data && typeof props.page.data === 'object' ? props.page.data : {});
const heroKicker = computed(() => pageData.value?.hero?.subtitle || 'Venda seu imóvel');
const heroTitle = computed(() => props.page?.banner_title || props.page?.titulo || 'Venda seu imóvel com segurança e estratégia.');
const heroSubtitle = computed(() => props.page?.banner_subtitle || 'Conte com um atendimento próximo, ágil e orientado para o melhor resultado.');
const heroButtonLabel = computed(() => pageData.value?.hero?.button_label || 'Preencher formulário');
const heroTitleColor = computed(() => props.page?.banner_title_color || '#ffffff');
const heroSubtitleColor = computed(() => props.page?.banner_subtitle_color || 'rgba(255,255,255,0.84)');
const heroOverlayColor = computed(() => props.page?.banner_overlay_color || '#000000');
const heroOverlayOpacity = computed(() => {
  const raw = Number(props.page?.banner_overlay_opacity ?? 72);
  return Math.max(0, Math.min(100, raw)) / 100;
});
const heroImage = computed(() => props.page?.banner_image || `data:image/svg+xml,${encodeURIComponent(
  `<svg xmlns="http://www.w3.org/2000/svg" width="1920" height="800" viewBox="0 0 1920 800">
    <defs>
      <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
        <stop offset="0" stop-color="#000000"/>
        <stop offset="1" stop-color="#57534e"/>
      </linearGradient>
    </defs>
    <rect width="1920" height="800" fill="url(#g)"/>
    <text x="960" y="420" text-anchor="middle" font-family="Arial, sans-serif" font-size="54" fill="rgba(255,255,255,0.35)">Venda seu imóvel</text>
  </svg>`
)}`);
const pageContent = computed(() => String(props.page?.conteudo || '').trim());

const page = usePage();
const recaptchaRef = ref(null);
const recaptchaSiteKey = computed(() => String(page.props.settings?.recaptcha_site_key || '').trim());
const captchaEnabled = computed(() => recaptchaSiteKey.value !== '');

const form = useForm({
  nome: '',
  telefone: '',
  email: '',
  tipo_negocio: '',
  regiao_condominio: '',
  valor_imovel: '',
  tipo_imovel: '',
  quartos: '',
  banheiros: '',
  area: '',
  mensagem: '',
  recaptcha_token: '',
});

async function submitForm() {
  if (captchaEnabled.value && !form.recaptcha_token) {
    form.setError('recaptcha_token', 'Confirme o captcha para continuar.');
    return;
  }

  form.post('/venda-seu-imovel/send', {
    preserveScroll: true,
    onSuccess: () => {
      alert('Dados enviados! Entraremos em contato em breve.');
      form.reset();
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
