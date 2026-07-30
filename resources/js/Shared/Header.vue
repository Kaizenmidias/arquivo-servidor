<template>
  <header class="fixed inset-x-0 top-0 z-50 transition-all duration-300">
    <div class="ui-shell">
      <div class="mt-3 flex h-[72px] items-center justify-between rounded-2xl border px-4 transition-all duration-300 lg:px-6" :class="headerSurfaceClass">
        <a href="/" class="flex min-w-0 items-center gap-3">
          <img v-if="logoUrl" :src="logoUrl" :alt="siteName" class="h-10 w-auto max-w-[180px] object-contain transition-[filter] duration-300 lg:max-w-[220px]" :class="logoToneClass" />
          <div v-else class="text-lg font-semibold tracking-tight" :class="textToneClass">{{ siteName }}</div>
        </a>

        <nav class="hidden xl:flex items-center gap-1">
          <div v-for="item in primaryLinks" :key="item.key" class="relative" @mouseenter="openDesktopSubmenu(item.key)" @mouseleave="closeDesktopSubmenu(item.key)">
            <a :href="item.url" class="rounded-full px-4 py-2 text-sm font-medium transition inline-flex items-center gap-1" :class="navItemClass(item.url, item.key)">
              <span>{{ item.label }}</span>
              <svg v-if="item.children?.length" class="h-3.5 w-3.5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 9l-7 7-7-7" />
              </svg>
            </a>
            <div v-if="item.children?.length && openDesktopMenuKey === item.key" class="absolute left-0 top-full h-3 w-full"></div>
            <transition name="fade">
              <div v-if="item.children?.length && openDesktopMenuKey === item.key" class="absolute left-0 top-full z-50 min-w-[280px] rounded-2xl border border-slate-200 bg-white p-2 shadow-[0_24px_60px_rgba(15,23,42,0.16)]">
                <a v-for="child in item.children" :key="child.key" :href="child.url" class="block rounded-xl px-4 py-3 text-sm font-medium transition" :class="navItemClass(child.url, child.key, true)">
                  {{ child.label }}
                </a>
              </div>
            </transition>
          </div>
        </nav>

        <div class="flex items-center gap-2">
          <a href="/contato" class="hidden rounded-full px-4 py-2 text-sm font-semibold transition lg:inline-flex" :class="secondaryActionClass">Fale conosco</a>
          <button type="button" class="flex h-9 w-9 items-center justify-center rounded-full transition" :class="iconButtonClass">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.55" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
          </button>
          <button type="button" class="relative flex h-9 w-9 items-center justify-center rounded-full transition" :class="iconButtonClass" aria-label="Abrir favoritos" @click="toggleFavoritesPanel">
            <span v-if="favoriteCount" class="absolute -top-1.5 -right-1.5 inline-flex min-w-[18px] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold leading-4 text-white shadow-sm">{{ favoriteCount > 99 ? '99+' : favoriteCount }}</span>
            <svg class="h-3.5 w-3.5" :fill="favoriteCount ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.55" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
          </button>
          <button type="button" class="flex h-9 w-9 items-center justify-center rounded-full transition" :class="iconButtonClass" @click="openMenu">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.55" d="M4 7h16M4 12h16M4 17h16" /></svg>
          </button>
        </div>
      </div>
    </div>

    <transition name="fade">
      <DrawerMenu v-if="isMenuOpen" :is-open="isMenuOpen" :menu-items="menuItems" @close="closeMenu" />
    </transition>

    <transition name="fade">
      <FavoritesPanel v-if="isFavoritesOpen" :is-open="isFavoritesOpen" :items="favoriteItems" @close="closeFavorites" @remove="removeFavorite" />
    </transition>
  </header>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import DrawerMenu from './DrawerMenu.vue';
import FavoritesPanel from './FavoritesPanel.vue';
import { useFavorites } from '@/composables/useFavorites';

const isMenuOpen = ref(false);
const isFavoritesOpen = ref(false);
const isScrolled = ref(false);
const openDesktopMenuKey = ref('');

