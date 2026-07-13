<template>
  <div class="space-y-4">
    <button
      type="button"
      class="group relative block w-full overflow-hidden rounded-[28px] bg-slate-950 shadow-2xl"
      @click="openModal(activeIndex)"
    >
      <div class="absolute inset-0 bg-gradient-to-t from-black/35 via-transparent to-transparent opacity-80"></div>
      <component
        :is="activeItem.kind === 'video' ? 'video' : 'img'"
        :src="activeItem.kind === 'video' ? activeItem.src : activeItem.full"
        :srcset="activeItem.kind === 'image' ? (activeItem.srcset || undefined) : undefined"
        :sizes="activeItem.kind === 'image' ? (activeItem.fullSizes || undefined) : undefined"
        :poster="activeItem.kind === 'video' ? activeItem.poster : undefined"
        :alt="activeItem.alt"
        :autoplay="false"
        :controls="false"
        :muted="true"
        :playsinline="true"
        class="h-[320px] w-full object-cover transition duration-500 group-hover:scale-[1.02] sm:h-[420px] lg:h-[640px]"
        :fetchpriority="activeIndex === 0 ? 'high' : 'auto'"
      />
      <div class="absolute inset-x-0 bottom-0 flex items-center justify-between gap-3 p-4 text-left text-white sm:p-6">
        <div class="min-w-0">
          <div class="text-xs font-semibold uppercase tracking-[0.28em] text-white/70">Galeria</div>
          <div class="mt-1 text-sm text-white/90 sm:text-base">
            Clique para ampliar e navegar pelas imagens
          </div>
        </div>
        <div class="rounded-full border border-white/20 bg-white/10 px-3 py-1 text-sm font-semibold backdrop-blur-md">
          {{ activeIndex + 1 }} / {{ totalItems }}
        </div>
      </div>
    </button>

    <DraggableScroller v-if="totalItems > 1" viewport-class="pb-2" content-class="flex gap-3 pr-3">
      <button
        v-for="(item, index) in items"
        :key="`strip-${item.id}`"
        type="button"
        :class="index === activeIndex ? 'border-white/90 ring-2 ring-slate-900/10' : 'border-transparent hover:border-slate-300'"
        class="relative h-20 w-28 shrink-0 overflow-hidden rounded-2xl border bg-slate-100 transition"
        @click="setActive(index)"
      >
        <component
          :is="item.kind === 'video' ? 'video' : 'img'"
          :src="item.kind === 'video' ? item.src : item.thumb"
          :srcset="item.kind === 'image' ? (item.srcset || undefined) : undefined"
          :sizes="item.kind === 'image' ? '(max-width: 768px) 33vw, 600px' : undefined"
          :poster="item.kind === 'video' ? item.poster : undefined"
          :alt="item.alt"
          :autoplay="false"
          :controls="false"
          :muted="true"
          :playsinline="true"
          class="h-full w-full object-cover"
          loading="lazy"
        />
        <div
          v-if="index === items.length - 1 && totalItems > items.length"
          class="absolute inset-0 flex items-center justify-center bg-black/60 text-xl font-bold text-white backdrop-blur-sm"
        >
          +{{ totalItems - items.length }}
        </div>
      </button>
    </DraggableScroller>

    <Teleport to="body">
      <Transition name="gallery-lightbox">
        <div
          v-if="isModalOpen"
          class="fixed inset-0 z-[120] flex items-center justify-center bg-black/95 px-2 py-2 backdrop-blur-sm sm:px-4"
          @click.self="closeModal"
        >
          <div class="absolute inset-x-0 top-0 flex items-center justify-between px-4 py-4 text-white sm:px-6">
            <div class="rounded-full border border-white/15 bg-white/10 px-4 py-2 text-sm font-semibold backdrop-blur-md">
              {{ activeIndex + 1 }} / {{ totalItems }}
            </div>
            <button
              type="button"
              class="flex h-11 w-11 items-center justify-center rounded-full border border-white/20 bg-black/65 text-white shadow-lg transition hover:bg-black/80"
              aria-label="Fechar galeria"
              @click="closeModal"
            >
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M18 6L6 18" />
              </svg>
            </button>
          </div>

          <div class="flex h-full w-full flex-col items-center justify-center gap-4 pt-16 sm:gap-5">
            <div class="relative flex h-[78vh] w-[92vw] items-center justify-center overflow-hidden rounded-[28px] bg-black shadow-[0_30px_90px_rgba(0,0,0,0.65)] sm:h-[82vh] sm:w-[90vw]">
              <button
                v-if="totalItems > 1"
                type="button"
                class="absolute left-3 top-1/2 z-10 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full border border-white/15 bg-black/35 text-white transition hover:bg-black/50 sm:left-5"
                aria-label="Imagem anterior"
                @click.stop="goPrev"
              >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
              </button>

              <div
                class="flex h-full w-full items-center justify-center overflow-hidden bg-black"
                style="touch-action: pan-y"
                @touchstart.passive="onTouchStart"
                @touchend.passive="onTouchEnd"
              >
                <Transition name="gallery-media" mode="out-in">
                  <component
                    :is="activeItem.kind === 'video' ? 'video' : 'img'"
                    :key="activeItem.id"
                    :src="activeItem.kind === 'video' ? activeItem.src : activeItem.full"
                    :srcset="activeItem.kind === 'image' ? (activeItem.srcset || undefined) : undefined"
                    :sizes="activeItem.kind === 'image' ? (activeItem.fullSizes || undefined) : undefined"
                    :poster="activeItem.kind === 'video' ? activeItem.poster : undefined"
                    :alt="activeItem.alt"
                    :autoplay="false"
                    :controls="activeItem.kind === 'video'"
                    :muted="false"
                    :playsinline="true"
                    class="gallery-stage-media h-full w-full bg-black object-cover"
                    :class="activeItem.kind === 'image' ? (isZoomed ? 'is-zoomed cursor-zoom-out' : 'cursor-zoom-in') : ''"
                    :loading="activeIndex <= 1 ? 'eager' : 'lazy'"
                    @click.stop="toggleZoom"
                  />
                </Transition>
              </div>

              <button
                v-if="totalItems > 1"
                type="button"
                class="absolute right-3 top-1/2 z-10 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full border border-white/15 bg-black/35 text-white transition hover:bg-black/50 sm:right-5"
                aria-label="Próxima imagem"
                @click.stop="goNext"
              >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </button>
            </div>

            <DraggableScroller v-if="totalItems > 1" viewport-class="w-[92vw] pb-1 sm:w-[90vw]" content-class="mx-auto flex w-max min-w-full gap-3 px-1">
                <button
                  v-for="(item, index) in items"
                  :key="`modal-thumb-${item.id}`"
                  type="button"
                  :class="index === activeIndex ? 'border-white/90 opacity-100' : 'border-transparent opacity-65 hover:opacity-100'"
                  class="relative h-20 w-28 shrink-0 overflow-hidden rounded-2xl border-2 bg-white/5 transition"
                  @click="setActive(index)"
                >
                  <component
                    :is="item.kind === 'video' ? 'video' : 'img'"
                    :src="item.kind === 'video' ? item.src : item.thumb"
                    :srcset="item.kind === 'image' ? (item.srcset || undefined) : undefined"
                    :sizes="item.kind === 'image' ? '(max-width: 768px) 33vw, 600px' : undefined"
                    :poster="item.kind === 'video' ? item.poster : undefined"
                    :alt="item.alt"
                    :autoplay="false"
                    :controls="false"
                    :muted="true"
                    :playsinline="true"
                    class="h-full w-full object-cover"
                    loading="lazy"
                  />
                </button>
            </DraggableScroller>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import DraggableScroller from '@/Shared/DraggableScroller.vue';

