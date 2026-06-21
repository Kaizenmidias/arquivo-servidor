<template>
  <header class="fixed inset-x-0 top-0 z-50 transition-all duration-300">
    <div class="ui-shell">
      <div
        class="mt-3 flex h-[72px] items-center justify-between rounded-2xl border px-4 transition-all duration-300 lg:px-6"
        :class="headerSurfaceClass"
      >
        <a href="/" class="flex min-w-0 items-center gap-3">
          <img v-if="logoUrl" :src="logoUrl" :alt="siteName" class="h-10 w-auto max-w-[180px] object-contain transition-[filter] duration-300 lg:max-w-[220px]" :class="logoToneClass" />
          <div v-else class="text-lg font-semibold tracking-tight" :class="textToneClass">{{ siteName }}</div>
        </a>

        <nav class="hidden xl:flex items-center gap-1">
          <a
            v-for="item in primaryLinks"
            :key="item.url"
            :href="item.url"
            class="rounded-full px-4 py-2 text-sm font-medium transition"
            :class="navItemClass(item.url)"
          >
            {{ item.label }}
          </a>
        </nav>

        <div class="flex items-center gap-2">
          <a
            href="/contato"
            class="hidden rounded-full px-4 py-2 text-sm font-semibold transition lg:inline-flex"
            :class="secondaryActionClass"
          >
            Fale conosco
          </a>
          <button type="button" class="flex h-9 w-9 items-center justify-center rounded-full transition" :class="iconButtonClass">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.55" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </button>
          <button type="button" class="hidden h-9 w-9 items-center justify-center rounded-full transition md:flex" :class="iconButtonClass">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.55" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
          </button>
          <button type="button" class="flex h-9 w-9 items-center justify-center rounded-full transition" :class="iconButtonClass" @click="openMenu">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.55" d="M4 7h16M4 12h16M4 17h16" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <transition name="fade">
      <DrawerMenu v-if="isMenuOpen" :is-open="isMenuOpen" :menu-items="menuItems" @close="closeMenu" />
    </transition>
  </header>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import DrawerMenu from './DrawerMenu.vue';

const isMenuOpen = ref(false);
const isScrolled = ref(false);

const page = usePage();
const menuItems = computed(() => page.props.menuItems || []);
const settings = computed(() => page.props.settings || {});
const logoUrl = computed(() => settings.value.logo_url || '');
const siteName = computed(() => settings.value.nome_empresa || 'Imobiliária');
const currentPath = computed(() => normalizeUrl(page.url || '/'));
const usesTransparentHeader = computed(() => page.component === 'Home' || currentPath.value === '/');
const isSolid = computed(() => !usesTransparentHeader.value || isScrolled.value || isMenuOpen.value);

const primaryLinks = [
  { label: 'Início', url: '/' },
  { label: 'Imóveis', url: '/imoveis' },
  { label: 'Venda seu Imóvel', url: '/venda-seu-imovel' },
];

const headerSurfaceClass = computed(() => {
  if (isSolid.value) {
    return 'border-white/70 bg-white/93 text-slate-900 shadow-[0_18px_50px_rgba(15,23,42,0.10)] backdrop-blur-xl';
  }

  return 'border-white/18 bg-[rgba(60,66,79,0.34)] text-white shadow-none backdrop-blur-sm';
});

const textToneClass = computed(() => (isSolid.value ? 'text-slate-900' : 'text-white'));
const logoToneClass = computed(() => (isSolid.value ? 'brightness-0 saturate-0' : 'brightness-100'));
const iconButtonClass = computed(() => (
  isSolid.value
    ? 'border border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100'
    : 'border border-white/18 bg-[rgba(60,66,79,0.42)] text-white hover:bg-[rgba(60,66,79,0.56)]'
));
const secondaryActionClass = computed(() => (
  isSolid.value
    ? 'bg-slate-900 text-white hover:bg-slate-800'
    : 'border border-white/18 bg-[rgba(60,66,79,0.42)] text-white hover:bg-[rgba(60,66,79,0.56)]'
));

function navItemClass(url) {
  const active = currentPath.value === normalizeUrl(url);

  if (isSolid.value) {
    return active
      ? 'bg-slate-900 text-white'
      : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900';
  }

  return active
    ? 'bg-[rgba(60,66,79,0.54)] text-white'
    : 'text-white/92 hover:bg-[rgba(60,66,79,0.36)] hover:text-white';
}

function normalizeUrl(value) {
  const path = String(value || '').split('?')[0].replace(/\/+$/, '');
  return path === '' ? '/' : path;
}

const getScrollTop = () => {
  if (typeof window === 'undefined' || typeof document === 'undefined') {
    return 0;
  }

  return Math.max(
    window.scrollY || 0,
    window.pageYOffset || 0,
    document.documentElement?.scrollTop || 0,
    document.body?.scrollTop || 0,
  );
};

const onScroll = () => {
  isScrolled.value = getScrollTop() > 0;
};

watch(isMenuOpen, (open) => {
  if (typeof document === 'undefined') {
    return;
  }

  document.body.classList.toggle('overflow-hidden', open);
});

onMounted(() => {
  onScroll();
  window.addEventListener('scroll', onScroll, { passive: true });
});

onBeforeUnmount(() => {
  window.removeEventListener('scroll', onScroll);
  if (typeof document !== 'undefined') {
    document.body.classList.remove('overflow-hidden');
  }
});

function openMenu() {
  isMenuOpen.value = true;
}

function closeMenu() {
  isMenuOpen.value = false;
}
</script>
