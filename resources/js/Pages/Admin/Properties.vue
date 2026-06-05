<template>
  <AdminLayout>
    <template #pageTitle>Imóveis</template>
    
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-xl font-semibold text-gray-800">Lista de Imóveis</h3>
      <div class="flex items-center gap-3">
        <Link
          v-if="!isTrash"
          :href="`${adminBase}/properties/trash`"
          class="bg-white hover:bg-gray-50 text-gray-800 border border-gray-200 px-6 py-2 rounded-lg font-semibold transition"
        >
          Lixeira
        </Link>
        <Link
          v-else
          :href="`${adminBase}/properties`"
          class="bg-white hover:bg-gray-50 text-gray-800 border border-gray-200 px-6 py-2 rounded-lg font-semibold transition"
        >
          Voltar
        </Link>
        <a href="/feed/imoveis.xml" target="_blank" rel="noopener" class="bg-white hover:bg-gray-50 text-gray-800 border border-gray-200 px-6 py-2 rounded-lg font-semibold transition">
          Exportar XML
        </a>
        <Link :href="`${adminBase}/properties/create`" class="bg-blue-900 hover:bg-blue-800 text-white px-6 py-2 rounded-lg font-semibold transition">
          Novo Imóvel
        </Link>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow border border-gray-200 p-4 mb-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">Categoria (Tipo do imóvel)</label>
          <select v-model="filters.property_type_id" class="w-full border border-gray-300 rounded-lg px-4 py-3 bg-white" @change="applyFilters">
            <option value="">Todas</option>
            <option v-for="t in propertyTypes" :key="t.id" :value="String(t.id)">
              {{ t.nome_subtipo ? `${t.nome_tipo} - ${t.nome_subtipo}` : t.nome_tipo }}
            </option>
          </select>
        </div>

        <div class="sm:col-span-1 lg:col-span-3 flex items-center justify-end gap-3">
          <button type="button" class="text-gray-700 hover:text-gray-900 font-semibold" @click="clearFilters">
            Limpar filtros
          </button>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow border border-gray-200 p-4 mb-6">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="text-sm text-gray-700">
          Selecionados: <span class="font-semibold">{{ selectedIds.length }}</span>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
          <select v-model="bulkAction" class="border border-gray-300 rounded-lg px-4 py-2.5 bg-white">
            <option value="">Ações em massa</option>
            <option v-if="!isTrash" value="delete">Mover para lixeira</option>
            <option v-if="!isTrash" value="activate">Ativar</option>
            <option v-if="!isTrash" value="deactivate">Inativar</option>
            <option v-if="isTrash" value="restore">Restaurar</option>
            <option v-if="isTrash" value="force_delete">Excluir permanentemente</option>
          </select>
          <button
            type="button"
            class="bg-gray-900 hover:bg-black text-white px-5 py-2.5 rounded-lg font-semibold transition disabled:opacity-60 disabled:cursor-not-allowed"
            :disabled="!bulkAction || selectedIds.length === 0"
            @click="applyBulk"
          >
            Aplicar
          </button>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow border border-gray-200 overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 w-10">
              <input type="checkbox" class="rounded border-gray-300" :checked="allSelected" @change="toggleSelectAll" />
            </th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Imóvel</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Tipo</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Negócio</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Preço</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Status</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Ações</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="property in properties" :key="property.id" class="hover:bg-gray-50">
            <td class="px-6 py-4">
              <input type="checkbox" class="rounded border-gray-300" :checked="selectedIds.includes(property.id)" @change="toggleSelected(property.id)" />
            </td>
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                  <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0 a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                  </svg>
                </div>
                <div>
                  <div class="font-semibold text-gray-800">{{ property.titulo }}</div>
                  <div class="text-sm text-gray-500">{{ property.endereco }}</div>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 text-gray-600">{{ property.propertyType?.nome_tipo || '-' }}</td>
            <td class="px-6 py-4">
              <span :class="operationBadgeClass(property.businessType?.name || property.operacao)" class="px-3 py-1 rounded-full text-xs font-semibold">
                {{ property.businessType?.name || property.operacao }}
              </span>
            </td>
            <td class="px-6 py-4 font-semibold text-gray-800">R$ {{ formatPrice(property.valor) }}</td>
            <td class="px-6 py-4">
              <span :class="property.ativo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'" class="px-3 py-1 rounded-full text-xs font-semibold">
                {{ property.ativo ? 'Ativo' : 'Inativo' }}
              </span>
            </td>
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <template v-if="!isTrash">
                  <Link :href="`${adminBase}/properties/${property.id}/edit`" class="text-blue-600 hover:text-blue-800 font-medium">Editar</Link>
                  <button type="button" class="text-gray-700 hover:text-gray-900 font-medium" @click="duplicate(property.id)">Duplicar</button>
                  <button type="button" class="text-red-600 hover:text-red-800 font-medium" @click="remove(property.id)">Excluir</button>
                </template>
                <template v-else>
                  <button type="button" class="text-green-700 hover:text-green-800 font-medium" @click="restore(property.id)">Restaurar</button>
                  <button type="button" class="text-red-600 hover:text-red-800 font-medium" @click="forceRemove(property.id)">Excluir</button>
                </template>
              </div>
            </td>
          </tr>
          <tr v-if="properties.length === 0">
            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
              Nenhum imóvel cadastrado ainda
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Shared/AdminLayout.vue';

