<template>
  <AdminLayout>
    <template #pageTitle>Layout</template>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <div class="bg-white rounded-xl shadow p-6 border border-gray-200">
        <h3 class="text-xl font-semibold text-gray-800 mb-6">Textos Gerais</h3>
        <div class="space-y-6">
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Título do Site</label>
            <input type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Imobiliária">
          </div>
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Slogan</label>
            <input type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Seu novo lar está aqui">
          </div>
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Telefone</label>
            <input type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="(00) 00000-0000">
          </div>
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">E-mail de Contato</label>
            <input type="email" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="contato@imobiliaria.com">
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-xl shadow p-6 border border-gray-200">
        <h3 class="text-xl font-semibold text-gray-800 mb-6">Redes Sociais</h3>
        <div class="space-y-6">
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Instagram</label>
            <input type="url" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="https://instagram.com/usuario">
          </div>
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Facebook</label>
            <input type="url" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="https://facebook.com/usuario">
          </div>
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">WhatsApp</label>
            <input type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="(00) 00000-0000">
          </div>
        </div>
      </div>
      
      <div class="lg:col-span-2">
        <button class="bg-blue-900 hover:bg-blue-800 text-white px-8 py-3 rounded-lg font-semibold transition">
          Salvar Alterações
        </button>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Shared/AdminLayout.vue';

const props = defineProps({
  settings: {
    type: Object,
    default: () => ({}),
  },
});
</script>
