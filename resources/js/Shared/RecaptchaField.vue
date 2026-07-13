<template>
  <div v-if="enabled" class="space-y-2">
    <div ref="containerRef"></div>
    <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
  </div>
</template>

<script setup>
import { computed, nextTick, onMounted, ref, watch } from 'vue';

const props = defineProps({
  siteKey: {
    type: String,
    default: '',
  },
  modelValue: {
    type: String,
    default: '',
  },
  error: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['update:modelValue']);

const enabled = computed(() => String(props.siteKey || '').trim() !== '');
const containerRef = ref(null);
const widgetId = ref(null);

function loadRecaptchaScript() {
  if (window.grecaptcha?.render) {
    return Promise.resolve(window.grecaptcha);
  }

  if (window.__recaptchaLoader) {
    return window.__recaptchaLoader;
  }

  window.__recaptchaLoader = new Promise((resolve, reject) => {
    const existing = document.querySelector('script[data-recaptcha-script="true"]');
    if (existing) {
      existing.addEventListener('load', () => resolve(window.grecaptcha));
      existing.addEventListener('error', reject);
      return;
    }

    const script = document.createElement('script');
    script.src = 'https://www.google.com/recaptcha/api.js?render=explicit';
    script.async = true;
    script.defer = true;
    script.setAttribute('data-recaptcha-script', 'true');
    script.onload = () => resolve(window.grecaptcha);
    script.onerror = reject;
    document.head.appendChild(script);
  });

  return window.__recaptchaLoader;
}

async function renderWidget() {
  if (!enabled.value || !containerRef.value) {
    return;
  }

  const grecaptcha = await loadRecaptchaScript();

  await nextTick();

  if (!containerRef.value || widgetId.value !== null) {
    return;
  }

  widgetId.value = grecaptcha.render(containerRef.value, {
    sitekey: props.siteKey,
    callback: (token) => emit('update:modelValue', token || ''),
    'expired-callback': () => emit('update:modelValue', ''),
    'error-callback': () => emit('update:modelValue', ''),
  });
}

function reset() {
  if (window.grecaptcha?.reset && widgetId.value !== null) {
    window.grecaptcha.reset(widgetId.value);
  }

  emit('update:modelValue', '');
}

watch(() => props.siteKey, async () => {
  widgetId.value = null;
  if (containerRef.value) {
    containerRef.value.innerHTML = '';
  }
  await renderWidget();
});

onMounted(async () => {
  await renderWidget();
});

defineExpose({ reset });
</script>
