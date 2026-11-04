<script setup lang="ts">
// Core
import { ref, reactive, computed, onMounted } from 'vue';

// Modules
import { useRouter } from 'vue-router';
import { mdiPencil, mdiDownload, mdiTrashCan, mdiAlert, mdiCircleSmall } from '@mdi/js';

// Components
import { appState } from '@/db/auth.ts';
import OrganismCompoundMassSpectraSection from '@/components/organisms/OrganismCompoundMassSpectraSection.vue';
import AtomSnackbar from '@/components/atoms/AtomSnackbar.vue';
import AtomChip from '@/components/atoms/AtomChip.vue';
import AtomButton from '@/components/atoms/AtomButton.vue';

// Intrefaces
import type { Compound } from '@/interfaces/Compound';
import type { MassTable } from '@/interfaces/MassTable';

// Props
const props = defineProps<{
  compoundId?: number | string;
}>();

// Router
const router = useRouter();

// Data
const deleteDialog = ref(false);
const showSnackbar = ref(false);
const snackbarMessage = ref('');

const form = reactive<Compound>({
  id: 0,
  abbr: '',
  name: '',
  molecularFormula: '',
  canonicalSmiles: '',
  compoundId: '',
  instrument: '',
  scanType: '',
  ionization: '',
  declusteringPotential: '',
  collisionEnergy: '',
  massTable: [],
  fragments: [],
  picture: '',
  sdf: '',
  types: [],
  typesText: '',
});

const typeTree = ref<any[]>([]);

const massSpectra: MassTable[] = reactive([]);

const hoveredMz = ref<number | null>(null);

const unassignedPeaks = computed(() => {
  const assignedMasses = new Set(
    form.fragments
      .filter((fragment) => fragment.molecularMass != null)
      .map((fragment) => Number(fragment.molecularMass))
  );
  return massSpectra.filter((entry) => !assignedMasses.has(entry.mz));
});

// Methods
const showAlert = (msg: string) => {
  snackbarMessage.value = msg;
  showSnackbar.value = true;
};

const openDeleteDialog = () => {
  deleteDialog.value = true;
};

const confirmDelete = async () => {
  if (!props.compoundId) return;

  try {
    const response = await fetch(`/api/compound/${props.compoundId}`, {
      method: 'DELETE',
    });

    if (!response.ok) {
      showAlert(`Failed to delete compound: ${response.status}`);
    }

    router.push('/compound/list');
  } catch (err: any) {
    showAlert(err.message || 'Error deleting compound');
  } finally {
    deleteDialog.value = false;
  }
};

const cancelDelete = () => {
  deleteDialog.value = false;
};

const isDoublyCharged = (molecularFormula: string | undefined) => {
  return molecularFormula?.endsWith(')2-');
};

