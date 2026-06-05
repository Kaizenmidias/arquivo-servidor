<template>
  <AdminLayout>
    <template #pageTitle>Instagram</template>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <div class="bg-white rounded-xl shadow p-6 border border-gray-200">
        <h3 class="text-xl font-semibold text-gray-800 mb-6">Conectar</h3>

        <div class="space-y-6">
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Usuário (opcional)</label>
            <input v-model="form.instagram_username" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="conectaimovel">
            <div v-if="form.errors.instagram_username" class="text-sm text-red-600 mt-1">{{ form.errors.instagram_username }}</div>
          </div>

          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">User ID</label>
            <input v-model="form.instagram_user_id" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="1784...">
            <div v-if="form.errors.instagram_user_id" class="text-sm text-red-600 mt-1">{{ form.errors.instagram_user_id }}</div>
          </div>

          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Access Token</label>
            <input v-model="form.instagram_access_token" type="password" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="IGQ...">
            <div v-if="form.errors.instagram_access_token" class="text-sm text-red-600 mt-1">{{ form.errors.instagram_access_token }}</div>
          </div>

          <div class="flex items-center gap-4">
            <button type="button" :disabled="form.processing" class="bg-blue-900 hover:bg-blue-800 disabled:opacity-60 text-white px-6 py-3 rounded-lg font-semibold transition" @click="save">
              Salvar
            </button>
            <button type="button" :disabled="refreshing" class="border border-gray-300 hover:bg-gray-50 disabled:opacity-60 text-gray-800 px-6 py-3 rounded-lg font-semibold transition" @click="refresh">
              Atualizar Feed
            </button>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow p-6 border border-gray-200">
        <h3 class="text-xl font-semibold text-gray-800 mb-6">Status</h3>

        <div class="space-y-3 text-sm text-gray-700">
          <div><span class="font-semibold">Mídias no cache:</span> {{ feedCount }}</div>
          <div v-if="lastRefresh"><span class="font-semibold">Última atualização:</span> {{ lastRefresh }}</div>
        </div>

        <div v-if="feedPreview.length > 0" class="mt-6 grid grid-cols-3 gap-3">
          <div v-for="(item, idx) in feedPreview" :key="idx" class="border border-gray-200 rounded-lg overflow-hidden">
            <img :src="item.thumbnail_url || item.media_url" class="w-full h-24 object-cover">
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed, ref } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Shared/AdminLayout.vue';

const page = usePage();
const adminBase = computed(() => page.props?.paths?.admin || '/admin');

const props = defineProps({
  settings: {
    type: Object,
    default: () => ({}),
  },
  instagramFeed: {
    type: Array,
    default: () => [],
  },
});

const form = useForm({
  instagram_username: props.settings?.instagram_username || '',
  instagram_user_id: props.settings?.instagram_user_id || '',
  instagram_access_token: props.settings?.instagram_access_token || '',
});

const refreshing = ref(false);

const save = () => {
  form.put(`${adminBase.value}/instagram`);
};

const refresh = () => {
  refreshing.value = true;
  router.post(`${adminBase.value}/instagram/refresh`, {}, { onFinish: () => (refreshing.value = false) });
};

const feedCount = computed(() => props.instagramFeed?.length || 0);
const feedPreview = computed(() => (props.instagramFeed || []).slice(0, 9));
const lastRefresh = computed(() => props.settings?.instagram_last_refresh || '');
</script>
