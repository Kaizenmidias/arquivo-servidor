<template>
  <div v-if="isOpen" class="fixed inset-0 z-[70] overflow-hidden">
    <div class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity" @click="close"></div>

    <aside :class="[
      'fixed inset-y-0 right-0 flex h-screen w-full max-w-md flex-col overflow-hidden border-l border-slate-200 bg-white text-slate-900 shadow-[0_24px_80px_rgba(15,23,42,0.24)] transition-transform duration-300 ease-in-out',
      isOpen ? 'translate-x-0' : 'translate-x-full',
    ]">
      <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-6">
        <div>
          <div class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">Favoritos</div>
          <h2 class="mt-1 text-lg font-bold text-slate-900">Seus imóveis salvos</h2>
          <p class="mt-1 text-sm text-slate-500">
            {{ items.length === 1 ? '1 imóvel favoritado' : `${items.length} imóveis favoritados` }}
          </p>
        </div>

        <button
          type="button"
          class="flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-slate-50 text-slate-500 transition hover:border-slate-300 hover:bg-white hover:text-slate-900"
          aria-label="Fechar favoritos"
          @click="close"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <div v-if="items.length" class="flex-1 overflow-y-auto px-4 py-4 sm:px-5">
        <div class="space-y-3">
          <div
            v-for="item in items"
            :key="item.id"
            class="group relative overflow-hidden rounded-[24px] border border-slate-200 bg-white shadow-sm transition hover:border-slate-300 hover:shadow-md"
          >
            <button
              type="button"
              class="absolute right-3 top-3 z-10 flex h-8 w-8 items-center justify-center rounded-full border border-red-100 bg-white/95 text-red-500 shadow-sm transition hover:border-red-200 hover:bg-red-50"
              :aria-label="`Remover ${item.title} dos favoritos`"
              @click.stop="remove(item.id)"
            >
              <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
              </svg>
            </button>

            <a :href="item.url" class="block" @click="close">
              <div class="aspect-[16/10] overflow-hidden bg-slate-100">
                <img :src="item.photo?.src || fallbackImage" :alt="item.title" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]" loading="lazy" />
              </div>

              <div class="space-y-3 p-4">
                <div>
                  <div v-if="item.code" class="mb-2 inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-slate-600">
                    {{ item.code }}
                  </div>
                  <h3 class="line-clamp-2 text-[15px] font-bold leading-snug text-slate-900">{{ item.title }}</h3>
                  <p v-if="item.location" class="mt-1 line-clamp-1 text-sm text-slate-500">{{ item.location }}</p>
                </div>

                <div v-if="item.prices?.length" class="space-y-1.5 border-t border-slate-100 pt-3">
                  <div v-for="row in item.prices" :key="`${item.id}-${row.key}`" class="flex items-center justify-between gap-3">
                    <span class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ row.label }}</span>
                    <span class="whitespace-nowrap text-right text-[13px] font-bold tracking-tight" :class="row.key === 'rent' ? 'text-orange-700' : 'text-blue-900'">
                      {{ formatCurrencyBRL(row.value) }}<span v-if="row.suffix" class="ml-0.5">{{ row.suffix }}</span>
                    </span>
                  </div>
                </div>

                <div v-else-if="item.price" class="border-t border-slate-100 pt-3 text-right text-[13px] font-bold tracking-tight text-blue-900">
                  {{ formatCurrencyBRL(item.price) }}
                </div>
              </div>
            </a>
          </div>
        </div>
      </div>

      <div v-else class="flex flex-1 flex-col items-center justify-center px-6 text-center">
        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
          </svg>
        </div>
        <h3 class="mt-4 text-base font-semibold text-slate-900">Nenhum favorito salvo</h3>
        <p class="mt-2 max-w-xs text-sm text-slate-500">
          Clique no coração dos cards para salvar imóveis e consultá-los aqui sem sair da página atual.
        </p>
      </div>
    </aside>
  </div>
</template>

<script setup>
const props = defineProps({
  isOpen: Boolean,
  items: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(['close', 'remove']);

const fallbackImage = `data:image/svg+xml,${encodeURIComponent(
  `<svg xmlns="http://www.w3.org/2000/svg" width="800" height="500" viewBox="0 0 800 500">
    <rect width="800" height="500" fill="#e2e8f0"/>
    <rect x="110" y="90" width="580" height="280" rx="24" fill="#cbd5e1"/>
    <path d="M220 330l125-110 75 68 78-74 132 116H220z" fill="#94a3b8"/>
    <circle cx="320" cy="190" r="28" fill="#f8fafc"/>
    <text x="400" y="425" text-anchor="middle" font-family="Arial, sans-serif" font-size="28" fill="#64748b">Imagem indisponível</text>
  </svg>`
)}`;

function close() {
  emit('close');
}

function remove(propertyId) {
  emit('remove', propertyId);
}

function formatCurrencyBRL(value) {
  return Number(value || 0).toLocaleString('pt-BR', {
    style: 'currency',
    currency: 'BRL',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  });
}
</script>