const props = defineProps({
  images: {
    type: Array,
    default: () => [],
  },
  initialIndex: {
    type: Number,
    default: 0,
  },
});

const placeholderImage = `data:image/svg+xml,${encodeURIComponent(
  `<svg xmlns="http://www.w3.org/2000/svg" width="1600" height="1000" viewBox="0 0 1600 1000">
    <defs>
      <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
        <stop offset="0" stop-color="#020617"/>
        <stop offset="1" stop-color="#111827"/>
      </linearGradient>
    </defs>
    <rect width="1600" height="1000" fill="url(#g)"/>
    <rect x="220" y="160" width="1160" height="680" rx="36" fill="rgba(255,255,255,0.08)"/>
    <path d="M360 720l250-235 155 130 180-170 295 275H360z" fill="rgba(255,255,255,0.18)"/>
    <circle cx="530" cy="380" r="70" fill="rgba(255,255,255,0.18)"/>
    <text x="800" y="900" text-anchor="middle" font-family="Arial, sans-serif" font-size="34" fill="rgba(255,255,255,0.72)">Imagem indisponivel</text>
  </svg>`
)}`;

function containsTemporaryImageUrl(value) {
  const normalized = typeof value === 'string' ? value : '';

  return normalized.includes('/storage/tmp/property-images/')
    || normalized.includes('/media/tmp/property-images/')
    || normalized.includes('tmp/property-images/');
}

