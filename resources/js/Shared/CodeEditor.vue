<template>
  <div class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-950 shadow-sm">
    <div ref="editorEl" :style="{ height }"></div>
  </div>
</template>

<script setup>
import 'monaco-editor/min/vs/editor/editor.main.css';
import EditorWorker from 'monaco-editor/esm/vs/editor/editor.worker?worker';
import HtmlWorker from 'monaco-editor/esm/vs/language/html/html.worker?worker';
import CssWorker from 'monaco-editor/esm/vs/language/css/css.worker?worker';
import JsonWorker from 'monaco-editor/esm/vs/language/json/json.worker?worker';
import TsWorker from 'monaco-editor/esm/vs/language/typescript/ts.worker?worker';
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  language: {
    type: String,
    default: 'html',
  },
  height: {
    type: String,
    default: '420px',
  },
  readOnly: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['update:modelValue']);

const editorEl = ref(null);
let editor = null;
let monaco = null;

onMounted(async () => {
  window.MonacoEnvironment = {
    getWorker(_, label) {
      if (label === 'json') return new JsonWorker();
      if (label === 'css' || label === 'scss' || label === 'less') return new CssWorker();
      if (label === 'html' || label === 'handlebars' || label === 'razor') return new HtmlWorker();
      if (label === 'typescript' || label === 'javascript') return new TsWorker();
      return new EditorWorker();
    },
  };

  monaco = await import('monaco-editor');

  editor = monaco.editor.create(editorEl.value, {
    value: props.modelValue || '',
    language: props.language,
    theme: 'vs-dark',
    automaticLayout: true,
    minimap: { enabled: false },
    fontSize: 14,
    readOnly: props.readOnly,
    wordWrap: 'on',
    scrollBeyondLastLine: false,
    lineNumbers: 'on',
    renderWhitespace: 'selection',
  });

  editor.onDidChangeModelContent(() => {
    emit('update:modelValue', editor.getValue());
  });
});

watch(
  () => props.modelValue,
  (value) => {
    if (!editor) return;
    const next = value ?? '';
    if (editor.getValue() !== next) {
      editor.setValue(next);
    }
  }
);

watch(
  () => props.language,
  (language) => {
    if (!editor || !monaco) return;
    const model = editor.getModel();
    if (model) {
      monaco.editor.setModelLanguage(model, language);
    }
  }
);

watch(
  () => props.readOnly,
  (readOnly) => {
    editor?.updateOptions({ readOnly });
  }
);

onBeforeUnmount(() => {
  editor?.dispose();
  editor = null;
});
</script>
