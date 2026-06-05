<template>
  <AdminLayout>
    <template #pageTitle>Meu Perfil</template>

    <div class="max-w-3xl">
      <div class="bg-white rounded-xl shadow border border-gray-200 p-6">
        <div class="flex items-center gap-4 mb-6">
          <div class="w-16 h-16 rounded-full overflow-hidden bg-gray-100 border border-gray-200 flex items-center justify-center">
            <img v-if="previewUrl || currentPhotoUrl" :src="previewUrl || currentPhotoUrl" alt="" class="w-full h-full object-cover" />
            <span v-else class="text-gray-500 font-semibold">{{ initials }}</span>
          </div>
          <div class="min-w-0">
            <div class="text-lg font-semibold text-gray-900 truncate">{{ form.name }}</div>
            <div class="text-sm text-gray-500 truncate">{{ form.email }}</div>
          </div>
        </div>

        <form @submit.prevent="save" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Nome</label>
              <input v-model="form.name" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
              <div v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</div>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">E-mail</label>
              <input v-model="form.email" type="email" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
              <div v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}</div>
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Foto de perfil</label>
            <input type="file" accept=".jpg,.jpeg,.png,.webp,.svg" @change="onPhotoChange" class="w-full border border-gray-300 rounded-lg px-4 py-3 bg-white" />
            <div v-if="form.errors.profile_photo" class="text-sm text-red-600 mt-1">{{ form.errors.profile_photo }}</div>
          </div>

          <div class="border-t border-gray-200 pt-6">
            <div class="text-sm font-semibold text-gray-900 mb-4">Trocar senha</div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Senha atual</label>
                <input v-model="form.current_password" type="password" class="w-full border border-gray-300 rounded-lg px-4 py-3" autocomplete="current-password" />
                <div v-if="form.errors.current_password" class="text-sm text-red-600 mt-1">{{ form.errors.current_password }}</div>
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Nova senha</label>
                <input v-model="form.password" type="password" class="w-full border border-gray-300 rounded-lg px-4 py-3" autocomplete="new-password" />
                <div v-if="form.errors.password" class="text-sm text-red-600 mt-1">{{ form.errors.password }}</div>
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Confirmar nova senha</label>
                <input v-model="form.password_confirmation" type="password" class="w-full border border-gray-300 rounded-lg px-4 py-3" autocomplete="new-password" />
              </div>
            </div>
          </div>

          <div class="flex items-center justify-end gap-3">
            <button type="button" class="text-gray-700 hover:text-gray-900 font-semibold" @click="reset">
              Cancelar
            </button>
            <button type="submit" class="bg-blue-900 hover:bg-blue-800 text-white px-5 py-2.5 rounded-lg font-semibold transition disabled:opacity-60 disabled:cursor-not-allowed" :disabled="form.processing">
              Salvar
            </button>
          </div>
        </form>
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
  user: {
    type: Object,
    default: () => null,
  },
});

const currentPhotoUrl = computed(() => props.user?.profile_photo_url || null);
const previewUrl = ref(null);

const form = useForm({
  name: props.user?.name || '',
  email: props.user?.email || '',
  profile_photo: null,
  current_password: '',
  password: '',
  password_confirmation: '',
});

const initials = computed(() => {
  const name = String(form.name || '').trim();
  if (!name) return 'U';
  const parts = name.split(/\s+/).filter(Boolean).slice(0, 2);
  return parts.map((p) => p[0]?.toUpperCase()).join('');
});

function onPhotoChange(e) {
  const file = e.target?.files?.[0] || null;
  form.profile_photo = file;
  if (previewUrl.value) {
    URL.revokeObjectURL(previewUrl.value);
    previewUrl.value = null;
  }
  if (file) {
    previewUrl.value = URL.createObjectURL(file);
  }
}

function reset() {
  if (previewUrl.value) {
    URL.revokeObjectURL(previewUrl.value);
    previewUrl.value = null;
  }
  form.reset();
  form.name = props.user?.name || '';
  form.email = props.user?.email || '';
  form.profile_photo = null;
  form.clearErrors();
}

function save() {
  form.post(`${adminBase.value}/profile`, {
    method: 'put',
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      form.current_password = '';
      form.password = '';
      form.password_confirmation = '';
      form.profile_photo = null;
      form.clearErrors();
      if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
        previewUrl.value = null;
      }
    },
  });
}
</script>
