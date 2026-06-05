<template>
  <AdminLayout>
    <template #pageTitle>{{ isEdit ? 'Editar Imóvel' : 'Novo Imóvel' }}</template>
    
    <div class="bg-white rounded-xl shadow p-6 border border-gray-200">
      <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="space-y-6">
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Título</label>
            <input v-model="form.titulo" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Título do imóvel">
            <div v-if="form.errors.titulo" class="text-sm text-red-600 mt-1">{{ form.errors.titulo }}</div>
          </div>
          
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Código de Referência</label>
            <input v-model="form.codigo_referencia" :disabled="!isEdit" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3 disabled:bg-gray-100 disabled:text-gray-500" placeholder="Gerado automaticamente">
            <div v-if="form.errors.codigo_referencia" class="text-sm text-red-600 mt-1">{{ form.errors.codigo_referencia }}</div>
          </div>
          
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-gray-700 mb-2 text-sm font-medium">Categoria</label>
              <select v-model="form.tipo_propriedade_id" class="w-full border border-gray-300 rounded-lg px-4 py-3">
                <option v-for="type in propertyTypes" :key="type.id" :value="type.id">
                  {{ type.nome_subtipo ? `${type.nome_tipo} / ${type.nome_subtipo}` : type.nome_tipo }}
                </option>
              </select>
              <div v-if="form.errors.tipo_propriedade_id" class="text-sm text-red-600 mt-1">{{ form.errors.tipo_propriedade_id }}</div>
            </div>
            <div>
              <label class="block text-gray-700 mb-2 text-sm font-medium">Negócio</label>
              <select v-model="form.business_type_id" class="w-full border border-gray-300 rounded-lg px-4 py-3">
                <option v-for="bt in businessTypes" :key="bt.id" :value="bt.id">{{ bt.name }}</option>
              </select>
              <div v-if="form.errors.business_type_id" class="text-sm text-red-600 mt-1">{{ form.errors.business_type_id }}</div>
            </div>
          </div>
          
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Preço</label>
            <input v-model="form.valor" type="text" inputmode="numeric" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="R$ 0.000,00" @input="onPriceInput">
            <div v-if="form.errors.valor" class="text-sm text-red-600 mt-1">{{ form.errors.valor }}</div>
          </div>
          
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Endereço</label>
            <input v-model="form.endereco" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Rua, Número">
            <div v-if="form.errors.endereco" class="text-sm text-red-600 mt-1">{{ form.errors.endereco }}</div>
          </div>

          <div class="grid grid-cols-3 gap-4">
            <div>
              <label class="block text-gray-700 mb-2 text-sm font-medium">Bairro</label>
              <input v-model="form.bairro" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Bairro">
              <div v-if="form.errors.bairro" class="text-sm text-red-600 mt-1">{{ form.errors.bairro }}</div>
            </div>
            <div>
              <label class="block text-gray-700 mb-2 text-sm font-medium">Cidade</label>
              <input v-model="form.cidade" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Cidade">
              <div v-if="form.errors.cidade" class="text-sm text-red-600 mt-1">{{ form.errors.cidade }}</div>
            </div>
            <div>
              <label class="block text-gray-700 mb-2 text-sm font-medium">UF</label>
              <input v-model="form.estado" type="text" maxlength="2" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="SP">
              <div v-if="form.errors.estado" class="text-sm text-red-600 mt-1">{{ form.errors.estado }}</div>
            </div>
          </div>
          
          <div class="grid grid-cols-3 gap-4">
            <div>
              <label class="block text-gray-700 mb-2 text-sm font-medium">Quartos</label>
              <input v-model.number="form.quartos" type="number" min="0" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="0">
              <div v-if="form.errors.quartos" class="text-sm text-red-600 mt-1">{{ form.errors.quartos }}</div>
            </div>
            <div>
              <label class="block text-gray-700 mb-2 text-sm font-medium">Banheiros</label>
              <input v-model.number="form.banheiros" type="number" min="0" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="0">
              <div v-if="form.errors.banheiros" class="text-sm text-red-600 mt-1">{{ form.errors.banheiros }}</div>
            </div>
            <div>
              <label class="block text-gray-700 mb-2 text-sm font-medium">Garagens</label>
              <input v-model.number="form.garagens" type="number" min="0" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="0">
              <div v-if="form.errors.garagens" class="text-sm text-red-600 mt-1">{{ form.errors.garagens }}</div>
            </div>
          </div>
        </div>
        
        <div class="space-y-6">
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Área (m²)</label>
            <input type="number" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="0">
          </div>

          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Flags</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <label class="flex items-center gap-2 text-sm text-gray-700">
                <input v-model="form.is_exclusive" type="checkbox" class="rounded border-gray-300">
                Exclusivo
              </label>
              <label class="flex items-center gap-2 text-sm text-gray-700">
                <input v-model="form.is_off_market" type="checkbox" class="rounded border-gray-300">
                Off Market
              </label>
            </div>
            <div v-if="form.errors.is_exclusive" class="text-sm text-red-600 mt-1">{{ form.errors.is_exclusive }}</div>
            <div v-if="form.errors.is_off_market" class="text-sm text-red-600 mt-1">{{ form.errors.is_off_market }}</div>
          </div>

          <div v-if="specialCategories.length > 0">
            <label class="block text-gray-700 mb-2 text-sm font-medium">Categorias Especiais</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <label v-for="sc in specialCategories" :key="sc.id" class="flex items-center gap-2 text-sm text-gray-700">
                <input v-model="form.special_category_ids" type="checkbox" :value="sc.id" class="rounded border-gray-300">
                {{ sc.name }}
              </label>
            </div>
            <div v-if="form.errors.special_category_ids" class="text-sm text-red-600 mt-1">{{ form.errors.special_category_ids }}</div>
          </div>

          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Exibir na Home</label>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <label class="flex items-center gap-2 text-sm text-gray-700">
                <input v-model="form.show_in_home_selecao_especial" type="checkbox" class="rounded border-gray-300">
                Seleção especial
              </label>
              <label class="flex items-center gap-2 text-sm text-gray-700">
                <input v-model="form.show_in_home_mais_procurados" type="checkbox" class="rounded border-gray-300">
                Mais procurados
              </label>
              <label class="flex items-center gap-2 text-sm text-gray-700">
                <input v-model="form.show_in_home_visto_recentemente" type="checkbox" class="rounded border-gray-300">
                Visto recentemente
              </label>
            </div>
            <div v-if="form.errors.show_in_home_selecao_especial" class="text-sm text-red-600 mt-1">{{ form.errors.show_in_home_selecao_especial }}</div>
            <div v-if="form.errors.show_in_home_mais_procurados" class="text-sm text-red-600 mt-1">{{ form.errors.show_in_home_mais_procurados }}</div>
            <div v-if="form.errors.show_in_home_visto_recentemente" class="text-sm text-red-600 mt-1">{{ form.errors.show_in_home_visto_recentemente }}</div>
          </div>

          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Imagem de Destaque</label>
            <input ref="featuredInputRef" type="file" accept="image/*" class="hidden" @change="onFeaturedSelected">
            <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-blue-400 transition cursor-pointer" @click="openFeaturedPicker">
              <svg class="w-10 h-10 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
              </svg>
              <p class="text-gray-600">Clique para enviar a imagem de destaque</p>
            </div>
            <div v-if="featuredPreviewUrl" class="mt-4">
              <img :src="featuredPreviewUrl" alt="Imagem de destaque" class="w-full h-48 object-cover rounded-xl border border-gray-200">
              <button type="button" class="mt-2 text-sm text-red-600 hover:text-red-800 font-medium" @click="clearFeatured">
                Remover imagem de destaque
              </button>
            </div>
            <div v-if="form.errors.featured_image" class="text-sm text-red-600 mt-1">{{ form.errors.featured_image }}</div>
          </div>

          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Galeria</label>
            <input ref="galleryInputRef" type="file" accept="image/*" multiple class="hidden" @change="onGallerySelected">
            <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-blue-400 transition cursor-pointer" @click="openGalleryPicker">
              <svg class="w-10 h-10 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
              </svg>
              <p class="text-gray-600">Clique para enviar imagens da galeria</p>
            </div>

            <div v-if="galleryItems.length > 0" class="mt-4 grid grid-cols-3 sm:grid-cols-4 gap-3">
              <div
                v-for="item in galleryItems"
                :key="item.id"
                class="relative border border-gray-200 rounded-lg overflow-hidden bg-white"
                draggable="true"
                @dragstart="onDragStart(item.id)"
                @dragover.prevent
                @drop="onDrop(item.id)"
              >
                <img :src="item.previewUrl" class="w-full h-24 object-cover">
                <button type="button" class="absolute top-1 right-1 bg-white/90 hover:bg-white text-red-600 px-2 py-1 rounded text-xs font-semibold" @click="removeGallery(item.id)">
                  Remover
                </button>
              </div>
            </div>

            <div v-if="form.errors.gallery_images" class="text-sm text-red-600 mt-1">{{ form.errors.gallery_images }}</div>
          </div>
          
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Descrição</label>
            <textarea v-model="form.descricao" rows="8" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Descrição do imóvel..."></textarea>
            <div v-if="form.errors.descricao" class="text-sm text-red-600 mt-1">{{ form.errors.descricao }}</div>
          </div>

          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">SEO</label>
            <div class="space-y-4">
              <div>
                <div class="text-sm text-gray-700 font-medium mb-2">Meta Title</div>
                <input v-model="form.meta_title" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Título SEO (opcional)">
                <div v-if="form.errors.meta_title" class="text-sm text-red-600 mt-1">{{ form.errors.meta_title }}</div>
              </div>
              <div>
                <div class="text-sm text-gray-700 font-medium mb-2">Meta Description</div>
                <textarea v-model="form.meta_description" rows="4" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Descrição SEO (opcional)"></textarea>
                <div v-if="form.errors.meta_description" class="text-sm text-red-600 mt-1">{{ form.errors.meta_description }}</div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="lg:col-span-2">
          <button type="submit" :disabled="form.processing" class="bg-blue-900 hover:bg-blue-800 disabled:opacity-60 text-white px-8 py-3 rounded-lg font-semibold transition">
            Salvar Imóvel
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Shared/AdminLayout.vue';

