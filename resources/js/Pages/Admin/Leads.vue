<template>
  <AdminLayout>
    <template #pageTitle>Leads</template>

    <div class="flex items-center justify-between gap-3 mb-4">
      <div class="inline-flex rounded-lg border border-gray-200 bg-white p-1">
        <button
          type="button"
          class="px-4 py-2 rounded-md text-sm font-semibold transition"
          :class="activeCategoria === 'leads' ? 'bg-blue-900 text-white' : 'text-gray-700 hover:bg-gray-50'"
          @click="activeCategoria = 'leads'"
        >
          Leads ({{ counts.leads }})
        </button>
        <button
          type="button"
          class="px-4 py-2 rounded-md text-sm font-semibold transition"
          :class="activeCategoria === 'venda-seu-imovel' ? 'bg-blue-900 text-white' : 'text-gray-700 hover:bg-gray-50'"
          @click="activeCategoria = 'venda-seu-imovel'"
        >
          Leads Venda seu Imóvel ({{ counts.venda }})
        </button>
      </div>

      <div class="text-sm text-gray-500">Arraste os cards entre colunas para atualizar o status.</div>
    </div>

    <div class="bg-white rounded-xl shadow border border-gray-200 p-4">
      <div class="flex gap-4 overflow-x-auto pb-2">
        <div
          v-for="status in statuses"
          :key="status"
          class="min-w-[320px] w-[320px] bg-gray-50 rounded-xl border border-gray-200"
          @dragover.prevent
          @drop="(e) => onDrop(e, status)"
        >
          <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
            <div class="font-semibold text-gray-800">{{ status }}</div>
            <div class="text-xs font-semibold text-gray-600 bg-white border border-gray-200 rounded-full px-2 py-0.5">
              {{ leadsByStatus[status].length }}
            </div>
          </div>

          <div class="p-3 space-y-3 min-h-[160px]">
            <button
              v-for="lead in leadsByStatus[status]"
              :key="lead.id"
              type="button"
              class="w-full text-left bg-white rounded-lg border border-gray-200 shadow-sm p-3 hover:border-blue-300 transition"
              draggable="true"
              @dragstart="(e) => onDragStart(e, lead)"
              @click="openLead(lead.id)"
            >
              <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                  <div class="font-semibold text-gray-900 truncate">{{ lead.nome }}</div>
                  <div class="text-xs text-gray-500 mt-0.5 truncate">{{ lead.origem || 'Site' }}</div>
                </div>

                <div class="flex items-center gap-1">
                  <button
                    type="button"
                    class="p-2 rounded-md hover:bg-gray-100 transition disabled:opacity-40 disabled:cursor-not-allowed"
                    :disabled="!lead.telefone"
                    @click.stop="openWhatsApp(lead)"
                    title="WhatsApp"
                  >
                    <svg class="w-5 h-5 text-green-600" viewBox="0 0 32 32" fill="currentColor" aria-hidden="true">
                      <path d="M19.11 17.4c-.27-.13-1.6-.79-1.85-.88-.25-.09-.43-.13-.61.13-.18.27-.7.88-.86 1.06-.16.18-.32.2-.59.07-.27-.13-1.15-.42-2.19-1.34-.81-.72-1.36-1.61-1.52-1.88-.16-.27-.02-.41.12-.54.12-.12.27-.32.4-.48.13-.16.18-.27.27-.45.09-.18.04-.34-.02-.48-.07-.13-.61-1.46-.84-2-.22-.53-.45-.46-.61-.46h-.52c-.18 0-.48.07-.73.34-.25.27-.95.93-.95 2.27s.98 2.63 1.12 2.81c.13.18 1.93 2.95 4.67 4.13.65.28 1.15.45 1.54.58.65.21 1.24.18 1.71.11.52-.08 1.6-.65 1.83-1.27.22-.61.22-1.14.16-1.25-.06-.11-.25-.18-.52-.32z" />
                      <path d="M26.59 5.41A14.04 14.04 0 0016 .99C8.27.99 1.99 7.27 1.99 15c0 2.47.65 4.88 1.88 7.01L1.79 30l8.16-2.15A13.94 13.94 0 0016 29.01c7.73 0 14.01-6.28 14.01-14.01 0-3.74-1.46-7.25-3.43-9.59zM16 26.59c-2.16 0-4.27-.58-6.09-1.67l-.44-.26-4.84 1.28 1.29-4.72-.29-.48a11.6 11.6 0 01-1.78-6.1C3.85 8.46 9.46 2.85 16 2.85c3.1 0 6.01 1.2 8.2 3.39a11.52 11.52 0 013.39 8.2c0 6.54-5.61 12.15-12.15 12.15z" />
                    </svg>
                  </button>

                  <button
                    type="button"
                    class="p-2 rounded-md hover:bg-gray-100 transition disabled:opacity-40 disabled:cursor-not-allowed"
                    :disabled="!lead.email"
                    @click.stop="openEmail(lead)"
                    title="E-mail"
                  >
                    <svg class="w-5 h-5 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                  </button>
                </div>
              </div>

              <div class="mt-2 space-y-1">
                <div v-if="lead.telefone" class="text-sm text-gray-700 truncate">{{ lead.telefone }}</div>
                <div v-if="lead.email" class="text-xs text-gray-500 truncate">{{ lead.email }}</div>
              </div>

              <div v-if="lead.proximo_contato_em" class="mt-2 text-xs text-gray-600">
                Próximo contato: {{ formatDateTime(lead.proximo_contato_em) }}
              </div>

              <div class="mt-2 flex items-center justify-between text-xs text-gray-500 gap-2">
                <span class="truncate">{{ formatDate(lead.created_at) }}</span>
                <a
                  v-if="lead.property?.id"
                  class="truncate text-blue-800 hover:text-blue-600"
                  :href="`${adminBase}/properties/${lead.property.id}/edit`"
                  @click.stop
                >
                  Imóvel: {{ lead.property?.titulo || `#${lead.property.id}` }}
                </a>
              </div>
            </button>

            <div v-if="leadsByStatus[status].length === 0" class="text-sm text-gray-400 text-center py-6">
              Solte um lead aqui
            </div>
          </div>
        </div>
      </div>

      <div v-if="filteredLeads.length === 0" class="text-center text-gray-500 py-10">
        Nenhum lead ainda
      </div>
    </div>

    <div v-if="selectedLead" class="fixed inset-0 z-50">
      <div class="absolute inset-0 bg-black/40" @click="closeLead"></div>
      <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="relative bg-white w-full max-w-3xl rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 flex items-start justify-between gap-4">
            <div class="min-w-0">
              <div class="text-lg font-semibold text-gray-900 truncate">{{ selectedLead.nome }}</div>
              <div class="text-sm text-gray-500 truncate">{{ selectedLead.origem || 'Site' }}</div>
            </div>
            <button type="button" class="p-2 rounded-lg hover:bg-gray-100 transition" @click="closeLead" title="Fechar">
              <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>

          <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <div class="text-xs font-semibold text-gray-500">Telefone</div>
                <div class="text-sm text-gray-900">{{ selectedLead.telefone || '-' }}</div>
              </div>
              <div>
                <div class="text-xs font-semibold text-gray-500">E-mail</div>
                <div class="text-sm text-gray-900">{{ selectedLead.email || '-' }}</div>
              </div>
              <div>
                <div class="text-xs font-semibold text-gray-500">Criado em</div>
                <div class="text-sm text-gray-900">{{ formatDateTime(selectedLead.created_at) }}</div>
              </div>
              <div v-if="selectedLead.property?.id">
                <div class="text-xs font-semibold text-gray-500">Imóvel</div>
                <a class="text-sm text-blue-800 hover:text-blue-600" :href="`${adminBase}/properties/${selectedLead.property.id}/edit`">
                  {{ selectedLead.property?.titulo || `#${selectedLead.property.id}` }}
                </a>
              </div>
            </div>

            <div class="flex items-center gap-3">
              <button
                type="button"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold transition disabled:opacity-60 disabled:cursor-not-allowed"
                :disabled="!selectedLead.telefone"
                @click="openWhatsApp(selectedLead)"
              >
                WhatsApp
              </button>
              <button
                type="button"
                class="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-lg font-semibold transition disabled:opacity-60 disabled:cursor-not-allowed"
                :disabled="!selectedLead.email"
                @click="openEmail(selectedLead)"
              >
                E-mail
              </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                <select v-model="editStatus" class="w-full border border-gray-300 rounded-lg px-4 py-3 bg-white">
                  <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Próximo contato</label>
                <input v-model="editProximoContato" type="datetime-local" class="w-full border border-gray-300 rounded-lg px-4 py-3 bg-white" />
              </div>
            </div>

            <div class="flex items-center justify-end gap-3">
              <button type="button" class="text-gray-700 hover:text-gray-900 font-semibold" @click="resetEdits">
                Descartar
              </button>
              <button
                type="button"
                class="bg-blue-900 hover:bg-blue-800 text-white px-5 py-2.5 rounded-lg font-semibold transition disabled:opacity-60 disabled:cursor-not-allowed"
                :disabled="saving"
                @click="saveLead"
              >
                Salvar
              </button>
            </div>

            <div v-if="selectedLead.mensagem" class="bg-gray-50 border border-gray-200 rounded-xl p-4">
              <div class="text-sm font-semibold text-gray-700 mb-2">Mensagem</div>
              <pre class="text-sm text-gray-700 whitespace-pre-wrap">{{ selectedLead.mensagem }}</pre>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="bg-white rounded-xl shadow border border-gray-200 p-6">
        <div class="flex items-center justify-between gap-3 mb-4">
          <div class="text-lg font-semibold text-gray-900">Colunas do CRM</div>
          <button type="button" class="bg-gray-900 hover:bg-black text-white px-4 py-2 rounded-lg font-semibold transition" @click="addColumn">
            Adicionar coluna
          </button>
        </div>

        <div class="space-y-3">
          <div v-for="col in columnsDraft" :key="col.id" class="flex items-center gap-3">
            <input v-model="col.name" type="text" class="flex-1 border border-gray-300 rounded-lg px-4 py-3" placeholder="Nome da coluna" />
            <button type="button" class="text-red-600 hover:text-red-700 font-semibold px-3 py-2" @click="removeColumn(col.id)">
              Remover
            </button>
          </div>
          <div v-if="columnsDraft.length === 0" class="text-sm text-gray-500">
            Adicione pelo menos uma coluna.
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow border border-gray-200 p-6">
        <div class="text-lg font-semibold text-gray-900 mb-4">Mensagens globais</div>

        <div class="space-y-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">WhatsApp</label>
            <textarea v-model="settingsForm.whatsapp_template" rows="4" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Use [Nome] para inserir o nome do cliente."></textarea>
            <div v-if="settingsForm.errors.whatsapp_template" class="text-sm text-red-600 mt-1">{{ settingsForm.errors.whatsapp_template }}</div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">E-mail (assunto)</label>
            <input v-model="settingsForm.email_subject_template" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Use [Nome] para inserir o nome do cliente." />
            <div v-if="settingsForm.errors.email_subject_template" class="text-sm text-red-600 mt-1">{{ settingsForm.errors.email_subject_template }}</div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">E-mail (mensagem)</label>
            <textarea v-model="settingsForm.email_body_template" rows="5" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Use [Nome] para inserir o nome do cliente."></textarea>
            <div v-if="settingsForm.errors.email_body_template" class="text-sm text-red-600 mt-1">{{ settingsForm.errors.email_body_template }}</div>
          </div>

          <div class="flex items-center justify-end gap-3">
            <button type="button" class="text-gray-700 hover:text-gray-900 font-semibold" @click="resetSettingsDraft">
              Descartar
            </button>
            <button
              type="button"
              class="bg-blue-900 hover:bg-blue-800 text-white px-5 py-2.5 rounded-lg font-semibold transition disabled:opacity-60 disabled:cursor-not-allowed"
              :disabled="settingsForm.processing"
              @click="saveSettings"
            >
              Salvar configurações
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Shared/AdminLayout.vue';
import { computed, ref, watch } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
  leads: {
    type: Array,
    default: () => [],
  },
});