const page = usePage();
const { favoriteCount, favoriteItems, hydrateFavorites, removeFavorite } = useFavorites();
const menuItems = computed(() => page.props.menuItems || []);
const settings = computed(() => page.props.settings || {});
const specialCategories = computed(() => Array.isArray(page.props.specialCategories) ? page.props.specialCategories : []);
const logoUrl = computed(() => settings.value.logo_url || '');
const siteName = computed(() => settings.value.nome_empresa || 'Imobiliária');
const currentPath = computed(() => normalizeUrl(page.url || '/'));
const usesTransparentHeader = computed(() => page.component === 'Home' || currentPath.value === '/');
const isSolid = computed(() => !usesTransparentHeader.value || isScrolled.value || isMenuOpen.value);

const primaryLinks = computed(() => [
  { key: 'home', label: 'Início', url: '/' },
  {
    key: 'imoveis',
    label: 'Imóveis',
    url: '/imoveis',
    children: specialCategories.value.map((category) => ({
      key: String(category.id),
      label: category.name,
      url: category.url || `/imoveis?special_category_ids[]=${category.id}`,
    })),
  },
  { key: 'venda', label: 'Venda seu Imóvel', url: '/venda-seu-imovel' },
]);

const headerSurfaceClass = computed(() => (isSolid.value
  ? 'border-white bg-white text-slate-900 shadow-[0_18px_50px_rgba(15,23,42,0.10)]'
  : 'border-white/18 bg-[rgba(60,66,79,0.34)] text-white shadow-none backdrop-blur-sm'));
const textToneClass = computed(() => (isSolid.value ? 'text-slate-900' : 'text-white'));
const logoToneClass = computed(() => (isSolid.value ? 'brightness-0 saturate-0' : 'brightness-100'));
const iconButtonClass = computed(() => (isSolid.value
  ? 'border border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100'
  : 'border border-white/18 bg-[rgba(60,66,79,0.42)] text-white hover:bg-[rgba(60,66,79,0.56)]'));
const secondaryActionClass = computed(() => (isSolid.value
  ? 'bg-slate-900 text-white hover:bg-slate-800'
  : 'border border-white/18 bg-[rgba(60,66,79,0.42)] text-white hover:bg-[rgba(60,66,79,0.56)]'));
const isOverlayOpen = computed(() => isMenuOpen.value || isFavoritesOpen.value);

function navItemClass(url, key, isChild = false) {
  const activePath = currentPath.value === normalizeUrl(url);
  const query = new URLSearchParams((page.url || '').split('?')[1] || '');
  const activeSpecialIds = [...query.getAll('special_category_ids[]'), ...query.getAll('special_category_ids')];
  const active = isChild ? activeSpecialIds.includes(String(key)) : activePath || (key === 'imoveis' && currentPath.value === '/imoveis');

  if (isSolid.value) {
    return active ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900';
  }

  return active ? 'bg-[rgba(60,66,79,0.54)] text-white' : 'text-white/92 hover:bg-[rgba(60,66,79,0.36)] hover:text-white';
}

function normalizeUrl(value) {
  const path = String(value || '').split('?')[0].replace(/\/+$/, '');
  return path === '' ? '/' : path;
}

function openDesktopSubmenu(key) {
  if (key === 'imoveis') openDesktopMenuKey.value = key;
}

function closeDesktopSubmenu(key) {
  if (openDesktopMenuKey.value === key) openDesktopMenuKey.value = '';
}

const getScrollTop = () => Math.max(window.scrollY || 0, window.pageYOffset || 0, document.documentElement?.scrollTop || 0, document.body?.scrollTop || 0);
const onScroll = () => { isScrolled.value = getScrollTop() > 0; };

watch(isOverlayOpen, (open) => {
  if (typeof document === 'undefined') return;
  document.body.classList.toggle('overflow-hidden', open);
});

onMounted(() => {
  hydrateFavorites();
  onScroll();
  window.addEventListener('scroll', onScroll, { passive: true });
});

onBeforeUnmount(() => {
  window.removeEventListener('scroll', onScroll);
  if (typeof document !== 'undefined') document.body.classList.remove('overflow-hidden');
});

function openMenu() {
  isFavoritesOpen.value = false;
  isMenuOpen.value = true;
}

function closeMenu() {
  isMenuOpen.value = false;
}

function toggleFavoritesPanel() {
  isMenuOpen.value = false;
  isFavoritesOpen.value = !isFavoritesOpen.value;
}

function closeFavorites() {
  isFavoritesOpen.value = false;
}
</script>
