<template>
  <AdminLayout>
    <template #pageTitle>Editar Postagem</template>

    <div class="mb-6">
      <Link :href="`${adminBase}/blog/posts`" class="text-blue-700 hover:text-blue-900 font-semibold">Voltar</Link>
    </div>

    <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <div class="lg:col-span-2 bg-white rounded-xl shadow p-6 border border-gray-200">
        <div class="space-y-6">
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Título</label>
            <input v-model="form.title" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3">
            <div v-if="form.errors.title" class="text-sm text-red-600 mt-1">{{ form.errors.title }}</div>
          </div>

          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Resumo</label>
            <textarea v-model="form.excerpt" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-3"></textarea>
            <div v-if="form.errors.excerpt" class="text-sm text-red-600 mt-1">{{ form.errors.excerpt }}</div>
          </div>

          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Conteúdo</label>
            <textarea v-model="form.content" rows="14" class="w-full border border-gray-300 rounded-lg px-4 py-3"></textarea>
            <div v-if="form.errors.content" class="text-sm text-red-600 mt-1">{{ form.errors.content }}</div>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow p-6 border border-gray-200">
        <div class="space-y-6">
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Categoria</label>
            <select v-model="form.category_id" class="w-full border border-gray-300 rounded-lg px-4 py-3">
              <option :value="null">Sem categoria</option>
              <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
            <div v-if="form.errors.category_id" class="text-sm text-red-600 mt-1">{{ form.errors.category_id }}</div>
          </div>

          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Imagem de Destaque</label>
            <input ref="featuredInputRef" type="file" accept="image/*" class="hidden" @change="onFeaturedSelected">
            <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-blue-400 transition cursor-pointer" @click="openFeaturedPicker">
              <svg class="w-10 h-10 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
              </svg>
              <p class="text-gray-600">Clique para enviar imagem</p>
            </div>
            <div v-if="currentFeaturedUrl || featuredPreviewUrl" class="mt-4">
              <img :src="featuredPreviewUrl || currentFeaturedUrl" alt="Imagem de destaque" class="w-full h-40 object-cover rounded-xl border border-gray-200">
            </div>
            <div v-if="form.errors.featured_image" class="text-sm text-red-600 mt-1">{{ form.errors.featured_image }}</div>
          </div>

          <div class="flex items-center gap-2">
            <input v-model="form.is_featured" type="checkbox" class="rounded border-gray-300">
            <span class="text-sm text-gray-700">Destaque</span>
          </div>

          <button type="submit" :disabled="form.processing" class="w-full bg-blue-900 hover:bg-blue-800 disabled:opacity-60 text-white px-6 py-3 rounded-lg font-semibold transition">
            Salvar
          </button>
        </div>
      </div>
    </form>
  </AdminLayout>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Shared/AdminLayout.vue';

const page = usePage();
const adminBase = computed(() => page.props?.paths?.admin || '/admin');
const csrfToken = typeof window.getCsrfToken === 'function' ? window.getCsrfToken() : '';

const props = defineProps({
  post: {
    type: Object,
    default: () => ({}),
  },
  categories: {
    type: Array,
    default: () => [],
  },
});

const form = useForm({
  title: props.post?.title ?? '',
  content: props.post?.content ?? '',
  excerpt: props.post?.excerpt ?? '',
  featured_image: null,
  category_id: props.post?.category_id ?? null,
  is_featured: !!props.post?.is_featured,
  _token: csrfToken,
});

const currentFeaturedUrl = computed(() => props.post?.featured_image || '');
const featuredInputRef = ref(null);
const featuredFile = ref(null);
const featuredPreviewUrl = ref('');

const openFeaturedPicker = () => {
  featuredInputRef.value?.click();
};

const onFeaturedSelected = (e) => {
  const file = e.target.files?.[0] || null;
  featuredFile.value = file;
  featuredPreviewUrl.value = file ? URL.createObjectURL(file) : '';
};

const submit = () => {
  form.featured_image = featuredFile.value;
  form._token = csrfToken;
  form.put(`${adminBase.value}/blog/posts/${props.post.id}`, {
    forceFormData: true,
  });
};
</script>
