<template>
  <div class="min-h-screen bg-gray-100 md:flex">
    <div v-if="isSidebarOpen" class="fixed inset-0 z-40 bg-black/60 md:hidden" @click="closeSidebar"></div>

    <aside
      :class="[
        'fixed inset-y-0 left-0 z-50 w-72 max-w-[85vw] bg-black text-white flex flex-col transform transition-transform duration-300 md:static md:z-auto md:w-64 md:max-w-none md:translate-x-0',
        isSidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0',
      ]"
    >
      <div class="p-6">
        <Link :href="`${adminBase}/profile`" class="flex items-center gap-3 group">
          <div class="w-10 h-10 rounded-full overflow-hidden bg-gray-900 border border-gray-800 flex items-center justify-center">
            <img v-if="user?.profile_photo_url" :src="user.profile_photo_url" alt="" class="w-full h-full object-cover" />
            <span v-else class="text-sm font-semibold text-gray-200">{{ initials }}</span>
          </div>
          <div class="min-w-0">
            <div class="text-sm text-gray-300">Olá,</div>
            <div class="text-base font-bold text-white truncate group-hover:underline">
              {{ user?.name || 'Usuário' }}
            </div>
          </div>
        </Link>
      </div>
      
      <nav class="mt-6 flex-1 overflow-y-auto pb-6">
        <Link v-if="can('dashboard')" :href="adminBase" class="flex items-center gap-3 px-6 py-3 hover:bg-gray-900 transition">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
          </svg>
          <span>Dashboard</span>
        </Link>
        
        <details v-if="showPropertiesGroup" class="group" open>
          <summary class="flex items-center gap-3 px-6 py-3 hover:bg-gray-900 transition cursor-pointer select-none">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            <span>Propriedades</span>
          </summary>
          <div class="pl-14">
            <Link v-if="can('properties')" :href="`${adminBase}/properties`" class="block px-6 py-2 text-gray-300 hover:text-white hover:bg-gray-900 transition rounded-r">
              Imóveis
            </Link>
            <Link v-if="can('business_types')" :href="`${adminBase}/business-types`" class="block px-6 py-2 text-gray-300 hover:text-white hover:bg-gray-900 transition rounded-r">
              Tipos de Negócio
            </Link>
            <Link v-if="can('properties')" :href="`${adminBase}/categories/property-types`" class="block px-6 py-2 text-gray-300 hover:text-white hover:bg-gray-900 transition rounded-r">
              Tipo de Imóvel
            </Link>
            <Link v-if="can('properties')" :href="`${adminBase}/categories/special`" class="block px-6 py-2 text-gray-300 hover:text-white hover:bg-gray-900 transition rounded-r">
              Categorias Especiais
            </Link>
            <Link v-if="can('properties')" :href="`${adminBase}/condominiums`" class="block px-6 py-2 text-gray-300 hover:text-white hover:bg-gray-900 transition rounded-r">
              Condomínios
            </Link>
          </div>
        </details>
        
        <Link v-if="can('pages')" :href="`${adminBase}/pages`" class="flex items-center gap-3 px-6 py-3 hover:bg-gray-900 transition">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
          </svg>
          <span>Páginas</span>
        </Link>

        <details v-if="can('pages')" class="group">
          <summary class="flex items-center gap-3 px-6 py-3 hover:bg-gray-900 transition cursor-pointer select-none">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2"></path>
            </svg>
            <span>Blog</span>
          </summary>
          <div class="pl-14">
            <Link :href="`${adminBase}/blog/posts`" class="block px-6 py-2 text-gray-300 hover:text-white hover:bg-gray-900 transition rounded-r">
              Postagens
            </Link>
            <Link :href="`${adminBase}/blog/categories`" class="block px-6 py-2 text-gray-300 hover:text-white hover:bg-gray-900 transition rounded-r">
              Categorias
            </Link>
          </div>
        </details>
        
        <Link v-if="can('leads')" :href="`${adminBase}/leads`" class="flex items-center gap-3 px-6 py-3 hover:bg-gray-900 transition">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
          </svg>
          <span>Leads</span>
        </Link>
        
        <Link v-if="can('settings')" :href="`${adminBase}/settings`" class="flex items-center gap-3 px-6 py-3 hover:bg-gray-900 transition">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37a1.724 1.724 0 002.572-1.065z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
          </svg>
          <span>Configurações</span>
        </Link>

        <details v-if="isAdmin" class="group" open>
          <summary class="flex items-center gap-3 px-6 py-3 hover:bg-gray-900 transition cursor-pointer select-none">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.25 3.75h1.5a.75.75 0 01.75.75v1.008c0 .263.153.501.392.614l.96.456a.75.75 0 00.829-.133l.714-.714a.75.75 0 011.06 0l1.061 1.06a.75.75 0 010 1.061l-.714.714a.75.75 0 00-.133.829l.456.96c.113.239.351.392.614.392h1.008a.75.75 0 01.75.75v1.5a.75.75 0 01-.75.75h-1.008a.75.75 0 00-.614.392l-.456.96a.75.75 0 00.133.829l.714.714a.75.75 0 010 1.06l-1.06 1.061a.75.75 0 01-1.061 0l-.714-.714a.75.75 0 00-.829-.133l-.96.456a.75.75 0 00-.392.614v1.008a.75.75 0 01-.75.75h-1.5a.75.75 0 01-.75-.75v-1.008a.75.75 0 00-.392-.614l-.96-.456a.75.75 0 00-.829.133l-.714.714a.75.75 0 01-1.06 0l-1.061-1.06a.75.75 0 010-1.061l.714-.714a.75.75 0 00.133-.829l-.456-.96a.75.75 0 00-.614-.392H3.75a.75.75 0 01-.75-.75v-1.5a.75.75 0 01.75-.75h1.008c.263 0 .501-.153.614-.392l.456-.96a.75.75 0 00-.133-.829l-.714-.714a.75.75 0 010-1.06l1.06-1.061a.75.75 0 011.061 0l.714.714a.75.75 0 00.829.133l.96-.456a.75.75 0 00.392-.614V4.5a.75.75 0 01.75-.75z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <span>Integrações</span>
          </summary>
          <div class="pl-14">
            <Link :href="`${adminBase}/integracoes/pixels-tags`" class="block px-6 py-2 text-gray-300 hover:text-white hover:bg-gray-900 transition rounded-r">
              Pixels e Tags
            </Link>
            <Link :href="`${adminBase}/integracoes/codigo-personalizado`" class="block px-6 py-2 text-gray-300 hover:text-white hover:bg-gray-900 transition rounded-r">
              Código Personalizado
            </Link>
          </div>
        </details>

        <Link v-if="can('instagram')" :href="`${adminBase}/instagram`" class="flex items-center gap-3 px-6 py-3 hover:bg-gray-900 transition">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7c2.761 0 5 2.239 5 5s-2.239 5-5 5-5-2.239-5-5 2.239-5 5-5z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 3h-9A4.5 4.5 0 003 7.5v9A4.5 4.5 0 007.5 21h9a4.5 4.5 0 004.5-4.5v-9A4.5 4.5 0 0016.5 3z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.5 6.5h.01"></path>
          </svg>
          <span>Instagram</span>
        </Link>

        <Link v-if="isAdmin" :href="`${adminBase}/users`" class="flex items-center gap-3 px-6 py-3 hover:bg-gray-900 transition">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-4-4h-1m-4 6H2v-2a4 4 0 014-4h7m4-6a4 4 0 11-8 0 4 4 0 018 0zm6 4a3 3 0 11-6 0 3 3 0 016 0z"></path>
          </svg>
          <span>Usuários</span>
        </Link>
      </nav>
      
      <div class="p-6 border-t border-gray-900 space-y-2">
        <Link :href="`${adminBase}/profile`" class="flex items-center gap-3 px-2 py-2 text-gray-300 hover:text-white hover:bg-gray-900 rounded-lg transition">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2a10 10 0 100 20 10 10 0 000-20z"></path>
          </svg>
          <span>Editar usuário</span>
        </Link>

        <Link href="/logout" method="post" as="button" class="w-full text-left flex items-center gap-3 px-2 py-2 text-gray-300 hover:text-white hover:bg-gray-900 rounded-lg transition">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"></path>
          </svg>
          <span>Logout</span>
        </Link>

        <a href="/" class="flex items-center gap-3 px-2 py-2 text-gray-300 hover:text-white hover:bg-gray-900 rounded-lg transition">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7m0 0l7-7m-7 7h18"></path>
          </svg>
          <span>Voltar ao Site</span>
        </a>
      </div>
    </aside>

    <!-- Content -->
    <main class="flex-1 min-w-0 flex flex-col">
      <header class="bg-white shadow px-4 py-3 md:px-6 md:py-4 border-b border-gray-200 flex items-center gap-3">
        <button type="button" class="md:hidden p-2 rounded-lg hover:bg-gray-100 transition" @click="toggleSidebar">
          <svg class="w-6 h-6 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
          </svg>
        </button>

        <h1 class="min-w-0 flex-1 text-lg md:text-2xl font-bold text-gray-800 truncate">
          <slot name="pageTitle">Dashboard</slot>
        </h1>
      </header>
      <div class="flex-1 overflow-auto p-4 md:p-6">
        <div
          v-if="flashMessage"
          :class="flashType === 'error' ? 'border-red-200 bg-red-50 text-red-800' : 'border-emerald-200 bg-emerald-50 text-emerald-800'"
          class="mb-6 rounded-xl border px-4 py-3 shadow-sm"
        >
          <div class="font-semibold">
            {{ flashType === 'error' ? 'Nao foi possivel concluir a acao.' : 'Sucesso.' }}
          </div>
          <div class="mt-1 text-sm">
            {{ flashMessage }}
          </div>
        </div>
        <slot></slot>
      </div>
    </main>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props?.auth?.user || null);
const adminBase = computed(() => page.props?.paths?.admin || '/admin');
const isAdmin = computed(() => user.value?.role === 'admin');
const permissions = computed(() => (Array.isArray(user.value?.permissions) ? user.value.permissions.map((p) => String(p)) : []));
const flash = computed(() => page.props?.flash || {});
const flashType = computed(() => (flash.value?.error ? 'error' : (flash.value?.success ? 'success' : '')));
const flashMessage = computed(() => flash.value?.error || flash.value?.success || '');
const showPropertiesGroup = computed(() => can('properties') || can('business_types'));

const isSidebarOpen = ref(false);

const can = (key) => {
  if (isAdmin.value) return true;
  return permissions.value.includes(key);
};

const initials = computed(() => {
  const name = String(user.value?.name || '').trim();
  if (!name) return 'U';
  const parts = name.split(/\s+/).filter(Boolean).slice(0, 2);
  return parts.map((p) => p[0]?.toUpperCase()).join('');
});

const closeSidebar = () => {
  isSidebarOpen.value = false;
};

const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value;
};

watch(
  () => page.url,
  () => {
    closeSidebar();
  }
);
</script>
