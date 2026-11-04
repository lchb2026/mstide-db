<script setup lang="ts">
// Core
import { onMounted, ref } from 'vue';

// Modules
import { mdiMolecule, mdiAtom } from '@mdi/js';

// Components
import AtomButton from '@/components/atoms/AtomButton.vue';

// Data
const statistics = ref<{
  compounds: number;
  fragments: number;
  assignedPeaks: number;
} | null>(null);

const statisticsLoading = ref(true);
const statisticItems = [
  { key: 'compounds', label: 'compounds' },
  { key: 'fragments', label: 'fragments' },
  { key: 'assignedPeaks', label: 'assigned peaks' },
] as const;
const numberFormatter = new Intl.NumberFormat('en-US');

const features = [
  {
    title: 'List of compounds',
    desc: 'Browse all nucleotides in the msTide 2.0 database with their structures, mass spectra, peak lists, analysis information and proposed fragment assignments.',
    link: '/compound/list',
    icon: mdiMolecule,
  },
  {
    title: 'List of fragments',
    desc: 'View identified ion fragments with proposed structures and <i>m/z</i> values. For each selected ion, a list of nucleotides containing this ion can be obtained.',
    link: '/fragment/list',
    icon: mdiAtom,
  },
];

// Hooks
onMounted(async () => {
  try {
    const response = await fetch('/api/statistics');

    if (!response.ok) {
      throw new Error('Failed to load database statistics');
    }

    statistics.value = await response.json();
  } catch (error) {
    console.warn('Unable to load database statistics:', error);
  } finally {
    statisticsLoading.value = false;
  }
});
</script>

<template>
  <v-main>
    <v-container class="pa-sm-6 pa-md-12 v-col-auto" fluid>
      <v-responsive class="text-center mx-auto" max-width="700">
        <h1 class="mt-2">msTide 2.0</h1>

        <p class="mt-4 text-body-1 text-medium-emphasis">
          An open-access database of negative-ion electrospray tandem mass spectrometry (ESI-MS/MS)
          data for biologically and therapeutically relevant di- and trinucleotides and their
          synthetic analogs. It includes mRNA cap analogs, nucleotide cofactors, and compounds with
          diverse structural modifications.
        </p>

        <p
          class="mt-4 d-flex flex-column flex-sm-row flex-wrap justify-center align-center ga-2 text-body-2 text-medium-emphasis"
          role="status"
          :aria-busy="statisticsLoading"
        >
          <template v-for="(item, index) in statisticItems" :key="item.key">
            <span v-if="index > 0" class="d-none d-sm-inline" aria-hidden="true"></span>
            <span class="statistics-item">
              <span
                class="statistics-count"
                :aria-label="statisticsLoading ? 'Loading' : undefined"
                :title="!statisticsLoading && !statistics ? 'Statistics unavailable' : undefined"
              >
                <Transition name="statistics-number" mode="out-in">
                  <span
                    v-if="statisticsLoading"
                    key="loading"
                    class="statistics-skeleton"
                    aria-hidden="true"
                  ></span>
                  <b v-else key="loaded">
                    {{ statistics ? numberFormatter.format(statistics[item.key]) : '—' }}
                  </b>
                </Transition>
              </span>
              {{ item.label }}
            </span>
          </template>
        </p>

        <p class="mt-4">
          <AtomButton color="secondary" href="/about"> Read more </AtomButton>
        </p>

        <p class="mt-5"></p>

        <p class="text-left" v-for="(item, i) in features" :key="i">
          <v-card class="mx-auto" :href="item.link" rel="noopener" variant="text">
            <v-card-title><v-icon :icon="item.icon"></v-icon> {{ item.title }}</v-card-title>
            <v-card-subtitle>Go to page</v-card-subtitle>
            <v-card-text v-html="item.desc"></v-card-text>
          </v-card>
        </p>
      </v-responsive>
    </v-container>
  </v-main>
</template>

<style lang="scss" scoped>
.statistics-item {
  display: inline-flex;
  align-items: center;
  gap: 0.35em;
  white-space: nowrap;
}

.statistics-count {
  display: inline-grid;
  min-width: 5ch;
  text-align: right;
  font-variant-numeric: tabular-nums;
}

.statistics-skeleton {
  align-self: center;
  width: 100%;
  height: 0.85em;
  border-radius: 4px;
  background: rgba(var(--v-theme-on-surface), 0.14);
  animation: statistics-pulse 1.2s ease-in-out infinite;
}

.statistics-number-enter-active,
.statistics-number-leave-active {
  transition:
    opacity 0.2s ease,
    transform 0.2s ease;
}

.statistics-number-enter-from,
.statistics-number-leave-to {
  opacity: 0;
  transform: translateY(3px);
}

@keyframes statistics-pulse {
  50% {
    opacity: 0.35;
  }
}

@media (prefers-reduced-motion: reduce) {
  .statistics-skeleton {
    animation: none;
  }

  .statistics-number-enter-active,
  .statistics-number-leave-active {
    transition: none;
  }
}
</style>
