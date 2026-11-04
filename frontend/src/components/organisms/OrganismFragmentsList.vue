<script setup lang="ts">
// Core
import { computed, onMounted, reactive, ref, shallowRef, watch } from 'vue';

// Modules
import { mdiArrowLeft, mdiArrowRight, mdiTuneVariant } from '@mdi/js';

// Components
import MoleculeScrollButton from '@/components/molecules/MoleculeScrollButton.vue';
import AtomButton from '@/components/atoms/AtomButton.vue';
import AtomChip from '@/components/atoms/AtomChip.vue';
import AtomSnackbar from '@/components/atoms/AtomSnackbar.vue';
import AtomTextField from '@/components/atoms/AtomTextField.vue';
import { appState } from '@/db/auth';

// Intrefaces
import type { Fragment } from '@/interfaces/Fragment';

// Data
const itemsPerPage = shallowRef(12);
const pageSize = 12;

const fragments = ref<Fragment[]>([]);
const loading = shallowRef(false);
const error = shallowRef<string | null>(null);
let debounceTimer: number | undefined;

const filters = reactive({
  name: '',
  compoundCanonicalSmiles: '',
  molecularMass: '',
  logic: 'AND',
});

const showFilters = ref(false);
const showSnackbar = ref(false);
const snackbarMessage = ref('');

// Watchers
watch(
  () => ({ ...filters }),
  () => {
    clearTimeout(debounceTimer);
    debounceTimer = window.setTimeout(() => {
      loadFragments();
    }, 400);
  },
  { deep: true }
);

// Computed
const viewAllText = computed(() => (itemsPerPage.value === pageSize ? 'View all' : 'View less'));

// Methods
const showAlert = (msg: string) => {
  snackbarMessage.value = msg;
  showSnackbar.value = true;
};

const loadFragments = async () => {
  loading.value = true;
  error.value = null;
  try {
    const res = await fetch(`/api/fragment/list`, {
      method: 'QUERY',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        name: filters.name || null,
        compoundCanonicalSmiles: filters.compoundCanonicalSmiles || null,
        molecularMass: filters.molecularMass || null,
        logic: filters.logic,
      }),
    });

    if (!res.ok) {
      showAlert(`Loading error: ${res.status}`);
    }

    const data: Fragment[] = await res.json();
    fragments.value = data;
  } catch (err: any) {
    error.value = err.message || 'Error loading data';
  } finally {
    loading.value = false;
  }
};

const onClickViewAll = () => {
  itemsPerPage.value = itemsPerPage.value === pageSize ? fragments.value.length : pageSize;
};

