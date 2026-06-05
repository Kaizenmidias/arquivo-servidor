<template>
  <AdminLayout>
    <template #pageTitle>Páginas</template>
    
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-xl font-semibold text-gray-800">Lista de Páginas</h3>
      <Link :href="`${adminBase}/pages/create`" class="bg-blue-900 hover:bg-blue-800 text-white px-6 py-2 rounded-lg font-semibold transition">
        Nova Página
      </Link>
    </div>

    <div class="bg-white rounded-xl shadow border border-gray-200 overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Página</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Slug</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Status</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Ações</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="page in pages" :key="page.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 font-semibold text-gray-800">{{ page.titulo }}</td>
            <td class="px-6 py-4 text-gray-600">{{ page.slug }}</td>
            <td class="px-6 py-4">
              <span :class="page.ativo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'" class="px-3 py-1 rounded-full text-xs font-semibold">
                {{ page.ativo ? 'Ativo' : 'Inativo' }}
              </span>
            </td>
            <td class="px-6 py-4">
              <div class="flex items-center gap-4">
                <Link :href="`${adminBase}/pages/${page.id}`" class="text-blue-600 hover:text-blue-800 font-medium">Editar</Link>
                <button type="button" class="text-gray-700 hover:text-gray-900 font-medium" @click="duplicate(page.id)">Duplicar</button>
                <button type="button" class="text-red-600 hover:text-red-800 font-medium" @click="remove(page.id)">Excluir</button>
              </div>
            </td>
          </tr>
          <tr v-if="pages.length === 0">
            <td colspan="4" class="px-6 py-12 text-center text-gray-500">Nenhuma página cadastrada</td>
          </tr>
        </tbody>
      </table>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Shared/AdminLayout.vue';

const page = usePage();
const adminBase = computed(() => page.props?.paths?.admin || '/admin');

defineProps({
  pages: {
    type: Array,
    default: () => [],
  },
});

const duplicate = (id) => {
  router.post(`${adminBase.value}/pages/${id}/duplicate`);
};

const remove = (id) => {
  router.delete(`${adminBase.value}/pages/${id}`);
};
</script>