function normalizePublicImageUrl(value) {
  const normalized = typeof value === 'string' ? value.trim() : '';

  if (!normalized || containsTemporaryImageUrl(normalized)) {
    return '';
  }

  return normalized;
}

function normalizePublicSrcset(value) {
  const normalized = typeof value === 'string' ? value.trim() : '';

  if (!normalized || containsTemporaryImageUrl(normalized)) {
    return null;
  }

  return normalized;
}

function clampIndex(index, length) {
  if (!length) return 0;
  const numeric = Number(index ?? 0);
  if (Number.isNaN(numeric)) return 0;
  return Math.min(Math.max(Math.trunc(numeric), 0), length - 1);
}

const items = computed(() => {
  const incoming = Array.isArray(props.images) ? props.images : [];

  if (!incoming.length) {
    return [
      {
        id: 'placeholder',
        kind: 'image',
        full: placeholderImage,
        thumb: placeholderImage,
        src: placeholderImage,
        poster: null,
        alt: 'Imagem indisponivel',
      },
    ];
  }

  return incoming.map((item, index) => {
    const kind = item?.type === 'video' ? 'video' : 'image';
    const full = normalizePublicImageUrl(item?.full);
    const medium = normalizePublicImageUrl(item?.medium);
    const thumb = normalizePublicImageUrl(item?.thumb);
    const src = normalizePublicImageUrl(item?.src);
    const poster = normalizePublicImageUrl(item?.poster);
    const srcset = normalizePublicSrcset(item?.srcset);
    const fallback = full || medium || thumb || src || poster || placeholderImage;

    if (import.meta.env.VITE_TRAE_DEBUG_FRONT_GALLERY_TMP_URLS === '1') {
      // #region debug-point B:gallery-item-received
      fetch('http://127.0.0.1:7777/event', {
        method: 'POST',
        body: JSON.stringify({
          sessionId: 'front-gallery-tmp-urls',
          runId: 'pre-fix',
          hypothesisId: 'B',
          location: 'resources/js/Components/PropertyGallery.vue:items',
          msg: '[DEBUG] Property gallery received item payload',
          data: {
            index,
            id: item?.id ?? null,
            src,
            thumb,
            medium,
            full,
            srcset,
            containsTmp: [
              src,
              thumb,
              medium,
              full,
              srcset,
            ].some((candidate) => containsTemporaryImageUrl(candidate)),
          },
          ts: Date.now(),
        }),
      }).catch(() => {});
      // #endregion
    }

    return {
      id: item?.id ?? `${kind}-${index}`,
      kind,
      full: full || fallback,
      medium: medium || full || thumb || fallback,
      thumb: thumb || medium || full || fallback,
      src: src || thumb || medium || full || fallback,
      srcset,
      sizes: item?.sizes || '(max-width: 768px) 100vw, 1200px',
      fullSizes: item?.full_sizes || '(max-width: 768px) 100vw, 1920px',
      poster: poster || thumb || medium || fallback,
      alt: item?.alt || `Midia ${index + 1}`,
    };
  });
});