const defaultStatuses = [
  'Novo Lead',
  'Contato Feito',
  'Visita agendada',
  'Venda concluída',
  'Realizar novo contato',
];

const page = usePage();
const sharedSettings = computed(() => page.props?.settings || {});
const adminBase = computed(() => page.props?.paths?.admin || '/admin');

function parseKanbanColumns() {
  const raw = sharedSettings.value?.leads_kanban_columns;
  if (Array.isArray(raw)) {
    const list = raw.map((v) => String(v || '').trim()).filter(Boolean);
    return list.length ? list : [...defaultStatuses];
  }
  if (typeof raw === 'string' && raw.trim() !== '') {
    try {
      const parsed = JSON.parse(raw);
      if (Array.isArray(parsed)) {
        const list = parsed.map((v) => String(v || '').trim()).filter(Boolean);
        return list.length ? list : [...defaultStatuses];
      }
    } catch {
    }
  }
  return [...defaultStatuses];
}

const statuses = ref(parseKanbanColumns());

const activeCategoria = ref('leads');
const draggingLeadId = ref(null);
const saving = ref(false);

const localLeads = ref(
  (props.leads || []).map((lead) => ({
    ...lead,
    categoria: lead.categoria || 'leads',
    status: lead.status || 'Novo Lead',
  }))
);

