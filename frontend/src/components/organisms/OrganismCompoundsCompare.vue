<script setup lang="ts">
// Core
import { ref, computed, onMounted } from 'vue';

// Components
import OrganismCompoundMassSpectraSection from '@/components/organisms/OrganismCompoundMassSpectraSection.vue';
import AtomChip from '@/components/atoms/AtomChip.vue';
import AtomSnackbar from '@/components/atoms/AtomSnackbar.vue';

// Intrefaces
import type { CompoundCompare } from '@/interfaces/CompoundCompare';
import type { MassTable } from '@/interfaces/MassTable';

// Props
const props = defineProps<{
  compoundIds?: string;
}>();

// Data
const colors = ['#FF9800', '#4CAF50', '#3F51B5', '#9C27B0', '#E91E63']; // orange, green, indigo, purple, pink
const compounds = ref<CompoundCompare[]>([]);
const massSpectras = ref<MassTable[][]>([]);
const loading = ref(true);
const error = ref<string | null>(null);

const showSnackbar = ref(false);
const snackbarMessage = ref('');

// Methods
const showAlert = (msg: string) => {
  snackbarMessage.value = msg;
  showSnackbar.value = true;
};

const calculateMatchFactor = (spectrumA: MassTable[], spectrumB: MassTable[]): number => {
  if (!spectrumA.length || !spectrumB.length) return 0;

  const maxA = Math.max(...spectrumA.map((p) => p.intensity));
  const maxB = Math.max(...spectrumB.map((p) => p.intensity));
  const normA = spectrumA.map((p) => ({ mz: p.mz, intensity: (p.intensity / maxA) * 100 }));
  const normB = spectrumB.map((p) => ({ mz: p.mz, intensity: (p.intensity / maxB) * 100 }));

  const mzSet = new Set<number>([...normA.map((p) => p.mz), ...normB.map((p) => p.mz)]);
  const mzValues = Array.from(mzSet).sort((a, b) => a - b);

  const aVec = mzValues.map((mz) => normA.find((p) => p.mz === mz)?.intensity ?? 0);
  const bVec = mzValues.map((mz) => normB.find((p) => p.mz === mz)?.intensity ?? 0);

  let dot = 0,
    normA2 = 0,
    normB2 = 0;
  for (let i = 0; i < mzValues.length; i++) {
    const w = Math.pow(mzValues[i], 2);
    dot += w * aVec[i] * bVec[i];
    normA2 += w * Math.pow(aVec[i], 2);
    normB2 += w * Math.pow(bVec[i], 2);
  }

  if (normA2 === 0 || normB2 === 0) return 0;

  const matchFactor = Math.round(999 * Math.pow(dot / Math.sqrt(normA2 * normB2), 3));

  return Math.min(Math.max(matchFactor, 0), 999);
};

const matchFactor = computed(() => {
  if (massSpectras.value.length === 2)
    return calculateMatchFactor(massSpectras.value[0], massSpectras.value[1]);

  return null;
});

// Hoox
onMounted(async () => {
  try {
    if (!props.compoundIds) {
      showAlert(`Error: No ids provided`);
      throw new Error('No ids provided');
    }

    const ids = props.compoundIds.split(',').map((id) => parseInt(id));
    const res = await fetch('/api/compound/compare', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ids }),
    });

    if (!res.ok) {
      showAlert(`Error: ${res.status}`);
      throw new Error(`Error: ${res.status}`);
    }

    compounds.value = await res.json();
    compounds.value.forEach((c) => {
      massSpectras.value.push(c.massTable);
    });
  } catch (e: any) {
    error.value = e.message;
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <v-main>
    <v-container class="v-col-auto">
      <AtomSnackbar
        v-model="showSnackbar"
        :message="snackbarMessage"
        color="error"
        :timeout="5000"
      />

      <div class="mb-4 d-flex justify-space-between align-center">
        <div class="d-flex align-center flex-wrap">
          <AtomChip class="pa-0" variant="text" size="x-large">
            <strong>Compare Compounds</strong>
          </AtomChip>
          <AtomChip
            v-for="(c, i) in compounds"
            :key="i"
            :class="['mx-2', { 'my-2': $display.xs }]"
            :color="colors[i]"
            size="x-large"
            :label="true"
          >
            <strong>{{ c.abbr }}</strong>
          </AtomChip>
        </div>
      </div>
      <div class="mb-4 d-flex justify-center align-center mx-auto">
        <span v-if="matchFactor !== null">
          <strong><i>Match Factor = </i></strong>
          {{ matchFactor }}
        </span>
      </div>
      <v-form class="mb-4">
        <v-row>
          <v-col cols="12" md="12">
            <OrganismCompoundMassSpectraSection :massSpectra="massSpectras" :compare="true" />
          </v-col>
        </v-row>
      </v-form>
      <div class="mb-4 mt-6">
        <v-divider class="border-opacity-50"></v-divider>
      </div>
      <div class="mb-4 d-flex justify-space-between align-center">
        <div>
          <AtomChip class="pa-0" variant="text" size="x-large">
            <strong>Compounds:</strong>
          </AtomChip>
        </div>
      </div>
      <v-form v-for="(compound, index) in compounds" :key="compound.id">
        <v-row>
          <v-col cols="12" md="6">
            <p>
              <AtomChip variant="text" size="x-large">
                <strong>Abbreviation:</strong>
              </AtomChip>
              <AtomChip class="mx-2 h-auto text-pre-wrap" :color="colors[index]" :label="true">
                <strong>{{ compound.abbr }}</strong>
              </AtomChip>
            </p>
            <p>
              <AtomChip variant="text" size="x-large">
                <strong>Name:</strong>
              </AtomChip>
              <AtomChip class="mx-2 h-auto text-pre-wrap" :color="colors[index]" :label="true"
                ><strong>{{ compound.name }}</strong>
              </AtomChip>
            </p>
          </v-col>
          <v-col cols="12" md="6">
            <p>
              <v-img v-if="compound.picture" :src="compound.picture" height="250" class="mb-3" />
            </p>
          </v-col>
        </v-row>
        <v-row>
          <v-col class="py-0">
            <p>
              <AtomChip variant="text" size="x-large">
                <strong>Mass spectra:</strong>
              </AtomChip>
            </p>
          </v-col>
        </v-row>
        <v-row>
          <v-col cols="12" md="12">
            <OrganismCompoundMassSpectraSection
              :massSpectra="[massSpectras[index]]"
              :compare="false"
            />
          </v-col>
        </v-row>
        <v-row v-if="index != Object.keys(compounds).length - 1" class="mb-4 mt-6">
          <v-col cols="12" md="12">
            <v-divider class="border-opacity-50"></v-divider>
          </v-col>
        </v-row>
      </v-form>
    </v-container>
  </v-main>
</template>
