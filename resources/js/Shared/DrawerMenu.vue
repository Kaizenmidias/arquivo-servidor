<template>
  <div v-if="isOpen" class="fixed inset-0 z-[70] overflow-hidden">
    <div class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity" @click="close"></div>
    <div :class="[
      'fixed inset-y-0 right-0 flex h-screen w-full max-w-sm flex-col overflow-hidden bg-[rgba(60,66,79,0.96)] text-white shadow-[0_24px_80px_rgba(15,23,42,0.45)] transform transition-transform duration-300 ease-in-out backdrop-blur-xl border-l border-white/10',
      isOpen ? 'translate-x-0' : 'translate-x-full'
    ]">
      <div class="flex shrink-0 items-center justify-between border-b border-white/10 px-6 py-5">
        <a href="/" class="flex items-center">
          <img v-if="logoUrl" :src="logoUrl" :alt="siteName" class="h-10 w-auto object-contain brightness-100" />
          <div v-else class="text-xl font-semibold tracking-tight">{{ siteName }}</div>
        </a>
        <button @click="close" class="flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-white/5 transition hover:bg-white/10">
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.55" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
      <nav class="mt-6 flex-1 overflow-y-auto px-4 pb-6 pr-3">
        <div class="mb-4 px-2 text-[11px] font-semibold uppercase tracking-[0.24em] text-white/45">Menu</div>
        <div class="space-y-2 pr-1">
          <template v-for="item in primaryLinks" :key="item.key">
            <a
              :href="item.url"
              @click="close"
              class="flex items-center space-x-3 rounded-2xl border border-transparent px-4 py-3.5 transition hover:border-white/10 hover:bg-white/8"
              :class="navClass(item.url, item.key, false)"
            >
              <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <template v-if="item.icon === 'tag'">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.55" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                </template>
                <template v-else-if="item.icon === 'key'">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.55" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                </template>
                <template v-else>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.55" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </template>
              </svg>
              <span class="text-[15px] font-medium">{{ item.label }}</span>
            </a>

            <div v-if="item.children?.length" class="ml-4 space-y-2 border-l border-white/10 pl-4">
              <a
                v-for="child in item.children"
                :key="child.key"
                :href="child.url"
                @click="close"
                class="flex items-center space-x-3 rounded-2xl border border-transparent px-4 py-3 text-sm transition hover:border-white/10 hover:bg-white/8"
                :class="navClass(child.url, child.key, true)"
              >
                <span class="h-2 w-2 rounded-full bg-current opacity-70"></span>
                <span class="font-medium">{{ child.label }}</span>
              </a>
            </div>
          </template>

          <a
            v-for="item in menuItems"
            :key="item.id"
            :href="item.url"
            @click="close"
            class="flex items-center space-x-3 rounded-2xl border border-transparent px-4 py-3.5 transition hover:border-white/10 hover:bg-white/8"
          >
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <template v-if="item.icon === 'users'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.55" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
              </template>
              <template v-else-if="item.icon === 'tag'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.55" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
              </template>
              <template v-else-if="item.icon === 'key'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.55" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
              </template>
              <template v-else-if="item.icon === 'calculator'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.55" d="M9 7h6m-6 4h3m-3 4h6M8 3h8a2 2 0 012 2v14a2 2 0 01-2 2H8a2 2 0 01-2-2V5a2 2 0 012-2z"></path>
              </template>
              <template v-else-if="item.icon === 'home'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.55" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
              </template>
              <template v-else-if="item.icon === 'user-tie'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.55" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
              </template>
              <template v-else-if="item.icon === 'newspaper'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.55" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V9m-2 14a2 2 0 002-2v-4a2 2 0 00-2-2h-1m-2 4h2m-2-4H5"></path>
              </template>
              <template v-else-if="item.icon === 'phone'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.55" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
              </template>
            </svg>
            <span class="text-[15px] font-medium">{{ item.label }}</span>
          </a>
        </div>
      </nav>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
  isOpen: Boolean,
  menuItems: {
    type: Array,
    default: () => [],
  },
});

const page = usePage();
const settings = computed(() => page.props.settings || {});
const siteName = computed(() => settings.value.nome_empresa || 'Imobiliária');
const logoUrl = computed(() => settings.value.logo_url || '');

function normalizeUrl(value) {
  const v = (value || '').toString().trim();
  if (!v) return '';
  try {
    const u = new URL(v, 'https://example.com');
    const path = u.pathname.replace(/\/+$/, '') || '/';
    return path;
  } catch {
    return v.replace(/\/+$/, '') || '/';
  }
}

const currentGroup = computed(() => new URLSearchParams((page.url || '').split('?')[1] || '').get('property_type_group') || '');

const primaryLinks = computed(() => {
  const candidates = [
    { key: 'home', label: 'Início', url: '/', icon: 'home' },
    {
      key: 'imoveis',
      label: 'Imóveis',
      url: '/imoveis',
      icon: 'tag',
      children: [
        { key: 'residencial', label: 'Imóveis Residenciais', url: '/imoveis?property_type_group=residencial' },
        { key: 'comercial', label: 'Imóveis Comerciais', url: '/imoveis?property_type_group=comercial' },
        { key: 'rural', label: 'Imóveis Rurais', url: '/imoveis?property_type_group=rural' },
      ],
    },
    { key: 'venda', label: 'Venda seu Imóvel', url: '/venda-seu-imovel', icon: 'key' },
  ];

  const items = Array.isArray(props.menuItems) ? props.menuItems : [];
  const reserved = new Set(items.map((i) => normalizeUrl(i?.url)));
  return candidates.filter((c) => !reserved.has(normalizeUrl(c.url)));
});

function navClass(url, key, isChild = false) {
  const activePath = normalizeUrl(page.url || '/') === normalizeUrl(url);
  const active = isChild ? currentGroup.value === key : activePath;
  return active ? 'bg-white text-slate-900' : 'text-white/90 hover:bg-white/8';
}

const emit = defineEmits(['close']);

function close() {
  emit('close');
}
</script>
