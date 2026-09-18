<template>
  <div class="space-y-3">
    <div>
      <h2 class="text-lg font-semibold text-gray-900">Vídeos</h2>
      <p class="text-sm text-gray-600">MP4, MOV ou WebM, até 200 MB. Após salvar, o vídeo será convertido para WebM e o original será removido.</p>
    </div>
    <input type="file" accept=".mp4,.mov,.webm,video/mp4,video/quicktime,video/webm" multiple @change="addFiles">
    <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
    <div v-for="item in items" :key="item.key" class="flex items-center justify-between gap-3 rounded-lg border p-3">
      <div class="min-w-0">
        <p class="truncate font-medium">{{ item.name }}</p>
        <p class="text-sm text-gray-600">{{ statusText(item) }}<span v-if="item.error" class="text-red-600"> — {{ item.error }}</span></p>
        <progress v-if="item.status === 'uploading'" :value="item.progress" max="100" class="w-full"></progress>
        <video v-if="item.url" :src="item.url" controls preload="metadata" class="mt-2 max-h-44 max-w-full rounded"></video>
      </div>
      <button type="button" class="text-sm font-semibold text-red-700" @click="remove(item)">Remover</button>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios';
import { onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({
  existingVideos: { type: Array, default: () => [] },
  uploadUrl: { type: String, required: true },
  statusUrl: { type: String, default: '' },
  maxFiles: { type: Number, default: 5 },
  maxFileSizeBytes: { type: Number, default: 200 * 1024 * 1024 },
});

const items = ref((props.existingVideos || []).map((video) => ({
  key: `existing-${video.id}`, id: video.id, name: video.original_name,
  status: video.status, error: video.processing_error || '',
  url: video.status === 'ready' && video.path ? `/media/${video.path}` : null,
  progress: 100, token: null,
})));
const removeIds = ref([]);
const error = ref('');
let timer = null;

function statusText(item) {
  return { uploading: `Enviando ${item.progress}%`, uploaded: 'Enviado; aguardando salvamento do imóvel', processing: 'Otimizando', ready: 'Otimizado', failed: 'Falha na otimização', error: 'Falha no envio' }[item.status] || item.status;
}

async function addFiles(event) {
  error.value = '';
  const files = Array.from(event.target.files || []);
  event.target.value = '';
  for (const file of files) {
    if (items.value.length >= props.maxFiles) { error.value = `Limite de ${props.maxFiles} vídeos por imóvel.`; break; }
    if (file.size > props.maxFileSizeBytes) { error.value = `${file.name} excede o limite de 200 MB.`; continue; }
    const item = { key: `new-${crypto.randomUUID()}`, id: null, name: file.name, status: 'uploading', error: '', url: null, progress: 0, token: null, controller: new AbortController() };
    items.value.push(item);
    const body = new FormData();
    body.append('file', file);
    try {
      const response = await axios.post(props.uploadUrl, body, {
        signal: item.controller.signal,
        onUploadProgress: (progress) => { item.progress = Math.round(100 * progress.loaded / Math.max(progress.total || file.size, 1)); },
      });
      if (!items.value.some((entry) => entry.key === item.key)) {
        await axios.delete(`${props.uploadUrl}/${response.data.token}`);
        continue;
      }
      item.token = response.data.token;
      item.status = 'uploaded';
      item.progress = 100;
    } catch (failure) {
      item.status = 'error';
      item.error = failure.response?.data?.message || 'Não foi possível enviar o vídeo.';
    }
  }
}

async function remove(item) {
  item.controller?.abort();
  if (item.id) removeIds.value.push(item.id);
  if (item.token) {
    try { await axios.delete(`${props.uploadUrl}/${item.token}`); } catch { /* O registro vinculado não pode ser removido como upload temporário. */ }
  }
  items.value = items.value.filter((entry) => entry.key !== item.key);
}

async function refresh() {
  if (!props.statusUrl) return;
  try {
    const response = await axios.get(props.statusUrl);
    for (const video of response.data.videos || []) {
      const item = items.value.find((entry) => entry.id === video.id);
      if (!item) continue;
      item.status = video.status;
      item.error = video.error || '';
      item.url = video.url;
    }
  } catch { /* A próxima consulta tentará novamente. */ }
}

function getSubmissionPayload() {
  return {
    video_upload_tokens: items.value.filter((item) => !item.id && item.token).map((item) => item.token),
    remove_video_ids: [...new Set(removeIds.value)],
    hasPendingUploads: items.value.some((item) => item.status === 'uploading'),
    hasUploadErrors: items.value.some((item) => item.status === 'error'),
  };
}

defineExpose({ getSubmissionPayload });
onMounted(() => { if (props.statusUrl) timer = setInterval(refresh, 5000); });
onBeforeUnmount(() => { if (timer) clearInterval(timer); });
</script>
