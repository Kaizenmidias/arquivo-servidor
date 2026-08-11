<template>
  <Layout>
    <section class="relative overflow-hidden bg-black text-white">
      <div class="absolute inset-0">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(255,255,255,0.08),_transparent_45%),linear-gradient(135deg,_rgba(0,0,0,0.92),_rgba(23,59,47,0.9))]"></div>
      </div>

      <div class="relative mx-auto max-w-[1400px] px-4 py-20 lg:py-28">
        <div class="max-w-3xl">
          <div class="text-xs font-semibold uppercase tracking-[0.24em] text-white/65">Blog</div>
          <h1 class="mt-5 text-4xl font-semibold leading-[1.02] md:text-5xl lg:text-6xl">
            Conteúdo do CMS integrado com a home
          </h1>
          <p class="mt-6 max-w-2xl text-base leading-8 text-white/80 md:text-lg">
            Publicações recentes, destaques editoriais e artigos publicados pela equipe da Meteorikah.
          </p>
        </div>
      </div>
    </section>

    <section class="bg-white">
      <div class="mx-auto max-w-[1400px] px-4 py-10 lg:py-14">
        <div class="flex flex-wrap items-center gap-3">
          <a
            href="/blog"
            class="rounded-full border px-4 py-2 text-sm font-semibold transition"
            :class="!activeCategory ? 'border-[#173b2f] bg-[#173b2f] text-white' : 'border-gray-200 bg-white text-gray-700 hover:border-[#173b2f] hover:text-[#173b2f]'"
          >
            Todas
          </a>
          <a
            v-for="category in categories"
            :key="category.id"
            :href="`/blog?category=${encodeURIComponent(category.slug)}`"
            class="rounded-full border px-4 py-2 text-sm font-semibold transition"
            :class="activeCategory === category.slug ? 'border-[#173b2f] bg-[#173b2f] text-white' : 'border-gray-200 bg-white text-gray-700 hover:border-[#173b2f] hover:text-[#173b2f]'"
          >
            {{ category.name }}
            <span class="ml-1 text-xs opacity-70">({{ category.count }})</span>
          </a>
        </div>
      </div>
    </section>

    <section v-if="featuredPosts.length" class="bg-[#f7f5f1] py-14 lg:py-20">
      <div class="mx-auto max-w-[1400px] px-4">
        <div class="mb-8 flex items-end justify-between gap-4">
          <div>
            <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-gray-500">Destaques</div>
            <h2 class="mt-3 text-3xl font-semibold text-black md:text-5xl">Posts em destaque</h2>
          </div>
        </div>
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
          <a v-for="post in featuredPosts" :key="post.id" :href="post.url" class="group block overflow-hidden rounded-[1.5rem] border border-black/10 bg-white shadow-[0_18px_50px_rgba(0,0,0,0.08)] transition hover:-translate-y-1 hover:shadow-[0_26px_70px_rgba(0,0,0,0.12)]">
            <img :src="post.image" :alt="post.title" class="h-60 w-full object-cover grayscale transition duration-500 group-hover:grayscale-0" />
            <div class="p-6">
              <div class="text-[11px] font-bold uppercase tracking-[0.18em] text-gray-500">{{ post.category }}</div>
              <h3 class="mt-3 text-2xl font-semibold text-black">{{ post.title }}</h3>
              <p class="mt-3 line-clamp-3 leading-7 text-black/65">{{ post.excerpt }}</p>
            </div>
          </a>
        </div>
      </div>
    </section>

    <section v-if="post" class="bg-white py-14 lg:py-20">
      <div class="mx-auto max-w-[1120px] px-4">
        <div class="grid gap-10 lg:grid-cols-[1.05fr_0.95fr] lg:items-center">
          <div>
            <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-gray-500">{{ post.category }}</div>
            <h2 class="mt-3 text-3xl font-semibold leading-tight text-black md:text-5xl">{{ post.title }}</h2>
            <div class="mt-4 flex flex-wrap items-center gap-3 text-sm text-gray-500">
              <span>{{ post.published_at }}</span>
              <span v-if="post.category">{{ post.category }}</span>
            </div>
            <p class="mt-6 max-w-2xl text-lg leading-8 text-gray-600">
              {{ post.excerpt }}
            </p>
          </div>
          <div class="overflow-hidden rounded-[2rem] border border-gray-200 shadow-[0_24px_70px_rgba(0,0,0,0.10)]">
            <img :src="post.image" :alt="post.title" class="h-full w-full object-cover" />
          </div>
        </div>

        <div class="mt-10 grid gap-10 lg:grid-cols-[1.15fr_0.85fr]">
          <div class="prose max-w-none prose-h2:text-black prose-h3:text-black prose-a:text-[#173b2f]" v-html="post.content"></div>
          <aside class="space-y-6">
            <div class="rounded-[1.5rem] border border-gray-200 bg-[#f7f5f1] p-6">
              <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-gray-500">Resumo</div>
              <p class="mt-3 leading-7 text-gray-700">{{ post.excerpt }}</p>
            </div>
            <div v-if="relatedPosts.length" class="rounded-[1.5rem] border border-gray-200 bg-white p-6 shadow-[0_18px_45px_rgba(0,0,0,0.05)]">
              <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-gray-500">Relacionados</div>
              <div class="mt-4 space-y-4">
                <a v-for="item in relatedPosts" :key="item.id" :href="item.url" class="block">
                  <div class="text-sm font-semibold uppercase tracking-[0.16em] text-gray-500">{{ item.category }}</div>
                  <div class="mt-1 text-lg font-semibold text-black">{{ item.title }}</div>
                </a>
              </div>
            </div>
          </aside>
        </div>
      </div>
    </section>

    <section v-else class="bg-[#f7f5f1] py-14 lg:py-20">
      <div class="mx-auto max-w-[1400px] px-4">
        <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
          <a v-for="postItem in posts.data" :key="postItem.id" :href="postItem.url" class="group">
            <div class="overflow-hidden rounded-[1.5rem] border border-black/10 bg-white shadow-[0_18px_50px_rgba(0,0,0,0.08)] transition hover:-translate-y-1 hover:shadow-[0_26px_70px_rgba(0,0,0,0.12)]">
              <img :src="postItem.image" :alt="postItem.title" class="h-56 w-full object-cover grayscale transition duration-500 group-hover:grayscale-0">
              <div class="p-6">
                <span class="text-sm font-semibold uppercase tracking-[0.18em] text-black/50">{{ postItem.category }}</span>
                <h3 class="mt-3 text-2xl font-semibold text-black">{{ postItem.title }}</h3>
                <p class="mt-3 line-clamp-3 leading-7 text-black/62">{{ postItem.excerpt }}</p>
                <div class="mt-5 flex items-center justify-between text-sm text-black/55">
                  <span>{{ postItem.published_at }}</span>
                  <span class="font-semibold text-[#173b2f]">Ler mais →</span>
                </div>
              </div>
            </div>
          </a>
        </div>

        <div v-if="posts.links && posts.links.length > 3" class="mt-8 flex justify-center">
          <Pagination :links="posts.links" />
        </div>
      </div>
    </section>
  </Layout>
</template>

<script setup>
import Layout from '@/Shared/Layout.vue';
import Pagination from '@/Shared/Pagination.vue';

defineProps({
  posts: {
    type: Object,
    default: () => ({
      data: [],
      links: [],
    }),
  },
  featuredPosts: {
    type: Array,
    default: () => [],
  },
  relatedPosts: {
    type: Array,
    default: () => [],
  },
  categories: {
    type: Array,
    default: () => [],
  },
  post: {
    type: Object,
    default: null,
  },
  activeCategory: {
    type: String,
    default: '',
  },
});
</script>
