<script setup lang="ts">
// Props
const props = defineProps<{
  label?: string;
  accept?: string;
  prependIcon?: any;
}>();

// Emits
const emit = defineEmits<{
  (e: 'change', ev: Event): void;
}>();
</script>

<template>
  <v-file-input
    :label="label"
    :accept="accept"
    :prepend-icon="prependIcon"
    variant="outlined"
    density="comfortable"
    clearable
    @change="emit('change', $event)"
  />
</template>
