<template>
  <AdminLayout>
    <template #pageTitle>Categorias Especiais</template>

    <div class="flex items-center justify-end mb-4">
      <button type="button" class="bg-blue-900 hover:bg-blue-800 text-white px-5 py-2.5 rounded-lg font-semibold transition" @click="toggleCreate">
        Nova categoria especial
      </button>
    </div>

    <div v-if="showCreate" class="bg-white rounded-xl shadow p-6 border border-gray-200 mb-8">
      <h3 class="text-lg font-semibold text-gray-800 mb-4">Adicionar</h3>
      <form @submit.prevent="create" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <div>
          <label class="block text-gray-700 mb-2 text-sm font-medium">Nome</label>
          <input ref="createNameRef" v-model="createForm.name" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Imóveis Residenciais">
          <div v-if="createForm.errors.name" class="text-sm text-red-600 mt-1">{{ createForm.errors.name }}</div>
        </div>

        <div>
          <label class="block text-gray-700 mb-2 text-sm font-medium">Ordem</label>
          <input v-model.number="createForm.sort_order" type="number" min="0" class="w-full border border-gray-300 rounded-lg px-4 py-3">
        </div>

        <div class="flex items-center gap-2">
          <label class="flex items-center gap-2 text-sm text-gray-700">
            <input v-model="createForm.is_active" type="checkbox" class="rounded border-gray-300">
            Ativo
          </label>
        </div>

        <div>
          <button type="submit" :disabled="createForm.processing" class="bg-blue-900 hover:bg-blue-800 disabled:opacity-60 text-white px-6 py-3 rounded-lg font-semibold transition">
            Salvar
          </button>
          <button type="button" class="ml-3 text-gray-700 hover:text-gray-900 font-semibold" @click="cancelCreate">
            Cancelar
          </button>
        </div>

        <div class="md:col-span-4">
          <label class="block text-gray-700 mb-2 text-sm font-medium">Tipos de imóvel vinculados</label>
          <select v-model="createForm.property_type_ids" multiple class="w-full min-h-40 border border-gray-300 rounded-lg px-4 py-3 bg-white">
            <option v-for="type in propertyTypes" :key="type.id" :value="type.id">
              {{ formatPropertyType(type) }}
            </option>
          </select>
          <div class="mt-2 text-xs text-gray-500">Segure Ctrl/Cmd para selecionar mais de um tipo.</div>
        </div>

        <div class="md:col-span-4">
          <label class="block text-gray-700 mb-2 text-sm font-medium">Descrição</label>
          <textarea v-model="createForm.description" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Descrição (opcional)"></textarea>
        </div>

        <div class="md:col-span-4">
          <label class="block text-gray-700 mb-2 text-sm font-medium">Capa</label>
          <input type="file" accept="image/*" class="w-full border border-gray-300 rounded-lg px-4 py-3" @change="onCreateCoverChange">
          <div v-if="createCoverPreview" class="mt-3">
            <img :src="createCoverPreview" alt="Preview da capa" class="h-40 w-full max-w-sm rounded-xl object-cover border border-gray-200">
          </div>
        </div>
      </form>
    </div>

    <div class="bg-white rounded-xl shadow border border-gray-200 overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Nome</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Slug</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Vínculos</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Ordem</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Status</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Ações</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 align-top">
              <template v-if="editingId === item.id">
                <input v-model="editForm.name" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                <div v-if="editForm.errors.name" class="text-sm text-red-600 mt-1">{{ editForm.errors.name }}</div>
                <textarea v-model="editForm.description" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 mt-2" placeholder="Descrição"></textarea>
                <input type="file" accept="image/*" class="w-full border border-gray-300 rounded-lg px-3 py-2 mt-2" @change="onEditCoverChange">
                <div v-if="editCoverPreview" class="mt-2">
                  <img :src="editCoverPreview" alt="Preview da capa" class="h-24 w-40 rounded-lg object-cover border border-gray-200">
                </div>
              </template>
              <template v-else>
                <div class="font-semibold text-gray-800">{{ item.name }}</div>
                <div v-if="item.description" class="text-sm text-gray-500 mt-1">{{ item.description }}</div>
                <img v-if="item.cover_url" :src="item.cover_url" alt="" class="mt-3 h-20 w-32 rounded-lg object-cover border border-gray-200">
              </template>
            </td>
            <td class="px-6 py-4 text-gray-600 align-top">{{ item.slug }}</td>
            <td class="px-6 py-4 align-top">
              <template v-if="editingId === item.id">
                <select v-model="editForm.property_type_ids" multiple class="w-full min-h-40 border border-gray-300 rounded-lg px-3 py-2 bg-white">
                  <option v-for="type in propertyTypes" :key="type.id" :value="type.id">
                    {{ formatPropertyType(type) }}
                  </option>
                </select>
              </template>
              <template v-else>
                <div class="flex flex-wrap gap-2">
                  <span v-for="type in item.property_types || item.propertyTypes || []" :key="type.id" class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-semibold">
                    {{ formatPropertyType(type) }}
                  </span>
                  <span v-if="!(item.property_types || item.propertyTypes || []).length" class="text-gray-400 text-sm">Sem vínculos</span>
                </div>
              </template>
            </td>
            <td class="px-6 py-4 align-top">
              <template v-if="editingId === item.id">
                <input v-model.number="editForm.sort_order" type="number" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2">
              </template>
              <template v-else>
                <span class="text-gray-700">{{ item.sort_order }}</span>
              </template>
            </td>
            <td class="px-6 py-4 align-top">
              <template v-if="editingId === item.id">
                <label class="flex items-center gap-2 text-sm text-gray-700">
                  <input v-model="editForm.is_active" type="checkbox" class="rounded border-gray-300">
                  Ativo
                </label>
              </template>
              <template v-else>
                <span :class="item.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'" class="px-3 py-1 rounded-full text-xs font-semibold">
                  {{ item.is_active ? 'Ativo' : 'Inativo' }}
                </span>
              </template>
            </td>
            <td class="px-6 py-4 align-top">
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
            <td colspan="6" class="px-6 py-12 text-center text-gray-500">Nenhuma categoria cadastrada</td>
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
const props = defineProps({
  items: { type: Array, default: () => [] },
  propertyTypes: { type: Array, default: () => [] },
});

