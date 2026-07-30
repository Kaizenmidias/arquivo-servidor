<template>
  <AdminLayout>
    <template #pageTitle>Configurações</template>

    <div class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <section class="bg-white rounded-xl shadow border border-gray-200 p-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Informações do Site</h3>
          <div class="space-y-4">
            <div>
              <label class="block text-gray-700 mb-2 text-sm font-medium">Nome do Site</label>
              <input v-model="form.nome_empresa" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
            </div>
            <div>
              <label class="block text-gray-700 mb-2 text-sm font-medium">Telefone</label>
              <input v-model="form.telefone" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
            </div>
            <div>
              <label class="block text-gray-700 mb-2 text-sm font-medium">E-mail</label>
              <input v-model="form.email_contato" type="email" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
            </div>
            <div>
              <label class="block text-gray-700 mb-2 text-sm font-medium">WhatsApp do site</label>
              <input v-model="form.whatsapp_number" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="5511999999999" />
              <p class="text-xs text-gray-500 mt-2">Use apenas o número com DDI e DDD.</p>
            </div>
            <div>
              <label class="block text-gray-700 mb-2 text-sm font-medium">Mensagem do WhatsApp</label>
              <textarea v-model="form.whatsapp_message" rows="4" class="w-full border border-gray-300 rounded-lg px-4 py-3"></textarea>
            </div>
            <div>
              <label class="block text-gray-700 mb-2 text-sm font-medium">Endereço</label>
              <input v-model="form.endereco" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
            </div>
          </div>
        </section>

        <section class="bg-white rounded-xl shadow border border-gray-200 p-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Redes Sociais</h3>
          <div class="space-y-4">
            <div>
              <label class="block text-gray-700 mb-2 text-sm font-medium">Instagram</label>
              <input v-model="form.instagram_url" type="url" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
            </div>
            <div>
              <label class="block text-gray-700 mb-2 text-sm font-medium">Facebook</label>
              <input v-model="form.facebook_url" type="url" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
            </div>
            <div>
              <label class="block text-gray-700 mb-2 text-sm font-medium">LinkedIn</label>
              <input v-model="form.linkedin_url" type="url" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
            </div>
          </div>
        </section>
      </div>

      <section class="bg-white rounded-xl shadow border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Cores do Site</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Cor Principal</label>
            <div class="flex items-center gap-3">
              <input type="color" v-model="form.primary_color" class="w-12 h-10 border-2 border-gray-300 rounded cursor-pointer">
              <span class="text-gray-700 font-mono text-sm">{{ form.primary_color }}</span>
            </div>
          </div>
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Cor Secundária</label>
            <div class="flex items-center gap-3">
              <input type="color" v-model="form.secondary_color" class="w-12 h-10 border-2 border-gray-300 rounded cursor-pointer">
              <span class="text-gray-700 font-mono text-sm">{{ form.secondary_color }}</span>
            </div>
          </div>
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Cor dos Botões</label>
            <div class="flex items-center gap-3">
              <input type="color" v-model="form.button_color" class="w-12 h-10 border-2 border-gray-300 rounded cursor-pointer">
              <span class="text-gray-700 font-mono text-sm">{{ form.button_color }}</span>
            </div>
          </div>
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Cor do Rodapé</label>
            <div class="flex items-center gap-3">
              <input type="color" v-model="form.footer_bg_color" class="w-12 h-10 border-2 border-gray-300 rounded cursor-pointer">
              <span class="text-gray-700 font-mono text-sm">{{ form.footer_bg_color }}</span>
            </div>
          </div>
        </div>
      </section>

      <section class="bg-white rounded-xl shadow border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Arquivos</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Logotipo</label>
            <input ref="logoInput" type="file" accept="image/*" class="hidden" @change="onLogoChange" />
            <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-blue-400 transition cursor-pointer" @click="pickLogo">
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
              <p class="text-gray-600">Clique para enviar favicon</p>
              <div v-if="faviconPreviewUrl" class="mt-4 flex items-center justify-center">
                <img :src="faviconPreviewUrl" alt="Favicon" class="h-8 w-8 object-contain" />
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="bg-white rounded-xl shadow border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Fonte</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="md:col-span-2">
            <label class="block text-gray-700 mb-2 text-sm font-medium">Fonte do Site</label>
            <select v-model="form.font_family" class="w-full border border-gray-300 rounded-lg px-4 py-3">
              <option value="">Padrão</option>
              <option value="Instrument Sans, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif">Instrument Sans</option>
              <option value="system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif">System UI</option>
              <option value="Arial, sans-serif">Arial</option>
              <option value="Georgia, serif">Georgia</option>
            </select>
          </div>
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Tamanho do Texto (px)</label>
            <input v-model.number="form.font_size_text" type="number" min="10" max="24" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
          </div>
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Tamanho do Título (px)</label>
            <input v-model.number="form.font_size_title" type="number" min="18" max="72" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
          </div>
        </div>
      </section>

      <section v-if="isAdmin" class="bg-white rounded-xl shadow border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Links do Painel</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Link do Painel (prefixo)</label>
            <input v-model="form.admin_path" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
          </div>
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Link do Login (prefixo)</label>
            <input v-model="form.login_path" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
          </div>
        </div>
      </section>

      <div class="flex justify-end">
        <button type="button" :disabled="form.processing" class="bg-blue-900 hover:bg-blue-800 disabled:opacity-60 text-white px-8 py-3 rounded-lg font-semibold transition" @click="save">
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

