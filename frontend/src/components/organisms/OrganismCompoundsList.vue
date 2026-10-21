<script setup lang="ts">
// Core
import { ref, shallowRef, reactive, watch, onMounted } from 'vue';

// Modules
import {
  mdiSelectCompare,
  mdiArrowLeft,
  mdiArrowRight,
  mdiTuneVariant,
  mdiMinusCircleOutline,
  mdiMinusCircleMultipleOutline,
  mdiPlus,
} from '@mdi/js';

// Components
import { appState } from '@/db/auth.ts';
import MoleculeScrollButton from '@/components/molecules/MoleculeScrollButton.vue';
import AtomSnackbar from '@/components/atoms/AtomSnackbar.vue';
import AtomChip from '@/components/atoms/AtomChip.vue';
import AtomButton from '@/components/atoms/AtomButton.vue';
import AtomTextField from '@/components/atoms/AtomTextField.vue';

// Intrefaces
import type { Compound } from '@/interfaces/Compound';
import type { CompoundType } from '@/interfaces/CompoundType';

// Data
const itemsPerPage = shallowRef(12);
const pageSize = 12;

const compounds = ref<Compound[]>([]);
const loading = shallowRef(false);
const error = shallowRef<string | null>(null);
let debounceTimer: number | undefined;

const allTypes = ref<CompoundType[]>([]);
const treeItems = ref<any[]>([]);

const filters = reactive({
  name: '',
  molecularFormula: '',
  compoundId: '',
  canonicalSmiles: '',
  mz: '',
  types: [],
  logic: 'AND',
  charge: ['1', '2'],
});

const selectedCompounds = ref<number[]>([]);
const showFilters = ref(false);
const showSnackbar = ref(false);
const snackbarMessage = ref('');

// Watchers
watch(
  () => ({ ...filters }),
  () => {
    clearTimeout(debounceTimer);
    debounceTimer = window.setTimeout(() => {
      loadCompounds();
    }, 400);
  },
  { deep: true }
);

// Methods
const showAlert = (msg: string) => {
  snackbarMessage.value = msg;
  showSnackbar.value = true;
};

const formatCompoundTypes = (types?: CompoundType[] | null, emptyValue = '-'): string => {
  if (!types || types.length === 0) return emptyValue;

  return types
    .map((t) => t.name)
    .filter(Boolean)
    .join(', ');
};

const loadCompounds = async () => {
  loading.value = true;
  error.value = null;
  try {
    const res = await fetch(`/api/compound/list`, {
      method: 'QUERY',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        name: filters.name || null,
        molecularFormula: filters.molecularFormula || null,
        compoundId: filters.compoundId || null,
        canonicalSmiles: filters.canonicalSmiles || null,
        mz: filters.mz || null,
        types: filters.types || null,
        logic: filters.logic,
        charge: filters.charge,
      }),
    });

    if (!res.ok) {
      showAlert(`Loading error: ${res.status}`);
    }

    const data: Compound[] = await res.json();

    compounds.value = data.map((c) => ({
      ...c,
      typesText: formatCompoundTypes(c.types),
    }));
  } catch (err: any) {
    error.value = err.message || 'Error loading data';
  } finally {
    loading.value = false;
  }
};

const onClickViewAll = () => {
  itemsPerPage.value = itemsPerPage.value === pageSize ? compounds.value.length : pageSize;
};

const buildTree = (types: CompoundType[]) => {
  const grouped: Record<number, CompoundType[]> = {};

  types.forEach((t) => {
    if (!t.group) return;
    if (!grouped[t.group.id]) grouped[t.group.id] = [];
    grouped[t.group.id].push(t);
  });

  const items: any[] = [];

  Object.values(grouped).forEach((groupItems, index) => {
    const group = groupItems[0].group;
    if (!group) return;

    items.push({ type: 'subheader', title: group.name });

    groupItems.forEach((item) => {
      items.push({ title: item.name, value: item.id });
    });

    if (index < Object.values(grouped).length - 1) {
      items.push({ type: 'divider', text: 'or' });
    }
  });

  return items;
};

const isDoublyCharged = (molecularFormula: string | undefined) => {
  return molecularFormula?.endsWith(')2-');
};

