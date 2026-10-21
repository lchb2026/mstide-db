<script setup lang="ts">
// Core
import { ref, computed, watch } from 'vue';
import { useRoute } from 'vue-router';

// Modules
import { mdiInformationOutline } from '@mdi/js';

// Components
import { appState } from '@/db/auth.ts';
import AtomChip from '@/components/atoms/AtomChip.vue';

// Interfaces
import type { MassTable } from '@/interfaces/MassTable';

// Route
const route = useRoute();

// Data
const localMassSpectra = ref<MassTable[][]>([]);

// Props
const props = defineProps<{
  massSpectra: MassTable[][];
}>();

// Emits
const emit = defineEmits<{
  (e: 'update:massSpectra', value: MassTable[][]): void;
}>();

// Methods
const toggleColumn = (index: number) => {
  const updated = localMassSpectra.value.map((table) =>
    table.map((entry, i) => (i === index ? { ...entry, visible: !entry.visible } : entry))
  );

  emit('update:massSpectra', updated);
};

const normalizeSpectra = (src: MassTable[][]): MassTable[][] => {
  return src.map((table) =>
    table.map((entry) => ({
      ...entry,
      visible: entry.visible ?? true,
    }))
  );
};

// Computed
const commonMz = computed(() => {
  if (props.massSpectra.length <= 1) return [];
  return props.massSpectra
    .map((arr) => arr.map((e) => e.mz))
    .reduce((a, b) => a.filter((mz) => b.includes(mz)));
});

const isLoggedIn = computed(() => appState.isLoggedIn);

const isAvailableForChange = computed(
  () => (route.name === 'CompoundEdit' || route.name === 'CompoundNew') && appState.isLoggedIn
);

// Watchers
watch(
  () => props.massSpectra,
  (newVal) => {
    localMassSpectra.value = normalizeSpectra(newVal);
  },
  { deep: true }
);

localMassSpectra.value = normalizeSpectra(props.massSpectra);
</script>

<template>
  <v-data-table
    v-if="localMassSpectra.length > 1"
    class="elevation-1"
    hide-default-footer
    hide-no-data
  >
    <template v-slot:headers>
      <tr>
        <th class="text-center border-sm">
          <strong>Common <i>m/z</i></strong>
        </th>
        <th v-for="(entry, index) in commonMz" :key="'mz-' + index" class="text-center border-sm">
          {{ entry }}
        </th>
      </tr>
    </template>
  </v-data-table>

  <v-data-table v-else class="elevation-1" hide-default-footer>
    <template v-slot:headers>
      <tr>
        <th class="text-center border-sm">
          <v-tooltip v-if="isLoggedIn" interactive open-on-click>
            <template v-slot:activator="{ props: activatorProps }">
              <v-icon
                :icon="mdiInformationOutline"
                v-bind="activatorProps"
                class="info-mass-spec"
              ></v-icon>
            </template>
            <AtomChip color="red-lighten-4" size="x-small">
              m/z values are hidden on the mass spectrometry chart
            </AtomChip>
            <br />
            <AtomChip color="green-lighten-4" size="x-small">
              m/z values are shown on the mass spectrometry chart
            </AtomChip>
          </v-tooltip>
          <strong><i>m/z</i></strong>
        </th>
        <th
          v-for="(entry, index) in localMassSpectra[0]"
          :key="'mz-' + index"
          :class="[
            'text-center',
            'border-sm',
            {
              'cursor-pointer': isAvailableForChange,
              'bg-green-lighten-4': isLoggedIn && entry.visible,
              'bg-red-lighten-4': isLoggedIn && !entry.visible,
            },
          ]"
          @click="isAvailableForChange ? toggleColumn(index) : null"
        >
          <div class="d-flex flex-column align-center">
            {{ entry.mz }}
          </div>
        </th>
      </tr>
    </template>

    <template v-slot:body>
      <tr>
        <td class="text-center border-sm"><strong>Intensity</strong></td>
        <td
          v-for="(entry, index) in localMassSpectra[0]"
          :key="'intensity-' + index"
          :class="[
            'text-center',
            'border-sm',
            {
              'cursor-pointer': isAvailableForChange,
              'bg-green-lighten-4': isLoggedIn && entry.visible,
              'bg-red-lighten-4': isLoggedIn && !entry.visible,
            },
          ]"
          @click="isAvailableForChange ? toggleColumn(index) : null"
        >
          {{ entry.intensity }}
        </td>
      </tr>
    </template>
  </v-data-table>
</template>

<style lang="scss" scoped>
th,
td {
  position: relative;

  .info-mass-spec {
    position: absolute;
    top: 4px;
    left: 4px;
    opacity: 0.6;

    &:hover {
      opacity: 1;
    }
  }
}
</style>
