<template>
  <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 bg-slate-50/80 px-3 py-3">
      <button
        v-for="action in inlineActions"
        :key="action.key"
        type="button"
        class="flex h-9 min-w-9 items-center justify-center rounded-lg border px-2 text-sm font-medium transition"
        :class="toolbarButtonClass(action.active())"
        :title="action.label"
        @click="action.run"
      >
        <span v-if="action.text">{{ action.text }}</span>
        <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="action.icon" />
        </svg>
      </button>

      <div class="mx-1 h-7 w-px bg-slate-200"></div>

      <button
        v-for="action in headingActions"
        :key="action.key"
        type="button"
        class="flex h-9 min-w-9 items-center justify-center rounded-lg border px-2 text-xs font-semibold uppercase tracking-wide transition"
        :class="toolbarButtonClass(action.active())"
        :title="action.label"
        @click="action.run"
      >
        {{ action.text }}
      </button>

      <div class="mx-1 h-7 w-px bg-slate-200"></div>

      <button
        v-for="action in alignmentActions"
        :key="action.key"
        type="button"
        class="flex h-9 min-w-9 items-center justify-center rounded-lg border px-2 text-sm font-medium transition"
        :class="toolbarButtonClass(action.active())"
        :title="action.label"
        @click="action.run"
      >
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="action.icon" />
        </svg>
      </button>

      <div class="mx-1 h-7 w-px bg-slate-200"></div>

      <button
        v-for="action in listActions"
        :key="action.key"
        type="button"
        class="flex h-9 min-w-9 items-center justify-center rounded-lg border px-2 text-sm font-medium transition"
        :class="toolbarButtonClass(action.active())"
        :title="action.label"
        @click="action.run"
      >
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="action.icon" />
        </svg>
      </button>

      <div class="mx-1 h-7 w-px bg-slate-200"></div>

      <button
        v-for="action in historyActions"
        :key="action.key"
        type="button"
        class="flex h-9 min-w-9 items-center justify-center rounded-lg border px-2 text-sm font-medium transition"
        :class="toolbarButtonClass(false, !action.enabled())"
        :title="action.label"
        :disabled="!action.enabled()"
        @click="action.run"
      >
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="action.icon" />
        </svg>
      </button>
    </div>

    <EditorContent :editor="editor" class="rich-text-editor" />

    <div class="border-t border-slate-200 bg-slate-50/80 px-4 py-2 text-xs text-slate-500">
      `Enter` cria um novo parágrafo. `Shift + Enter` cria uma quebra simples.
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, watch } from 'vue';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import Placeholder from '@tiptap/extension-placeholder';
import StarterKit from '@tiptap/starter-kit';
import TextAlign from '@tiptap/extension-text-align';
import Underline from '@tiptap/extension-underline';

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  placeholder: {
    type: String,
    default: 'Descreva o imóvel...',
  },
});

const emit = defineEmits(['update:modelValue']);

const editor = useEditor({
  content: props.modelValue || '',
  extensions: [
    StarterKit.configure({
      heading: {
        levels: [1, 2, 3],
      },
    }),
    Underline,
    TextAlign.configure({
      types: ['heading', 'paragraph'],
      alignments: ['left', 'center', 'right', 'justify'],
      defaultAlignment: 'left',
    }),
    Placeholder.configure({
      placeholder: props.placeholder,
    }),
  ],
  editorProps: {
    attributes: {
      class: 'min-h-[320px] px-4 py-4 focus:outline-none',
    },
  },
  onUpdate: ({ editor: instance }) => {
    emit('update:modelValue', instance.getHTML());
  },
});

watch(
  () => props.modelValue,
  (value) => {
    const instance = editor.value;
    if (!instance) return;

    const incoming = value || '';
    if (incoming === instance.getHTML()) return;

    instance.commands.setContent(incoming, false);
  }
);

onBeforeUnmount(() => {
  editor.value?.destroy();
});

const inlineActions = computed(() => [
  {
    key: 'bold',
    label: 'Negrito',
    text: 'B',
    active: () => editor.value?.isActive('bold') ?? false,
    run: () => editor.value?.chain().focus().toggleBold().run(),
  },
  {
    key: 'italic',
    label: 'Itálico',
    text: 'I',
    active: () => editor.value?.isActive('italic') ?? false,
    run: () => editor.value?.chain().focus().toggleItalic().run(),
  },
  {
    key: 'underline',
    label: 'Sublinhado',
    text: 'U',
    active: () => editor.value?.isActive('underline') ?? false,
    run: () => editor.value?.chain().focus().toggleUnderline().run(),
  },
  {
    key: 'strike',
    label: 'Tachado',
    text: 'S',
    active: () => editor.value?.isActive('strike') ?? false,
    run: () => editor.value?.chain().focus().toggleStrike().run(),
  },
]);

