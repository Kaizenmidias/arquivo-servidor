<template>
  <AdminLayout>
    <template #pageTitle>Meu Perfil</template>

    <div class="max-w-6xl">
      <div class="grid grid-cols-1 xl:grid-cols-[360px_minmax(0,1fr)] gap-6">
        <section class="bg-gradient-to-br from-slate-950 via-slate-900 to-blue-950 rounded-3xl p-6 text-white shadow-xl overflow-hidden relative">
          <div class="absolute inset-0 opacity-20 bg-[radial-gradient(circle_at_top_right,_rgba(255,255,255,0.35),_transparent_40%)]"></div>
          <div class="relative">
            <div class="flex items-center gap-4">
              <div class="w-24 h-24 rounded-3xl overflow-hidden bg-white/10 border border-white/15 flex items-center justify-center backdrop-blur">
                <img v-if="avatarPreview || currentUser.profile_photo_url" :src="avatarPreview || currentUser.profile_photo_url" alt="" class="w-full h-full object-cover" />
                <span v-else class="text-2xl font-semibold">{{ initials }}</span>
              </div>
              <div class="min-w-0">
                <div class="text-sm uppercase tracking-[0.2em] text-blue-200/80">Conta autenticada</div>
                <div class="text-2xl font-semibold truncate mt-1">{{ currentUser.name || 'Usuario' }}</div>
                <div class="text-sm text-blue-100/80 truncate mt-1">{{ currentUser.email || 'Sem e-mail' }}</div>
              </div>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-3">
              <div class="rounded-2xl bg-white/8 border border-white/10 px-4 py-3 backdrop-blur">
                <div class="text-xs uppercase tracking-wide text-blue-100/70">Status do e-mail</div>
                <div class="mt-1 font-medium">
                  {{ currentUser.email_verified_at ? 'Verificado' : 'Pendente de verificacao' }}
                </div>
              </div>
              <div class="rounded-2xl bg-white/8 border border-white/10 px-4 py-3 backdrop-blur">
                <div class="text-xs uppercase tracking-wide text-blue-100/70">Seguranca</div>
                <div class="mt-1 text-sm text-blue-50/90">Atualize seus dados, troque a senha com confirmacao e mantenha seu avatar sincronizado no painel.</div>
              </div>
            </div>
          </div>
        </section>

        <div class="space-y-6">
          <section class="bg-white rounded-3xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-start justify-between gap-4 mb-6">
              <div>
                <h2 class="text-xl font-semibold text-gray-900">Dados da conta</h2>
                <p class="text-sm text-gray-500 mt-1">Atualize seu nome e e-mail com persistencia real no banco.</p>
              </div>
              <div class="hidden sm:flex items-center gap-2 rounded-full bg-blue-50 text-blue-700 px-3 py-1.5 text-xs font-semibold">
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                Integracao ativa
              </div>
            </div>

            <div v-if="infoState.message" :class="feedbackClasses(infoState.type)" class="mb-4 rounded-2xl px-4 py-3 text-sm">
              {{ infoState.message }}
            </div>

            <form class="space-y-5" @submit.prevent="saveProfileInfo">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">Nome</label>
                  <input v-model="infoForm.name" type="text" class="w-full rounded-2xl border border-gray-300 px-4 py-3.5 bg-gray-50/60 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" />
                  <div v-if="infoErrors.name" class="text-sm text-red-600 mt-2">{{ infoErrors.name }}</div>
                </div>
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">E-mail</label>
                  <input v-model="infoForm.email" type="email" class="w-full rounded-2xl border border-gray-300 px-4 py-3.5 bg-gray-50/60 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" />
                  <div v-if="infoErrors.email" class="text-sm text-red-600 mt-2">{{ infoErrors.email }}</div>
                </div>
              </div>

              <div class="flex items-center justify-end gap-3">
                <button type="button" class="text-gray-600 hover:text-gray-900 font-semibold px-2 py-2" :disabled="infoProcessing" @click="resetInfoForm">
                  Cancelar
                </button>
                <button type="submit" class="inline-flex items-center justify-center min-w-40 rounded-2xl bg-blue-900 hover:bg-blue-800 text-white px-5 py-3 font-semibold transition disabled:opacity-60 disabled:cursor-not-allowed" :disabled="infoProcessing">
                  {{ infoProcessing ? 'Salvando...' : 'Salvar dados' }}
                </button>
              </div>
            </form>
          </section>

          <section class="bg-white rounded-3xl shadow-sm border border-gray-200 p-6">
            <div class="mb-6">
              <h2 class="text-xl font-semibold text-gray-900">Foto de perfil</h2>
              <p class="text-sm text-gray-500 mt-1">Envie um avatar seguro em JPG, PNG ou WEBP com preview imediato.</p>
            </div>

            <div v-if="avatarState.message" :class="feedbackClasses(avatarState.type)" class="mb-4 rounded-2xl px-4 py-3 text-sm">
              {{ avatarState.message }}
            </div>

            <form class="space-y-5" @submit.prevent="saveAvatar">
              <div class="flex flex-col md:flex-row gap-5 items-start">
                <div class="w-28 h-28 rounded-3xl overflow-hidden border border-gray-200 bg-gray-100 flex items-center justify-center shadow-inner">
                  <img v-if="avatarPreview || currentUser.profile_photo_url" :src="avatarPreview || currentUser.profile_photo_url" alt="" class="w-full h-full object-cover" />
                  <span v-else class="text-2xl font-semibold text-gray-500">{{ initials }}</span>
                </div>
                <div class="flex-1 w-full">
                  <label class="block text-sm font-semibold text-gray-700 mb-2">Selecionar imagem</label>
                  <input type="file" accept=".jpg,.jpeg,.png,.webp" class="w-full rounded-2xl border border-dashed border-gray-300 px-4 py-3.5 bg-gray-50/60 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" @change="onAvatarChange" />
                  <p class="text-xs text-gray-500 mt-2">Tamanho maximo de 5 MB. A imagem anterior e substituida automaticamente.</p>
                  <div v-if="avatarErrors.profile_photo" class="text-sm text-red-600 mt-2">{{ avatarErrors.profile_photo }}</div>
                </div>
              </div>

              <div class="flex items-center justify-end gap-3">
                <button type="button" class="text-gray-600 hover:text-gray-900 font-semibold px-2 py-2" :disabled="avatarProcessing" @click="resetAvatarForm">
                  Cancelar
                </button>
                <button type="submit" class="inline-flex items-center justify-center min-w-40 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white px-5 py-3 font-semibold transition disabled:opacity-60 disabled:cursor-not-allowed" :disabled="avatarProcessing || !avatarForm.profile_photo">
                  {{ avatarProcessing ? 'Enviando...' : 'Atualizar avatar' }}
                </button>
              </div>
            </form>
          </section>

          <section class="bg-white rounded-3xl shadow-sm border border-gray-200 p-6">
            <div class="mb-6">
              <h2 class="text-xl font-semibold text-gray-900">Seguranca da senha</h2>
              <p class="text-sm text-gray-500 mt-1">Use sua senha atual para confirmar a alteracao e proteger sua conta.</p>
            </div>

            <div v-if="passwordState.message" :class="feedbackClasses(passwordState.type)" class="mb-4 rounded-2xl px-4 py-3 text-sm">
              {{ passwordState.message }}
            </div>

            <form class="space-y-5" @submit.prevent="savePassword">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                  <label class="block text-sm font-semibold text-gray-700 mb-2">Senha atual</label>
                  <div class="relative">
                    <input
                      v-model="passwordForm.current_password"
                      :type="passwordFieldType('current_password')"
                      autocomplete="current-password"
                      class="w-full rounded-2xl border border-gray-300 px-4 py-3.5 pr-14 bg-gray-50/60 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                    />
                    <button
                      type="button"
                      class="absolute inset-y-0 right-0 inline-flex items-center justify-center px-4 text-gray-500 transition hover:text-gray-700"
                      :aria-label="passwordVisibility.current_password ? 'Ocultar senha atual' : 'Mostrar senha atual'"
                      @click="togglePasswordVisibility('current_password')"
                    >
                      <svg v-if="passwordVisibility.current_password" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.584 10.587A2 2 0 0012 15a2 2 0 001.414-.586"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.88 5.09A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.965 9.965 0 01-4.132 5.411M6.228 6.228A9.956 9.956 0 002.458 12c1.274 4.057 5.064 7 9.542 7 1.61 0 3.131-.38 4.478-1.056"></path>
                      </svg>
                      <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      </svg>
                    </button>
                  </div>
                  <div v-if="passwordErrors.current_password" class="text-sm text-red-600 mt-2">{{ passwordErrors.current_password }}</div>
                </div>
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">Nova senha</label>
                  <div class="relative">
                    <input
                      v-model="passwordForm.password"
                      :type="passwordFieldType('password')"
                      autocomplete="new-password"
                      class="w-full rounded-2xl border border-gray-300 px-4 py-3.5 pr-14 bg-gray-50/60 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                    />
                    <button
                      type="button"
                      class="absolute inset-y-0 right-0 inline-flex items-center justify-center px-4 text-gray-500 transition hover:text-gray-700"
                      :aria-label="passwordVisibility.password ? 'Ocultar nova senha' : 'Mostrar nova senha'"
                      @click="togglePasswordVisibility('password')"
                    >
                      <svg v-if="passwordVisibility.password" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.584 10.587A2 2 0 0012 15a2 2 0 001.414-.586"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.88 5.09A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.965 9.965 0 01-4.132 5.411M6.228 6.228A9.956 9.956 0 002.458 12c1.274 4.057 5.064 7 9.542 7 1.61 0 3.131-.38 4.478-1.056"></path>
                      </svg>
                      <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      </svg>
                    </button>
                  </div>
                  <div v-if="passwordErrors.password" class="text-sm text-red-600 mt-2">{{ passwordErrors.password }}</div>
                </div>
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">Confirmar nova senha</label>
                  <div class="relative">
                    <input
                      v-model="passwordForm.password_confirmation"
                      :type="passwordFieldType('password_confirmation')"
                      autocomplete="new-password"
                      class="w-full rounded-2xl border border-gray-300 px-4 py-3.5 pr-14 bg-gray-50/60 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                    />
                    <button
                      type="button"
                      class="absolute inset-y-0 right-0 inline-flex items-center justify-center px-4 text-gray-500 transition hover:text-gray-700"
                      :aria-label="passwordVisibility.password_confirmation ? 'Ocultar confirmacao de senha' : 'Mostrar confirmacao de senha'"
                      @click="togglePasswordVisibility('password_confirmation')"
                    >
                      <svg v-if="passwordVisibility.password_confirmation" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.584 10.587A2 2 0 0012 15a2 2 0 001.414-.586"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.88 5.09A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.965 9.965 0 01-4.132 5.411M6.228 6.228A9.956 9.956 0 002.458 12c1.274 4.057 5.064 7 9.542 7 1.61 0 3.131-.38 4.478-1.056"></path>
                      </svg>
                      <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      </svg>
                    </button>
                  </div>
                </div>
              </div>

              <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                A nova senha deve ter no minimo 8 caracteres, com letras maiusculas, minusculas, numeros e simbolos.
              </div>

              <div class="flex items-center justify-end gap-3">
                <button type="button" class="text-gray-600 hover:text-gray-900 font-semibold px-2 py-2" :disabled="passwordProcessing" @click="resetPasswordForm">
                  Cancelar
                </button>
                <button type="submit" class="inline-flex items-center justify-center min-w-40 rounded-2xl bg-emerald-700 hover:bg-emerald-600 text-white px-5 py-3 font-semibold transition disabled:opacity-60 disabled:cursor-not-allowed" :disabled="passwordProcessing">
                  {{ passwordProcessing ? 'Atualizando...' : 'Atualizar senha' }}
                </button>
              </div>
            </form>
          </section>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed, reactive, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Shared/AdminLayout.vue';