const showCreate = ref(false);
const createNameRef = ref(null);
const createCoverPreview = ref('');
const editingId = ref(null);
const editCoverPreview = ref('');

const createForm = useForm({
  name: '',
  description: '',
  cover: null,
  is_active: true,
  sort_order: 0,
  property_type_ids: [],
});

const editForm = useForm({
  name: '',
  description: '',
  cover: null,
  is_active: true,
  sort_order: 0,
  property_type_ids: [],
});

const formatPropertyType = (type) => {
  if (!type) return '-';
  return type.nome_subtipo ? `${type.nome_tipo} / ${type.nome_subtipo}` : type.nome_tipo;
};

const toggleCreate = async () => {
  showCreate.value = !showCreate.value;
  if (showCreate.value) {
    await nextTick();
    createNameRef.value?.focus?.();
  }
};

const cancelCreate = () => {
  createForm.reset();
  createForm.clearErrors();
  createCoverPreview.value = '';
  showCreate.value = false;
};

const create = () => {
  createForm.post(`${adminBase.value}/categories/special`, {
    forceFormData: true,
    onSuccess: () => cancelCreate(),
  });
};

const readFilePreview = (file, callback) => {
  if (!(file instanceof File)) {
    callback('');
    return;
  }

  const reader = new FileReader();
  reader.onload = (event) => callback(String(event.target?.result || ''));
  reader.readAsDataURL(file);
};

const onCreateCoverChange = (event) => {
  const file = event.target?.files?.[0] || null;
  createForm.cover = file;
  readFilePreview(file, (result) => {
    createCoverPreview.value = result;
  });
};

const onEditCoverChange = (event) => {
  const file = event.target?.files?.[0] || null;
  editForm.cover = file;
  readFilePreview(file, (result) => {
    editCoverPreview.value = result;
  });
};

const startEdit = (item) => {
  editingId.value = item.id;
  editForm.defaults({
    name: item.name,
    description: item.description ?? '',
    cover: null,
    is_active: item.is_active,
    sort_order: item.sort_order,
    property_type_ids: (item.property_types || item.propertyTypes || []).map((type) => type.id),
  });
  editForm.reset();
  editForm.clearErrors();
  editCoverPreview.value = item.cover_url || '';
};

const cancelEdit = () => {
  editingId.value = null;
  editForm.reset();
  editForm.clearErrors();
  editCoverPreview.value = '';
};

const saveEdit = () => {
  if (!editingId.value) return;
  editForm
    .transform((data) => ({ ...data, _method: 'put' }))
    .post(`${adminBase.value}/categories/special/${editingId.value}`, {
      forceFormData: true,
      onSuccess: () => cancelEdit(),
    });
};

const remove = (id) => {
  router.delete(`${adminBase.value}/categories/special/${id}`);
};
</script>