const headingActions = computed(() => [
  {
    key: 'h1',
    label: 'Título H1',
    text: 'H1',
    active: () => editor.value?.isActive('heading', { level: 1 }) ?? false,
    run: () => editor.value?.chain().focus().toggleHeading({ level: 1 }).run(),
  },
  {
    key: 'h2',
    label: 'Título H2',
    text: 'H2',
    active: () => editor.value?.isActive('heading', { level: 2 }) ?? false,
    run: () => editor.value?.chain().focus().toggleHeading({ level: 2 }).run(),
  },
  {
    key: 'h3',
    label: 'Título H3',
    text: 'H3',
    active: () => editor.value?.isActive('heading', { level: 3 }) ?? false,
    run: () => editor.value?.chain().focus().toggleHeading({ level: 3 }).run(),
  },
]);

const alignmentActions = computed(() => [
  {
    key: 'align-left',
    label: 'Alinhar à esquerda',
    icon: 'M4 6h16M4 10h10M4 14h16M4 18h10',
    active: () => editor.value?.isActive({ textAlign: 'left' }) ?? false,
    run: () => editor.value?.chain().focus().setTextAlign('left').run(),
  },
  {
    key: 'align-center',
    label: 'Centralizar',
    icon: 'M4 6h16M7 10h10M4 14h16M7 18h10',
    active: () => editor.value?.isActive({ textAlign: 'center' }) ?? false,
    run: () => editor.value?.chain().focus().setTextAlign('center').run(),
  },
  {
    key: 'align-right',
    label: 'Alinhar à direita',
    icon: 'M4 6h16M10 10h10M4 14h16M10 18h10',
    active: () => editor.value?.isActive({ textAlign: 'right' }) ?? false,
    run: () => editor.value?.chain().focus().setTextAlign('right').run(),
  },
  {
    key: 'align-justify',
    label: 'Justificar',
    icon: 'M4 6h16M4 10h16M4 14h16M4 18h16',
    active: () => editor.value?.isActive({ textAlign: 'justify' }) ?? false,
    run: () => editor.value?.chain().focus().setTextAlign('justify').run(),
  },
]);

const listActions = computed(() => [
  {
    key: 'bullet-list',
    label: 'Lista com marcadores',
    icon: 'M9 6h11M9 12h11M9 18h11M5 6h.01M5 12h.01M5 18h.01',
    active: () => editor.value?.isActive('bulletList') ?? false,
    run: () => editor.value?.chain().focus().toggleBulletList().run(),
  },
  {
    key: 'ordered-list',
    label: 'Lista numerada',
    icon: 'M10 6h10M10 12h10M10 18h10M4 7h2V5H4v2zm0 6h2v-3H4m0 8h2v-3H4',
    active: () => editor.value?.isActive('orderedList') ?? false,
    run: () => editor.value?.chain().focus().toggleOrderedList().run(),
  },
]);

const historyActions = computed(() => [
  {
    key: 'undo',
    label: 'Desfazer',
    icon: 'M9 14 4 9m0 0 5-5M4 9h9a7 7 0 1 1 0 14h-1',
    enabled: () => editor.value?.can().chain().focus().undo().run() ?? false,
    run: () => editor.value?.chain().focus().undo().run(),
  },
  {
    key: 'redo',
    label: 'Refazer',
    icon: 'm15 14 5-5m0 0-5-5m5 5H11a7 7 0 0 0 0 14h1',
    enabled: () => editor.value?.can().chain().focus().redo().run() ?? false,
    run: () => editor.value?.chain().focus().redo().run(),
  },
]);

function toolbarButtonClass(isActive, isDisabled = false) {
  if (isDisabled) {
    return 'cursor-not-allowed border-slate-200 bg-slate-100 text-slate-300';
  }

  return isActive
    ? 'border-slate-900 bg-slate-900 text-white shadow-sm'
    : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900';
}
</script>

<style scoped>
:deep(.rich-text-editor .ProseMirror) {
  color: #0f172a;
  font-size: 0.975rem;
  line-height: 1.75;
}

:deep(.rich-text-editor .ProseMirror p.is-editor-empty:first-child::before) {
  content: attr(data-placeholder);
  color: #94a3b8;
  float: left;
  height: 0;
  pointer-events: none;
}

:deep(.rich-text-editor .ProseMirror > * + *) {
  margin-top: 0.9rem;
}

:deep(.rich-text-editor .ProseMirror h1) {
  font-size: 1.5rem;
  font-weight: 700;
  line-height: 1.2;
}

:deep(.rich-text-editor .ProseMirror h2) {
  font-size: 1.25rem;
  font-weight: 700;
  line-height: 1.25;
}

:deep(.rich-text-editor .ProseMirror h3) {
  font-size: 1.05rem;
  font-weight: 700;
  line-height: 1.35;
}

:deep(.rich-text-editor .ProseMirror ul),
:deep(.rich-text-editor .ProseMirror ol) {
  padding-left: 1.25rem;
}

:deep(.rich-text-editor .ProseMirror ul) {
  list-style: disc;
}

:deep(.rich-text-editor .ProseMirror ol) {
  list-style: decimal;
}

:deep(.rich-text-editor .ProseMirror strong) {
  font-weight: 700;
}

:deep(.rich-text-editor .ProseMirror em) {
  font-style: italic;
}

:deep(.rich-text-editor .ProseMirror u) {
  text-decoration: underline;
}

:deep(.rich-text-editor .ProseMirror s) {
  text-decoration: line-through;
}
</style>
