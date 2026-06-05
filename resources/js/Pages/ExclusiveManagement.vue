<template>
  <Layout>
    <!-- Hero -->
    <section class="bg-gradient-to-r from-green-700 to-green-800 text-white py-16">
      <div class="container mx-auto px-4 text-center">
        <h1 class="text-4xl font-bold">Gestão Exclusiva</h1>
      </div>
    </section>
    <section class="py-16 bg-white">
      <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center text-gray-800 mb-12">Vantagens da Gestão Exclusiva</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div class="bg-gray-50 p-8 rounded-xl text-center">
            <div class="text-green-600 text-5xl mb-4">📈</div>
            <h3 class="text-xl font-bold text-gray-800 mb-4">Maior Visibilidade</h3>
            <p class="text-gray-600">Seu imóvel receberá atenção especial e maior exposição em nossos canais.</p>
          </div>
          <div class="bg-gray-50 p-8 rounded-xl text-center">
            <div class="text-green-600 text-5xl mb-4">⏱️</div>
            <h3 class="text-xl font-bold text-gray-800 mb-4">Venda Mais Rápida</h3>
            <p class="text-gray-600">Dedicamos nossos esforços para vender seu imóvel no menor tempo possível.</p>
          </div>
          <div class="bg-gray-50 p-8 rounded-xl text-center">
            <div class="text-green-600 text-5xl mb-4">💰</div>
            <h3 class="text-xl font-bold text-gray-800 mb-4">Melhor Preço</h3>
            <p class="text-gray-600">Nossa expertise garante que você obtenha o melhor valor pelo seu imóvel.</p>
          </div>
        </div>
      </div>
    </section>
  </Layout>
</template>
<script setup>
import Layout from '@/Shared/Layout.vue';
</script>
