<template>
  <div class="space-y-6">
    <div>
      <div class="flex items-center justify-between gap-3">
        <label class="block text-gray-700 text-sm font-medium">Imagem de Destaque</label>
        <div class="text-xs text-gray-500">
          {{ uploadHeadline }}
        </div>
      </div>
      <input ref="featuredInputRef" type="file" :accept="acceptAttr" class="hidden" @change="onFeaturedSelected">
      <div
        class="mt-2 border-2 border-dashed rounded-xl p-6 text-center transition cursor-pointer"
        :class="featuredDropActive ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-blue-400'"
        @click="openFeaturedPicker"
        @dragenter.prevent="featuredDropActive = true"
        @dragover.prevent="featuredDropActive = true"
        @dragleave.prevent="featuredDropActive = false"
        @drop.prevent="onFeaturedDrop"
      >
        <svg class="w-10 h-10 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
        <p class="text-gray-700 font-medium">Arraste ou clique para enviar a imagem de destaque</p>
        <p class="text-xs text-gray-500 mt-2">Upload assíncrono com Uppy. Formatos: JPG, JPEG, PNG e WEBP. Máximo {{ maxSizeLabel }} por arquivo.</p>
      </div>

      <div v-if="featuredItem" class="mt-4 border border-gray-200 rounded-xl overflow-hidden bg-white">
        <img :src="featuredItem.previewUrl" alt="Imagem de destaque" class="w-full h-52 object-cover">
        <div class="p-4 flex items-start justify-between gap-4">
          <div class="min-w-0">
            <div class="font-medium text-gray-900 truncate">{{ featuredItem.name }}</div>
            <div class="text-xs text-gray-500 mt-1">{{ statusLabel(featuredItem) }}</div>
            <div v-if="featuredItem.error" class="text-xs text-red-600 mt-1">{{ featuredItem.error }}</div>
            <div v-if="featuredItem.progress > 0 && featuredItem.progress < 100" class="mt-3 w-full max-w-xs bg-gray-100 rounded-full h-2">
              <div class="bg-blue-600 h-2 rounded-full transition-all" :style="{ width: `${featuredItem.progress}%` }"></div>
            </div>
          </div>
          <div class="flex gap-2 shrink-0">
            <button v-if="featuredItem.status === 'error'" type="button" class="text-xs font-semibold text-blue-700 hover:text-blue-900" @click.stop="retryItem(featuredItem)">
              Reenviar
            </button>
            <button v-if="featuredItem.status === 'uploading'" type="button" class="text-xs font-semibold text-amber-700 hover:text-amber-900" @click.stop="cancelItem(featuredItem)">
              Cancelar
            </button>
            <button type="button" class="text-xs font-semibold text-red-600 hover:text-red-800" @click.stop="removeFeatured">
              Remover
            </button>
          </div>
        </div>
      </div>
    </div>

    <div>
      <div class="flex items-center justify-between gap-3">
        <label class="block text-gray-700 text-sm font-medium">Galeria</label>
        <div class="text-xs text-gray-500">
          {{ uploadHeadline }}
        </div>
      </div>
      <input ref="galleryInputRef" type="file" :accept="acceptAttr" multiple class="hidden" @change="onGallerySelected">
      <div
        class="mt-2 border-2 border-dashed rounded-xl p-6 text-center transition cursor-pointer"
        :class="galleryDropActive ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-blue-400'"
        @click="openGalleryPicker"
        @dragenter.prevent="galleryDropActive = true"
        @dragover.prevent="galleryDropActive = true"
        @dragleave.prevent="galleryDropActive = false"
        @drop.prevent="onGalleryDrop"
      >
        <svg class="w-10 h-10 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
        <p class="text-gray-700 font-medium">Arraste várias imagens ou clique para enviar</p>
        <p class="text-xs text-gray-500 mt-2">Uploads paralelos com Uppy. Até {{ maxFilesLabel }} por imóvel, com processamento em fila, conversão para WEBP e geração automática das versões 1920px, 1200px e 600px.</p>
      </div>

      <div v-if="uploadError" class="text-sm text-red-600 mt-2">{{ uploadError }}</div>
      <div v-if="selectedGalleryCount > 0 || selectedFeaturedCount > 0" class="mt-3 grid grid-cols-2 lg:grid-cols-5 gap-3">
        <div class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2">
          <div class="text-[11px] uppercase tracking-wide text-gray-500">Selecionadas</div>
          <div class="text-lg font-semibold text-gray-900">{{ totalSelectedCount }}</div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2">
          <div class="text-[11px] uppercase tracking-wide text-gray-500">Enviadas</div>
          <div class="text-lg font-semibold text-emerald-700">{{ uploadedCount }}</div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2">
          <div class="text-[11px] uppercase tracking-wide text-gray-500">Processando</div>
          <div class="text-lg font-semibold text-amber-700">{{ processingCount }}</div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2">
          <div class="text-[11px] uppercase tracking-wide text-gray-500">Enviando</div>
          <div class="text-lg font-semibold text-blue-700">{{ uploadingCount }}</div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2">
          <div class="text-[11px] uppercase tracking-wide text-gray-500">Falhas</div>
          <div class="text-lg font-semibold text-red-700">{{ failedCount }}</div>
        </div>
      </div>
      <div v-if="totalSelectedCount > 0" class="mt-3 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3">
        <div class="flex items-center justify-between gap-4 text-sm">
          <div class="font-medium text-blue-950">{{ uploadProgressText }}</div>
          <div class="font-semibold text-blue-900 whitespace-nowrap">{{ uploadedCount }}/{{ totalSelectedCount }}</div>
        </div>
        <div class="mt-2 h-2 rounded-full bg-blue-100 overflow-hidden">
          <div class="h-2 rounded-full bg-blue-700 transition-all duration-300" :style="{ width: `${uploadCompletionPercentage}%` }"></div>
        </div>
      </div>

      <div v-if="galleryItems.length > 0" class="mt-4 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
        <div
          v-for="item in galleryItems"
          :key="item.id"
          class="relative border border-gray-200 rounded-xl overflow-hidden bg-white shadow-sm"
          draggable="true"
          @dragstart="onDragStart(item.id)"
          @dragover.prevent
          @drop="onDrop(item.id)"
        >
          <img :src="item.previewUrl" class="w-full h-28 object-cover">
          <div class="p-3">
            <div class="text-xs font-medium text-gray-900 truncate">{{ item.name }}</div>
            <div class="text-[11px] text-gray-500 mt-1">{{ statusLabel(item) }}</div>
            <div v-if="item.progress > 0 && item.progress < 100" class="mt-2 bg-gray-100 rounded-full h-1.5">
              <div class="bg-blue-600 h-1.5 rounded-full transition-all" :style="{ width: `${item.progress}%` }"></div>
            </div>
            <div v-if="item.error" class="mt-1 text-[11px] text-red-600 line-clamp-2">{{ item.error }}</div>
          </div>
          <div class="absolute top-2 right-2 flex gap-1">
            <button v-if="!item.isExisting || !item.principal" type="button" class="bg-white/95 hover:bg-white text-slate-700 px-2 py-1 rounded text-[11px] font-semibold" @click.stop="setAsFeatured(item.id)">
              Destaque
            </button>
            <button v-if="item.status === 'error'" type="button" class="bg-white/95 hover:bg-white text-blue-700 px-2 py-1 rounded text-[11px] font-semibold" @click.stop="retryItem(item)">
              Retry
            </button>
            <button v-if="item.status === 'uploading'" type="button" class="bg-white/95 hover:bg-white text-amber-700 px-2 py-1 rounded text-[11px] font-semibold" @click.stop="cancelItem(item)">
              Cancelar
            </button>
            <button type="button" class="bg-white/95 hover:bg-white text-red-600 px-2 py-1 rounded text-[11px] font-semibold" @click.stop="removeGallery(item.id)">
              Remover
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios';
import Uppy from '@uppy/core';
import XHRUpload from '@uppy/xhr-upload';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
  existingPhotos: {
    type: Array,
    default: () => [],
  },
  uploadUrl: {
    type: String,
    required: true,
  },
  deleteUploadBaseUrl: {
    type: String,
    required: true,
  },
  maxFiles: {
    type: [Number, null],
    default: null,
  },
  maxFileSizeBytes: {
    type: Number,
    default: 50 * 1024 * 1024,
  },
  parallelUploads: {
    type: Number,
    default: 6,
  },
});

