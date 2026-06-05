<template>
  <AdminLayout>
    <template #pageTitle>Configurações</template>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="bg-white rounded-xl shadow border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Informações do Site</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Nome do Site</label>
            <input v-model="form.nome_empresa" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Imobiliária" />
            <div v-if="form.errors.nome_empresa" class="text-sm text-red-600 mt-1">{{ form.errors.nome_empresa }}</div>
          </div>
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Telefone</label>
            <input v-model="form.telefone" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="(00) 00000-0000" />
            <div v-if="form.errors.telefone" class="text-sm text-red-600 mt-1">{{ form.errors.telefone }}</div>
          </div>
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">E-mail</label>
            <input v-model="form.email_contato" type="email" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="contato@site.com" />
            <div v-if="form.errors.email_contato" class="text-sm text-red-600 mt-1">{{ form.errors.email_contato }}</div>
          </div>
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">WhatsApp</label>
            <input v-model="form.whatsapp" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="(00) 00000-0000" />
            <div v-if="form.errors.whatsapp" class="text-sm text-red-600 mt-1">{{ form.errors.whatsapp }}</div>
          </div>
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Endereço</label>
            <input v-model="form.endereco" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Rua, número - Cidade/UF" />
            <div v-if="form.errors.endereco" class="text-sm text-red-600 mt-1">{{ form.errors.endereco }}</div>
          </div>
          <div class="pt-4">
            <button type="button" :disabled="form.processing" class="bg-blue-900 hover:bg-blue-800 disabled:opacity-60 text-white px-6 py-2 rounded-lg font-semibold transition" @click="save">Salvar Alterações</button>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Redes Sociais</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Instagram</label>
            <input v-model="form.instagram_url" type="url" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="https://instagram.com/usuario" />
            <div v-if="form.errors.instagram_url" class="text-sm text-red-600 mt-1">{{ form.errors.instagram_url }}</div>
          </div>
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Facebook</label>
            <input v-model="form.facebook_url" type="url" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="https://facebook.com/usuario" />
            <div v-if="form.errors.facebook_url" class="text-sm text-red-600 mt-1">{{ form.errors.facebook_url }}</div>
          </div>
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">LinkedIn</label>
            <input v-model="form.linkedin_url" type="url" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="https://linkedin.com/in/usuario" />
            <div v-if="form.errors.linkedin_url" class="text-sm text-red-600 mt-1">{{ form.errors.linkedin_url }}</div>
          </div>
          <div class="pt-4">
            <button type="button" :disabled="form.processing" class="bg-blue-900 hover:bg-blue-800 disabled:opacity-60 text-white px-6 py-2 rounded-lg font-semibold transition" @click="save">Salvar Alterações</button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="isAdmin" class="bg-white rounded-xl shadow border border-gray-200 p-6 mt-6">
      <h3 class="text-lg font-semibold text-gray-800 mb-4">Links do Painel</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-gray-700 mb-2 text-sm font-medium">Link do Painel (prefixo)</label>
          <input v-model="form.admin_path" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="admin" />
          <div class="text-xs text-gray-500 mt-2">Exemplo: /{{ normalizedAdminPath }}</div>
          <div v-if="form.errors.admin_path" class="text-sm text-red-600 mt-1">{{ form.errors.admin_path }}</div>
        </div>
        <div>
          <label class="block text-gray-700 mb-2 text-sm font-medium">Link do Login (prefixo)</label>
          <input v-model="form.login_path" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="login" />
          <div class="text-xs text-gray-500 mt-2">Exemplo: /{{ normalizedLoginPath }}</div>
          <div v-if="form.errors.login_path" class="text-sm text-red-600 mt-1">{{ form.errors.login_path }}</div>
        </div>
      </div>

      <div class="pt-4">
        <button type="button" :disabled="form.processing" class="bg-blue-900 hover:bg-blue-800 disabled:opacity-60 text-white px-6 py-2 rounded-lg font-semibold transition" @click="save">
          Salvar Alterações
        </button>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Shared/AdminLayout.vue';

const props = defineProps({
  settings: {
    type: Object,
    default: () => ({}),
  },
});

const form = useForm({
  nome_empresa: props.settings?.nome_empresa || '',
  telefone: props.settings?.telefone || '',
  email_contato: props.settings?.email_contato || '',
  whatsapp: props.settings?.whatsapp || '',
  endereco: props.settings?.endereco || '',
  instagram_url: props.settings?.instagram_url || '',
  facebook_url: props.settings?.facebook_url || '',
  linkedin_url: props.settings?.linkedin_url || '',
  admin_path: props.settings?.admin_path || 'admin',
  login_path: props.settings?.login_path || 'login',
});

const page = usePage();
const adminBase = computed(() => page.props?.paths?.admin || '/admin');
const isAdmin = computed(() => page.props?.auth?.user?.role === 'admin');

const normalizedAdminPath = computed(() => String(form.admin_path || 'admin').replace(/^\/+|\/+$/g, '') || 'admin');
const normalizedLoginPath = computed(() => String(form.login_path || 'login').replace(/^\/+|\/+$/g, '') || 'login');

const save = () => {
  form.post(`${adminBase.value}/settings`, { method: 'put', forceFormData: true });
};
</script>