const page = usePage();
const adminBase = computed(() => page.props?.paths?.admin || '/admin');

const props = defineProps({
  propertyTypes: {
    type: Array,
    default: () => [],
  },
  businessTypes: {
    type: Array,
    default: () => [],
  },
  specialCategories: {
    type: Array,
    default: () => [],
  },
  property: {
    type: Object,
    default: null,
  },
  selectedSpecialCategoryIds: {
    type: Array,
    default: () => [],
  },
  generatedReferenceCode: {
    type: String,
    default: '',
  },
});

const isEdit = computed(() => !!props.property?.id);

const defaultPropertyTypeId = computed(() => props.propertyTypes[0]?.id ?? null);
const defaultBusinessTypeId = computed(() => props.businessTypes[0]?.id ?? null);

const formatCurrencyNumberBRL = (value) => {
  const number = Number(value || 0);
  return number.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
};

const form = useForm({
  titulo: props.property?.titulo || '',
  codigo_referencia: props.property?.codigo_referencia || (isEdit.value ? '' : props.generatedReferenceCode),
  meta_title: props.property?.meta_title || '',
  meta_description: props.property?.meta_description || '',
  descricao: props.property?.descricao || '',
  tipo_propriedade_id: props.property?.tipo_propriedade_id ?? defaultPropertyTypeId.value,
  business_type_id: props.property?.business_type_id ?? defaultBusinessTypeId.value,
  valor: isEdit.value ? formatCurrencyNumberBRL(props.property?.valor) : 'R$ 0,00',
  endereco: props.property?.endereco || '',
  bairro: props.property?.bairro || '',
  cidade: props.property?.cidade || '',
  estado: props.property?.estado || 'SP',
  quartos: props.property?.quartos ?? null,
  banheiros: props.property?.banheiros ?? null,
  garagens: props.property?.garagens ?? null,
  is_exclusive: !!props.property?.is_exclusive,
  is_off_market: !!props.property?.is_off_market,
  show_in_home_selecao_especial: !!props.property?.show_in_home_selecao_especial,
  show_in_home_mais_procurados: !!props.property?.show_in_home_mais_procurados,
  show_in_home_visto_recentemente: !!props.property?.show_in_home_visto_recentemente,
  featured_image: null,
  gallery_images: [],
  remove_photo_ids: [],
  photo_order_ids: [],
  special_category_ids: props.selectedSpecialCategoryIds || [],
});

