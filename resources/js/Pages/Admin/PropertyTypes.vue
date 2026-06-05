<template>
  <AdminLayout>
    <template #pageTitle>Tipos de Imóvel</template>

    <div class="flex items-center justify-end mb-4">
      <button type="button" class="bg-blue-900 hover:bg-blue-800 text-white px-5 py-2.5 rounded-lg font-semibold transition" @click="toggleCreate">
        Nova categoria (tipo)
      </button>
    </div>

    <div v-if="showCreate" class="bg-white rounded-xl shadow p-6 border border-gray-200 mb-8">
      <h3 class="text-lg font-semibold text-gray-800 mb-4">Adicionar</h3>

      <form @submit.prevent="create" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
        <div>
          <label class="block text-gray-700 mb-2 text-sm font-medium">Tipo</label>
          <input ref="createTipoRef" v-model="createForm.nome_tipo" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Casa, Apartamento...">
          <div v-if="createForm.errors.nome_tipo" class="text-sm text-red-600 mt-1">{{ createForm.errors.nome_tipo }}</div>
        </div>

        <div>
          <label class="block text-gray-700 mb-2 text-sm font-medium">Subtipo</label>
          <input v-model="createForm.nome_subtipo" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Cobertura, Studio...">
          <div v-if="createForm.errors.nome_subtipo" class="text-sm text-red-600 mt-1">{{ createForm.errors.nome_subtipo }}</div>
        </div>

        <div>
          <button type="button" class="text-gray-700 hover:text-gray-900 font-semibold mr-3" @click="cancelCreate">
            Cancelar
          </button>
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
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Tipo</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Subtipo</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Slug</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Ações</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50">
            <td class="px-6 py-4">
              <template v-if="editingId === item.id">
                <input v-model="editForm.nome_tipo" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                <div v-if="editForm.errors.nome_tipo" class="text-sm text-red-600 mt-1">{{ editForm.errors.nome_tipo }}</div>
              </template>
              <template v-else>
                <div class="font-semibold text-gray-800">{{ item.nome_tipo }}</div>
              </template>
            </td>
            <td class="px-6 py-4">
              <template v-if="editingId === item.id">
                <input v-model="editForm.nome_subtipo" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                <div v-if="editForm.errors.nome_subtipo" class="text-sm text-red-600 mt-1">{{ editForm.errors.nome_subtipo }}</div>
              </template>
              <template v-else>
                <span class="text-gray-700">{{ item.nome_subtipo || '-' }}</span>
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
            <td colspan="4" class="px-6 py-12 text-center text-gray-500">Nenhum tipo cadastrado</td>
          </tr>
        </tbody>
      </table>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed, nextTick, ref } from 'vue';
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
  nome_tipo: '',
  nome_subtipo: '',
});

const showCreate = ref(false);
const createTipoRef = ref(null);

const toggleCreate = async () => {
  showCreate.value = !showCreate.value;
  if (showCreate.value) {
    await nextTick();
    createTipoRef.value?.focus?.();
  }
};

const cancelCreate = () => {
  createForm.reset();
  createForm.clearErrors();
  showCreate.value = false;
};

const create = () => {
  createForm.post(`${adminBase.value}/categories/property-types`, {
    onSuccess: () => {
      createForm.reset();
      createForm.clearErrors();
      showCreate.value = false;
    },
  });
};

const editingId = ref(null);
const editForm = useForm({
  nome_tipo: '',
  nome_subtipo: '',
});

const startEdit = (item) => {
  editingId.value = item.id;
  editForm.defaults({
    nome_tipo: item.nome_tipo,
    nome_subtipo: item.nome_subtipo ?? '',
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
  editForm.put(`${adminBase.value}/categories/property-types/${editingId.value}`, {
    onSuccess: () => cancelEdit(),
  });
};

const remove = (id) => {
  router.delete(`${adminBase.value}/categories/property-types/${id}`);
};
</script>