const acceptAttr = '.jpg,.jpeg,.png,.webp';
const placeholderImage = `data:image/svg+xml,${encodeURIComponent(
  `<svg xmlns="http://www.w3.org/2000/svg" width="800" height="600" viewBox="0 0 800 600">
    <rect width="800" height="600" fill="#0f172a"/>
    <rect x="80" y="110" width="640" height="380" rx="24" fill="rgba(255,255,255,0.08)"/>
    <text x="400" y="322" text-anchor="middle" font-family="Arial, sans-serif" font-size="28" fill="rgba(255,255,255,0.75)">Processando imagem</text>
  </svg>`
)}`;

const featuredInputRef = ref(null);
const galleryInputRef = ref(null);
const featuredDropActive = ref(false);
const galleryDropActive = ref(false);
const dragSourceId = ref(null);
const uploadError = ref('');
const removedPhotoIds = ref([]);
const featuredItem = ref(null);
const galleryItems = ref([]);
let featuredUppy = null;
let galleryUppy = null;
const csrfToken = typeof window.getCsrfToken === 'function' ? window.getCsrfToken() : '';
const xsrfToken = typeof window.getCookieValue === 'function' ? window.getCookieValue('XSRF-TOKEN') : '';

const TRACKED_PENDING_STATUSES = ['queued', 'uploading'];
const TRACKED_SUCCESS_STATUSES = ['uploaded', 'processing', 'optimizing', 'completed'];
const TRACKED_ERROR_STATUSES = ['error', 'failed'];
const TRACKED_PROCESSING_STATUSES = ['pending', 'processing', 'optimizing'];

