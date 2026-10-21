<script setup lang="ts">
// Props
const props = defineProps<{
  class?: string;
  block?: boolean;
  disabled?: boolean;
  prependIcon?: any;
  icon?: any;
  rounded?: string;
  size?: string;
  density?: 'default' | 'comfortable' | 'compact';
  href?: string;
}>();

// Emits
const emit = defineEmits<{
  (e: 'click', ev: MouseEvent): void;
}>();
</script>

<template>
  <v-btn
    :class="props.class"
    :block="props.block"
    :disabled="props.disabled"
    :prepend-icon="props.prependIcon"
    :icon="props.icon"
    :rounded="props.rounded"
    :size="props.size"
    :density="props.density"
    :href="props.href"
    @click="emit('click', $event)"
  >
    <v-icon v-if="props.icon" :icon="props.icon" />
    <slot />
  </v-btn>
</template>
