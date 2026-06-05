<template>
  <AdminLayout>
    <template #pageTitle>Nova Página</template>

    <div class="mb-6">
      <Link :href="`${adminBase}/pages`" class="text-blue-700 hover:text-blue-900 font-semibold">Voltar</Link>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <div class="lg:col-span-2 bg-white rounded-xl shadow p-6 border border-gray-200">
        <h3 class="text-xl font-semibold text-gray-800 mb-6">Conteúdo</h3>

        <div class="space-y-6">
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Título</label>
            <input type="text" v-model="form.titulo" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Título da página">
            <div v-if="form.errors.titulo" class="text-sm text-red-600 mt-1">{{ form.errors.titulo }}</div>
          </div>

          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Slug</label>
            <input type="text" v-model="form.slug" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="minha-pagina">
            <div v-if="form.errors.slug" class="text-sm text-red-600 mt-1">{{ form.errors.slug }}</div>
          </div>

          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Conteúdo</label>
            <textarea v-model="form.conteudo" rows="15" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Conteúdo da página..."></textarea>
            <div v-if="form.errors.conteudo" class="text-sm text-red-600 mt-1">{{ form.errors.conteudo }}</div>
          </div>

          <div class="border-t border-gray-200 pt-6">
            <h4 class="text-base font-semibold text-gray-800 mb-4">Banner</h4>
            <div class="space-y-4">
              <div>
                <label class="block text-gray-700 mb-2 text-sm font-medium">Título do banner</label>
                <input type="text" v-model="form.banner_title" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Título">
              </div>
              <div>
                <label class="block text-gray-700 mb-2 text-sm font-medium">Subtítulo do banner</label>
                <textarea v-model="form.banner_subtitle" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Subtítulo..."></textarea>
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-gray-700 mb-2 text-sm font-medium">Cor do título</label>
                  <div class="flex items-center gap-3">
                    <input type="color" v-model="form.banner_title_color" class="w-12 h-10 border-2 border-gray-300 rounded cursor-pointer">
                    <span class="text-gray-700 font-mono text-sm">{{ form.banner_title_color }}</span>
                  </div>
                </div>
                <div>
                  <label class="block text-gray-700 mb-2 text-sm font-medium">Cor do subtítulo</label>
                  <div class="flex items-center gap-3">
                    <input type="color" v-model="form.banner_subtitle_color" class="w-12 h-10 border-2 border-gray-300 rounded cursor-pointer">
                    <span class="text-gray-700 font-mono text-sm">{{ form.banner_subtitle_color }}</span>
                  </div>
                </div>
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-gray-700 mb-2 text-sm font-medium">Cor do overlay</label>
                  <div class="flex items-center gap-3">
                    <input type="color" v-model="form.banner_overlay_color" class="w-12 h-10 border-2 border-gray-300 rounded cursor-pointer">
                    <span class="text-gray-700 font-mono text-sm">{{ form.banner_overlay_color }}</span>
                  </div>
                </div>
                <div>
                  <label class="block text-gray-700 mb-2 text-sm font-medium">Opacidade do overlay</label>
                  <input type="number" min="0" max="100" v-model.number="form.banner_overlay_opacity" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="70">
                </div>
              </div>
              <div>
                <label class="block text-gray-700 mb-2 text-sm font-medium">Imagem do banner</label>
                <input ref="bannerInputRef" type="file" accept="image/*" class="hidden" @change="onBannerSelected">
                <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-blue-400 transition cursor-pointer" @click="bannerInputRef?.click()">
                  <p class="text-gray-600">Clique para enviar</p>
                </div>
                <div v-if="bannerPreviewUrl" class="mt-4">
                  <img :src="bannerPreviewUrl" class="w-full h-48 object-cover rounded-xl border border-gray-200">
                  <button type="button" class="mt-2 text-sm text-red-600 hover:text-red-800 font-medium" @click="clearBanner">Remover imagem</button>
                </div>
              </div>
            </div>
          </div>

          <label class="flex items-center gap-2 text-sm text-gray-700">
            <input v-model="form.ativo" type="checkbox" class="rounded border-gray-300">
            Página ativa
          </label>
          <div v-if="form.errors.ativo" class="text-sm text-red-600 mt-1">{{ form.errors.ativo }}</div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow p-6 border border-gray-200">
        <h3 class="text-xl font-semibold text-gray-800 mb-6">SEO</h3>

        <div class="space-y-6">
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Meta Title</label>
            <input type="text" v-model="form.meta_title" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Meta título">
            <div v-if="form.errors.meta_title" class="text-sm text-red-600 mt-1">{{ form.errors.meta_title }}</div>
          </div>

          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Meta Description</label>
            <textarea v-model="form.meta_description" rows="4" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Meta descrição..."></textarea>
            <div v-if="form.errors.meta_description" class="text-sm text-red-600 mt-1">{{ form.errors.meta_description }}</div>
          </div>

          <button type="button" :disabled="form.processing" class="bg-blue-900 hover:bg-blue-800 disabled:opacity-60 text-white px-8 py-3 rounded-lg font-semibold transition" @click="save">
            Criar Página
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Shared/AdminLayout.vue';

const page = usePage();
const adminBase = computed(() => page.props?.paths?.admin || '/admin');

const form = useForm({
  titulo: '',
  slug: '',
  conteudo: '',
  banner_title: '',
  banner_subtitle: '',
  banner_image: '',
  banner_title_color: '#ffffff',
  banner_subtitle_color: '#ffffff',
  banner_overlay_color: '#0f172a',
  banner_overlay_opacity: 70,
  banner_image_file: null,
  meta_title: '',
  meta_description: '',
  ativo: true,
});

const bannerInputRef = ref(null);
const bannerPreviewUrl = ref('');

const onBannerSelected = (e) => {
  const file = e.target.files?.[0] || null;
  form.banner_image_file = file;
  bannerPreviewUrl.value = file ? URL.createObjectURL(file) : '';
  if (bannerInputRef.value) bannerInputRef.value.value = '';
};

const clearBanner = () => {
  form.banner_image_file = null;
  form.banner_image = '';
  bannerPreviewUrl.value = '';
};

const save = () => {
  form.post(`${adminBase.value}/pages`, { forceFormData: true });
};
</script>