const props = defineProps({
  settings: { type: Object, default: () => ({}) },
});

const page = usePage();
const adminBase = computed(() => page.props?.paths?.admin || '/admin');
const isAdmin = computed(() => page.props?.auth?.user?.role === 'admin');

const form = useForm({
  _method: 'put',
  nome_empresa: props.settings?.nome_empresa || '',
  telefone: props.settings?.telefone || '',
  email_contato: props.settings?.email_contato || '',
  whatsapp_number: props.settings?.whatsapp_number || props.settings?.whatsapp || '',
  whatsapp_message: props.settings?.whatsapp_message || 'Olá! Vim do site e tenho interesse em um imóvel',
  endereco: props.settings?.endereco || '',
  instagram_url: props.settings?.instagram_url || '',
  facebook_url: props.settings?.facebook_url || '',
  linkedin_url: props.settings?.linkedin_url || '',
  primary_color: props.settings?.primary_color || '#1e3a8a',
  secondary_color: props.settings?.secondary_color || '#f97316',
  button_color: props.settings?.button_color || props.settings?.secondary_color || '#f97316',
  footer_bg_color: props.settings?.footer_bg_color || '#111827',
  font_family: props.settings?.font_family || '',
  font_size_text: Number(props.settings?.font_size_text ?? 16),
  font_size_title: Number(props.settings?.font_size_title ?? 40),
  home_hero_overlay_color: props.settings?.home_hero_overlay_color || '#0f172a',
  home_hero_overlay_opacity: Number(props.settings?.home_hero_overlay_opacity ?? 70),
  properties_banner_title: props.settings?.properties_banner_title || 'Imóveis',
  properties_banner_subtitle: props.settings?.properties_banner_subtitle || '',
  properties_banner_title_color: props.settings?.properties_banner_title_color || '#ffffff',
  properties_banner_subtitle_color: props.settings?.properties_banner_subtitle_color || '#ffffff',
  properties_banner_overlay_color: props.settings?.properties_banner_overlay_color || '#0f172a',
  properties_banner_overlay_opacity: Number(props.settings?.properties_banner_overlay_opacity ?? 70),
  admin_path: props.settings?.admin_path || 'admin',
  login_path: props.settings?.login_path || 'login',
  recaptcha_site_key: props.settings?.recaptcha_site_key || '',
  recaptcha_secret_key: props.settings?.recaptcha_secret_key || '',
  logo_file: null,
  favicon_file: null,
  properties_banner_image_file: null,
});

const logoInput = ref(null);
const faviconInput = ref(null);
const logoPreviewUrl = computed(() => (form.logo_file instanceof File ? URL.createObjectURL(form.logo_file) : (props.settings?.logo_url || '')));
const faviconPreviewUrl = computed(() => (form.favicon_file instanceof File ? URL.createObjectURL(form.favicon_file) : (props.settings?.favicon_url || '')));
const pickLogo = () => logoInput.value?.click();
const pickFavicon = () => faviconInput.value?.click();

const onLogoChange = (event) => {
  form.logo_file = event?.target?.files?.[0] || null;
};

const onFaviconChange = (event) => {
  form.favicon_file = event?.target?.files?.[0] || null;
};

const save = () => {
  form.post(`${adminBase.value}/settings`, { forceFormData: true, preserveScroll: true });
};
</script>
