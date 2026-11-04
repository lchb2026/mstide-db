<script setup lang="ts">
// Core
import { ref, computed } from 'vue';

// Modules
import { mdiEye, mdiEyeOff } from '@mdi/js';

// Props
const props = defineProps<{
  modelValue: any;
  label?: string;
  hideDetails?: boolean;
  type?: string;
  density?: 'default' | 'comfortable' | 'compact';
}>();

// Emits
const emit = defineEmits<{
  (e: 'update:modelValue', value: any): void;
}>();

// Data
const showPassword = ref(false);

// Computed
const internalValue = computed({
  get: () => props.modelValue,
  set: (val: any) => emit('update:modelValue', val),
});
</script>

<template>
  <v-text-field
    v-model="internalValue"
    :label="props.label"
    :hide-details="props.hideDetails"
    clearable
    variant="outlined"
    :density="props.density ? props.density : 'comfortable'"
    :type="showPassword ? 'text' : props.type"
    :append-icon="props.type === 'password' ? (showPassword ? mdiEyeOff : mdiEye) : ''"
    persistent-placeholder
    @click:append="showPassword = !showPassword"
  />
</template>