const featuredInputRef = ref(null);
const featuredFile = ref(null);
const featuredPreviewUrl = ref(props.property?.photos?.find((p) => p?.principal)?.url || '');

const openFeaturedPicker = () => {
  featuredInputRef.value?.click();
};

const clearFeatured = () => {
  featuredFile.value = null;
  featuredPreviewUrl.value = '';
  if (featuredInputRef.value) featuredInputRef.value.value = '';
};

const onFeaturedSelected = (e) => {
  const file = e.target.files?.[0] || null;
  featuredFile.value = file;
  featuredPreviewUrl.value = file ? URL.createObjectURL(file) : '';
};

const galleryInputRef = ref(null);
const dragSourceId = ref(null);
const galleryItems = ref([]);
const removedPhotoIds = ref([]);

if (isEdit.value) {
  const existing = Array.isArray(props.property?.photos) ? props.property.photos : [];
  galleryItems.value = existing
    .filter((p) => !p?.principal)
    .sort((a, b) => Number(a?.ordem ?? 0) - Number(b?.ordem ?? 0))
    .map((p) => ({
      id: `existing-${p.id}`,
      existingPhotoId: p.id,
      file: null,
      previewUrl: p.url,
    }));
}

const openGalleryPicker = () => {
  galleryInputRef.value?.click();
};

