<template>
  <AdminLayout>
    <template #pageTitle>Integrações - Código Personalizado</template>

    <div class="space-y-6">
      <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4">
          <div>
            <h1 class="text-2xl font-bold text-slate-900">Código Personalizado</h1>
            <p class="mt-2 max-w-3xl text-sm text-slate-500">
              Crie scripts para Head, Body inicial ou Body final, com escopo global ou aplicado a páginas específicas.
            </p>
          </div>

          <Link
            :href="`${adminBase}/integracoes/codigo-personalizado/criar`"
            class="inline-flex items-center rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800"
          >
            Novo código
          </Link>
        </div>
      </div>

      <div class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
        <form class="flex flex-col gap-4 md:flex-row md:items-end" @submit.prevent="applySearch">
          <div class="flex-1">
            <label class="mb-2 block text-sm font-medium text-slate-700">Pesquisar por nome</label>
            <input
              v-model="searchTerm"
              type="text"
              placeholder="Ex.: tracking, banner, chat..."
              class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-900/10"
            />
          </div>
          <button
            type="submit"
            class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800"
          >
            Pesquisar
          </button>
          <Link
            v-if="searchTerm"
            :href="`${adminBase}/integracoes/codigo-personalizado`"
            class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
          >
            Limpar
          </Link>
        </form>
      </div>

      <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
        <div v-if="scripts.length === 0" class="px-6 py-16 text-center">
          <div class="mx-auto max-w-md">
            <h2 class="text-lg font-bold text-slate-900">Nenhum código encontrado</h2>
            <p class="mt-2 text-sm text-slate-500">
              Use o botão “Novo código” para criar scripts personalizados e controlar a inserção no frontend.
            </p>
          </div>
        </div>

        <div v-else class="divide-y divide-slate-100">
          <article
            v-for="script in scripts"
            :key="script.id"
            class="p-6 transition hover:bg-slate-50/60"
          >
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
              <div class="min-w-0 space-y-3">
                <div class="flex flex-wrap items-center gap-2">
                  <h2 class="text-lg font-bold text-slate-900">{{ script.name }}</h2>
                  <span
                    class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em]"
                    :class="script.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'"
                  >
                    {{ script.is_active ? 'Ativo' : 'Inativo' }}
                  </span>
                </div>

                <p v-if="script.description" class="max-w-3xl text-sm text-slate-500">
                  {{ script.description }}
                </p>

                <div class="flex flex-wrap gap-2 text-xs font-semibold uppercase tracking-[0.14em]">
                  <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-600">{{ locationLabel(script.location) }}</span>
                  <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-600">{{ scopeLabel(script.scope) }}</span>
                  <span v-if="script.page_target" class="rounded-full bg-slate-100 px-3 py-1 text-slate-600">
                    {{ pageTargetLabel(script.page_target) }}
                  </span>
                </div>
              </div>

              <div class="flex flex-wrap items-center gap-2">
                <Link
                  :href="`${adminBase}/integracoes/codigo-personalizado/${script.id}/editar`"
                  class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                >
                  Editar
                </Link>
                <button
                  type="button"
                  class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                  @click="duplicateScript(script)"
                >
                  Duplicar
                </button>
                <button
                  type="button"
                  class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold transition"
                  :class="script.is_active ? 'text-amber-700 hover:bg-amber-50' : 'text-emerald-700 hover:bg-emerald-50'"
                  @click="toggleScript(script)"
                >
                  {{ script.is_active ? 'Desativar' : 'Ativar' }}
                </button>
                <button
                  type="button"
                  class="rounded-2xl border border-red-200 px-4 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50"
                  @click="deleteScript(script)"
                >
                  Excluir
                </button>
              </div>
            </div>
          </article>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Shared/AdminLayout.vue';

const page = usePage();
const adminBase = computed(() => page.props?.paths?.admin || '/admin');

const props = defineProps({
  scripts: {
    type: Array,
    default: () => [],
  },
  search: {
    type: String,
    default: '',
  },
  locationOptions: {
    type: Object,
    default: () => ({}),
  },
  scopeOptions: {
    type: Object,
    default: () => ({}),
  },
  pageOptions: {
    type: Array,
    default: () => [],
  },
});

const searchTerm = ref(props.search || '');

const pageTargetLabels = computed(() => Object.fromEntries((props.pageOptions || []).map((option) => [option.value, option.label])));

function applySearch() {
  router.get(`${adminBase.value}/integracoes/codigo-personalizado`, {
    search: searchTerm.value || undefined,
  }, {
    preserveScroll: true,
    preserveState: true,
    replace: true,
  });
}

function locationLabel(location) {
  return props.locationOptions?.[location] || location;
}

function scopeLabel(scope) {
  return props.scopeOptions?.[scope] || scope;
}

function pageTargetLabel(value) {
  return pageTargetLabels.value?.[value] || value;
}

function duplicateScript(script) {
  router.post(`${adminBase.value}/integracoes/codigo-personalizado/${script.id}/duplicar`, {}, {
    preserveScroll: true,
  });
}

function toggleScript(script) {
  router.patch(`${adminBase.value}/integracoes/codigo-personalizado/${script.id}/toggle`, {}, {
    preserveScroll: true,
  });
}

function deleteScript(script) {
  if (!window.confirm(`Excluir o código "${script.name}"?`)) return;

  router.delete(`${adminBase.value}/integracoes/codigo-personalizado/${script.id}`, {
    preserveScroll: true,
  });
}
</script>
