<template>
  <Layout>
    <section class="bg-gradient-to-r from-teal-700 to-teal-800 text-white py-16">
      <div class="container mx-auto px-4 text-center">
        <h1 class="text-4xl font-bold">Seja um Corretor Parceiro</h1>
      </div>
    </section>
    <section class="py-16 bg-gray-50">
      <div class="container mx-auto px-4">
        <div class="bg-white rounded-xl shadow-lg p-8 max-w-3xl mx-auto">
          <form @submit.prevent="submit" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-gray-700 mb-2 font-medium">Nome Completo</label>
                <input type="text" v-model="form.nome" class="w-full border border-gray-300 rounded-lg px-4 py-3" required />
              </div>
              <div>
                <label class="block text-gray-700 mb-2 font-medium">CRECI</label>
                <input type="text" v-model="form.creci" class="w-full border border-gray-300 rounded-lg px-4 py-3" required />
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-gray-700 mb-2 font-medium">Telefone</label>
                <input type="tel" v-model="form.telefone" class="w-full border border-gray-300 rounded-lg px-4 py-3" required />
              </div>
              <div>
                <label class="block text-gray-700 mb-2 font-medium">E-mail</label>
                <input type="email" v-model="form.email" class="w-full border border-gray-300 rounded-lg px-4 py-3" required />
              </div>
            </div>
            <div>
              <label class="block text-gray-700 mb-2 font-medium">Cidade</label>
              <input type="text" v-model="form.cidade" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
            </div>
            <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold py-3 rounded-lg transition disabled:opacity-60 disabled:cursor-not-allowed" :disabled="form.processing">
              Seja um Parceiro
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
  creci: '',
  telefone: '',
  email: '',
  cidade: '',
});

function submit() {
  form.post('/corretor-parceiro/send', {
    preserveScroll: true,
    onSuccess: () => {
      alert('Obrigado pelo interesse! Entraremos em contato em breve.');
      form.reset();
      form.clearErrors();
    },
  });
}
</script>
