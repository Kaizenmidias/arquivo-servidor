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
          <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 space-y-4">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
              <div>
                <label class="block text-slate-800 text-sm font-semibold">Vínculos</label>
                <p class="mt-1 text-xs text-slate-500">Escolha uma ou mais categorias de imóveis para agrupar nesta Categoria Especial.</p>
              </div>
              <button
                type="button"
                class="inline-flex w-full md:w-auto items-center justify-center gap-2 rounded-lg border border-blue-300 bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700"
                @click="toggleCreatePicker"
              >
                <span>{{ showCreatePicker ? 'Fechar seletor' : 'Adicionar Categoria' }}</span>
              </button>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-4 space-y-3">
              <div v-if="selectedCreateTypes.length > 0" class="flex flex-wrap gap-2">
                <span
                  v-for="type in selectedCreateTypes"
                  :key="type.id"
                  class="inline-flex items-center gap-2 rounded-full bg-slate-100 border border-slate-200 px-3 py-1.5 text-sm text-slate-700"
                >
                  <span>{{ formatPropertyType(type) }}</span>
                  <button type="button" class="text-slate-400 hover:text-red-600" @click="removeCreateType(type.id)">×</button>
                </span>
              </div>
              <div v-else class="text-sm text-slate-500">Nenhum vínculo adicionado ainda.</div>

              <transition name="fade">
                <div v-if="showCreatePicker" class="rounded-xl border border-slate-200 bg-slate-50 p-4 shadow-sm">
                  <div class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Selecione uma ou mais categorias</div>
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-56 overflow-y-auto pr-1">
                    <button
                      v-for="type in availableCreateTypes"
                      :key="type.id"
                      type="button"
                      class="text-left rounded-lg border px-3 py-2 text-sm transition"
                      :class="selectedCreateIds.includes(type.id) ? 'border-slate-900 bg-slate-900 text-white' : 'border-gray-200 bg-white text-gray-700 hover:border-gray-300 hover:bg-gray-50'"
                      @click="toggleCreateType(type.id)"
                    >
                      {{ formatPropertyType(type) }}
                    </button>
                    <div v-if="availableCreateTypes.length === 0" class="col-span-full text-sm text-slate-500">
                      Todas as categorias já foram selecionadas.
                    </div>
                  </div>
                </div>
              </transition>
            </div>
          </div>
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
                <div class="space-y-3">
                  <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                      <label class="block text-slate-800 text-sm font-semibold">Vínculos</label>
                      <p class="mt-1 text-xs text-slate-500">Adicione ou remova categorias vinculadas a esta Categoria Especial.</p>
                    </div>
                    <button
                      type="button"
                      class="inline-flex w-full md:w-auto items-center justify-center gap-2 rounded-lg border border-blue-300 bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700"
                      @click="toggleEditPicker(item.id)"
                    >
                      {{ openPickerId === item.id ? 'Fechar seletor' : 'Adicionar Categoria' }}
                    </button>
                  </div>

                  <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <div v-if="selectedEditTypes.length > 0" class="flex flex-wrap gap-2">
                      <span
                        v-for="type in selectedEditTypes"
                        :key="type.id"
                        class="inline-flex items-center gap-2 rounded-full bg-slate-100 border border-slate-200 px-3 py-1.5 text-sm text-slate-700"
                      >
                        <span>{{ formatPropertyType(type) }}</span>
                        <button type="button" class="text-slate-400 hover:text-red-600" @click="removeEditType(type.id)">×</button>
                      </span>
                    </div>
                    <div v-else class="text-sm text-slate-500">Nenhum vínculo adicionado ainda.</div>

                    <transition name="fade">
                      <div v-if="openPickerId === item.id" class="mt-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Selecione uma ou mais categorias</div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-56 overflow-y-auto pr-1">
                          <button
                            v-for="type in availableEditTypes"
                            :key="type.id"
                            type="button"
                            class="text-left rounded-lg border px-3 py-2 text-sm transition"
                            :class="editForm.property_type_ids.includes(type.id) ? 'border-slate-900 bg-slate-900 text-white' : 'border-gray-200 bg-white text-gray-700 hover:border-gray-300 hover:bg-gray-50'"
                            @click="toggleEditType(type.id)"
                          >
                            {{ formatPropertyType(type) }}
                          </button>
                          <div v-if="availableEditTypes.length === 0" class="col-span-full text-sm text-slate-500">
                            Todas as categorias já foram selecionadas.
                          </div>
                        </div>
                      </div>
                    </transition>
                  </div>
                </div>
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
const propertyTypes = computed(() => props.propertyTypes || []);

const showCreate = ref(false);
const createNameRef = ref(null);
const createCoverPreview = ref('');
const editingId = ref(null);
const editCoverPreview = ref('');
const showCreatePicker = ref(false);
const openPickerId = ref(null);

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

const selectedCreateIds = computed(() => Array.isArray(createForm.property_type_ids) ? createForm.property_type_ids.map((id) => Number(id)) : []);
const selectedCreateTypes = computed(() => propertyTypes.value.filter((type) => selectedCreateIds.value.includes(Number(type.id))));
const availableCreateTypes = computed(() => propertyTypes.value.filter((type) => !selectedCreateIds.value.includes(Number(type.id))));
const selectedEditIds = computed(() => Array.isArray(editForm.property_type_ids) ? editForm.property_type_ids.map((id) => Number(id)) : []);
const selectedEditTypes = computed(() => propertyTypes.value.filter((type) => selectedEditIds.value.includes(Number(type.id))));
const availableEditTypes = computed(() => propertyTypes.value.filter((type) => !selectedEditIds.value.includes(Number(type.id))));

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

const toggleCreatePicker = () => {
  showCreatePicker.value = !showCreatePicker.value;
};

const toggleCreateType = (id) => {
  const value = Number(id);
  const current = new Set(selectedCreateIds.value);
  if (current.has(value)) current.delete(value);
  else current.add(value);
  createForm.property_type_ids = [...current];
};

const removeCreateType = (id) => {
  const value = Number(id);
  createForm.property_type_ids = selectedCreateIds.value.filter((current) => Number(current) !== value);
};

const cancelCreate = () => {
  createForm.reset();
  createForm.clearErrors();
  createCoverPreview.value = '';
  showCreatePicker.value = false;
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
  openPickerId.value = null;
};

const cancelEdit = () => {
  editingId.value = null;
  editForm.reset();
  editForm.clearErrors();
  editCoverPreview.value = '';
  openPickerId.value = null;
};

const toggleEditPicker = (id) => {
  openPickerId.value = openPickerId.value === id ? null : id;
};

const toggleEditType = (id) => {
  const value = Number(id);
  const current = new Set(selectedEditIds.value);
  if (current.has(value)) current.delete(value);
  else current.add(value);
  editForm.property_type_ids = [...current];
};

const removeEditType = (id) => {
  const value = Number(id);
  editForm.property_type_ids = selectedEditIds.value.filter((current) => Number(current) !== value);
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