watch(
  () => props.leads,
  (next) => {
    localLeads.value = (next || []).map((lead) => ({
      ...lead,
      categoria: lead.categoria || 'leads',
      status: lead.status || 'Novo Lead',
    }));
  }
);

const counts = computed(() => {
  const all = localLeads.value;
  return {
    leads: all.filter((l) => (l.categoria || 'leads') === 'leads').length,
    venda: all.filter((l) => (l.categoria || 'leads') === 'venda-seu-imovel').length,
  };
});

const filteredLeads = computed(() => {
  return localLeads.value.filter((l) => (l.categoria || 'leads') === activeCategoria.value);
});

const leadsByStatus = computed(() => {
  const map = Object.fromEntries(statuses.value.map((s) => [s, []]));
  for (const lead of filteredLeads.value) {
    const status = statuses.value.includes(lead.status) ? lead.status : (statuses.value[0] || 'Novo Lead');
    map[status].push(lead);
  }
  return map;
});

const selectedLeadId = ref(null);
const selectedLead = computed(() => localLeads.value.find((l) => l.id === selectedLeadId.value) || null);

const editStatus = ref(statuses.value[0] || 'Novo Lead');
const editProximoContato = ref('');

const defaultWhatsAppTemplate = 'Olá [Nome], tudo bem?';
const defaultEmailSubjectTemplate = 'Contato - [Nome]';
const defaultEmailBodyTemplate = 'Olá [Nome],\n\nTudo bem?\n\n';

