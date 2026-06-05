<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 overflow-hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-gray-600 bg-opacity-75 transition-opacity" @click="close"></div>
    <!-- Side panel -->
    <div :class="[
      'fixed inset-y-0 right-0 max-w-xs w-full bg-blue-900 text-white shadow-xl transform transition-transform duration-300 ease-in-out',
      isOpen ? 'translate-x-0' : 'translate-x-full'
    ]">
      <div class="flex items-center justify-between h-20 px-4">
        <div class="text-2xl font-bold">Imobiliária</div>
        <button @click="close" class="p-2 hover:bg-blue-800 rounded-full transition">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
      <nav class="mt-6 px-4">
        <div class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">Menu</div>
        <div class="space-y-4">
          <a v-for="item in menuItems" :key="item.id" :href="item.url" @click="close" class="flex items-center space-x-3 p-3 hover:bg-blue-800 rounded-lg transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <template v-if="item.icon === 'users'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
              </template>
              <template v-else-if="item.icon === 'tag'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
              </template>
              <template v-else-if="item.icon === 'key'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
              </template>
              <template v-else-if="item.icon === 'calculator'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m-6 4h3m-3 4h6M8 3h8a2 2 0 012 2v14a2 2 0 01-2 2H8a2 2 0 01-2-2V5a2 2 0 012-2z"></path>
              </template>
              <template v-else-if="item.icon === 'home'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
              </template>
              <template v-else-if="item.icon === 'user-tie'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
              </template>
              <template v-else-if="item.icon === 'newspaper'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V9m-2 14a2 2 0 002-2v-4a2 2 0 00-2-2h-1m-2 4h2m-2-4H5"></path>
              </template>
              <template v-else-if="item.icon === 'phone'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
              </template>
            </svg>
            <span class="text-lg font-medium">{{ item.label }}</span>
          </a>
        </div>
      </nav>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  isOpen: Boolean,
  menuItems: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(['close']);

function close() {
  emit('close');
}
</script>
