<template>
  <AdminLayout>
    <template #pageTitle>Blog: Postagens</template>

    <div class="flex items-center justify-between mb-6">
      <h3 class="text-xl font-semibold text-gray-800">Lista de Postagens</h3>
      <Link :href="`${adminBase}/blog/posts/create`" class="bg-blue-900 hover:bg-blue-800 text-white px-6 py-2 rounded-lg font-semibold transition">
        Nova Postagem
      </Link>
    </div>

    <div class="bg-white rounded-xl shadow border border-gray-200 overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Título</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Categoria</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Status</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Ações</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="post in posts" :key="post.id" class="hover:bg-gray-50">
            <td class="px-6 py-4">
              <div class="font-semibold text-gray-800">{{ post.title }}</div>
              <div class="text-sm text-gray-500">{{ post.slug }}</div>
            </td>
            <td class="px-6 py-4 text-gray-700">{{ post.category?.name || '-' }}</td>
            <td class="px-6 py-4">
              <span v-if="post.published_at" class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-semibold">
                Publicado
              </span>
              <span v-else class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-xs font-semibold">
                Rascunho
              </span>
            </td>
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <Link :href="`${adminBase}/blog/posts/${post.id}/edit`" class="text-blue-600 hover:text-blue-800 font-medium">Editar</Link>
                <button @click="remove(post.id)" class="text-red-600 hover:text-red-800 font-medium">Excluir</button>
              </div>
            </td>
          </tr>
          <tr v-if="posts.length === 0">
            <td colspan="4" class="px-6 py-12 text-center text-gray-500">Nenhuma postagem cadastrada</td>
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
  posts: {
    type: Array,
    default: () => [],
  },
  categories: {
    type: Array,
    default: () => [],
  },
});

const remove = (id) => {
  router.delete(`${adminBase.value}/blog/posts/${id}`);
};
</script>
