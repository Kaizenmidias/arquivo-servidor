<template>
  <AdminLayout>
    <template #pageTitle>Aparência</template>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <div class="bg-white rounded-xl shadow p-6 border border-gray-200">
        <h3 class="text-xl font-semibold text-gray-800 mb-6">Cores do Site</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Cor Principal</label>
            <div class="flex gap-3 items-center">
              <input type="color" v-model="form.primary_color" class="w-12 h-10 border-2 border-gray-300 rounded cursor-pointer">
              <span class="text-gray-700 font-mono">{{ form.primary_color }}</span>
            </div>
          </div>
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Cor Secundária</label>
            <div class="flex gap-3 items-center">
              <input type="color" v-model="form.secondary_color" class="w-12 h-10 border-2 border-gray-300 rounded cursor-pointer">
              <span class="text-gray-700 font-mono">{{ form.secondary_color }}</span>
            </div>
          </div>
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Cor dos Botões</label>
            <div class="flex gap-3 items-center">
              <input type="color" v-model="form.button_color" class="w-12 h-10 border-2 border-gray-300 rounded cursor-pointer">
              <span class="text-gray-700 font-mono">{{ form.button_color }}</span>
            </div>
          </div>
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Cor do Rodapé</label>
            <div class="flex gap-3 items-center">
              <input type="color" v-model="form.footer_bg_color" class="w-12 h-10 border-2 border-gray-300 rounded cursor-pointer">
              <span class="text-gray-700 font-mono">{{ form.footer_bg_color }}</span>
            </div>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-xl shadow p-6 border border-gray-200">
        <h3 class="text-xl font-semibold text-gray-800 mb-6">Arquivos</h3>
        <div class="space-y-6">
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Logotipo</label>
            <input ref="logoInput" type="file" accept="image/*" class="hidden" @change="onLogoChange" />
            <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-blue-400 transition cursor-pointer" @click="pickLogo">
              <svg class="w-10 h-10 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
              </svg>
              <p class="text-gray-600">Clique para enviar logotipo</p>
              <div v-if="logoPreviewUrl" class="mt-4 flex items-center justify-center">
                <img :src="logoPreviewUrl" alt="Logo" class="h-12 object-contain" />
              </div>
            </div>
          </div>
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Favicon</label>
            <input ref="faviconInput" type="file" accept=".ico,image/png,image/jpeg,image/webp,image/svg+xml" class="hidden" @change="onFaviconChange" />
            <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-blue-400 transition cursor-pointer" @click="pickFavicon">
              <svg class="w-10 h-10 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
              </svg>
              <p class="text-gray-600">Clique para enviar favicon</p>
              <div v-if="faviconPreviewUrl" class="mt-4 flex items-center justify-center">
                <img :src="faviconPreviewUrl" alt="Favicon" class="h-8 w-8 object-contain" />
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-xl shadow p-6 border border-gray-200">
        <h3 class="text-xl font-semibold text-gray-800 mb-6">Fonte</h3>
        <div class="space-y-6">
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Fonte do Site</label>
            <select v-model="form.font_family" class="w-full border border-gray-300 rounded-lg px-4 py-3">
              <option value="">Padrão</option>
              <option value="Instrument Sans, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif">Instrument Sans</option>
              <option value="system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif">System UI</option>
              <option value="Arial, sans-serif">Arial</option>
              <option value="Georgia, serif">Georgia</option>
            </select>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
              <label class="block text-gray-700 mb-2 text-sm font-medium">Tamanho do Texto (px)</label>
              <input v-model.number="form.font_size_text" type="number" min="10" max="24" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
            </div>
            <div>
              <label class="block text-gray-700 mb-2 text-sm font-medium">Tamanho do Título (px)</label>
              <input v-model.number="form.font_size_title" type="number" min="18" max="72" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow p-6 border border-gray-200 lg:col-span-2">
        <h3 class="text-xl font-semibold text-gray-800 mb-6">Overlay do Hero (Home)</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 items-end">
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Cor do Overlay</label>
            <div class="flex gap-3 items-center">
              <input type="color" v-model="form.home_hero_overlay_color" class="w-12 h-10 border-2 border-gray-300 rounded cursor-pointer">
              <span class="text-gray-700 font-mono text-sm">{{ form.home_hero_overlay_color }}</span>
            </div>
          </div>
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Opacidade do Overlay (%)</label>
            <input v-model.number="form.home_hero_overlay_opacity" type="number" min="0" max="100" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
          </div>
        </div>
      </div>
      
      <div class="lg:col-span-2">
        <button @click="save" :disabled="form.processing" class="bg-blue-900 hover:bg-blue-800 disabled:opacity-60 text-white px-8 py-3 rounded-lg font-semibold transition">
          Salvar Alterações
        </button>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Shared/AdminLayout.vue';

const page = usePage();
const adminBase = computed(() => page.props?.paths?.admin || '/admin');

const props = defineProps({
  settings: {
    type: Object,
    default: () => ({}),
  },
});

const form = useForm({
  _method: 'put',
  primary_color: props.settings?.primary_color || '#1e3a8a',
  secondary_color: props.settings?.secondary_color || '#f97316',
  button_color: props.settings?.button_color || props.settings?.secondary_color || '#f97316',
  footer_bg_color: props.settings?.footer_bg_color || '#111827',
  font_family: props.settings?.font_family || '',
  font_size_text: Number(props.settings?.font_size_text ?? 16),
  font_size_title: Number(props.settings?.font_size_title ?? 40),
  home_hero_overlay_color: props.settings?.home_hero_overlay_color || '#0f172a',
  home_hero_overlay_opacity: Number(props.settings?.home_hero_overlay_opacity ?? 70),
  logo_file: null,
  favicon_file: null,
});

const logoInput = ref(null);
const faviconInput = ref(null);

const logoPreviewUrl = computed(() => {
  if (form.logo_file instanceof File) return URL.createObjectURL(form.logo_file);
  return props.settings?.logo_url || '';
});
const faviconPreviewUrl = computed(() => {
  if (form.favicon_file instanceof File) return URL.createObjectURL(form.favicon_file);
  return props.settings?.favicon_url || '';
});

const pickLogo = () => {
  logoInput.value?.click();
};
const pickFavicon = () => {
  faviconInput.value?.click();
};

const onLogoChange = (event) => {
  const file = event?.target?.files?.[0] || null;
  form.logo_file = file;
};
const onFaviconChange = (event) => {
  const file = event?.target?.files?.[0] || null;
  form.favicon_file = file;
};

const save = () => {
  form.post(`${adminBase.value}/appearance`, { forceFormData: true });
};
</script>