// Hoox
onMounted(async () => {
  const typeRes = await fetch('/api/compound/types');
  if (typeRes.ok) {
    allTypes.value = await typeRes.json();
    treeItems.value = buildTree(allTypes.value);
  }

  await loadCompounds();
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
      <div class="mb-4 d-flex align-center">
        <AtomChip class="pl-0" variant="text" size="x-large">
          <strong class="mr-1">Compounds List</strong>
        </AtomChip>

        <div v-if="$display.xs" class="mr-1">
          <v-btn-toggle v-model="filters.charge" variant="outlined" multiple mandatory>
            <v-btn value="1" :icon="mdiMinusCircleOutline" color="blue-darken-3"></v-btn>
            <v-btn value="2" :icon="mdiMinusCircleMultipleOutline" color="deep-purple"></v-btn>
          </v-btn-toggle>
        </div>

        <div v-else class="mr-4">
          <v-btn-toggle v-model="filters.charge" class="mr-1" variant="outlined" multiple mandatory>
            <v-btn value="1" color="blue-darken-3"> 1 charge (-) </v-btn>
            <v-btn value="2" color="deep-purple"> 2 charges (2-) </v-btn>
          </v-btn-toggle>
        </div>

        <AtomButton
          v-if="appState.isLoggedIn && $display.xs"
          :icon="mdiPlus"
          color="primary"
          size="default"
          rounded=""
          href="/compound/new"
          target="_blank"
          class="mr-1"
        >
        </AtomButton>
        <AtomButton
          v-if="appState.isLoggedIn && !$display.xs"
          color="primary"
          size="large"
          href="/compound/new"
          target="_blank"
          class="mr-1"
        >
          <span>+ Add new</span>
        </AtomButton>

        <div class="ml-auto d-flex">
          <AtomButton
            v-if="$display.xs"
            color="primary"
            size="default"
            :icon="mdiSelectCompare"
            rounded=""
            :disabled="selectedCompounds.length < 2"
            :href="`/compound/compare?ids=${selectedCompounds.join(',')}`"
            target="_blank"
          >
          </AtomButton>
          <AtomButton
            v-else
            color="primary"
            size="large"
            :prepend-icon="mdiSelectCompare"
            :disabled="selectedCompounds.length < 2"
            :href="`/compound/compare?ids=${selectedCompounds.join(',')}`"
            target="_blank"
          >
            <span class="d-none d-sm-flex">Compare</span>
          </AtomButton>
        </div>
      </div>
      <v-data-iterator :items="compounds" :items-per-page="itemsPerPage">
        <template
          v-if="$display.xs || $display.sm"
          v-slot:header="{ page, pageCount, prevPage, nextPage }"
        >
          <div class="d-flex justify-space-between align-center mb-4">
            <div class="d-flex gap-2 flex-grow-1 me-4">
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
                :disabled="compounds.length < itemsPerPage"
                color="secondary"
                size="large"
                @click="onClickViewAll"
              >
                {{ itemsPerPage === pageSize ? 'View all' : 'View less' }}
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
                class="mr-3 border"
                v-model="filters.logic"
                mandatory
                color="secondary"
                title="Filter logic"
                density="default"
              >
                <v-btn value="OR">OR</v-btn>
                <v-btn value="AND">AND</v-btn>
              </v-btn-toggle>
              <AtomTextField
                class="mr-3"
                v-model="filters.name"
                label="Name"
                :hideDetails="true"
                density="compact"
              />
              <v-autocomplete
                v-model="filters.types"
                :items="treeItems"
                label="Types"
                multiple
                clearable
                persistent-placeholder
                hide-details="auto"
                variant="outlined"
                density="compact"
                item-color="info"
              >
                <template v-slot:selection="{ item, index }">
                  <span v-if="index === 0" class="text-grey text-caption align-self-center">
                    (+ {{ filters.types.length }})
                  </span>
                </template>
              </v-autocomplete>
            </div>
          </div>
          <div v-if="showFilters" class="d-flex justify-space-between align-center mb-4">
            <div class="d-flex gap-3 flex-grow-1">
              <AtomTextField
                class="mr-3"
                v-model="filters.molecularFormula"
                label="Molecular formula"
                :hideDetails="true"
                density="compact"
              />
              <AtomTextField
                v-model="filters.compoundId"
                label="ID"
                :hideDetails="true"
                density="compact"
              />
            </div>
          </div>
          <div v-if="showFilters" class="d-flex justify-space-between align-center mb-4">
            <div class="d-flex gap-3 flex-grow-1">
              <AtomTextField
                class="mr-3"
                v-model="filters.canonicalSmiles"
                label="Canonical SMILES"
                :hideDetails="true"
                density="compact"
              />
              <AtomTextField
                v-model="filters.mz"
                label="m/z"
                :hideDetails="true"
                density="compact"
                title="Multiple values separated by commas or spaces (',' or ' ')"
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
              <AtomTextField class="mr-3" v-model="filters.name" label="Name" :hideDetails="true" />
              <AtomTextField
                class="mr-3"
                v-model="filters.molecularFormula"
                label="Molecular formula"
                :hideDetails="true"
              />
              <AtomTextField
                class="mr-3"
                v-model="filters.compoundId"
                label="ID"
                :hideDetails="true"
              />
              <AtomTextField
                class="mr-3"
                v-model="filters.canonicalSmiles"
                label="Canonical SMILES"
                :hideDetails="true"
              />
              <AtomTextField
                class="mr-3"
                v-model="filters.mz"
                label="m/z"
                :hideDetails="true"
                title="Multiple values separated by commas or spaces (',' or ' ')"
              />
              <v-autocomplete
                v-model="filters.types"
                :items="treeItems"
                label="Types"
                multiple
                clearable
                persistent-placeholder
                hide-details="auto"
                variant="outlined"
                density="comfortable"
                item-color="info"
              >
                <template v-slot:selection="{ item, index }">
                  <v-chip
                    v-if="index < 1"
                    color="info"
                    variant="outlined"
                    :text="item.title"
                  ></v-chip>
                  <span v-if="index === 1" class="text-grey text-caption align-self-center">
                    (+{{ filters.types.length - 1 }} others)
                  </span>
                </template>
              </v-autocomplete>
            </div>
            <div class="d-flex align-center gap-2">
              <AtomButton
                class="mr-3"
                :disabled="compounds.length < itemsPerPage"
                color="secondary"
                size="large"
                @click="onClickViewAll"
              >
                {{ itemsPerPage === pageSize ? 'View all' : 'View less' }}
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
              <v-card class="mx-auto h-100" :href="`/compound/view/${item.raw.id}`" target="_blank">
                <v-checkbox
                  :disabled="
                    selectedCompounds.length === 5 && !selectedCompounds.includes(item.raw.id ?? -1)
                  "
                  v-model="selectedCompounds"
                  :value="item.raw.id"
                  class="compare-checkbox"
                  color="secondary"
                />
                <AtomChip
                  v-if="isDoublyCharged(item.raw.molecularFormula)"
                  class="charge-info"
                  color="deep-purple"
                  size="x-small"
                  >2 charges (2-)
                </AtomChip>
                <AtomChip v-else class="charge-info" color="blue-darken-3" size="x-small"
                  >1 charge (-)
                </AtomChip>
                <v-list lines="two" density="compact" class="pt-0">
                  <v-list-item
                    :class="[
                      'text-center',
                      isDoublyCharged(item.raw.molecularFormula)
                        ? 'bg-deep-purple-lighten-5'
                        : 'bg-blue-lighten-5',
                    ]"
                  >
                    <v-list-item-title>
                      <h3>
                        {{ item.raw.abbr }}
                      </h3>
                    </v-list-item-title>
                    <v-list-item-subtitle :title="item.raw.name" class="text-truncate">
                      {{ item.raw.name }}
                    </v-list-item-subtitle>
                  </v-list-item>
                </v-list>
                <v-img
                  class="compound-img"
                  :src="item.raw.picture"
                  alt="Compound picture"
                  height="200"
                />
                <v-list lines="two" density="compact">
                  <v-list-item>
                    <v-list-item-title>{{ item.raw.molecularFormula || '-' }}</v-list-item-title>
                    <v-list-item-subtitle>Molecular formula</v-list-item-subtitle>
                  </v-list-item>
                  <v-list-item>
                    <v-list-item-title>{{ item.raw.compoundId || '-' }}</v-list-item-title>
                    <v-list-item-subtitle>ID</v-list-item-subtitle>
                  </v-list-item>
                  <v-list-item>
                    <v-list-item-title :title="item.raw.canonicalSmiles">
                      {{ item.raw.canonicalSmiles || '-' }}
                    </v-list-item-title>
                    <v-list-item-subtitle>Canonical SMILES</v-list-item-subtitle>
                  </v-list-item>
                  <v-list-item>
                    <v-list-item-title :title="item.raw.typesText">
                      {{ item.raw.typesText }}
                    </v-list-item-title>
                    <v-list-item-subtitle>Types</v-list-item-subtitle>
                  </v-list-item>
                </v-list>
              </v-card>
            </v-col>
          </v-row>
        </template>

        <template v-slot:footer="{ page, pageCount, prevPage, nextPage }">
          <v-footer class="justify-space-between mt-4" color="secondary" rounded>
            <v-chip v-if="!$display.xs" class="pa-0" variant="text" size="x-large">
              Found: {{ compounds.length }}
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
.compare-checkbox {
  position: absolute;
  top: -8px;
  z-index: 10;
}

.charge-info {
  position: absolute;
  top: 8px;
  right: 8px;
  z-index: 10;
}

.compound-img {
  :deep(img) {
    padding: 0 16px;
  }
}

.list-info {
  opacity: 0.6;

  &:hover {
    opacity: 1;
  }
}
</style>
