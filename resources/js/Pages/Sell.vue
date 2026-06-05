<template>
  <Layout>
    <!-- Hero -->
    <section class="relative bg-gradient-to-r from-orange-600 to-orange-700 text-white py-24">
      <div class="absolute inset-0 opacity-20">
        <img :src="placeholderImage" alt="" class="w-full h-full object-cover" />
      </div>
      <div class="container mx-auto px-4 relative z-10 text-center">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Venda Seu Imóvel</h1>
        <p class="text-xl max-w-2xl mx-auto">Deixe-nos ajudar você a vender seu imóvel rapidamente e pelo melhor preço</p>
      </div>
    </section>

    <!-- Form -->
    <section class="py-16 bg-gray-50">
      <div class="container mx-auto px-4">
        <div class="bg-white rounded-xl shadow-lg p-8 max-w-3xl mx-auto">
          <h2 class="text-2xl font-bold text-gray-800 mb-8 text-center">Cadastre Seu Imóvel</h2>
          
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
                </select>
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
            <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-6 rounded-lg transition disabled:opacity-60 disabled:cursor-not-allowed" :disabled="form.processing">
              Enviar
            </button>
          </form>
        </div>
      </div>
    </section>
  </Layout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import Layout from '@/Shared/Layout.vue';

const placeholderImage = `data:image/svg+xml,${encodeURIComponent(
  `<svg xmlns="http://www.w3.org/2000/svg" width="1920" height="800" viewBox="0 0 1920 800">
    <defs>
      <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
        <stop offset="0" stop-color="#7c2d12"/>
        <stop offset="1" stop-color="#ea580c"/>
      </linearGradient>
    </defs>
    <rect width="1920" height="800" fill="url(#g)"/>
    <text x="960" y="420" text-anchor="middle" font-family="Arial, sans-serif" font-size="54" fill="rgba(255,255,255,0.35)">Venda seu imóvel</text>
  </svg>`
)}`;

const form = useForm({
  nome: '',
  telefone: '',
  email: '',
  tipo_imovel: '',
  quartos: '',
  banheiros: '',
  area: '',
  mensagem: '',
});

function submitForm() {
  form.post('/venda-seu-imovel/send', {
    preserveScroll: true,
    onSuccess: () => {
      alert('Dados enviados! Entraremos em contato em breve.');
      form.reset();
      form.clearErrors();
    },
  });
}
</script>