const page = usePage();
const adminBase = computed(() => page.props?.paths?.admin || '/admin');

const props = defineProps({
  user: {
    type: Object,
    default: () => null,
  },
});

const currentUser = ref({
  id: props.user?.id || null,
  name: props.user?.name || '',
  email: props.user?.email || '',
  email_verified_at: props.user?.email_verified_at || null,
  profile_photo_url: props.user?.profile_photo_url || null,
});

const infoForm = reactive({
  name: currentUser.value.name,
  email: currentUser.value.email,
});

const avatarForm = reactive({
  profile_photo: null,
});

const passwordForm = reactive({
  current_password: '',
  password: '',
  password_confirmation: '',
});
const passwordVisibility = reactive({
  current_password: false,
  password: false,
  password_confirmation: false,
});

const infoErrors = reactive({});
const avatarErrors = reactive({});
const passwordErrors = reactive({});

const infoProcessing = ref(false);
const avatarProcessing = ref(false);
const passwordProcessing = ref(false);

const infoState = reactive({ type: '', message: '' });
const avatarState = reactive({ type: '', message: '' });
const passwordState = reactive({ type: '', message: '' });

const avatarPreview = ref(null);

const initials = computed(() => {
  const name = String(currentUser.value.name || infoForm.name || '').trim();
  if (!name) return 'U';
  return name
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0]?.toUpperCase())
    .join('');
});