const onGallerySelected = (e) => {
  const files = Array.from(e.target.files || []);
  for (const file of files) {
    const id = crypto.randomUUID();
    galleryItems.value.push({
      id,
      file,
      previewUrl: URL.createObjectURL(file),
    });
  }
  if (galleryInputRef.value) galleryInputRef.value.value = '';
};

const removeGallery = (id) => {
  const item = galleryItems.value.find((x) => x.id === id);
  if (item?.existingPhotoId) {
    removedPhotoIds.value = Array.from(new Set([...removedPhotoIds.value, item.existingPhotoId]));
  }
  galleryItems.value = galleryItems.value.filter((x) => x.id !== id);
};

const onDragStart = (id) => {
  dragSourceId.value = id;
};

const onDrop = (targetId) => {
  const sourceId = dragSourceId.value;
  dragSourceId.value = null;
  if (!sourceId || sourceId === targetId) return;

  const list = [...galleryItems.value];
  const sourceIndex = list.findIndex((x) => x.id === sourceId);
  const targetIndex = list.findIndex((x) => x.id === targetId);
  if (sourceIndex === -1 || targetIndex === -1) return;

  const [moved] = list.splice(sourceIndex, 1);
  list.splice(targetIndex, 0, moved);
  galleryItems.value = list;
};

const formatCurrencyBRL = (value) => {
  const digits = String(value ?? '').replace(/\D/g, '');
  const number = Number(digits) / 100;
  return number.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
};

const onPriceInput = () => {
  form.valor = formatCurrencyBRL(form.valor);
};

const submit = () => {
  form.featured_image = featuredFile.value;
  form.gallery_images = galleryItems.value.filter((x) => x.file instanceof File).map((x) => x.file);
  form.remove_photo_ids = removedPhotoIds.value;
  form.photo_order_ids = galleryItems.value
    .filter((x) => x.existingPhotoId && !removedPhotoIds.value.includes(x.existingPhotoId))
    .map((x) => x.existingPhotoId);

  if (isEdit.value) {
    form.transform((data) => ({ ...data, _method: 'put' })).post(`${adminBase.value}/properties/${props.property.id}`, { forceFormData: true });
    return;
  }

  form.post(`${adminBase.value}/properties`, { forceFormData: true });
};
</script>
