<template>
  <Layout>
    <!-- Hero -->
    <section class="bg-gradient-to-r from-purple-800 to-purple-900 text-white py-16">
      <div class="container mx-auto px-4 text-center">
        <h1 class="text-4xl font-bold">Off Market</h1>
        <p class="text-xl mt-4">Imóveis exclusivos que não estão disponíveis publicamente</p>
      </div>
    </section>
    
    <!-- Content -->
    <section class="py-16 bg-gray-50">
      <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
          <div>
            <h2 class="text-3xl font-bold text-gray-800 mb-6">Imóveis Exclusivos</h2>
            <p class="text-gray-600 mb-4">
              Nossos imóveis Off Market são propriedades que não são anunciadas publicamente, garantindo privacidade tanto para compradores quanto para vendedores.
            </p>
            <p class="text-gray-600 mb-4">
              Este tipo de negócio é ideal para quem busca discreção e exclusividade, com acesso a oportunidades únicas no mercado.
            </p>
          </div>
          <div>
            <img :src="placeholderImage" alt="" class="rounded-xl shadow-lg w-full" />
          </div>
        </div>
      </div>
    </section>
  </Layout>
</template>
<script setup>
import Layout from '@/Shared/Layout.vue';

const placeholderImage = `data:image/svg+xml,${encodeURIComponent(
  `<svg xmlns="http://www.w3.org/2000/svg" width="600" height="400" viewBox="0 0 600 400">
    <defs>
      <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
        <stop offset="0" stop-color="#3b0764"/>
        <stop offset="1" stop-color="#7c3aed"/>
      </linearGradient>
    </defs>
    <rect width="600" height="400" fill="url(#g)"/>
    <text x="300" y="215" text-anchor="middle" font-family="Arial, sans-serif" font-size="22" fill="rgba(255,255,255,0.7)">Off Market</text>
  </svg>`
)}`;
</script>