function clearObject(target) {
  Object.keys(target).forEach((key) => {
    delete target[key];
  });
}

function setState(target, type = '', message = '') {
  target.type = type;
  target.message = message;
}

function feedbackClasses(type) {
  if (type === 'success') {
    return 'border border-emerald-200 bg-emerald-50 text-emerald-800';
  }

  return 'border border-red-200 bg-red-50 text-red-700';
}

function normalizeErrors(error) {
  const rawErrors = error?.response?.data?.errors || {};

  return Object.fromEntries(
    Object.entries(rawErrors).map(([field, messages]) => [
      field,
      Array.isArray(messages) ? (messages[0] || '') : String(messages || ''),
    ]),
  );
}

function syncUser(user) {
  if (!user) return;

  currentUser.value = {
    ...currentUser.value,
    ...user,
  };

  infoForm.name = currentUser.value.name || '';
  infoForm.email = currentUser.value.email || '';

  router.reload({
    only: ['auth'],
    preserveScroll: true,
    preserveState: true,
  });
}

function resetInfoForm() {
  infoForm.name = currentUser.value.name || '';
  infoForm.email = currentUser.value.email || '';
  clearObject(infoErrors);
  setState(infoState);
}

function resetPasswordForm() {
  passwordForm.current_password = '';
  passwordForm.password = '';
  passwordForm.password_confirmation = '';
  passwordVisibility.current_password = false;
  passwordVisibility.password = false;
  passwordVisibility.password_confirmation = false;
  clearObject(passwordErrors);
  setState(passwordState);
}

