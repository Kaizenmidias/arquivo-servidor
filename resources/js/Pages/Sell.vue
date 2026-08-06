<template>
  <Layout>
    <!-- Hero -->
    <section class="relative overflow-hidden bg-black py-24 text-white lg:py-32">
      <div class="absolute inset-0">
        <img :src="placeholderImage" alt="" class="h-full w-full object-cover opacity-45 grayscale" />
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_74%_18%,rgba(255,255,255,0.18),transparent_24%),linear-gradient(90deg,#000_0%,rgba(0,0,0,0.86)_48%,rgba(0,0,0,0.38)_100%)]"></div>
      </div>
      <div class="relative z-10 mx-auto max-w-[1180px] px-4">
        <span class="inline-flex border border-white/25 bg-white/10 px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.28em] text-white/80 backdrop-blur-sm">Avaliação especializada</span>
        <h1 class="mt-6 max-w-3xl text-5xl font-semibold leading-[0.98] tracking-tight md:text-6xl lg:text-7xl">Venda Seu Imóvel</h1>
        <p class="mt-6 max-w-2xl text-lg leading-8 text-white/82 md:text-xl">Deixe-nos ajudar você a vender seu imóvel rapidamente e pelo melhor preço</p>
      </div>
    </section>

    <!-- Form -->
    <section class="bg-[#f7f5f1] py-16 lg:py-20">
      <div class="mx-auto max-w-[1180px] px-4">
        <div class="-mt-24 mx-auto max-w-4xl border border-black/10 bg-white p-6 shadow-[0_28px_80px_rgba(0,0,0,0.16)] md:p-10">
          <h2 class="mb-8 text-center text-3xl font-semibold text-black md:text-4xl">Cadastre Seu Imóvel</h2>
          
          <form @submit.prevent="submitForm" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-gray-700 font-medium mb-2">Nome Completo</label>
                <input type="text" v-model="form.nome" placeholder="Seu nome" class="w-full border border-gray-300 rounded-lg px-4 py-3" required />
              </div>
              <div>
                <label class="block text-gray-700 font-medium mb-2">Telefone</label>
                <input type="tel" v-model="form.telefone" placeholder="(11) 99999-9999" class="w-full border border-gray-300 rounded-lg px-4 py-3" required />
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-gray-700 font-medium mb-2">E-mail</label>
                <input type="email" v-model="form.email" placeholder="seu@email.com" class="w-full border border-gray-300 rounded-lg px-4 py-3" required />
              </div>
              <div>
                <label class="block text-gray-700 font-medium mb-2">Tipo do Imóvel</label>
                <select v-model="form.tipo_imovel" class="w-full border border-gray-300 rounded-lg px-4 py-3" required>
                  <option value="">Selecione</option>
                  <option value="casa">Casa</option>
                  <option value="apartamento">Apartamento</option>
                  <option value="terreno">Terreno</option>
                  <option value="comercial">Comercial</option>
                  <option value="outro">Outro</option>
                </select>
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div>
                <label class="block text-gray-700 font-medium mb-2">Tipo de Negócio</label>
                <select v-model="form.tipo_negocio" class="w-full border border-gray-300 rounded-lg px-4 py-3" required>
                  <option value="">Selecione</option>
                  <option value="venda">Venda</option>
                  <option value="locacao">Locação</option>
                </select>
              </div>
              <div>
                <label class="block text-gray-700 font-medium mb-2">Região / Condomínio</label>
                <input type="text" v-model="form.regiao_condominio" placeholder="Ex: Centro, Alphaville" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
              </div>
              <div>
                <label class="block text-gray-700 font-medium mb-2">Valor do Imóvel (R$)</label>
                <input type="text" v-model="form.valor_imovel" placeholder="Ex: 500.000" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div>
                <label class="block text-gray-700 font-medium mb-2">Quartos</label>
                <input type="number" v-model="form.quartos" placeholder="3" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
              </div>
              <div>
                <label class="block text-gray-700 font-medium mb-2">Banheiros</label>
                <input type="number" v-model="form.banheiros" placeholder="2" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
              </div>
              <div>
                <label class="block text-gray-700 font-medium mb-2">Área (m²)</label>
                <input type="number" v-model="form.area" placeholder="150" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
              </div>
            </div>
            <div>
              <label class="block text-gray-700 font-medium mb-2">Mensagem</label>
              <textarea v-model="form.mensagem" rows="4" placeholder="Descreva seu imóvel..." class="w-full border border-gray-300 rounded-lg px-4 py-3"></textarea>
            </div>
            <RecaptchaField
              v-if="captchaEnabled"
              ref="recaptchaRef"
              v-model="form.recaptcha_token"
              :site-key="recaptchaSiteKey"
              :error="form.errors.recaptcha_token"
            />
            <button type="submit" class="w-full bg-[#173b2f] py-4 px-6 text-sm font-bold uppercase tracking-[0.18em] text-white transition hover:bg-[#10291f] disabled:cursor-not-allowed disabled:opacity-60" :disabled="form.processing">
              Enviar
            </button>
          </form>
        </div>
      </div>
    </section>
  </Layout>
</template>

<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Layout from '@/Shared/Layout.vue';
import RecaptchaField from '@/Shared/RecaptchaField.vue';

const placeholderImage = `data:image/svg+xml,${encodeURIComponent(
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
)}`;

const page = usePage();
const recaptchaRef = ref(null);
const recaptchaSiteKey = computed(() => String(page.props.settings?.recaptcha_site_key || '').trim());
const captchaEnabled = computed(() => recaptchaSiteKey.value !== '');

const form = useForm({
  nome: '',
  telefone: '',
  email: '',
  tipo_negocio: '', // Novo campo
  regiao_condominio: '', // Novo campo
  valor_imovel: '', // Novo campo
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