// Hoox
onMounted(async () => {
  if (props.compoundId) {
    try {
      const response = await fetch(`/api/compound/${props.compoundId}`);
      if (!response.ok) showAlert(`Error: ${response.status}`);
      const data: Compound = await response.json();

      Object.assign(form, data);

      const grouped: Record<number, any> = {};
      for (const t of data.types || []) {
        if (!t.group) continue;

        if (!grouped[t.group.id]) {
          grouped[t.group.id] = {
            id: `group-${t.group.id}`,
            code: t.group.code,
            name: t.group.name,
            children: [],
          };
        }

        grouped[t.group.id].children.push({
          id: t.id,
          name: t.name,
        });
      }

      typeTree.value = Object.values(grouped);

      const massTable = data.massTable || [];
      massTable.map((i) => {
        if (i.visible === undefined) i.visible = true;
      });
      massSpectra.splice(0, massSpectra.length, ...massTable);
    } catch (err) {
      showAlert('Failed to load compound data.');
    }
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

      <v-dialog v-model="deleteDialog" max-width="500">
        <v-card>
          <v-card-title>
            <v-icon :icon="mdiAlert" color="error"></v-icon> Confirm Deletion
          </v-card-title>
          <v-card-text>
            Are you sure you want to delete compound <strong>{{ form.name }}</strong
            >?
          </v-card-text>
        </v-card>
        <AtomButton color="secondary" size="large" @click="cancelDelete">Cancel</AtomButton>
        <AtomButton color="error" size="large" @click="confirmDelete">Delete</AtomButton>
      </v-dialog>

      <div class="mb-4 d-flex justify-space-between align-center">
        <div>
          <AtomChip class="pa-0" variant="text" size="x-large">
            <strong>View Compound</strong>
          </AtomChip>
          <AtomChip
            class="mx-2 h-auto text-pre-wrap"
            :color="form.molecularFormula?.endsWith(')2-') ? 'deep-purple' : 'blue-darken-3'"
            size="x-large"
            :label="true"
          >
            <strong>{{ form.abbr }} ({{ form.name }})</strong>
          </AtomChip>
          <AtomButton
            v-if="appState.isLoggedIn"
            :prepend-icon="mdiPencil"
            color="primary"
            @click="router.push(`/compound/edit/${props.compoundId}`)"
            >Edit
          </AtomButton>
        </div>
        <div>
          <AtomChip v-if="isDoublyCharged(form.molecularFormula)" color="deep-purple" :label="true"
            >2 charges (2-)
          </AtomChip>
          <AtomChip v-else color="blue-darken-3" :label="true">1 charge (-)</AtomChip>
          <AtomButton
            v-if="appState.isLoggedIn"
            :prepend-icon="mdiTrashCan"
            color="error"
            @click="openDeleteDialog"
            >Delete
          </AtomButton>
        </div>
      </div>
      <v-form>
        <v-row>
          <v-col cols="12" md="6">
            <p>
              <AtomChip variant="text" size="x-large">
                <strong>Abbreviation:</strong>
              </AtomChip>
              <AtomChip class="mx-2" color="info" :label="true">
                <strong>{{ form.abbr }}</strong>
              </AtomChip>
            </p>
            <p>
              <AtomChip variant="text" size="x-large">
                <strong>Name:</strong>
              </AtomChip>
              <AtomChip class="mx-2 h-auto text-pre-wrap" color="info" :label="true">
                <strong>{{ form.name }}</strong>
              </AtomChip>
            </p>
            <p>
              <AtomChip variant="text" size="x-large">
                <strong>Molecular formula:</strong>
              </AtomChip>
              <AtomChip class="mx-2" color="info" :label="true">
                <strong>{{ form.molecularFormula }}</strong>
              </AtomChip>
            </p>
            <p>
              <AtomChip variant="text" size="x-large">
                <strong>ID:</strong>
              </AtomChip>
              <AtomChip class="mx-2" color="info" :label="true">
                <strong>{{ form.compoundId }}</strong>
              </AtomChip>
            </p>
            <p>
              <AtomChip variant="text" size="x-large">
                <strong>Canonical SMILES:</strong>
              </AtomChip>
              <AtomChip
                class="mx-2 h-auto text-pre-wrap"
                color="info"
                :label="true"
                :title="form.canonicalSmiles"
              >
                <strong>{{ form.canonicalSmiles }}</strong>
              </AtomChip>
            </p>
            <p>
              <AtomChip variant="text" size="x-large">
                <strong>Instrument:</strong>
              </AtomChip>
              <AtomChip class="mx-2" color="info" :label="true">
                <strong>{{ form.instrument }}</strong>
              </AtomChip>
            </p>
            <p>
              <AtomChip variant="text" size="x-large">
                <strong>Scan type:</strong>
              </AtomChip>
              <AtomChip class="mx-2" color="info" :label="true">
                <strong>{{ form.scanType }}</strong>
              </AtomChip>
            </p>
            <p>
              <AtomChip variant="text" size="x-large">
                <strong>Ionization:</strong>
              </AtomChip>
              <AtomChip class="mx-2" color="info" :label="true">
                <strong>{{ form.ionization }}</strong>
              </AtomChip>
            </p>
            <p>
              <AtomChip variant="text" size="x-large">
                <strong>Declustering potential:</strong>
              </AtomChip>
              <AtomChip class="mx-2" color="info" :label="true">
                <strong>{{ form.declusteringPotential }}</strong>
              </AtomChip>
            </p>
            <p>
              <AtomChip variant="text" size="x-large">
                <strong>Collision energy:</strong>
              </AtomChip>
              <AtomChip class="mx-2" color="info" :label="true">
                <strong>{{ form.collisionEnergy }}</strong>
              </AtomChip>
            </p>
          </v-col>
          <v-col cols="12" md="6">
            <p>
              <AtomButton
                v-if="form.sdf"
                color="secondary"
                :prepend-icon="mdiDownload"
                :href="form.sdf"
                :download="`comp_${form.abbr}.sdf`"
              >
                Download SDF file
              </AtomButton>
            </p>
            <p class="pt-4"><v-img :src="form.picture" alt="Compound picture" /></p>
            <p>
              <AtomChip variant="text" size="x-large">
                <strong>Types:</strong>
              </AtomChip>
              <v-treeview :items="typeTree" item-title="name" item-value="id" open-all>
                <template v-slot:prepend="{ item }">
                  <v-icon v-if="!item.children" :icon="mdiCircleSmall"></v-icon>
                </template>
                <template v-slot:title="{ item }">
                  <AtomChip v-if="!item.children" color="info" :label="true">
                    <strong>{{ item.name }}</strong>
                  </AtomChip>
                  <span v-else>{{ item.name }}</span>
                </template>
              </v-treeview>
            </p>
          </v-col>
        </v-row>
        <v-row>
          <v-col class="py-0">
            <p>
              <AtomChip class="px-0" variant="text" size="x-large">
                <strong>Mass spectra:</strong>
              </AtomChip>
            </p>
          </v-col>
        </v-row>
        <v-row>
          <v-col cols="12" md="12">
            <OrganismCompoundMassSpectraSection
              v-model:hoveredMz="hoveredMz"
              :massSpectra="[massSpectra]"
              :compare="false"
              :fragments="form.fragments"
            />
          </v-col>
        </v-row>
        <v-row>
          <v-col class="py-0">
            <p>
              <AtomChip class="px-0" variant="text" size="x-large">
                <strong>Fragments:</strong>
                <span class="ml-2 text-body-1">
                  {{ massSpectra.length - unassignedPeaks.length }} of
                  {{ massSpectra.length }} peaks assigned
                </span>
              </AtomChip>
            </p>
          </v-col>
        </v-row>
        <v-row>
          <v-col
            class="pa-2"
            v-for="fragment in form.fragments"
            :key="fragment.id"
            cols="12"
            sm="6"
            md="4"
            lg="3"
          >
            <v-card
              class="fragment-card d-flex flex-column align-center pa-3"
              :class="{
                'is-hovered':
                  hoveredMz !== null &&
                  fragment.molecularMass != null &&
                  Number(fragment.molecularMass) === hoveredMz,
              }"
              @mouseenter="
                hoveredMz = fragment.molecularMass == null ? null : Number(fragment.molecularMass)
              "
              @mouseleave="hoveredMz = null"
              @focus="
                hoveredMz = fragment.molecularMass == null ? null : Number(fragment.molecularMass)
              "
              @blur="hoveredMz = null"
              :href="`/fragment/view/${fragment.id}`"
              target="_blank"
            >
              <h3 class="mb-1">
                <b>{{ fragment.name }}</b>
              </h3>
              <v-img
                :src="fragment.picture || '/placeholder.png'"
                alt="Fragment picture"
                width="50%"
                height="150"
                class="mb-2"
              />
              <h3><i>m/z:</i> {{ fragment.molecularMass }}</h3>
            </v-card>
          </v-col>
        </v-row>
        <v-row>
          <v-col cols="12">
            <div class="unassigned-peaks text-body-1">
              <strong>Unassigned <span class="text-body-1">({{ unassignedPeaks.length }})</span>:</strong>
              <template v-for="(entry, index) in unassignedPeaks" :key="index">
                <span v-if="index > 0" aria-hidden="true">|</span>
                <button
                  type="button"
                  class="unassigned-peak"
                  :class="{ 'is-hovered': hoveredMz === entry.mz }"
                  :title="'m/z: ' + entry.mz + ' · Intensity: ' + entry.intensity"
                  @mouseenter="hoveredMz = entry.mz"
                  @mouseleave="hoveredMz = null"
                  @focus="hoveredMz = entry.mz"
                  @blur="hoveredMz = null"
                >
                  {{ entry.mz }}
                </button>
              </template>
            </div>
          </v-col>
        </v-row>
      </v-form>
    </v-container>
  </v-main>
</template>

<style lang="scss" scoped>
.fragment-card {
  transition:
    outline-color 0.2s ease,
    background-color 0.2s ease;
  outline: 2px solid transparent;
  outline-offset: -2px;

  &.is-hovered {
    outline-color: rgb(var(--v-theme-primary));
  }
}

.unassigned-peaks {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 6px;
}

.unassigned-peak {
  font: inherit;
  padding: 2px 6px;
  border: 1px solid transparent;
  border-radius: 4px;
  cursor: pointer;
  transition:
    border-color 0.2s ease,
    background-color 0.2s ease;

  &.is-hovered {
    border-color: rgb(var(--v-theme-primary));
  }
}
</style>
