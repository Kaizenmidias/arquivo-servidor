<template>
  <AdminLayout>
    <template #pageTitle>Blog: Categorias</template>

    <div class="bg-white rounded-xl shadow p-6 border border-gray-200 mb-8">
      <h3 class="text-lg font-semibold text-gray-800 mb-4">Adicionar</h3>

      <form @submit.prevent="create" class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
        <div>
          <label class="block text-gray-700 mb-2 text-sm font-medium">Nome</label>
          <input v-model="createForm.name" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Mercado, Dicas...">
          <div v-if="createForm.errors.name" class="text-sm text-red-600 mt-1">{{ createForm.errors.name }}</div>
        </div>
        <div>
          <button type="submit" :disabled="createForm.processing" class="bg-blue-900 hover:bg-blue-800 disabled:opacity-60 text-white px-6 py-3 rounded-lg font-semibold transition">
            Salvar
          </button>
        </div>
      </form>
    </div>

    <div class="bg-white rounded-xl shadow border border-gray-200 overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Nome</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Slug</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Ações</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50">
            <td class="px-6 py-4">
              <template v-if="editingId === item.id">
                <input v-model="editForm.name" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                <div v-if="editForm.errors.name" class="text-sm text-red-600 mt-1">{{ editForm.errors.name }}</div>
              </template>
              <template v-else>
                <div class="font-semibold text-gray-800">{{ item.name }}</div>
              </template>
            </td>
            <td class="px-6 py-4 text-gray-600">{{ item.slug }}</td>
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <template v-if="editingId === item.id">
                  <button @click="saveEdit" :disabled="editForm.processing" class="text-blue-600 hover:text-blue-800 font-medium disabled:opacity-60">Salvar</button>
                  <button @click="cancelEdit" class="text-gray-600 hover:text-gray-800 font-medium">Cancelar</button>
                </template>
                <template v-else>
                  <button @click="startEdit(item)" class="text-blue-600 hover:text-blue-800 font-medium">Editar</button>
                  <button @click="remove(item.id)" class="text-red-600 hover:text-red-800 font-medium">Excluir</button>
                </template>
              </div>
            </td>
          </tr>
          <tr v-if="items.length === 0">
            <td colspan="3" class="px-6 py-12 text-center text-gray-500">Nenhuma categoria cadastrada</td>
          </tr>
        </tbody>
      </table>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed, ref } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Shared/AdminLayout.vue';

const page = usePage();
const adminBase = computed(() => page.props?.paths?.admin || '/admin');

defineProps({
  items: {
    type: Array,
    default: () => [],
  },
});

const createForm = useForm({
  name: '',
});

const create = () => {
  createForm.post(`${adminBase.value}/blog/categories`, {
    onSuccess: () => createForm.reset(),
  });
};

const editingId = ref(null);
const editForm = useForm({
  name: '',
});

const startEdit = (item) => {
  editingId.value = item.id;
  editForm.defaults({
    name: item.name,
  });
  editForm.reset();
  editForm.clearErrors();
};

const cancelEdit = () => {
  editingId.value = null;
  editForm.reset();
  editForm.clearErrors();
};

const saveEdit = () => {
  if (!editingId.value) return;
  editForm.put(`${adminBase.value}/blog/categories/${editingId.value}`, {
    onSuccess: () => cancelEdit(),
  });
};

const remove = (id) => {
  router.delete(`${adminBase.value}/blog/categories/${id}`);
};
</script>
