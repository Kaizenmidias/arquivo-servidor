<template>
  <section class="bg-white py-8 border-b border-gray-100">
    <div class="max-w-[1400px] mx-auto px-4">
      
      <div class="relative">
        <!-- Carousel Container -->
        <div
          ref="carouselRef"
          class="overflow-x-hidden cursor-grab active:cursor-grabbing"
          @mousedown="startDrag"
          @mousemove="onDrag"
          @mouseup="stopDrag"
          @mouseleave="stopDrag"
          @touchstart="startDrag"
          @touchmove="onDrag"
          @touchend="stopDrag"
        >
          <div
            class="flex gap-4 transition-transform duration-300 ease-out"
            :style="{ transform: `translateX(${position}px)` }"
            ref="trackRef"
          >
            <a v-for="category in categories" :key="category.id" :href="category.link" class="group flex-shrink-0 w-64">
              <div class="relative rounded-2xl overflow-hidden shadow-md group-hover:shadow-lg transition-shadow">
                <img
                  :src="category.image"
                  :alt="category.name"
                  class="w-full h-80 object-cover transform group-hover:scale-110 transition-transform duration-500 ease-out"
                />
                <!-- Overlay -->
                <div class="absolute inset-0 bg-black/60 group-hover:bg-black/40 transition-colors duration-300"></div>
                
                <div class="absolute top-4 left-4">
                  <div class="bg-white/90 p-2 rounded-full shadow-md backdrop-blur-sm">
                    <svg class="w-6 h-6 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                  </div>
                </div>
                
                <div class="absolute bottom-0 left-0 right-0 p-6">
                  <h3 class="text-white font-bold text-xl mb-1">{{ category.name }}</h3>
                  <p class="text-white/95 text-base mb-2">{{ category.description }}</p>
                  <div class="flex items-center gap-2 text-white font-semibold">
                    Ver imóveis
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                  </div>
                </div>
              </div>
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const carouselRef = ref(null);
const trackRef = ref(null);
const position = ref(0);
const isDragging = ref(false);
const startX = ref(0);
const startPosition = ref(0);
let maxScroll = 0;

const placeholderImage = `data:image/svg+xml,${encodeURIComponent(
  `<svg xmlns="http://www.w3.org/2000/svg" width="800" height="1000" viewBox="0 0 800 1000">
    <defs>
      <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
        <stop offset="0" stop-color="#0b1220"/>
        <stop offset="1" stop-color="#1d4ed8"/>
      </linearGradient>
    </defs>
    <rect width="800" height="1000" fill="url(#g)"/>
    <rect x="70" y="120" width="660" height="520" rx="28" fill="rgba(255,255,255,0.10)"/>
    <path d="M210 560l190-170 90 85 110-105 210 220H210z" fill="rgba(255,255,255,0.26)"/>
    <circle cx="290" cy="330" r="46" fill="rgba(255,255,255,0.22)"/>
    <text x="400" y="760" text-anchor="middle" font-family="Arial, sans-serif" font-size="26" fill="rgba(255,255,255,0.78)">Categoria</text>
  </svg>`
)}`;

const categories = ref([
  {
    id: 1, name: 'Retrofit', description: 'Reforme e transforme seu lar', image: placeholderImage, link: '/imoveis?filter=retrofit' },
  {
    id: 2, name: 'Mansões', description: 'Tamboré 1, 2 e 3', image: placeholderImage, link: '/imoveis?filter=mansoes' },
  {
    id: 3, name: 'Casas Alpha Central', description: 'Residenciais Alphaville Zero...', image: placeholderImage, link: '/imoveis?filter=alpha' },
  {
    id: 4, name: 'Casas Térreas', description: 'Arquitetura rara, unic...', image: placeholderImage, link: '/imoveis?filter=terreo' },
  {
    id: 5, name: 'Villagios', description: 'Tamboré 4, 5, 6 e 7/Singular...', image: placeholderImage, link: '/imoveis?filter=villagios' },
  {
    id: 6, name: 'Exclusividades', description: 'Nossas gestões exclusivas co...', image: placeholderImage, link: '/imoveis?filter=exclusivos' },
  {
    id: 7, name: 'Vistas Incríveis', description: 'Um quadro natural em cada janela', image: placeholderImage, link: '/imoveis?filter=vistas' }
]);

const startDrag = (e) => {
  isDragging.value = true;
  startX.value = e.type.includes('mouse') ? e.pageX : e.touches[0].clientX;
  startPosition.value = position.value;
  if (trackRef.value) {
    trackRef.value.style.transition = 'none';
  }
};

const onDrag = (e) => {
  if (!isDragging.value) return;
  const currentX = e.type.includes('mouse') ? e.pageX : e.touches[0].clientX;
  const diff = currentX - startX.value;
  position.value = Math.min(0, Math.max(-maxScroll, startPosition.value + diff));
};

const stopDrag = () => {
  isDragging.value = false;
  if (trackRef.value) {
    trackRef.value.style.transition = 'transform 0.3s ease-out';
  }
};

onMounted(() => {
  if (carouselRef.value && trackRef.value) {
    maxScroll = trackRef.value.scrollWidth - carouselRef.value.clientWidth;
  }
  window.addEventListener('resize', () => {
    if (carouselRef.value && trackRef.value) {
      maxScroll = trackRef.value.scrollWidth - carouselRef.value.clientWidth;
    }
  });
});

onUnmounted(() => {
  window.removeEventListener('resize', () => {});
});
</script>