const maxSizeLabel = computed(() => `${Math.round(props.maxFileSizeBytes / 1024 / 1024)}MB`);
const maxFilesLabel = computed(() => `${Math.max(1, Number(props.maxFiles || 200))} imagens`);
const selectedFeaturedCount = computed(() => (featuredItem.value ? 1 : 0));
const selectedGalleryCount = computed(() => galleryItems.value.length);
const totalSelectedCount = computed(() => selectedFeaturedCount.value + selectedGalleryCount.value);
const uploadingCount = computed(() => getTrackedItems().filter((item) => item.status === 'uploading').length);
const processingCount = computed(() => getTrackedItems().filter((item) => TRACKED_PROCESSING_STATUSES.includes(item.status)).length);
const uploadedCount = computed(() => getTrackedItems().filter((item) => TRACKED_SUCCESS_STATUSES.includes(item.status)).length);
const failedCount = computed(() => getTrackedItems().filter((item) => TRACKED_ERROR_STATUSES.includes(item.status)).length);
const pendingUploadCount = computed(() => getTrackedItems().filter((item) => TRACKED_PENDING_STATUSES.includes(item.status)).length);
const uploadHeadline = computed(() => {
  if (totalSelectedCount.value === 0) {
    return 'Nenhuma imagem selecionada';
  }

  return `${uploadedCount.value} de ${totalSelectedCount.value} imagens enviadas`;
});
const uploadCompletionPercentage = computed(() => {
  if (totalSelectedCount.value === 0) {
    return 0;
  }

  return Math.max(0, Math.min(100, Math.round((uploadedCount.value / totalSelectedCount.value) * 100)));
});
const uploadProgressText = computed(() => {
  if (failedCount.value > 0) {
    return `${uploadedCount.value} de ${totalSelectedCount.value} imagens enviadas, ${failedCount.value} falharam`;
  }

  if (pendingUploadCount.value > 0) {
    return `${uploadedCount.value} de ${totalSelectedCount.value} imagens enviadas, ${pendingUploadCount.value} pendentes`;
  }

  return `${uploadedCount.value} de ${totalSelectedCount.value} imagens enviadas`;
});

function normalizeExistingItem(photo) {
  const failed = photo?.processing_status === 'failed';
  const status = failed
    ? 'failed'
    : photo?.processing_status === 'optimizing'
      ? 'optimizing'
    : photo?.processing_status === 'processing'
      ? 'processing'
      : photo?.processing_status === 'pending'
        ? 'pending'
        : photo?.processing_status === 'completed'
          ? 'completed'
          : 'uploaded';

  return {
    id: `existing-${photo.id}`,
    uppyFileId: null,
    existingPhotoId: photo.id,
    token: null,
    file: null,
    previewUrl: photo.thumb_small_url || photo.medium_url || photo.original_url || photo.url || placeholderImage,
    name: photo.principal ? 'Imagem de destaque' : `Imagem ${photo.id}`,
    status,
    progress: ['processing', 'optimizing', 'completed'].includes(status) ? 100 : 0,
    error: photo.processing_error || '',
    isExisting: true,
    principal: !!photo?.principal,
  };
}