function togglePasswordVisibility(field) {
  passwordVisibility[field] = !passwordVisibility[field];
}

function passwordFieldType(field) {
  return passwordVisibility[field] ? 'text' : 'password';
}

function resetAvatarForm() {
  avatarForm.profile_photo = null;
  clearObject(avatarErrors);
  setState(avatarState);
  if (avatarPreview.value) {
    URL.revokeObjectURL(avatarPreview.value);
    avatarPreview.value = null;
  }
}

function onAvatarChange(event) {
  const file = event.target?.files?.[0] || null;
  avatarForm.profile_photo = file;
  clearObject(avatarErrors);
  setState(avatarState);

  if (avatarPreview.value) {
    URL.revokeObjectURL(avatarPreview.value);
    avatarPreview.value = null;
  }

  if (file) {
    avatarPreview.value = URL.createObjectURL(file);
  }
}

async function saveProfileInfo() {
  infoProcessing.value = true;
  clearObject(infoErrors);
  setState(infoState);

  try {
    const { data } = await window.axios.put(`${adminBase.value}/profile`, {
      name: infoForm.name,
      email: infoForm.email,
    });

    syncUser(data.user);
    setState(infoState, 'success', data.message || 'Perfil atualizado com sucesso.');
  } catch (error) {
    Object.assign(infoErrors, normalizeErrors(error));
    setState(infoState, 'error', error?.response?.data?.message || 'Nao foi possivel atualizar os dados do perfil.');
  } finally {
    infoProcessing.value = false;
  }
}

async function saveAvatar() {
  if (!avatarForm.profile_photo) {
    clearObject(avatarErrors);
    avatarErrors.profile_photo = 'Selecione uma imagem para continuar.';
    setState(avatarState, 'error', 'Selecione uma imagem valida para atualizar o avatar.');
    return;
  }

  avatarProcessing.value = true;
  clearObject(avatarErrors);
  setState(avatarState);

  const payload = new FormData();
  payload.append('profile_photo', avatarForm.profile_photo);

  try {
    const { data } = await window.axios.post(`${adminBase.value}/profile/avatar`, payload);

    syncUser(data.user);
    setState(avatarState, 'success', data.message || 'Avatar atualizado com sucesso.');
    avatarForm.profile_photo = null;

    if (avatarPreview.value) {
      URL.revokeObjectURL(avatarPreview.value);
      avatarPreview.value = null;
    }
  } catch (error) {
    Object.assign(avatarErrors, normalizeErrors(error));
    setState(avatarState, 'error', error?.response?.data?.message || 'Nao foi possivel atualizar o avatar.');
  } finally {
    avatarProcessing.value = false;
  }
}

async function savePassword() {
  passwordProcessing.value = true;
  clearObject(passwordErrors);
  setState(passwordState);

  try {
    const { data } = await window.axios.put(`${adminBase.value}/profile/password`, {
      current_password: passwordForm.current_password,
      password: passwordForm.password,
      password_confirmation: passwordForm.password_confirmation,
    });

    resetPasswordForm();
    setState(passwordState, 'success', data.message || 'Senha atualizada com sucesso.');
  } catch (error) {
    Object.assign(passwordErrors, normalizeErrors(error));
    setState(passwordState, 'error', error?.response?.data?.message || 'Nao foi possivel atualizar a senha.');
  } finally {
    passwordProcessing.value = false;
  }
}
</script>
