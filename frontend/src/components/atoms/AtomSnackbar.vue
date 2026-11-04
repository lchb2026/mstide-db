<script setup lang="ts">
// Core
import { ref, watch } from 'vue';

// Modules
import type { Anchor } from 'vuetify';
import { mdiClose } from '@mdi/js';

// Components
import AtomButton from '@/components/atoms/AtomButton.vue';

// Props
const props = defineProps<{
  modelValue: boolean;
  message: string;
  color?: 'error' | 'success' | 'info';
  timeout?: number;
  location?: Anchor;
  elevation?: number;
}>();

// Emits
const emit = defineEmits(['update:modelValue']);

// Data
const show = ref(props.modelValue);

// Watchers
watch(
  () => props.modelValue,
  (val) => (show.value = val)
);

watch(show, (val) => {
  if (!val) emit('update:modelValue', false);
});

// Methods
const close = () => {
  show.value = false;
};
</script>

<template>
  <v-snackbar
    v-model="show"
    :color="props.color || 'error'"
    :timeout="props.timeout || 5000"
    :location="props.location || 'top'"
    :elevation="props.elevation || 6"
    @timeout="close"
  >
    {{ props.message }}
    <template #actions>
      <AtomButton @click="close" :icon="mdiClose" />
    </template>
  </v-snackbar>
</template>
