<template>
  <Layout>
    <!-- Internal Hero -->
    <section class="bg-gradient-to-r from-blue-800 to-blue-900 text-white py-16">
      <div class="container mx-auto px-4">
        <h1 class="text-4xl font-bold text-center">Calculadora Imobiliária</h1>
        <div class="flex justify-center mt-4 text-sm">
          <span><a href="/" class="hover:text-blue-200">Início</a></span>
          <span class="mx-2">/</span>
          <span>Calculadora</span>
        </div>
      </div>
    </section>

    <!-- Calculator -->
    <section class="py-16 bg-gray-50">
      <div class="container mx-auto px-4">
        <div class="bg-white rounded-xl shadow-lg p-8 max-w-3xl mx-auto">
          <h2 class="text-2xl font-bold text-gray-800 mb-8 text-center">Simule Seu Financiamento</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="space-y-6">
              <div>
                <label class="block text-gray-700 font-medium mb-2">Valor do Imóvel</label>
                <input 
                  type="number" 
                  v-model="propertyValue" 
                  placeholder="R$ 0,00" 
                  class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  @input="calculate"
                />
              </div>
              <div>
                <label class="block text-gray-700 font-medium mb-2">Entrada (%)</label>
                <input 
                  type="number" 
                  v-model="downPaymentPercent" 
                  placeholder="20" 
                  class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  @input="calculate"
                />
              </div>
              <div>
                <label class="block text-gray-700 font-medium mb-2">Prazo (meses)</label>
                <input 
                  type="number" 
                  v-model="loanTerm" 
                  placeholder="360" 
                  class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  @input="calculate"
                />
              </div>
              <div>
                <label class="block text-gray-700 font-medium mb-2">Taxa de Juros Anual (%)</label>
                <input 
                  type="number" 
                  v-model="interestRate" 
                  step="0.01" 
                  placeholder="8.5" 
                  class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  @input="calculate"
                />
              </div>
            </div>
            
            <div class="bg-blue-50 rounded-xl p-8">
              <h3 class="text-xl font-bold text-blue-800 mb-6">Resultado da Simulação</h3>
              <div class="space-y-4">
                <div class="flex justify-between text-lg">
                  <span class="text-gray-700">Valor do Imóvel:</span>
                  <span class="font-semibold text-gray-800">{{ formatCurrency(propertyValue) }}</span>
                </div>
                <div class="flex justify-between text-lg">
                  <span class="text-gray-700">Entrada:</span>
                  <span class="font-semibold text-gray-800">{{ formatCurrency(downPayment) }}</span>
                </div>
                <div class="flex justify-between text-lg">
                  <span class="text-gray-700">Valor Financiado:</span>
                  <span class="font-semibold text-gray-800">{{ formatCurrency(loanAmount) }}</span>
                </div>
                <hr class="border-blue-200" />
                <div class="flex justify-between text-2xl font-bold text-blue-600">
                  <span>Parcela Estimada:</span>
                  <span>{{ formatCurrency(monthlyPayment) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </Layout>
</template>

<script setup>
import { ref, computed } from 'vue';
import Layout from '@/Shared/Layout.vue';

const propertyValue = ref(350000);
const downPaymentPercent = ref(20);
const loanTerm = ref(360);
const interestRate = ref(8.5);

const downPayment = computed(() => propertyValue.value * (downPaymentPercent.value / 100));
const loanAmount = computed(() => propertyValue.value - downPayment.value);
const monthlyRate = computed(() => interestRate.value / 100 / 12);
const monthlyPayment = computed(() => {
  if (monthlyRate.value === 0) return loanAmount.value / loanTerm.value;
  return (loanAmount.value * monthlyRate.value * Math.pow(1 + monthlyRate.value, loanTerm.value)) / 
    (Math.pow(1 + monthlyRate.value, loanTerm.value) - 1);
});

function formatCurrency(value) {
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value || 0);
}
</script>
