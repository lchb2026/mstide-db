<script setup lang="ts">
// Props
const props = defineProps<{
  color?: string;
  size?: string;
  label?: boolean;
  variant?: 'text' | 'flat' | 'elevated' | 'tonal' | 'outlined' | 'plain' | undefined;
}>();
</script>

<template>
  <v-chip :color="props.color" :size="props.size" :label="props.label" :variant="props.variant">
    <slot />
  </v-chip>
</template>