const page = usePage();
const adminBase = computed(() => page.props?.paths?.admin || '/admin');

const props = defineProps({
  properties: {
    type: Array,
    default: () => [],
  },
  propertyTypes: {
    type: Array,
    default: () => [],
  },
  selectedPropertyTypeId: {
    type: [Number, String, null],
    default: null,
  },
  isTrash: {
    type: Boolean,
    default: false,
  },
});

const propertyTypes = computed(() => props.propertyTypes || []);
const isTrash = computed(() => !!props.isTrash);

const filters = reactive({
  property_type_id: props.selectedPropertyTypeId ? String(props.selectedPropertyTypeId) : '',
});

const selectedIds = ref([]);
const bulkAction = ref('');

watch(
  () => props.properties,
  () => {
    const currentIds = new Set((props.properties || []).map((p) => p.id));
    selectedIds.value = selectedIds.value.filter((id) => currentIds.has(id));
  }
);

const allSelected = computed(() => {
  if (!props.properties?.length) return false;
  return props.properties.every((p) => selectedIds.value.includes(p.id));
});

const formatPrice = (value) => {
  return Number(value || 0).toLocaleString('pt-BR');
};

const operationBadgeClass = (operation) => {
  if (operation === 'Comprar' || operation === 'Venda') return 'bg-green-100 text-green-700';
  if (operation === 'Alugar' || operation === 'Aluguel') return 'bg-blue-100 text-blue-700';
  return 'bg-orange-100 text-orange-700';
};

const remove = (id) => {
  router.delete(`${adminBase.value}/properties/${id}`);
};

const duplicate = (id) => {
  router.post(`${adminBase.value}/properties/${id}/duplicate`, {}, { preserveScroll: true });
};

const applyFilters = () => {
  const path = isTrash.value ? `${adminBase.value}/properties/trash` : `${adminBase.value}/properties`;
  router.get(path, { property_type_id: filters.property_type_id || undefined }, { preserveState: true, replace: true });
};

const clearFilters = () => {
  filters.property_type_id = '';
  applyFilters();
};

const restore = (id) => {
  router.post(`${adminBase.value}/properties/${id}/restore`, {}, { preserveScroll: true });
};

const forceRemove = (id) => {
  router.delete(`${adminBase.value}/properties/${id}/force`, { preserveScroll: true });
};

const toggleSelected = (id) => {
  const idx = selectedIds.value.indexOf(id);
  if (idx >= 0) {
    selectedIds.value.splice(idx, 1);
  } else {
    selectedIds.value.push(id);
  }
};

const toggleSelectAll = () => {
  if (allSelected.value) {
    selectedIds.value = [];
    return;
  }
  selectedIds.value = (props.properties || []).map((p) => p.id);
};

const applyBulk = () => {
  router.post(
    `${adminBase.value}/properties/bulk`,
    { action: bulkAction.value, ids: selectedIds.value },
    {
      preserveScroll: true,
      onSuccess: () => {
        selectedIds.value = [];
        bulkAction.value = '';
      },
    }
  );
};
</script>
