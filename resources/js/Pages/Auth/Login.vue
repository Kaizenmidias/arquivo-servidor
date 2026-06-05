<template>
  <Head title="Login" />

  <div class="min-h-screen bg-neutral-950 text-white flex items-center justify-center px-4 py-12">
    <div class="absolute inset-0">
      <div
        class="absolute inset-0"
        :style="{
          background:
            'radial-gradient(1200px 600px at 10% 10%, rgba(255,255,255,0.10), transparent 60%), radial-gradient(900px 500px at 90% 20%, rgba(255,255,255,0.08), transparent 55%), linear-gradient(135deg, var(--site-primary) 0%, #0b1220 55%, #000 100%)',
        }"
      ></div>
    </div>

    <div class="relative w-full max-w-5xl grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
      <div class="hidden lg:block">
        <div class="max-w-md">
          <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 border border-white/20 text-sm tracking-wide">
            <span class="opacity-90">Painel Administrativo</span>
          </div>
          <h1 class="mt-6 text-4xl font-semibold leading-tight">
            Bem-vindo de volta!
          </h1>
          <p class="mt-4 text-white/80">
            Entre na sua conta para acessar o painel e gerenciar imóveis, páginas e configurações.
          </p>
        </div>
      </div>

      <div class="bg-white/10 backdrop-blur-xl border border-white/15 rounded-3xl p-8 shadow-2xl">
        <div class="flex flex-col items-center justify-center gap-3">
          <img v-if="logoUrl" :src="logoUrl" alt="Logo" class="h-12 w-auto object-contain" />
          <div v-else class="h-12 w-12 rounded-xl bg-white/15 border border-white/20"></div>
        </div>

        <div class="mt-8">
          <form class="space-y-4" @submit.prevent="submit">
            <div>
              <label class="block text-sm font-medium text-white/90 mb-2">Email</label>
              <input
                v-model="form.email"
                type="email"
                autocomplete="email"
                class="w-full rounded-xl px-4 py-3 bg-white/10 border border-white/20 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/30"
                placeholder="seuemail@exemplo.com"
              />
              <div v-if="form.errors.email" class="mt-2 text-sm text-red-200">{{ form.errors.email }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-white/90 mb-2">Senha</label>
              <input
                v-model="form.password"
                type="password"
                autocomplete="current-password"
                class="w-full rounded-xl px-4 py-3 bg-white/10 border border-white/20 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/30"
                placeholder="••••••••"
              />
              <div v-if="form.errors.password" class="mt-2 text-sm text-red-200">{{ form.errors.password }}</div>
            </div>

            <div class="flex items-center justify-between">
              <label class="inline-flex items-center gap-2 text-sm text-white/80 select-none">
                <input v-model="form.remember" type="checkbox" class="rounded border-white/30 bg-white/10" />
                Lembrar-me
              </label>
            </div>

            <button
              type="submit"
              class="w-full rounded-xl py-3 font-semibold transition text-white bg-black hover:bg-neutral-900 disabled:opacity-60"
              :disabled="form.processing"
            >
              Entrar
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const settings = computed(() => page.props.settings || {});
const logoUrl = computed(() => settings.value.logo_url || '');
const loginPath = computed(() => page.props?.paths?.login || '/login');

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submit = () => {
  form.post(loginPath.value, {
    onFinish: () => form.reset('password'),
  });
};
</script>
