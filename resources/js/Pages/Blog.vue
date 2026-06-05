<template>
  <Layout>
    <section class="bg-gradient-to-r from-emerald-700 to-emerald-800 text-white py-16">
      <div class="container mx-auto px-4 text-center">
        <h1 class="text-4xl font-bold">Blog</h1>
        <p class="text-xl mt-4">Dicas e notícias do mercado imobiliário</p>
      </div>
    </section>
    <section class="py-12 bg-gray-50">
      <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div v-for="i in 6" :key="i" class="bg-white rounded-xl shadow overflow-hidden">
            <img :src="placeholderImage" alt="" class="w-full h-48 object-cover" />
            <div class="p-6">
              <span class="text-blue-600 text-sm font-semibold uppercase">Categoria</span>
              <h3 class="text-xl font-bold text-gray-800 mt-2">Título do Post {{ i }}</h3>
              <p class="text-gray-600 mt-2 line-clamp-3">Um breve resumo do conteúdo do post para dar uma ideia do que se trata o artigo...</p>
              <button class="mt-4 text-blue-600 font-semibold hover:text-blue-700">Leia mais →</button>
            </div>
          </div>
        </div>
      </div>
    </section>
  </Layout>
</template>
<script setup>
import Layout from '@/Shared/Layout.vue';

const placeholderImage = `data:image/svg+xml,${encodeURIComponent(
  `<svg xmlns="http://www.w3.org/2000/svg" width="400" height="250" viewBox="0 0 400 250">
    <defs>
      <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
        <stop offset="0" stop-color="#0f172a"/>
        <stop offset="1" stop-color="#047857"/>
      </linearGradient>
    </defs>
    <rect width="400" height="250" fill="url(#g)"/>
    <text x="200" y="140" text-anchor="middle" font-family="Arial, sans-serif" font-size="18" fill="rgba(255,255,255,0.7)">Blog</text>
  </svg>`
)}`;
</script>