const totalItems = computed(() => items.value.length);
const activeIndex = ref(clampIndex(props.initialIndex, totalItems.value));
const isModalOpen = ref(false);
const isZoomed = ref(false);
const touchStartX = ref(0);

const activeItem = computed(() => items.value[clampIndex(activeIndex.value, totalItems.value)] || items.value[0]);

function setActive(index) {
  activeIndex.value = clampIndex(index, totalItems.value);
}

function openModal(index = activeIndex.value) {
  setActive(index);
  isModalOpen.value = true;
  isZoomed.value = false;
}

function closeModal() {
  isModalOpen.value = false;
  isZoomed.value = false;
}

function goNext() {
  if (totalItems.value <= 1) return;
  isZoomed.value = false;
  activeIndex.value = (activeIndex.value + 1) % totalItems.value;
}

function goPrev() {
  if (totalItems.value <= 1) return;
  isZoomed.value = false;
  activeIndex.value = (activeIndex.value - 1 + totalItems.value) % totalItems.value;
}

function toggleZoom() {
  if (!isModalOpen.value || activeItem.value.kind !== 'image') return;

  isZoomed.value = !isZoomed.value;
}

function preloadIndex(index) {
  const item = items.value[clampIndex(index, totalItems.value)];
  if (!item || item.kind !== 'image' || typeof window === 'undefined') return;

  const image = new window.Image();
  image.src = item.full;
}

function preloadAround(index) {
  preloadIndex(index);
  preloadIndex(index + 1);
  preloadIndex(index - 1);
}

function handleKeydown(event) {
  if (!isModalOpen.value) return;

  if (event.key === 'Escape') {
    closeModal();
    return;
  }

  if (event.key === 'ArrowRight') {
    goNext();
    return;
  }

  if (event.key === 'ArrowLeft') {
    goPrev();
  }
}

function onTouchStart(event) {
  touchStartX.value = event.changedTouches?.[0]?.clientX || 0;
}

function onTouchEnd(event) {
  const touchEndX = event.changedTouches?.[0]?.clientX || 0;
  const delta = touchEndX - touchStartX.value;

  if (Math.abs(delta) < 40) return;
  if (delta < 0) {
    goNext();
    return;
  }

  goPrev();
}

watch(
  () => props.initialIndex,
  (value) => {
    setActive(value);
    isZoomed.value = false;
  }
);

watch(
  items,
  (value) => {
    activeIndex.value = clampIndex(activeIndex.value, value.length);
    isZoomed.value = false;
    preloadAround(activeIndex.value);
  },
  { immediate: true }
);

watch(
  activeIndex,
  (value) => {
    isZoomed.value = false;
    preloadAround(value);
  }
);

watch(
  isModalOpen,
  (open) => {
    if (typeof document === 'undefined') return;

    document.body.style.overflow = open ? 'hidden' : '';

    if (typeof window !== 'undefined') {
      if (open) {
        window.addEventListener('keydown', handleKeydown);
      } else {
        window.removeEventListener('keydown', handleKeydown);
      }
    }
  }
);

onBeforeUnmount(() => {
  if (typeof document !== 'undefined') {
    document.body.style.overflow = '';
  }

  if (typeof window !== 'undefined') {
    window.removeEventListener('keydown', handleKeydown);
  }
});
</script>

<style scoped>
.gallery-lightbox-enter-active,
.gallery-lightbox-leave-active {
  transition: opacity 0.28s ease;
}

.gallery-lightbox-enter-from,
.gallery-lightbox-leave-to {
  opacity: 0;
}

.gallery-media-enter-active,
.gallery-media-leave-active {
  transition: opacity 0.24s ease, transform 0.24s ease;
}

.gallery-media-enter-from,
.gallery-media-leave-to {
  opacity: 0;
  transform: scale(0.98);
}

.gallery-stage-media {
  transition: transform 220ms ease;
}

.gallery-stage-media.is-zoomed {
  transform: scale(1.6);
}
</style>
