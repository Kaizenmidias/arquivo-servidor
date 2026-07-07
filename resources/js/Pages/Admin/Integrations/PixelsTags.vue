<template>
  <AdminLayout>
    <template #pageTitle>Integrações - Pixels e Tags</template>

    <div class="mx-auto max-w-5xl space-y-6">
      <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4">
          <div>
            <h1 class="text-2xl font-bold text-slate-900">Pixels e Tags</h1>
            <p class="mt-2 max-w-3xl text-sm text-slate-500">
              Gerencie pixels e tags sem editar arquivos do projeto. As integrações ativas são carregadas dinamicamente no frontend público.
            </p>
          </div>
          <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
            Alterações entram em vigor após salvar e serão refletidas no cache automaticamente.
          </div>
        </div>
      </div>

      <div class="space-y-6">
        <IntegrationCard
          title="Google Tag Manager"
          description="Insira o ID do container do GTM e controle a ativação da integração."
          :active="gtmForm.is_active"
        >
          <div class="grid grid-cols-1 gap-4 md:grid-cols-[minmax(0,1fr)_220px]">
            <div>
              <label class="mb-2 block text-sm font-medium text-slate-700">ID do GTM</label>
              <input
                v-model="gtmForm.external_id"
                type="text"
                placeholder="GTM-XXXXXXX"
                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-900/10"
              />
              <div v-if="gtmForm.errors.external_id" class="mt-2 text-sm text-red-600">
                {{ gtmForm.errors.external_id }}
              </div>
            </div>

            <div class="flex items-end">
              <label class="flex w-full cursor-pointer items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                <span class="text-sm font-medium text-slate-700">Ativar</span>
                <input v-model="gtmForm.is_active" type="checkbox" class="h-5 w-5 rounded border-slate-300 text-slate-900 focus:ring-slate-900" />
              </label>
            </div>
          </div>

          <template #footer>
            <button
              type="button"
              class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60"
              :disabled="gtmForm.processing"
              @click="saveProvider('gtm')"
            >
              Salvar GTM
            </button>
          </template>
        </IntegrationCard>

        <IntegrationCard
          title="Meta Pixel"
          description="Cadastre o Pixel ID e habilite o disparo do evento PageView no frontend público."
          :active="metaForm.is_active"
        >
          <div class="grid grid-cols-1 gap-4 md:grid-cols-[minmax(0,1fr)_220px]">
            <div>
              <label class="mb-2 block text-sm font-medium text-slate-700">Pixel ID</label>
              <input
                v-model="metaForm.external_id"
                type="text"
                placeholder="123456789012345"
                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-900/10"
              />
              <div v-if="metaForm.errors.external_id" class="mt-2 text-sm text-red-600">
                {{ metaForm.errors.external_id }}
              </div>
            </div>

            <div class="flex items-end">
              <label class="flex w-full cursor-pointer items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                <span class="text-sm font-medium text-slate-700">Ativar</span>
                <input v-model="metaForm.is_active" type="checkbox" class="h-5 w-5 rounded border-slate-300 text-slate-900 focus:ring-slate-900" />
              </label>
            </div>
          </div>

          <template #footer>
            <button
              type="button"
              class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60"
              :disabled="metaForm.processing"
              @click="saveProvider('meta_pixel')"
            >
              Salvar Meta Pixel
            </button>
          </template>
        </IntegrationCard>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Shared/AdminLayout.vue';
import IntegrationCard from '@/Pages/Admin/Integrations/Partials/IntegrationCard.vue';

const page = usePage();
const adminBase = computed(() => page.props?.paths?.admin || '/admin');

const props = defineProps({
  integrations: {
    type: Array,
    default: () => [],
  },
});

const integrationsMap = computed(() => Object.fromEntries((props.integrations || []).map((item) => [item.key, item])));

const gtmForm = useForm({
  external_id: integrationsMap.value.gtm?.external_id || '',
  is_active: !!integrationsMap.value.gtm?.is_active,
});

const metaForm = useForm({
  external_id: integrationsMap.value.meta_pixel?.external_id || '',
  is_active: !!integrationsMap.value.meta_pixel?.is_active,
});

const saveProvider = (provider) => {
  const form = provider === 'gtm' ? gtmForm : metaForm;

  form.put(`${adminBase.value}/integracoes/pixels-tags/${provider}`, {
    preserveScroll: true,
    onSuccess: () => form.clearErrors(),
  });
};

watch(
  () => props.integrations,
  () => {
    gtmForm.external_id = integrationsMap.value.gtm?.external_id || '';
    gtmForm.is_active = !!integrationsMap.value.gtm?.is_active;
    metaForm.external_id = integrationsMap.value.meta_pixel?.external_id || '';
    metaForm.is_active = !!integrationsMap.value.meta_pixel?.is_active;
  }
);
</script>