function makeId() {
  return Math.random().toString(36).slice(2, 10);
}

const columnsDraft = ref(statuses.value.map((name) => ({ id: makeId(), name })));

const settingsForm = useForm({
  kanban_columns: statuses.value,
  whatsapp_template: sharedSettings.value?.leads_whatsapp_template || defaultWhatsAppTemplate,
  email_subject_template: sharedSettings.value?.leads_email_subject_template || defaultEmailSubjectTemplate,
  email_body_template: sharedSettings.value?.leads_email_body_template || defaultEmailBodyTemplate,
});

watch(sharedSettings, () => {
  const cols = parseKanbanColumns();
  statuses.value = cols;
  columnsDraft.value = cols.map((name) => ({ id: makeId(), name }));
  settingsForm.kanban_columns = cols;
  settingsForm.whatsapp_template = sharedSettings.value?.leads_whatsapp_template || defaultWhatsAppTemplate;
  settingsForm.email_subject_template = sharedSettings.value?.leads_email_subject_template || defaultEmailSubjectTemplate;
  settingsForm.email_body_template = sharedSettings.value?.leads_email_body_template || defaultEmailBodyTemplate;
});

function openLead(id) {
  selectedLeadId.value = id;
  if (!selectedLead.value) return;
  resetEdits();
}

function closeLead() {
  selectedLeadId.value = null;
}

function resetEdits() {
  if (!selectedLead.value) return;
  editStatus.value = selectedLead.value.status || (statuses.value[0] || 'Novo Lead');
  editProximoContato.value = toDatetimeLocal(selectedLead.value.proximo_contato_em);
}