function createUploadItem(file) {
  return {
    id: file.id,
    uppyFileId: file.id,
    existingPhotoId: null,
    token: null,
    file: file.data || null,
    previewUrl: file.preview || (file.data instanceof File ? URL.createObjectURL(file.data) : placeholderImage),
    name: file.name,
    status: 'queued',
    progress: 0,
    error: '',
    isExisting: false,
    principal: false,
  };
}

function openFeaturedPicker() {
  featuredInputRef.value?.click();
}

function openGalleryPicker() {
  galleryInputRef.value?.click();
}

async function onFeaturedSelected(event) {
  const file = event.target.files?.[0];
  if (!file) return;
  await replaceFeatured(file);
  if (featuredInputRef.value) featuredInputRef.value.value = '';
}

async function onGallerySelected(event) {
  const files = Array.from(event.target.files || []);
  await appendGallery(files);
  if (galleryInputRef.value) galleryInputRef.value.value = '';
}

async function onFeaturedDrop(event) {
  featuredDropActive.value = false;
  const file = Array.from(event.dataTransfer?.files || [])[0];
  if (!file) return;
  await replaceFeatured(file);
}

async function onGalleryDrop(event) {
  galleryDropActive.value = false;
  const files = Array.from(event.dataTransfer?.files || []);
  await appendGallery(files);
}

async function replaceFeatured(file) {
  uploadError.value = '';
  if (!validateBeforeAdd([file], featuredItem.value ? 1 : 0)) return;

  await removeFeatured();
  addFilesToUppy(featuredUppy, [file]);
}

async function appendGallery(files) {
  uploadError.value = '';
  const validFiles = files.filter(Boolean);
  if (!validateBeforeAdd(validFiles)) return;

  addFilesToUppy(galleryUppy, validFiles);
}

function validateBeforeAdd(files, replacingCount = 0) {
  const totalAfterAdd = currentImageCount() - replacingCount + files.length;
  const maxFiles = Number(props.maxFiles || 200);

  if (totalAfterAdd > maxFiles) {
    uploadError.value = `Cada imóvel pode ter no máximo ${maxFiles} imagens.`;
    return false;
  }

  for (const file of files) {
    if (file.size > props.maxFileSizeBytes) {
      uploadError.value = `A imagem ${file.name} excede o limite de ${maxSizeLabel.value}.`;
      return false;
    }
  }

  return true;
}

async function removeFeatured() {
  if (!featuredItem.value) return;
  if (featuredItem.value.uppyFileId && featuredUppy?.getFile(featuredItem.value.uppyFileId)) {
    featuredUppy.removeFile(featuredItem.value.uppyFileId);
  }
  await removeItemToken(featuredItem.value);

  if (featuredItem.value.existingPhotoId) {
    removedPhotoIds.value = Array.from(new Set([...removedPhotoIds.value, featuredItem.value.existingPhotoId]));
  }

  featuredItem.value = null;
}

async function removeGallery(id) {
  const item = galleryItems.value.find((entry) => entry.id === id);
  if (!item) return;

  if (item.uppyFileId && galleryUppy?.getFile(item.uppyFileId)) {
    galleryUppy.removeFile(item.uppyFileId);
  }
  await removeItemToken(item);

  if (item.existingPhotoId) {
    removedPhotoIds.value = Array.from(new Set([...removedPhotoIds.value, item.existingPhotoId]));
  }

  galleryItems.value = galleryItems.value.filter((entry) => entry.id !== id);
}

async function removeItemToken(item) {
  if (!item?.token) return;

  try {
    await axios.delete(`${props.deleteUploadBaseUrl}/${item.token}`);
  } catch {
    // Best effort cleanup. The temp upload also expires server-side.
  }
}

