<template>
  <div class="flex h-screen bg-gray-100">
    <!-- Sidebar -->
    <aside class="w-64 bg-black text-white flex flex-col">
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
      
      <nav class="mt-6 flex-1">
        <Link v-if="can('dashboard')" :href="adminBase" class="flex items-center gap-3 px-6 py-3 hover:bg-gray-900 transition">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
          </svg>
          <span>Dashboard</span>
        </Link>
        
        <Link v-if="can('properties')" :href="`${adminBase}/properties`" class="flex items-center gap-3 px-6 py-3 hover:bg-gray-900 transition">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
          </svg>
          <span>Imóveis</span>
        </Link>

        <Link v-if="can('business_types')" :href="`${adminBase}/business-types`" class="flex items-center gap-3 px-6 py-3 hover:bg-gray-900 transition">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h6m6-10h2a2 2 0 012 2v4a2 2 0 01-2 2h-2m-6 0h6"></path>
          </svg>
          <span>Tipos de Negócio</span>
        </Link>

        <details v-if="can('properties')" class="group">
          <summary class="flex items-center gap-3 px-6 py-3 hover:bg-gray-900 transition cursor-pointer select-none">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6m16 0v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6m16 0H4"></path>
            </svg>
            <span>Categorias</span>
          </summary>
          <div class="pl-14">
            <Link :href="`${adminBase}/categories/property-types`" class="block px-6 py-2 text-gray-300 hover:text-white hover:bg-gray-900 transition rounded-r">
              Tipos de Imóvel
            </Link>
            <Link :href="`${adminBase}/categories/special`" class="block px-6 py-2 text-gray-300 hover:text-white hover:bg-gray-900 transition rounded-r">
              Categorias Especiais
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
        
        <Link v-if="can('appearance')" :href="`${adminBase}/appearance`" class="flex items-center gap-3 px-6 py-3 hover:bg-gray-900 transition">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
          </svg>
          <span>Aparência</span>
        </Link>
        
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
    <main class="flex-1 overflow-auto">
      <header class="bg-white shadow px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">
          <slot name="pageTitle">Dashboard</slot>
        </h1>
      </header>
      <div class="p-6">
        <slot></slot>
      </div>
    </main>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props?.auth?.user || null);
const adminBase = computed(() => page.props?.paths?.admin || '/admin');
const isAdmin = computed(() => user.value?.role === 'admin');
const permissions = computed(() => (Array.isArray(user.value?.permissions) ? user.value.permissions.map((p) => String(p)) : []));

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
</script>