function toDatetimeLocal(value) {
  if (!value) return '';
  const d = new Date(value);
  if (Number.isNaN(d.getTime())) return '';
  const pad = (n) => String(n).padStart(2, '0');
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

function toLaravelDateTime(datetimeLocalValue) {
  if (!datetimeLocalValue) return null;
  const match = String(datetimeLocalValue).match(/^(\d{4}-\d{2}-\d{2})T(\d{2}:\d{2})$/);
  if (!match) return null;
  return `${match[1]} ${match[2]}:00`;
}

function onDragStart(e, lead) {
  draggingLeadId.value = lead.id;
  try {
    e.dataTransfer?.setData('text/plain', String(lead.id));
    e.dataTransfer.effectAllowed = 'move';
  } catch {
  }
}

function onDrop(e, status) {
  const raw = e.dataTransfer?.getData?.('text/plain');
  const id = Number(raw || draggingLeadId.value);
  if (!id) return;
  updateLead(id, { status });
}

function updateLead(id, payload) {
  const lead = localLeads.value.find((l) => l.id === id);
  if (!lead) return;

  const previous = { status: lead.status, proximo_contato_em: lead.proximo_contato_em };

  if (Object.prototype.hasOwnProperty.call(payload, 'status') && payload.status) {
    lead.status = payload.status;
  }
  if (Object.prototype.hasOwnProperty.call(payload, 'proximo_contato_em')) {
    const next = payload.proximo_contato_em;
    if (typeof next === 'string' && next.includes(' ') && !next.includes('T')) {
      lead.proximo_contato_em = next.replace(' ', 'T');
    } else {
      lead.proximo_contato_em = next;
    }
  }

  router.patch(`${adminBase.value}/leads/${id}`, payload, {
    preserveScroll: true,
    onError: () => {
      lead.status = previous.status;
      lead.proximo_contato_em = previous.proximo_contato_em;
    },
  });
}

function saveLead() {
  if (!selectedLead.value) return;
  saving.value = true;
  const payload = {
    status: editStatus.value,
    proximo_contato_em: toLaravelDateTime(editProximoContato.value),
  };
  const id = selectedLead.value.id;
  const lead = localLeads.value.find((l) => l.id === id);
  if (!lead) {
    saving.value = false;
    return;
  }

  const previous = { status: lead.status, proximo_contato_em: lead.proximo_contato_em };

  lead.status = payload.status || lead.status;
  lead.proximo_contato_em = payload.proximo_contato_em ? payload.proximo_contato_em.replace(' ', 'T') : null;

  router.patch(`${adminBase.value}/leads/${id}`, payload, {
    preserveScroll: true,
    onFinish: () => {
      saving.value = false;
    },
    onError: () => {
      lead.status = previous.status;
      lead.proximo_contato_em = previous.proximo_contato_em;
    },
  });
}

function replaceNome(template, lead) {
  const name = String(lead?.nome || '').trim();
  return String(template || '').replaceAll('[Nome]', name);
}

function normalizePhone(raw) {
  const digits = String(raw || '').replace(/\D/g, '');
  if (!digits) return '';
  if (digits.startsWith('55')) return digits;
  if (digits.length === 10 || digits.length === 11) return `55${digits}`;
  return digits;
}

function openWhatsApp(lead) {
  const phone = normalizePhone(lead.telefone);
  if (!phone) return;
  const msg = replaceNome(settingsForm.whatsapp_template, lead);
  const url = `https://wa.me/${phone}?text=${encodeURIComponent(msg)}`;
  window.open(url, '_blank', 'noopener,noreferrer');
}

function openEmail(lead) {
  if (!lead.email) return;
  const subject = replaceNome(settingsForm.email_subject_template, lead);
  const body = replaceNome(settingsForm.email_body_template, lead);
  const url = `mailto:${encodeURIComponent(lead.email)}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
  window.location.href = url;
}

function addColumn() {
  columnsDraft.value.push({ id: makeId(), name: '' });
}

function removeColumn(id) {
  columnsDraft.value = columnsDraft.value.filter((c) => c.id !== id);
}

function sanitizeColumns() {
  const list = columnsDraft.value
    .map((c) => String(c.name || '').trim())
    .filter(Boolean);

  const uniq = [];
  const seen = new Set();
  for (const item of list) {
    const key = item.toLowerCase();
    if (seen.has(key)) continue;
    seen.add(key);
    uniq.push(item);
  }

  return uniq.length ? uniq : [...defaultStatuses];
}

function resetSettingsDraft() {
  const cols = parseKanbanColumns();
  statuses.value = cols;
  columnsDraft.value = cols.map((name) => ({ id: makeId(), name }));
  settingsForm.kanban_columns = cols;
  settingsForm.whatsapp_template = sharedSettings.value?.leads_whatsapp_template || defaultWhatsAppTemplate;
  settingsForm.email_subject_template = sharedSettings.value?.leads_email_subject_template || defaultEmailSubjectTemplate;
  settingsForm.email_body_template = sharedSettings.value?.leads_email_body_template || defaultEmailBodyTemplate;
  settingsForm.clearErrors();
}

function saveSettings() {
  const cols = sanitizeColumns();
  settingsForm.kanban_columns = cols;

  settingsForm.put(`${adminBase.value}/leads/settings`, {
    preserveScroll: true,
    onSuccess: () => {
      statuses.value = cols;
      columnsDraft.value = cols.map((name) => ({ id: makeId(), name }));
      settingsForm.clearErrors();
    },
  });
}

function formatDate(value) {
  if (!value) return '';
  const d = new Date(value);
  if (Number.isNaN(d.getTime())) return '';
  return d.toLocaleDateString('pt-BR');
}

function formatDateTime(value) {
  if (!value) return '';
  const d = new Date(value);
  if (Number.isNaN(d.getTime())) return '';
  return d.toLocaleString('pt-BR');
}
</script>