async function retryItem(item) {
  if (item?.uppyFileId && galleryUppy?.getFile(item.uppyFileId)) {
    try {
      await galleryUppy.retryUpload(item.uppyFileId);
      return;
    } catch {
    }
  }

  if (item?.uppyFileId && featuredUppy?.getFile(item.uppyFileId)) {
    try {
      await featuredUppy.retryUpload(item.uppyFileId);
      return;
    } catch {
    }
  }

  if (!(item?.file instanceof File)) return;
  if (item === featuredItem.value) {
    addFilesToUppy(featuredUppy, [item.file]);
    return;
  }

  addFilesToUppy(galleryUppy, [item.file]);
}

function cancelItem(item) {
  if (!item?.uppyFileId) return;

  if (featuredUppy?.getFile(item.uppyFileId)) {
    featuredUppy.removeFile(item.uppyFileId);
    featuredItem.value = null;
    return;
  }

  if (galleryUppy?.getFile(item.uppyFileId)) {
    galleryUppy.removeFile(item.uppyFileId);
    galleryItems.value = galleryItems.value.filter((entry) => entry.id !== item.id);
  }
}

function onDragStart(id) {
  dragSourceId.value = id;
}

function onDrop(targetId) {
  const sourceId = dragSourceId.value;
  dragSourceId.value = null;
  if (!sourceId || sourceId === targetId) return;

  const list = [...galleryItems.value];
  const sourceIndex = list.findIndex((entry) => entry.id === sourceId);
  const targetIndex = list.findIndex((entry) => entry.id === targetId);
  if (sourceIndex === -1 || targetIndex === -1) return;

  const [moved] = list.splice(sourceIndex, 1);
  list.splice(targetIndex, 0, moved);
  galleryItems.value = list;
}

function setAsFeatured(id) {
  const index = galleryItems.value.findIndex((entry) => entry.id === id);
  if (index === -1) return;

  const nextFeatured = galleryItems.value[index];
  const currentFeatured = featuredItem.value;

  galleryItems.value.splice(index, 1);
  nextFeatured.principal = true;
  featuredItem.value = nextFeatured;

  if (currentFeatured) {
    currentFeatured.principal = false;
    galleryItems.value.unshift(currentFeatured);
  }
}

function statusLabel(item) {
  return {
    pending: 'Na fila de processamento',
    queued: 'Na fila para envio',
    uploading: `Enviando ${item.progress}%`,
    uploaded: 'Upload temporario concluido',
    processing: 'Processando imagem',
    optimizing: 'Otimizando e convertendo para WEBP',
    completed: 'Processamento concluido',
    failed: 'Falhou no processamento',
    error: 'Falha no envio',
  }[item.status] || 'Pendente';
}

function formatUploadError(error, response) {
  const statusCode = response?.status || response?.body?.status || error?.status;
  const serverMessage = response?.body?.message || response?.body?.error || '';

  if (Number(statusCode) === 419) {
    return 'Falha de autenticacao/CSRF no upload. Recarregue a pagina e tente novamente.';
  }

  if (serverMessage) {
    return serverMessage;
  }

  return error?.message || 'Falha ao enviar a imagem.';
}

function currentImageCount() {
  return (featuredItem.value ? 1 : 0) + galleryItems.value.length;
}

function getSubmissionPayload() {
  const trackedItems = getTrackedItems();
  const pendingItems = trackedItems.filter((item) => TRACKED_PENDING_STATUSES.includes(item.status));
  const errorItems = trackedItems.filter((item) => TRACKED_ERROR_STATUSES.includes(item.status));

  return {
    featured_upload_token: featuredItem.value?.token || null,
    featured_existing_photo_id: featuredItem.value?.existingPhotoId || null,
    gallery_upload_tokens: galleryItems.value
      .filter((item) => !item.existingPhotoId && item.token)
      .map((item) => item.token),
    remove_photo_ids: removedPhotoIds.value,
    photo_order_ids: galleryItems.value
      .filter((item) => item.existingPhotoId && !removedPhotoIds.value.includes(item.existingPhotoId))
      .map((item) => item.existingPhotoId),
    selected_count: totalSelectedCount.value,
    uploaded_count: uploadedCount.value,
    pending_count: pendingItems.length,
    failed_count: errorItems.length,
    hasPendingUploads: pendingItems.length > 0,
    hasUploadErrors: errorItems.length > 0,
    pendingItems: pendingItems.map((item) => ({ id: item.id, name: item.name, status: item.status })),
    errorItems: errorItems.map((item) => ({ id: item.id, name: item.name, status: item.status, error: item.error })),
  };
}

