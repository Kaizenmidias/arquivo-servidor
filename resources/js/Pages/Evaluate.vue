<template>
  <Layout>
    <section class="bg-gradient-to-r from-indigo-700 to-indigo-800 text-white py-16">
      <div class="container mx-auto px-4 text-center">
        <h1 class="text-4xl font-bold">Avalie Seu Imóvel</h1>
      </div>
    </section>
    <section class="py-16 bg-gray-50">
      <div class="container mx-auto px-4">
        <div class="bg-white rounded-xl shadow-lg p-8 max-w-3xl mx-auto">
          <form @submit.prevent="submit" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-gray-700 mb-2 font-medium">Nome</label>
                <input type="text" v-model="form.nome" class="w-full border border-gray-300 rounded-lg px-4 py-3" required />
              </div>
              <div>
                <label class="block text-gray-700 mb-2 font-medium">Telefone</label>
                <input type="tel" v-model="form.telefone" class="w-full border border-gray-300 rounded-lg px-4 py-3" required />
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-gray-700 mb-2 font-medium">E-mail</label>
                <input type="email" v-model="form.email" class="w-full border border-gray-300 rounded-lg px-4 py-3" required />
              </div>
              <div>
                <label class="block text-gray-700 mb-2 font-medium">Tipo de Imóvel</label>
                <select v-model="form.tipo_imovel" class="w-full border border-gray-300 rounded-lg px-4 py-3">
                  <option value="">Selecione</option>
                  <option value="casa">Casa</option>
                  <option value="apartamento">Apartamento</option>
                </select>
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-gray-700 mb-2 font-medium">Cidade</label>
                <input type="text" v-model="form.cidade" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
              </div>
              <div>
                <label class="block text-gray-700 mb-2 font-medium">Bairro</label>
                <input type="text" v-model="form.bairro" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
              </div>
            </div>
            <div>
              <label class="block text-gray-700 mb-2 font-medium">Valor Estimado</label>
              <input type="number" v-model="form.valor_estimado" placeholder="R$" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
            </div>
            <div>
              <label class="block text-gray-700 mb-2 font-medium">Mensagem</label>
              <textarea v-model="form.mensagem" rows="4" class="w-full border border-gray-300 rounded-lg px-4 py-3"></textarea>
            </div>
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-lg transition disabled:opacity-60 disabled:cursor-not-allowed" :disabled="form.processing">
              Enviar Avaliação
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

const form = useForm({
  nome: '',
  telefone: '',
  email: '',
  tipo_imovel: '',
  cidade: '',
  bairro: '',
  valor_estimado: '',
  mensagem: '',
});

function submit() {
  form.post('/avalie-seu-imovel/send', {
    preserveScroll: true,
    onSuccess: () => {
      alert('Obrigado! Entraremos em contato para realizar a avaliação do seu imóvel.');
      form.reset();
      form.clearErrors();
    },
  });
}
</script>
