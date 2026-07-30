<template>
  <AdminLayout>
    <template #pageTitle>Páginas</template>
    
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-xl font-semibold text-gray-800">Lista de Páginas</h3>
      <Link :href="`${adminBase}/pages/create`" class="bg-blue-900 hover:bg-blue-800 text-white px-6 py-2 rounded-lg font-semibold transition">
        Nova Página
      </Link>
    </div>

    <div class="bg-white rounded-xl shadow border border-gray-200 overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Página</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Slug</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Status</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Ações</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="page in pages" :key="page.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 font-semibold text-gray-800">{{ page.titulo }}</td>
            <td class="px-6 py-4 text-gray-600">{{ page.slug }}</td>
            <td class="px-6 py-4">
              <span :class="page.ativo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'" class="px-3 py-1 rounded-full text-xs font-semibold">
                {{ page.ativo ? 'Ativo' : 'Inativo' }}
              </span>
            </td>
            <td class="px-6 py-4">
              <div class="flex items-center gap-4">
                <Link :href="`${adminBase}/pages/${page.id}`" class="text-blue-600 hover:text-blue-800 font-medium">Editar</Link>
                <button type="button" class="text-gray-700 hover:text-gray-900 font-medium" @click="duplicate(page.id)">Duplicar</button>
                <button type="button" class="text-red-600 hover:text-red-800 font-medium" @click="remove(page.id)">Excluir</button>
              </div>
            </td>
          </tr>
          <tr v-if="pages.length === 0">
            <td colspan="4" class="px-6 py-12 text-center text-gray-500">Nenhuma página cadastrada</td>
          </tr>
        </tbody>
      </table>
    </div>

    <section class="mt-8 bg-white rounded-xl shadow border border-gray-200 p-6">
      <h3 class="text-lg font-semibold text-gray-800 mb-4">Banner da página de imóveis</h3>
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="space-y-5">
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Título</label>
            <input v-model="form.properties_banner_title" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
          </div>
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Subtítulo</label>
            <input v-model="form.properties_banner_subtitle" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 items-end">
            <div>
              <label class="block text-gray-700 mb-2 text-sm font-medium">Cor do Título</label>
              <div class="flex items-center gap-3">
                <input type="color" v-model="form.properties_banner_title_color" class="w-12 h-10 border-2 border-gray-300 rounded cursor-pointer">
                <span class="text-gray-700 font-mono text-sm">{{ form.properties_banner_title_color }}</span>
              </div>
            </div>
            <div>
              <label class="block text-gray-700 mb-2 text-sm font-medium">Cor do Subtítulo</label>
              <div class="flex items-center gap-3">
                <input type="color" v-model="form.properties_banner_subtitle_color" class="w-12 h-10 border-2 border-gray-300 rounded cursor-pointer">
                <span class="text-gray-700 font-mono text-sm">{{ form.properties_banner_subtitle_color }}</span>
              </div>
            </div>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 items-end">
            <div>
              <label class="block text-gray-700 mb-2 text-sm font-medium">Cor do Overlay</label>
              <div class="flex items-center gap-3">
                <input type="color" v-model="form.properties_banner_overlay_color" class="w-12 h-10 border-2 border-gray-300 rounded cursor-pointer">
                <span class="text-gray-700 font-mono text-sm">{{ form.properties_banner_overlay_color }}</span>
              </div>
            </div>
            <div>
              <label class="block text-gray-700 mb-2 text-sm font-medium">Opacidade do Overlay (%)</label>
              <input v-model.number="form.properties_banner_overlay_opacity" type="number" min="0" max="100" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
            </div>
          </div>
        </div>
        <div>
          <label class="block text-gray-700 mb-2 text-sm font-medium">Imagem do Banner</label>
          <input ref="propertiesBannerInput" type="file" accept="image/*" class="hidden" @change="onPropertiesBannerChange" />
          <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-blue-400 transition cursor-pointer" @click="pickPropertiesBanner">
            <p class="text-gray-600">Clique para enviar imagem do banner</p>
            <div v-if="propertiesBannerPreviewUrl" class="mt-4 flex items-center justify-center">
              <img :src="propertiesBannerPreviewUrl" alt="Banner Imóveis" class="h-32 w-full object-cover rounded-lg" />
            </div>
          </div>
        </div>
      </div>
      <div class="flex justify-end mt-6">
        <button type="button" :disabled="form.processing" class="bg-blue-900 hover:bg-blue-800 disabled:opacity-60 text-white px-6 py-3 rounded-lg font-semibold transition" @click="saveAppearance">
          Salvar banner
        </button>
      </div>
    </section>
  </AdminLayout>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Shared/AdminLayout.vue';

const page = usePage();
const adminBase = computed(() => page.props?.paths?.admin || '/admin');

const props = defineProps({
  pages: {
    type: Array,
    default: () => [],
  },
  settings: {
    type: Object,
    default: () => ({}),
  },
});

const form = useForm({
  properties_banner_title: props.settings?.properties_banner_title || 'Imóveis',
  properties_banner_subtitle: props.settings?.properties_banner_subtitle || '',
  properties_banner_title_color: props.settings?.properties_banner_title_color || '#ffffff',
  properties_banner_subtitle_color: props.settings?.properties_banner_subtitle_color || '#ffffff',
  properties_banner_overlay_color: props.settings?.properties_banner_overlay_color || '#0f172a',
  properties_banner_overlay_opacity: Number(props.settings?.properties_banner_overlay_opacity ?? 70),
  properties_banner_image_file: null,
});

const propertiesBannerInput = ref(null);
const propertiesBannerPreviewUrl = computed(() => (form.properties_banner_image_file instanceof File ? URL.createObjectURL(form.properties_banner_image_file) : (props.settings?.properties_banner_image_url || '')));
const pickPropertiesBanner = () => propertiesBannerInput.value?.click();
const onPropertiesBannerChange = (event) => {
  form.properties_banner_image_file = event?.target?.files?.[0] || null;
};
const saveAppearance = () => {
  form.post(`${adminBase.value}/settings`, { forceFormData: true, preserveScroll: true });
};

const duplicate = (id) => {
  router.post(`${adminBase.value}/pages/${id}/duplicate`);
};

const remove = (id) => {
  router.delete(`${adminBase.value}/pages/${id}`);
};
</script>