defineExpose({
  getSubmissionPayload,
});

function createUppy(kind) {
  const uppy = new Uppy({
    autoProceed: false,
    allowMultipleUploadBatches: true,
    retryDelays: [0, 1000, 3000, 5000],
    restrictions: {
      allowedFileTypes: ['.jpg', '.jpeg', '.png', '.webp'],
      maxFileSize: props.maxFileSizeBytes,
      maxNumberOfFiles: kind === 'featured' ? 1 : Number(props.maxFiles || 200),
    },
  });

  uppy.use(XHRUpload, {
    endpoint: props.uploadUrl,
    method: 'post',
    fieldName: 'file',
    formData: true,
    bundle: false,
    limit: props.parallelUploads,
    timeout: 300000,
    withCredentials: true,
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json',
      ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
      ...(xsrfToken ? { 'X-XSRF-TOKEN': xsrfToken } : {}),
    },
  });

  uppy.on('file-added', (file) => {
    const item = createUploadItem(file);
    if (kind === 'featured') {
      featuredItem.value = item;
      return;
    }

    galleryItems.value.push(item);
  });

  uppy.on('upload-progress', (file, progress) => {
    const item = findItemByUppyId(file.id);
    if (!item) return;
    item.status = 'uploading';
    item.progress = Math.round(((progress.bytesUploaded || 0) / Math.max(progress.bytesTotal || 1, 1)) * 100);
  });

  uppy.on('upload-success', (file, response) => {
    const item = findItemByUppyId(file.id);
    if (!item) return;
    item.token = response?.body?.token || null;
    item.status = 'uploaded';
    item.progress = 100;
    item.error = '';
  });

  uppy.on('upload-error', (file, error, response) => {
    const item = findItemByUppyId(file.id);
    if (!item) return;
    item.status = 'error';
    item.error = formatUploadError(error, response);
  });

  uppy.on('restriction-failed', (file, error) => {
    uploadError.value = error?.message || `Falha ao validar ${file?.name || 'a imagem'}.`;
  });

  return uppy;
}

function addFilesToUppy(uppy, files) {
  if (!uppy) return;

  let addedAtLeastOneFile = false;
  files.forEach((file) => {
    try {
      uppy.addFile({
        name: file.name,
        type: file.type,
        data: file,
      });
      addedAtLeastOneFile = true;
    } catch (error) {
      uploadError.value = error?.message || 'Nao foi possivel adicionar a imagem para upload.';
    }
  });

  if (addedAtLeastOneFile) {
    uppy.upload().catch(() => {
      // O tratamento individual ja acontece nos eventos do Uppy.
    });
  }
}

function findItemByUppyId(fileId) {
  if (featuredItem.value?.uppyFileId === fileId) {
    return featuredItem.value;
  }

  return galleryItems.value.find((entry) => entry.uppyFileId === fileId) || null;
}

function getTrackedItems() {
  return [featuredItem.value, ...galleryItems.value].filter(Boolean);
}

function syncExistingPhotos(photos) {
  const existingFeatured = photos.find((photo) => photo?.principal);
  const existingGallery = photos
    .filter((photo) => !photo?.principal)
    .sort((a, b) => Number(a?.ordem ?? 0) - Number(b?.ordem ?? 0));

  if (!featuredItem.value || featuredItem.value.isExisting) {
    featuredItem.value = existingFeatured ? normalizeExistingItem(existingFeatured) : featuredItem.value?.isExisting ? null : featuredItem.value;
  }

  const stagedGallery = galleryItems.value.filter((entry) => !entry.isExisting);
  galleryItems.value = [...existingGallery.map(normalizeExistingItem), ...stagedGallery];
}

onMounted(() => {
  featuredUppy = createUppy('featured');
  galleryUppy = createUppy('gallery');
  syncExistingPhotos(props.existingPhotos || []);
});

onBeforeUnmount(() => {
  featuredUppy?.destroy();
  galleryUppy?.destroy();
});

watch(
  () => props.existingPhotos,
  (photos) => {
    syncExistingPhotos(Array.isArray(photos) ? photos : []);
  },
  { deep: true }
);
</script>
