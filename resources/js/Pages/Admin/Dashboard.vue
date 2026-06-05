<template>
  <AdminLayout>
    <template #pageTitle>Dashboard</template>

    <div class="space-y-8">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <div class="text-sm text-gray-500">Bem-vindo(a) de volta!</div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <div class="bg-white border border-gray-200 rounded-xl shadow px-3 py-2 flex items-center gap-2">
            <input v-model="rangeStart" type="date" class="text-sm border border-gray-200 rounded-lg px-2 py-1">
            <span class="text-gray-400 text-sm">—</span>
            <input v-model="rangeEnd" type="date" class="text-sm border border-gray-200 rounded-lg px-2 py-1">
            <button type="button" class="ml-1 text-sm font-semibold text-blue-700 hover:text-blue-800" @click="applyRange">Aplicar</button>
          </div>
          <button type="button" class="bg-white border border-gray-200 rounded-xl shadow px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50">
            Exportar Relatório
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
        <div
          v-for="card in kpiCards"
          :key="card.key"
          class="bg-white rounded-xl shadow border border-gray-200 p-5"
        >
          <div class="flex items-start gap-4">
            <div class="w-11 h-11 rounded-xl bg-gray-50 border border-gray-200 flex items-center justify-center text-gray-700 flex-shrink-0">
              <svg v-if="card.icon === 'home'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9-9 9 9v9a2 2 0 01-2 2h-4a2 2 0 01-2-2V12H9v7a2 2 0 01-2 2H3v-9z"/></svg>
              <svg v-else-if="card.icon === 'star'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l2.21 6.804a1 1 0 00.95.69h7.154c.969 0 1.371 1.24.588 1.81l-5.79 4.205a1 1 0 00-.364 1.118l2.21 6.804c.3.921-.755 1.688-1.54 1.118l-5.79-4.205a1 1 0 00-1.176 0l-5.79 4.205c-.784.57-1.838-.197-1.539-1.118l2.21-6.804a1 1 0 00-.364-1.118L.1 12.231c-.783-.57-.38-1.81.588-1.81h7.154a1 1 0 00.95-.69l2.21-6.804z"/></svg>
              <svg v-else-if="card.icon === 'users'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H2v-2a4 4 0 014-4h1m6-4a4 4 0 10-8 0 4 4 0 008 0zm6 4a3 3 0 10-6 0 3 3 0 006 0z"/></svg>
              <svg v-else-if="card.icon === 'bolt'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
              <svg v-else-if="card.icon === 'eye'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              <svg v-else-if="card.icon === 'mail'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l9 6 9-6M4 6h16a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2z"/></svg>
            </div>
            <div class="min-w-0 flex-1">
              <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ card.title }}</div>
              <div class="text-3xl font-extrabold mt-2" :class="card.accent">{{ card.value }}</div>
              <div class="text-sm text-gray-600 mt-1">{{ card.subtitle }}</div>
              <div class="flex items-center gap-2 mt-3 text-xs">
                <div v-if="deltaText(card.delta)" class="font-bold" :class="deltaClass(card.delta)">
                  {{ deltaText(card.delta) }}
                </div>
                <div v-else class="font-bold text-gray-400">—</div>
                <div class="text-gray-500">vs. período anterior</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <div v-for="s in statusCards" :key="s.key" class="bg-white rounded-xl shadow border border-gray-200 p-4">
          <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ s.title }}</div>
          <div class="text-2xl font-extrabold text-gray-900 mt-2">{{ s.value }}</div>
          <div class="mt-2 text-xs flex items-center gap-2">
            <div v-if="deltaText(s.delta)" class="font-bold" :class="deltaClass(s.delta)">{{ deltaText(s.delta) }}</div>
            <div v-else class="font-bold text-gray-400">—</div>
            <div class="text-gray-500">vs. período anterior</div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-xl shadow border border-gray-200 p-6">
          <div class="flex items-center justify-between mb-4">
            <div class="text-lg font-semibold text-gray-900">Evolução de Leads e Visualizações</div>
            <div class="text-sm text-gray-500">Últimos 12 meses</div>
          </div>
          <div>
            <div class="flex items-center gap-4 mb-3 text-sm">
              <div class="flex items-center gap-2 text-gray-700 font-semibold">
                <span class="w-3 h-3 rounded-full bg-emerald-500"></span> Leads
              </div>
              <div class="flex items-center gap-2 text-gray-700 font-semibold">
                <span class="w-3 h-3 rounded-full bg-purple-500"></span> Visualizações
              </div>
            </div>
            <svg :viewBox="`0 0 ${chartW} ${chartH}`" class="w-full h-72">
              <g>
                <line v-for="i in 4" :key="'g'+i" :x1="chartPadX" :x2="chartW-chartPadX" :y1="chartPadY + ((chartH-chartPadY*2)/4)*i" :y2="chartPadY + ((chartH-chartPadY*2)/4)*i" stroke="#e5e7eb" stroke-width="1"/>
                <line :x1="chartPadX" :x2="chartW-chartPadX" :y1="chartH-chartPadY" :y2="chartH-chartPadY" stroke="#e5e7eb" stroke-width="1"/>
                <line :x1="chartPadX" :x2="chartPadX" :y1="chartPadY" :y2="chartH-chartPadY" stroke="#e5e7eb" stroke-width="1"/>
              </g>
              <polyline :points="leadsPolyline" fill="none" stroke="#10b981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
              <polyline :points="viewsPolyline" fill="none" stroke="#a855f7" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
              <g>
                <circle v-for="(p, idx) in leadsPoints" :key="'l'+idx" :cx="p.x" :cy="p.y" r="4" fill="#10b981" stroke="white" stroke-width="2"/>
                <circle v-for="(p, idx) in viewsPoints" :key="'v'+idx" :cx="p.x" :cy="p.y" r="4" fill="#a855f7" stroke="white" stroke-width="2"/>
              </g>
              <g>
                <text v-for="(lab, idx) in trend.labels" :key="'t'+idx" :x="labelX(idx)" :y="chartH-6" font-size="12" fill="#6b7280" text-anchor="middle">{{ lab }}</text>
              </g>
            </svg>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow border border-gray-200 p-6">
          <div class="flex items-center justify-between mb-4">
            <div class="text-lg font-semibold text-gray-900">Origem dos Leads</div>
            <div class="text-sm text-gray-500">%</div>
          </div>
          <div>
            <div v-if="leadOriginTotal <= 0" class="text-sm text-gray-600">Sem dados de origem de leads.</div>
            <div v-else class="flex items-start gap-6">
              <svg viewBox="0 0 220 220" class="w-44 h-44">
                <g>
                  <path v-for="(a, idx) in leadOriginArcs" :key="idx" :d="a.d" :fill="a.color"></path>
                </g>
                <circle cx="110" cy="110" r="56" fill="white"></circle>
              </svg>
              <div class="flex-1 space-y-2">
                <div v-for="(a, idx) in leadOriginArcs" :key="'l'+idx" class="flex items-center justify-between gap-4">
                  <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: a.color }"></span>
                    <div class="text-sm font-semibold text-gray-800 truncate">{{ a.label }}</div>
                  </div>
                  <div class="text-sm font-extrabold text-gray-900">{{ leadOriginPercent(a.value) }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-xl shadow border border-gray-200 overflow-hidden">
          <div class="p-6 border-b border-gray-200">
            <div class="text-lg font-semibold text-gray-900">Imóveis Mais Visualizados</div>
            <div class="text-sm text-gray-500">Top 10</div>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead class="bg-gray-50 text-gray-600">
                <tr>
                  <th class="text-left font-semibold px-6 py-3">Imóvel</th>
                  <th class="text-left font-semibold px-6 py-3">Cidade</th>
                  <th class="text-right font-semibold px-6 py-3">Visualizações</th>
                  <th class="text-right font-semibold px-6 py-3">Leads</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="p in topProperties" :key="p.id" class="hover:bg-gray-50/60">
                  <td class="px-6 py-3">
                    <div class="flex items-center gap-3">
                      <div class="w-12 h-10 rounded-lg overflow-hidden bg-gray-100 border border-gray-200 flex-shrink-0">
                        <img v-if="p.photo_url" :src="p.photo_url" alt="" class="w-full h-full object-cover">
                      </div>
                      <div class="font-medium text-gray-900 line-clamp-1">{{ p.title }}</div>
                    </div>
                  </td>
                  <td class="px-6 py-3 text-gray-700">{{ p.city }}</td>
                  <td class="px-6 py-3 text-right font-semibold text-gray-900">{{ formatNumber(p.views) }}</td>
                  <td class="px-6 py-3 text-right font-semibold text-gray-900">{{ formatNumber(p.leads) }}</td>
                </tr>
                <tr v-if="topProperties.length === 0">
                  <td colspan="4" class="px-6 py-8 text-center text-gray-500">Sem dados para exibir.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow border border-gray-200 p-6">
          <div class="flex items-center justify-between mb-4">
            <div class="text-lg font-semibold text-gray-900">Módulo SEO</div>
            <a :href="`${adminBase}/properties`" class="text-sm font-semibold text-blue-700 hover:text-blue-800">Ver imóveis</a>
          </div>
          <div class="space-y-3">
            <div v-for="row in seoRows" :key="row.key" class="flex items-center justify-between gap-4 bg-gray-50 border border-gray-200 rounded-xl px-4 py-3">
              <div class="text-sm font-semibold text-gray-800">{{ row.label }}</div>
              <div class="text-sm font-extrabold text-gray-900">{{ formatNumber(row.value) }}</div>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow border border-gray-200 overflow-hidden">
          <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <div>
              <div class="text-lg font-semibold text-gray-900">Leads Recentes</div>
              <div class="text-sm text-gray-500">Últimos recebidos</div>
            </div>
            <a :href="`${adminBase}/leads`" class="text-sm font-semibold text-blue-700 hover:text-blue-800">Ver todos os leads</a>
          </div>
          <div class="divide-y divide-gray-100">
            <div v-for="l in recentLeads" :key="l.id" class="p-5 hover:bg-gray-50/60">
              <div class="flex items-center justify-between gap-4">
                <div class="min-w-0">
                  <div class="font-semibold text-gray-900 truncate">{{ l.name }}</div>
                  <div class="text-sm text-gray-600 truncate">{{ l.phone }}<span v-if="l.property"> • {{ l.property }}</span></div>
                </div>
                <div class="text-right flex-shrink-0">
                  <div class="text-xs text-gray-500">{{ formatDateTime(l.created_at) }}</div>
                  <div class="mt-1 inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">{{ l.status }}</div>
                </div>
              </div>
            </div>
            <div v-if="recentLeads.length === 0" class="p-6 text-center text-gray-500">Nenhum lead recebido.</div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow border border-gray-200 overflow-hidden">
          <div class="p-6 border-b border-gray-200">
            <div class="text-lg font-semibold text-gray-900">Últimos Imóveis Cadastrados</div>
            <div class="text-sm text-gray-500">Últimos 5 registros</div>
          </div>
          <div class="divide-y divide-gray-100">
            <div v-for="p in recentProperties" :key="p.id" class="p-5 hover:bg-gray-50/60">
              <div class="flex items-center gap-4">
                <div class="w-14 h-12 rounded-lg overflow-hidden bg-gray-100 border border-gray-200 flex-shrink-0">
                  <img v-if="p.photo_url" :src="p.photo_url" alt="" class="w-full h-full object-cover">
                </div>
                <div class="min-w-0 flex-1">
                  <div class="font-semibold text-gray-900 truncate">{{ p.title }}</div>
                  <div class="text-sm text-gray-600 truncate">{{ p.city }}<span v-if="p.type"> • {{ p.type }}</span></div>
                </div>
                <div class="text-xs text-gray-500 flex-shrink-0">{{ formatDateTime(p.created_at) }}</div>
              </div>
            </div>
            <div v-if="recentProperties.length === 0" class="p-6 text-center text-gray-500">Nenhum imóvel cadastrado.</div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow border border-gray-200 overflow-hidden">
          <div class="p-6 border-b border-gray-200">
            <div class="text-lg font-semibold text-gray-900">Atividades Recentes</div>
            <div class="text-sm text-gray-500">Eventos do portal</div>
          </div>
          <div class="p-6 space-y-4">
            <div v-for="a in activities" :key="a.at + a.title" class="flex items-start gap-3">
              <div class="w-2.5 h-2.5 mt-2 rounded-full bg-blue-700 flex-shrink-0"></div>
              <div class="min-w-0">
                <div class="text-sm font-semibold text-gray-900">{{ a.title }}</div>
                <div class="text-sm text-gray-600 truncate">{{ a.description }}</div>
                <div class="text-xs text-gray-500 mt-1">{{ formatDateTime(a.at) }}</div>
              </div>
            </div>
            <div v-if="activities.length === 0" class="text-center text-gray-500">Sem atividades.</div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div v-if="blogStats" class="bg-white rounded-xl shadow border border-gray-200 p-6">
          <div class="text-lg font-semibold text-gray-900 mb-4">Estatísticas do Blog</div>
          <div class="grid grid-cols-3 gap-4">
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
              <div class="text-xs text-gray-500">Total de artigos</div>
              <div class="text-2xl font-bold text-gray-900">{{ formatNumber(blogStats.total_posts) }}</div>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
              <div class="text-xs text-gray-500">Visualizações totais</div>
              <div class="text-2xl font-bold text-gray-900">{{ formatNumber(blogStats.total_views) }}</div>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
              <div class="text-xs text-gray-500">Artigo mais acessado</div>
              <div class="text-sm font-semibold text-gray-900 mt-1 line-clamp-2">{{ blogStats.top_post?.title || 'Sem dados' }}</div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow border border-gray-200 p-6">
          <div class="text-lg font-semibold text-gray-900 mb-4">Status das Integrações</div>
          <div class="space-y-3">
            <div v-for="it in integrationRows" :key="it.key" class="flex items-center justify-between gap-4 bg-gray-50 border border-gray-200 rounded-xl px-4 py-3">
              <div class="text-sm font-semibold text-gray-800">{{ it.label }}</div>
              <div class="text-sm font-extrabold" :class="it.ok ? 'text-emerald-700' : 'text-gray-500'">
                {{ it.ok ? 'Conectado' : 'Não conectado' }}
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow border border-gray-200 p-6">
          <div class="text-lg font-semibold text-gray-900 mb-4">Alertas Inteligentes</div>
          <div class="space-y-3">
            <div
              v-for="(al, idx) in alerts"
              :key="idx"
              class="flex items-start gap-3 p-3 rounded-xl border"
              :class="al.level === 'warning' ? 'bg-amber-50 border-amber-200 text-amber-900' : 'bg-blue-50 border-blue-200 text-blue-900'"
            >
              <div class="w-2.5 h-2.5 rounded-full mt-1.5" :class="al.level === 'warning' ? 'bg-amber-500' : 'bg-blue-600'"></div>
              <div class="text-sm font-semibold">{{ al.text }}</div>
            </div>
            <div v-if="alerts.length === 0" class="text-sm text-gray-600">Nenhum alerta no momento.</div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Shared/AdminLayout.vue';

const props = defineProps({
  range: { type: Object, default: () => ({ start: '', end: '' }) },
  kpis: { type: Object, default: () => ({}) },
  propertyStatus: { type: Object, default: () => ({}) },
  trend: { type: Object, default: () => ({ labels: [], leads: [], views: [] }) },
  leadOrigins: { type: Array, default: () => [] },
  seo: { type: Object, default: () => ({}) },
  topProperties: { type: Array, default: () => [] },
  recentLeads: { type: Array, default: () => [] },
  recentProperties: { type: Array, default: () => [] },
  activities: { type: Array, default: () => [] },
  blogStats: { type: Object, default: null },
  integrations: { type: Object, default: () => ({}) },
  alerts: { type: Array, default: () => [] },
});

const page = usePage();
const adminBase = computed(() => page.props?.paths?.admin || '/admin');

const rangeStart = ref(props.range?.start || '');
const rangeEnd = ref(props.range?.end || '');

watch(
  () => props.range,
  (v) => {
    rangeStart.value = v?.start || '';
    rangeEnd.value = v?.end || '';
  }
);

function applyRange() {
  const payload = {};
  if (rangeStart.value) payload.start = rangeStart.value;
  if (rangeEnd.value) payload.end = rangeEnd.value;
  router.get(`${adminBase.value}`, payload, { preserveState: true, preserveScroll: true });
}

function formatNumber(value) {
  const n = Number(value || 0);
  return n.toLocaleString('pt-BR');
}

function formatDateTime(value) {
  if (!value) return '';
  const d = new Date(value);
  return d.toLocaleString('pt-BR', { day: '2-digit', month: '2-digit', year: '2-digit', hour: '2-digit', minute: '2-digit' });
}

function deltaText(value) {
  if (value === null || value === undefined) return null;
  const v = Number(value);
  if (Number.isNaN(v)) return null;
  const sign = v > 0 ? '+' : '';
  return `${sign}${v.toFixed(1)}%`;
}

function deltaClass(value) {
  if (value === null || value === undefined) return 'text-gray-400';
  const v = Number(value);
  if (Number.isNaN(v)) return 'text-gray-400';
  if (v > 0) return 'text-emerald-600';
  if (v < 0) return 'text-red-600';
  return 'text-gray-500';
}

const kpiCards = computed(() => [
  {
    key: 'properties_active',
    title: 'Imóveis Ativos',
    value: formatNumber(props.kpis?.properties_active),
    subtitle: 'Total de imóveis publicados',
    delta: props.kpis?.properties_active_delta ?? null,
    icon: 'home',
    accent: 'text-blue-900',
  },
  {
    key: 'properties_featured',
    title: 'Imóveis em Destaque',
    value: formatNumber(props.kpis?.properties_featured),
    subtitle: 'Total de imóveis em destaque',
    delta: props.kpis?.properties_featured_delta ?? null,
    icon: 'star',
    accent: 'text-indigo-700',
  },
  {
    key: 'leads_total',
    title: 'Leads Captados',
    value: formatNumber(props.kpis?.leads_total),
    subtitle: 'Total de leads recebidos',
    delta: props.kpis?.leads_total_delta ?? null,
    icon: 'users',
    accent: 'text-emerald-700',
  },
  {
    key: 'leads_today',
    title: 'Leads Hoje',
    value: formatNumber(props.kpis?.leads_today),
    subtitle: 'Leads recebidos hoje',
    delta: null,
    icon: 'bolt',
    accent: 'text-emerald-700',
  },
  {
    key: 'property_views_total',
    title: 'Visualizações dos Imóveis',
    value: formatNumber(props.kpis?.property_views_total),
    subtitle: 'Total de visualizações',
    delta: props.kpis?.property_views_total_delta ?? null,
    icon: 'eye',
    accent: 'text-purple-700',
  },
  {
    key: 'contacts_total',
    title: 'Contatos Recebidos',
    value: formatNumber(props.kpis?.contacts_total),
    subtitle: 'Total de contatos via formulários',
    delta: props.kpis?.contacts_total_delta ?? null,
    icon: 'mail',
    accent: 'text-orange-600',
  },
]);

const statusCards = computed(() => [
  { key: 'sale', title: 'Venda', value: formatNumber(props.propertyStatus?.sale), delta: props.propertyStatus?.sale_delta ?? null },
  { key: 'rent', title: 'Aluguel', value: formatNumber(props.propertyStatus?.rent), delta: props.propertyStatus?.rent_delta ?? null },
  { key: 'season', title: 'Temporada', value: formatNumber(props.propertyStatus?.season), delta: props.propertyStatus?.season_delta ?? null },
  { key: 'exclusive', title: 'Exclusivos', value: formatNumber(props.propertyStatus?.exclusive), delta: props.propertyStatus?.exclusive_delta ?? null },
  { key: 'off_market', title: 'Off Market', value: formatNumber(props.propertyStatus?.off_market), delta: props.propertyStatus?.off_market_delta ?? null },
  { key: 'inactive', title: 'Inativos', value: formatNumber(props.propertyStatus?.inactive), delta: props.propertyStatus?.inactive_delta ?? null },
]);

const seoRows = computed(() => [
  { key: 'missing_meta_title', label: 'Imóveis sem Meta Title', value: Number(props.seo?.missing_meta_title || 0) },
  { key: 'missing_meta_description', label: 'Imóveis sem Meta Description', value: Number(props.seo?.missing_meta_description || 0) },
  { key: 'missing_images', label: 'Imóveis sem imagens', value: Number(props.seo?.missing_images || 0) },
  { key: 'missing_location', label: 'Imóveis sem localização completa', value: Number(props.seo?.missing_location || 0) },
  { key: 'missing_slug_optimized', label: 'Imóveis sem slug otimizado', value: Number(props.seo?.missing_slug_optimized || 0) },
]);

const integrationRows = computed(() => [
  { key: 'google_analytics', label: 'Google Analytics', ok: !!props.integrations?.google_analytics },
  { key: 'google_tag_manager', label: 'Google Tag Manager', ok: !!props.integrations?.google_tag_manager },
  { key: 'meta_pixel', label: 'Meta Pixel', ok: !!props.integrations?.meta_pixel },
  { key: 'microsoft_clarity', label: 'Microsoft Clarity', ok: !!props.integrations?.microsoft_clarity },
]);

const chartW = 760;
const chartH = 260;
const chartPadX = 44;
const chartPadY = 26;

const chartMaxValue = computed(() => {
  const leads = Array.isArray(props.trend?.leads) ? props.trend.leads : [];
  const views = Array.isArray(props.trend?.views) ? props.trend.views : [];
  const all = [...leads, ...views].map((v) => Number(v || 0));
  return Math.max(1, ...all);
});

function labelX(idx) {
  const labels = Array.isArray(props.trend?.labels) ? props.trend.labels : [];
  const len = Math.max(1, labels.length);
  const step = (chartW - chartPadX * 2) / Math.max(1, len - 1);
  return chartPadX + idx * step;
}

function pointsFor(arr) {
  const labels = Array.isArray(props.trend?.labels) ? props.trend.labels : [];
  const len = Math.max(1, labels.length);
  const step = (chartW - chartPadX * 2) / Math.max(1, len - 1);
  return labels.map((_, idx) => {
    const x = chartPadX + idx * step;
    const v = Number(arr?.[idx] || 0);
    const y = chartH - chartPadY - (v / chartMaxValue.value) * (chartH - chartPadY * 2);
    return { x, y, v };
  });
}

const leadsPoints = computed(() => pointsFor(props.trend?.leads || []));
const viewsPoints = computed(() => pointsFor(props.trend?.views || []));
const leadsPolyline = computed(() => leadsPoints.value.map((p) => `${p.x},${p.y}`).join(' '));
const viewsPolyline = computed(() => viewsPoints.value.map((p) => `${p.x},${p.y}`).join(' '));

const leadOriginTotal = computed(() => (Array.isArray(props.leadOrigins) ? props.leadOrigins : []).reduce((acc, i) => acc + Number(i.count || 0), 0));

function polarToCartesian(cx, cy, r, angle) {
  const a = ((angle - 90) * Math.PI) / 180.0;
  return { x: cx + r * Math.cos(a), y: cy + r * Math.sin(a) };
}

function describeArc(cx, cy, r, startAngle, endAngle) {
  const start = polarToCartesian(cx, cy, r, endAngle);
  const end = polarToCartesian(cx, cy, r, startAngle);
  const largeArcFlag = endAngle - startAngle <= 180 ? '0' : '1';
  return ['M', start.x, start.y, 'A', r, r, 0, largeArcFlag, 0, end.x, end.y, 'L', cx, cy, 'Z'].join(' ');
}

const leadOriginArcs = computed(() => {
  const items = Array.isArray(props.leadOrigins) ? props.leadOrigins : [];
  const total = leadOriginTotal.value;
  const colors = ['#2563eb', '#7c3aed', '#10b981', '#f97316', '#ef4444', '#64748b'];
  if (total <= 0) return [];
  let start = 0;
  return items.map((it, idx) => {
    const value = Number(it.count || 0);
    const angle = (value / total) * 360;
    const end = start + angle;
    const d = describeArc(110, 110, 100, start, end);
    const arc = { label: it.label, value, color: colors[idx % colors.length], d };
    start = end;
    return arc;
  });
});

function leadOriginPercent(value) {
  const total = leadOriginTotal.value;
  if (total <= 0) return '0%';
  return `${Math.round((Number(value || 0) / total) * 100)}%`;
}
</script>
