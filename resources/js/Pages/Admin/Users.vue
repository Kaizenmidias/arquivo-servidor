<template>
  <AdminLayout>
    <template #pageTitle>Usuários</template>

    <div class="flex items-center justify-between mb-6">
      <h3 class="text-xl font-semibold text-gray-800">Usuários</h3>
      <Link :href="`${adminBase}/users/create`" class="bg-blue-900 hover:bg-blue-800 text-white px-6 py-2 rounded-lg font-semibold transition">
        Novo usuário
      </Link>
    </div>

    <div v-if="page.props?.errors?.user" class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-4">
      {{ page.props.errors.user }}
    </div>

    <div class="bg-white rounded-xl shadow border border-gray-200 overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Nome</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">E-mail</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Perfil</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Acesso ao painel</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Ações</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="u in users" :key="u.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 font-semibold text-gray-800">{{ u.name }}</td>
            <td class="px-6 py-4 text-gray-700">{{ u.email }}</td>
            <td class="px-6 py-4">
              <span :class="u.role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-700'" class="px-3 py-1 rounded-full text-xs font-semibold">
                {{ u.role === 'admin' ? 'Administrador' : 'Usuário' }}
              </span>
            </td>
            <td class="px-6 py-4">
              <span :class="u.admin_enabled ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'" class="px-3 py-1 rounded-full text-xs font-semibold">
                {{ u.admin_enabled ? 'Ativo' : 'Bloqueado' }}
              </span>
            </td>
            <td class="px-6 py-4">
              <div class="flex items-center gap-4">
                <Link :href="`${adminBase}/users/${u.id}/edit`" class="text-blue-600 hover:text-blue-800 font-medium">Editar</Link>
                <button type="button" class="text-red-600 hover:text-red-800 font-medium" @click="remove(u.id)">Excluir</button>
              </div>
            </td>
          </tr>
          <tr v-if="users.length === 0">
            <td colspan="5" class="px-6 py-12 text-center text-gray-500">Nenhum usuário cadastrado</td>
          </tr>
        </tbody>
      </table>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Shared/AdminLayout.vue';

const props = defineProps({
  users: { type: Array, default: () => [] },
});

const page = usePage();
const adminBase = computed(() => page.props?.paths?.admin || '/admin');
const users = computed(() => props.users || []);

const remove = (id) => {
  router.delete(`${adminBase.value}/users/${id}`, { preserveScroll: true });
};
</script>

