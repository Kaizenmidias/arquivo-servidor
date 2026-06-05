<template>
  <AdminLayout>
    <template #pageTitle>Novo usuário</template>

    <div class="bg-white rounded-xl shadow border border-gray-200 p-6 max-w-2xl">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="md:col-span-2">
          <label class="block text-gray-700 mb-2 text-sm font-medium">Nome</label>
          <input v-model="form.name" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
          <div v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</div>
        </div>

        <div class="md:col-span-2">
          <label class="block text-gray-700 mb-2 text-sm font-medium">E-mail</label>
          <input v-model="form.email" type="email" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
          <div v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}</div>
        </div>

        <div>
          <label class="block text-gray-700 mb-2 text-sm font-medium">Perfil</label>
          <select v-model="form.role" class="w-full border border-gray-300 rounded-lg px-4 py-3 bg-white">
            <option value="user">Usuário</option>
            <option value="admin">Administrador</option>
          </select>
          <div v-if="form.errors.role" class="text-sm text-red-600 mt-1">{{ form.errors.role }}</div>
        </div>

        <div>
          <label class="block text-gray-700 mb-2 text-sm font-medium">Acesso ao painel</label>
          <select v-model="form.admin_enabled" class="w-full border border-gray-300 rounded-lg px-4 py-3 bg-white">
            <option :value="true">Ativo</option>
            <option :value="false">Bloqueado</option>
          </select>
          <div v-if="form.errors.admin_enabled" class="text-sm text-red-600 mt-1">{{ form.errors.admin_enabled }}</div>
        </div>

        <div class="md:col-span-2">
          <label class="block text-gray-700 mb-2 text-sm font-medium">Abas do painel (acessos)</label>
          <div class="border border-gray-300 rounded-lg p-4 bg-white">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <label v-for="opt in permissionOptions" :key="opt.key" class="flex items-center gap-3">
                <input v-model="form.permissions" type="checkbox" :value="opt.key" class="h-4 w-4" :disabled="isAdminSelected" />
                <span class="text-sm text-gray-800">{{ opt.label }}</span>
              </label>
            </div>
          </div>
          <div v-if="isAdminSelected" class="text-xs text-gray-500 mt-2">Administrador sempre tem acesso total.</div>
          <div v-if="form.errors.permissions" class="text-sm text-red-600 mt-1">{{ form.errors.permissions }}</div>
          <div v-if="form.errors['permissions.0']" class="text-sm text-red-600 mt-1">{{ form.errors['permissions.0'] }}</div>
        </div>

        <div class="md:col-span-2">
          <label class="block text-gray-700 mb-2 text-sm font-medium">Senha</label>
          <input v-model="form.password" type="password" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
          <div v-if="form.errors.password" class="text-sm text-red-600 mt-1">{{ form.errors.password }}</div>
        </div>

        <div class="md:col-span-2">
          <label class="block text-gray-700 mb-2 text-sm font-medium">Confirmar senha</label>
          <input v-model="form.password_confirmation" type="password" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
        </div>
      </div>

      <div class="flex items-center gap-3 mt-6">
        <button
          type="button"
          class="bg-blue-900 hover:bg-blue-800 disabled:opacity-60 text-white px-6 py-2 rounded-lg font-semibold transition"
          :disabled="form.processing"
          @click="save"
        >
          Criar usuário
        </button>
        <Link :href="`${adminBase}/users`" class="text-gray-700 hover:text-gray-900 font-semibold">Cancelar</Link>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed, watch } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Shared/AdminLayout.vue';

const page = usePage();
const adminBase = computed(() => page.props?.paths?.admin || '/admin');

const permissionOptions = [
  { key: 'dashboard', label: 'Dashboard' },
  { key: 'properties', label: 'Imóveis' },
  { key: 'business_types', label: 'Tipos de Negócio' },
  { key: 'pages', label: 'Páginas' },
  { key: 'appearance', label: 'Aparência' },
  { key: 'leads', label: 'Leads' },
  { key: 'settings', label: 'Configurações' },
  { key: 'instagram', label: 'Instagram' },
  { key: 'users', label: 'Usuários' },
];

const defaultPermissions = [
  'dashboard',
  'properties',
  'business_types',
  'pages',
  'appearance',
  'leads',
  'settings',
  'instagram',
];

const form = useForm({
  name: '',
  email: '',
  role: 'user',
  admin_enabled: true,
  permissions: [...defaultPermissions],
  password: '',
  password_confirmation: '',
});

const isAdminSelected = computed(() => form.role === 'admin');

watch(
  () => form.role,
  (role) => {
    if (role === 'admin') {
      form.permissions = permissionOptions.map((o) => o.key);
    } else if (!Array.isArray(form.permissions) || form.permissions.length === 0) {
      form.permissions = [...defaultPermissions];
    } else {
      form.permissions = form.permissions.filter((k) => k !== 'users');
    }
  },
  { immediate: true }
);

const save = () => {
  form.post(`${adminBase.value}/users`, { preserveScroll: true });
};
</script>
