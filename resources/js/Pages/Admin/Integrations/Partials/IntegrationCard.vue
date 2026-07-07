<template>
  <section class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-[0_18px_60px_rgba(15,23,42,0.08)]">
    <div class="flex flex-wrap items-start justify-between gap-4">
      <div class="space-y-1">
        <h2 class="text-xl font-bold text-slate-900">{{ title }}</h2>
        <p class="max-w-2xl text-sm text-slate-500">{{ description }}</p>
      </div>
      <span
        class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em]"
        :class="active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'"
      >
        {{ active ? 'Ativo' : 'Inativo' }}
      </span>
    </div>

    <div class="mt-6 space-y-5">
      <slot />
    </div>

    <div class="mt-6 flex flex-wrap items-center justify-end gap-3">
      <slot name="footer" />
    </div>
  </section>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true,
  },
  description: {
    type: String,
    default: '',
  },
  active: {
    type: Boolean,
    default: false,
  },
});
</script>
