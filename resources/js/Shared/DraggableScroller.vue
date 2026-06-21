<template>
  <div
    ref="scroller"
    class="ui-hide-scrollbar ui-drag-scroll overflow-x-auto"
    :class="[viewportClass, { 'is-dragging': isPointerDown }]"
    @mousedown="onPointerDown"
    @mousemove="onPointerMove"
    @mouseup="onPointerUp"
    @mouseleave="onPointerUp"
    @dragstart.prevent
    @click.capture="onClickCapture"
  >
    <div :class="contentClass">
      <slot />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  viewportClass: {
    type: [String, Array, Object],
    default: '',
  },
  contentClass: {
    type: [String, Array, Object],
    default: '',
  },
});

const scroller = ref(null);
const isPointerDown = ref(false);
const startX = ref(0);
const startScrollLeft = ref(0);
const moved = ref(false);

function onPointerDown(event) {
  if (event.button !== 0 || !scroller.value) return;

  isPointerDown.value = true;
  moved.value = false;
  startX.value = event.pageX - scroller.value.offsetLeft;
  startScrollLeft.value = scroller.value.scrollLeft;
}

function onPointerMove(event) {
  if (!isPointerDown.value || !scroller.value) return;

  event.preventDefault();
  const currentX = event.pageX - scroller.value.offsetLeft;
  const walk = currentX - startX.value;

  if (Math.abs(walk) > 6) {
    moved.value = true;
  }

  scroller.value.scrollLeft = startScrollLeft.value - walk;
}

function onPointerUp() {
  isPointerDown.value = false;
}

function onClickCapture(event) {
  if (!moved.value) return;

  event.preventDefault();
  event.stopPropagation();
  moved.value = false;
}
</script>