// Hoox
onMounted(async () => {
  await loadFragments();
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
      <div class="mb-4">
        <AtomChip class="pl-0" variant="text" size="x-large">
          <strong>Fragments List</strong>
        </AtomChip>
        <AtomButton
          v-if="appState.isLoggedIn"
          size="large"
          color="primary"
          href="/fragment/new"
          target="_blank"
        >
          + Add new
        </AtomButton>
      </div>
      <v-data-iterator :items="fragments" :items-per-page="itemsPerPage">
        <template
          v-if="$display.xs || $display.sm"
          v-slot:header="{ page, pageCount, prevPage, nextPage }"
        >
          <div class="d-flex justify-space-between align-center mb-4">
            <div class="d-flex gap-2 flex-grow-1">
              <AtomButton
                color="secondary"
                size="default"
                @click="showFilters = !showFilters"
                :icon="mdiTuneVariant"
                rounded=""
              ></AtomButton>
            </div>

            <div class="d-flex align-center gap-2">
              <AtomButton
                class="mr-3"
                :disabled="fragments.length < itemsPerPage"
                color="secondary"
                size="large"
                @click="onClickViewAll"
              >
                {{ viewAllText }}
              </AtomButton>
              <AtomButton
                class="mr-1"
                :disabled="page === 1"
                :icon="mdiArrowLeft"
                rounded=""
                color="secondary"
                size="default"
                @click="prevPage"
              />
              <AtomButton
                :disabled="page === pageCount"
                :icon="mdiArrowRight"
                rounded=""
                color="secondary"
                size="default"
                @click="nextPage"
              />
            </div>
          </div>
          <div v-if="showFilters" class="d-flex justify-space-between align-center mb-4">
            <div class="d-flex gap-3 flex-grow-1">
              <v-btn-toggle
                class="mr-3"
                v-model="filters.logic"
                mandatory
                color="secondary"
                title="Filter logic"
                density="compact"
              >
                <v-btn value="OR">OR</v-btn>
                <v-btn value="AND">AND</v-btn>
              </v-btn-toggle>
            </div>
          </div>
          <div v-if="showFilters" class="d-flex justify-space-between align-center mb-4">
            <div class="d-flex gap-3 flex-grow-1">
              <AtomTextField
                class="mr-3"
                v-model="filters.name"
                label="Abbreviation"
                :hideDetails="true"
                density="compact"
              />
              <AtomTextField
                v-model="filters.molecularMass"
                label="m/z"
                :hideDetails="true"
                density="compact"
                title="Multiple values separated by commas or spaces (',' or ' ')"
              />
            </div>
          </div>
          <div v-if="showFilters" class="d-flex justify-space-between align-center mb-4">
            <div class="d-flex gap-3 flex-grow-1">
              <AtomTextField
                v-model="filters.compoundCanonicalSmiles"
                label="Compound canonical SMILES"
                :hideDetails="true"
                density="compact"
              />
            </div>
          </div>
        </template>

        <template v-else v-slot:header="{ page, pageCount, prevPage, nextPage }">
          <div class="d-flex justify-space-between align-center mb-4">
            <div class="d-flex gap-3 flex-grow-1 me-4">
              <v-btn-toggle
                class="mr-3 border"
                v-model="filters.logic"
                mandatory
                color="secondary"
                title="Filter logic"
              >
                <v-btn value="OR">OR</v-btn>
                <v-btn value="AND">AND</v-btn>
              </v-btn-toggle>
              <AtomTextField
                class="mr-3"
                v-model="filters.name"
                label="Abbreviation"
                :hideDetails="true"
              />
              <AtomTextField
                class="mr-3"
                v-model="filters.compoundCanonicalSmiles"
                label="Compound canonical SMILES"
                :hideDetails="true"
              />
              <AtomTextField
                class="mr-3"
                v-model="filters.molecularMass"
                label="m/z"
                :hideDetails="true"
                title="Multiple values separated by commas or spaces (',' or ' ')"
              />
            </div>
            <div class="d-flex align-center gap-2">
              <AtomButton
                class="mr-3"
                :disabled="fragments.length < itemsPerPage"
                color="secondary"
                size="large"
                @click="onClickViewAll"
              >
                {{ viewAllText }}
              </AtomButton>
              <AtomButton
                class="mr-1"
                :disabled="page === 1"
                :prepend-icon="mdiArrowLeft"
                color="secondary"
                size="large"
                @click="prevPage"
              />
              <AtomButton
                :disabled="page === pageCount"
                :prepend-icon="mdiArrowRight"
                color="secondary"
                size="large"
                @click="nextPage"
              />
            </div>
          </div>
        </template>

        <template v-slot:default="{ items }">
          <v-row>
            <v-col v-for="(item, i) in items" :key="i" cols="12" sm="6" md="3" xl="3">
              <v-card class="mx-auto h-100" :href="`/fragment/view/${item.raw.id}`" target="_blank">
                <v-list lines="two" density="compact">
                  <v-list-item class="text-center">
                    <v-list-item-title>
                      <h3>
                        {{ item.raw.name }}
                      </h3>
                    </v-list-item-title>
                  </v-list-item>
                </v-list>
                <v-img
                  class="fragment-img"
                  :src="item.raw.picture"
                  alt="Fragment picture"
                  height="130px"
                />
                <v-list lines="two" density="compact">
                  <v-list-item>
                    <v-list-item-title>{{ item.raw.molecularMass || '-' }}</v-list-item-title>
                    <v-list-item-subtitle><i>m/z</i></v-list-item-subtitle>
                  </v-list-item>
                </v-list>
              </v-card>
            </v-col>
          </v-row>
        </template>

        <template v-slot:footer="{ page, pageCount, prevPage, nextPage }">
          <v-footer class="d-flex justify-space-between mt-4" color="secondary" rounded>
            <v-chip v-if="!$display.xs" class="pa-0" variant="text" size="x-large">
              Found: {{ fragments.length }}
            </v-chip>
            <v-chip class="pa-0" variant="text" size="x-large">
              Page {{ page }} of {{ pageCount }}
            </v-chip>
            <div v-if="$display.xs || $display.sm" class="d-flex align-center gap-2">
              <AtomButton
                class="mr-1"
                :disabled="page === 1"
                :icon="mdiArrowLeft"
                rounded=""
                color="secondary"
                size="default"
                @click="prevPage"
              />
              <AtomButton
                :disabled="page === pageCount"
                :icon="mdiArrowRight"
                rounded=""
                color="secondary"
                size="default"
                @click="nextPage"
              />
            </div>
            <div v-else>
              <AtomButton
                class="mr-1"
                :disabled="page === 1"
                :prepend-icon="mdiArrowLeft"
                color="secondary"
                size="large"
                @click="prevPage"
              />
              <AtomButton
                :disabled="page === pageCount"
                :prepend-icon="mdiArrowRight"
                color="secondary"
                size="large"
                @click="nextPage"
              />
            </div>
          </v-footer>
        </template>
      </v-data-iterator>

      <MoleculeScrollButton />
    </v-container>
  </v-main>
</template>

<style lang="scss" scoped>
.fragment-img {
  :deep(img) {
    padding: 0 16px;
  }
}
</style>
