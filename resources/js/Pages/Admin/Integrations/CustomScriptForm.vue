<template>
  <AdminLayout>
    <template #pageTitle>Integrações - {{ mode === 'edit' ? 'Editar código' : 'Novo código' }}</template>

    <div class="mx-auto max-w-5xl space-y-6">
      <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4">
          <div>
            <h1 class="text-2xl font-bold text-slate-900">
              {{ mode === 'edit' ? 'Editar código personalizado' : 'Criar código personalizado' }}
            </h1>
            <p class="mt-2 max-w-3xl text-sm text-slate-500">
              O código é renderizado apenas no frontend público. No painel, ele é exibido em editor próprio e nunca executado.
            </p>
          </div>

          <Link
            :href="`${adminBase}/integracoes/codigo-personalizado`"
            class="rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
          >
            Voltar
          </Link>
        </div>
      </div>

      <form class="space-y-6" @submit.prevent="save">
        <section class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
          <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
              <label class="mb-2 block text-sm font-medium text-slate-700">Nome interno</label>
              <input
                v-model="form.name"
                type="text"
                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-900/10"
              />
              <div v-if="form.errors.name" class="mt-2 text-sm text-red-600">{{ form.errors.name }}</div>
            </div>

            <div class="md:col-span-2">
              <label class="mb-2 block text-sm font-medium text-slate-700">Descrição</label>
              <input
                v-model="form.description"
                type="text"
                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-900/10"
              />
              <div v-if="form.errors.description" class="mt-2 text-sm text-red-600">{{ form.errors.description }}</div>
            </div>

            <div>
              <label class="mb-2 block text-sm font-medium text-slate-700">Local de inserção</label>
              <select
                v-model="form.location"
                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-900/10"
              >
                <option v-for="(label, value) in locationOptions" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
              <div v-if="form.errors.location" class="mt-2 text-sm text-red-600">{{ form.errors.location }}</div>
            </div>

            <div>
              <label class="mb-2 block text-sm font-medium text-slate-700">Escopo</label>
              <select
                v-model="form.scope"
                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-900/10"
              >
                <option v-for="(label, value) in scopeOptions" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
              <div v-if="form.errors.scope" class="mt-2 text-sm text-red-600">{{ form.errors.scope }}</div>
            </div>

            <div v-if="form.scope === 'page'" class="md:col-span-2">
              <label class="mb-2 block text-sm font-medium text-slate-700">Página</label>
              <select
                v-model="form.page_target"
                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-900/10"
              >
                <option value="">Selecione uma página</option>
                <optgroup
                  v-for="group in pageGroups"
                  :key="group.name"
                  :label="group.name"
                >
                  <option v-for="option in group.options" :key="option.value" :value="option.value">
                    {{ option.label }}
                  </option>
                </optgroup>
              </select>
              <div v-if="form.errors.page_target" class="mt-2 text-sm text-red-600">{{ form.errors.page_target }}</div>
            </div>

            <div class="md:col-span-2">
              <label class="mb-2 block text-sm font-medium text-slate-700">Status</label>
              <label class="flex cursor-pointer items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                <span class="text-sm font-medium text-slate-700">Ativo</span>
                <input v-model="form.is_active" type="checkbox" class="h-5 w-5 rounded border-slate-300 text-slate-900 focus:ring-slate-900" />
              </label>
              <div v-if="form.errors.is_active" class="mt-2 text-sm text-red-600">{{ form.errors.is_active }}</div>
            </div>
          </div>
        </section>

        <section class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
          <div class="mb-4 flex items-center justify-between gap-4">
            <div>
              <h2 class="text-lg font-bold text-slate-900">Editor de código</h2>
              <p class="mt-1 text-sm text-slate-500">Você pode colar HTML, JavaScript ou CSS conforme a necessidade da campanha.</p>
            </div>
          </div>

          <CodeEditor v-model="form.code" language="html" height="480px" />
          <div v-if="form.errors.code" class="mt-2 text-sm text-red-600">{{ form.errors.code }}</div>
        </section>

        <div class="flex flex-wrap items-center gap-3">
          <button
            type="submit"
            class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60"
            :disabled="form.processing"
          >
            {{ mode === 'edit' ? 'Salvar alterações' : 'Criar código' }}
          </button>
          <Link
            :href="`${adminBase}/integracoes/codigo-personalizado`"
            class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
          >
            Cancelar
          </Link>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed, watch } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Shared/AdminLayout.vue';
import CodeEditor from '@/Shared/CodeEditor.vue';

const page = usePage();
const adminBase = computed(() => page.props?.paths?.admin || '/admin');

const props = defineProps({
  mode: {
    type: String,
    default: 'create',
  },
  script: {
    type: Object,
    default: null,
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

const pageGroups = computed(() => {
  const grouped = {};

  (props.pageOptions || []).forEach((option) => {
    const groupName = option.group || 'Páginas';
    if (!grouped[groupName]) {
      grouped[groupName] = [];
    }
    grouped[groupName].push(option);
  });

  return Object.entries(grouped).map(([name, options]) => ({ name, options }));
});

const form = useForm({
  name: props.script?.name || '',
  description: props.script?.description || '',
  location: props.script?.location || 'head',
  scope: props.script?.scope || 'sitewide',
  page_target: props.script?.page_target || '',
  is_active: props.script?.is_active ?? true,
  code: props.script?.code || '',
});

function save() {
  const baseUrl = `${adminBase.value}/integracoes/codigo-personalizado`;

  if (props.mode === 'edit' && props.script?.id) {
    form
      .transform((data) => ({
        ...data,
        _method: 'put',
      }))
      .post(`${baseUrl}/${props.script.id}`, {
        forceFormData: true,
        preserveScroll: true,
      });
    return;
  }

  form.post(baseUrl, {
    forceFormData: true,
    preserveScroll: true,
  });
}

watch(
  () => form.scope,
  (scope) => {
    if (scope !== 'page') {
      form.page_target = '';
    }
  }
);
</script>
