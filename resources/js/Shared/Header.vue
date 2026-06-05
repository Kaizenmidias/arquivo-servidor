<template>
  <header class="fixed top-0 left-0 right-0 z-50 text-white transition-colors duration-300" :class="headerBgClass">
    <div class="container mx-auto px-4">
      <div class="flex items-center justify-between h-20">
        <!-- Logo -->
        <div class="flex items-center">
          <a href="/" class="text-2xl font-bold flex items-center gap-2">
            <img v-if="logoUrl" :src="logoUrl" alt="Logo" class="h-10 w-auto object-contain" />
            <svg v-else class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
          </a>
        </div>

        <!-- Desktop Nav -->
        <nav class="hidden md:flex items-center space-x-8">
          <a href="/" class="hover:text-white/80 transition font-medium">Início</a>
          <a href="/imoveis" class="hover:text-white/80 transition font-medium">Imóveis</a>
          <a href="/venda-seu-imovel" class="hover:text-white/80 transition font-medium">Venda seu Imóvel</a>
        </nav>

        <!-- Right Side -->
        <div class="flex items-center space-x-4">
          <!-- Search -->
          <button class="p-2 hover:bg-white/10 rounded-full transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </button>

          <!-- Favorites -->
          <button class="p-2 hover:bg-white/10 rounded-full transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
          </button>

          <!-- Hamburger Menu -->
          <button @click="openMenu" class="p-2 hover:bg-white/10 rounded-full transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Drawer Menu -->
    <transition name="fade">
      <DrawerMenu v-if="isMenuOpen" :is-open="isMenuOpen" :menu-items="menuItems" @close="closeMenu" />
    </transition>
  </header>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import DrawerMenu from './DrawerMenu.vue';

const isMenuOpen = ref(false);
const isScrolled = ref(false);

const page = usePage();
const menuItems = computed(() => page.props.menuItems || []);
const settings = computed(() => page.props.settings || {});
const logoUrl = computed(() => settings.value.logo_url || '');

const headerBgClass = computed(() => {
  return isScrolled.value ? 'bg-black shadow-md' : 'bg-transparent';
});

const onScroll = () => {
  isScrolled.value = (window.scrollY || 0) > 10;
};

onMounted(() => {
  onScroll();
  window.addEventListener('scroll', onScroll, { passive: true });
});

onBeforeUnmount(() => {
  window.removeEventListener('scroll', onScroll);
});

function openMenu() {
  isMenuOpen.value = true;
}

function closeMenu() {
  isMenuOpen.value = false;
}
</script>
