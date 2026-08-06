<template>
  <Layout>
    <section class="relative overflow-hidden bg-black py-20 text-white lg:py-28">
      <div class="relative mx-auto max-w-[1180px] px-4">
        <h1 class="text-5xl font-semibold leading-[0.98] tracking-tight md:text-6xl lg:text-7xl">Blog</h1>
        <p class="mt-6 max-w-2xl text-lg leading-8 text-white/82 md:text-xl">Dicas e notícias do mercado imobiliário</p>
      </div>
    </section>
    <section class="bg-[#f7f5f1] py-14 lg:py-20">
      <div class="mx-auto max-w-[1180px] px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div v-for="i in 6" :key="i" class="group overflow-hidden border border-black/10 bg-white shadow-[0_18px_50px_rgba(0,0,0,0.08)] transition hover:-translate-y-1 hover:shadow-[0_28px_70px_rgba(0,0,0,0.12)]">
            <img :src="placeholderImage" alt="" class="h-56 w-full object-cover grayscale transition group-hover:grayscale-0" />
            <div class="p-6">
              <span class="text-sm font-semibold uppercase tracking-[0.18em] text-black/50">Categoria</span>
              <h3 class="mt-3 text-2xl font-semibold text-black">Título do Post {{ i }}</h3>
              <p class="mt-3 line-clamp-3 leading-7 text-black/62">Um breve resumo do conteúdo do post para dar uma ideia do que se trata o artigo...</p>
              <button class="mt-5 font-semibold text-[#173b2f] hover:text-[#10291f]">Leia mais →</button>
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
        <stop offset="0" stop-color="#000000"/>
        <stop offset="1" stop-color="#57534e"/>
      </linearGradient>
    </defs>
    <rect width="400" height="250" fill="url(#g)"/>
    <text x="200" y="140" text-anchor="middle" font-family="Arial, sans-serif" font-size="18" fill="rgba(255,255,255,0.7)">Blog</text>
  </svg>`
)}`;
</script>
