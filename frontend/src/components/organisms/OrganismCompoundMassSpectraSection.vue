<script setup lang="ts">
// Core
import { ref, watch } from 'vue';

// Components
import MoleculeMassSpectraTable from '@/components/molecules/MoleculeMassSpectraTable.vue';
import MoleculeMassSpectraChart from '@/components/molecules/MoleculeMassSpectraChart.vue';

// Interfaces
import type { MassTable } from '@/interfaces/MassTable';
import type { Fragment } from '@/interfaces/Fragment';

// Props
const props = defineProps<{
  massSpectra: MassTable[][];
  compare: boolean;
  fragments?: Fragment[];
}>();

// Emits
const emit = defineEmits<{
  (e: 'update:massSpectra', value: MassTable[][]): void;
}>();

// Data
const localMassSpectra = ref<MassTable[][]>(JSON.parse(JSON.stringify(props.massSpectra)));
const hoveredMz = defineModel<number | null>('hoveredMz', { default: null });

// Watchers
watch(
  () => props.massSpectra,
  (newVal) => {
    localMassSpectra.value = JSON.parse(JSON.stringify(newVal));
  },
  { deep: true }
);

// Methods
const updateMassSpectra = (newValue: MassTable[][]) => {
  localMassSpectra.value = newValue;
  emit('update:massSpectra', localMassSpectra.value);
};
</script>

<template>
  <MoleculeMassSpectraTable
    :massSpectra="localMassSpectra"
    @update:massSpectra="updateMassSpectra"
  />
  <MoleculeMassSpectraChart
    v-model:hoveredMz="hoveredMz"
    :massSpectra="localMassSpectra"
    :compare="props.compare"
    :fragments="props.fragments"
  />
</template>
