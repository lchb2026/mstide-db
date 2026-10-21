<script setup lang="ts">
// Core
import { ref, computed, onMounted } from 'vue';

// Modules
import { mdiFileUpload, mdiFileImage, mdiFileChart, mdiDownload } from '@mdi/js';

// Components
import AtomSnackbar from '@/components/atoms/AtomSnackbar.vue';
import AtomTextField from '@/components/atoms/AtomTextField.vue';
import AtomFileInput from '@/components/atoms/AtomFileInput.vue';
import AtomButton from '@/components/atoms/AtomButton.vue';

// Interfaces
import type { Compound } from '@/interfaces/Compound';
import type { CompoundType } from '@/interfaces/CompoundType';
import type { Fragment } from '@/interfaces/Fragment';

// Props
const props = defineProps<{
  form: Compound;
}>();

// Emits
const emit = defineEmits<{
  (e: 'update:form', value: Compound): void;
  (e: 'sdfUpload', event: Event): void;
  (e: 'pictureUpload', event: Event): void;
  (e: 'msUpload', event: Event): void;
}>();

// Data
const allTypes = ref<CompoundType[]>([]);
const availableFragments = ref<Fragment[]>([]);
const ATOMIC_MASSES: Record<string, number> = {
  H: 1.008,
  He: 4.0026,
  Li: 6.94,
  Be: 9.0122,
  B: 10.81,
  C: 12.011,
  N: 14.007,
  O: 15.999,
  F: 18.998,
  Ne: 20.18,
  Na: 22.99,
  Mg: 24.305,
  Al: 26.982,
  Si: 28.085,
  P: 30.974,
  S: 32.06,
  Cl: 35.45,
  Ar: 39.948,
  K: 39.098,
  Ca: 40.078,
  Sc: 44.956,
  Ti: 47.867,
  V: 50.942,
  Cr: 52.0,
  Mn: 54.938,
  Fe: 55.845,
  Co: 58.933,
  Ni: 58.693,
  Cu: 63.546,
  Zn: 65.38,
  Ga: 69.723,
  Ge: 72.63,
  As: 74.922,
  Se: 78.971,
  Br: 79.904,
  Kr: 83.798,
  Rb: 85.468,
  Sr: 87.62,
  Y: 88.906,
  Zr: 91.224,
  Nb: 92.906,
  Mo: 95.95,
  Tc: 98,
  Ru: 101.07,
  Rh: 102.91,
  Pd: 106.42,
  Ag: 107.868,
  Cd: 112.414,
  In: 114.818,
  Sn: 118.71,
  Sb: 121.76,
  I: 126.904,
  Te: 127.6,
  Xe: 131.293,
  Cs: 132.905,
  Ba: 137.327,
  La: 138.905,
  Ce: 140.116,
  Pr: 140.907,
  Nd: 144.242,
  Pm: 145,
  Sm: 150.36,
  Eu: 152.0,
  Gd: 157.25,
  Tb: 158.925,
  Dy: 162.5,
  Ho: 164.93,
  Er: 167.259,
  Tm: 168.934,
  Yb: 173.04,
  Lu: 175.0,
  Hf: 178.49,
  Ta: 180.948,
  W: 183.84,
  Re: 186.207,
  Os: 190.23,
  Ir: 192.217,
  Pt: 195.084,
  Au: 196.967,
  Hg: 200.592,
  Tl: 204.38,
  Pb: 207.2,
  Bi: 208.98,
  Po: 209,
  At: 210,
  Rn: 222,
  Fr: 223,
  Ra: 226,
  Ac: 227,
  Th: 232.038,
  Pa: 231.036,
  U: 238.029,
  Np: 237,
  Pu: 244,
  Am: 243,
  Cm: 247,
  Bk: 247,
  Cf: 251,
  Es: 252,
  Fm: 257,
  Md: 258,
  No: 259,
  Lr: 262,
  Rf: 267,
  Db: 270,
  Sg: 271,
  Bh: 270,
  Hs: 277,
  Mt: 276,
  Ds: 281,
  Rg: 280,
  Cn: 285,
  Nh: 284,
  Fl: 289,
  Mc: 288,
  Lv: 293,
  Ts: 294,
  Og: 294,
};

const showSnackbar = ref(false);
const snackbarMessage = ref('');

// Computed
const groupedTypes = computed(() => {
  if (!allTypes.value.length) return [];

  const groups = new Map<string, CompoundType[]>();

  for (const t of allTypes.value) {
    const groupName = t.group?.name || 'Other';
    if (!groups.has(groupName)) groups.set(groupName, []);
    groups.get(groupName)!.push(t);
  }

  const result: any[] = [];
  const entries = Array.from(groups.entries());

  entries.forEach(([groupName, types], index) => {
    result.push({ type: 'subheader', name: groupName });
    result.push(
      ...types.map((t) => ({
        id: t.id,
        name: t.name,
        group: t.group,
      }))
    );
    if (index < entries.length - 1) result.push({ type: 'divider' });
  });

  return result;
});

// Methods
const showAlert = (msg: string) => {
  snackbarMessage.value = msg;
  showSnackbar.value = true;
};

const handleUpdate = (key: keyof Compound, value: string) => {
  emit('update:form', { ...props.form, [key]: value });
};

const parseFormula = (formula: string): Record<string, number> => {
  const elementRegex = /([A-Z][a-z]*)(\d*)/g;
  const elements: Record<string, number> = {};
  let match;
  while ((match = elementRegex.exec(formula)) !== null) {
    const [, element, count] = match;
    elements[element] = parseInt(count || '1', 10);
  }
  return elements;
};

const getMaxSecondDigit = async (mass: number): Promise<number> => {
  try {
    const response = await fetch(`/api/compound/maxSecondDigit?mass=${mass}`);
    if (!response.ok) showAlert(`Error: ${response.status}`);
    const data = await response.json();
    return data.maxSecond ?? 0;
  } catch (err: any) {
    showAlert(err.message);
    return 0;
  }
};

const generateCompoundID = async () => {
  const formula = props.form.molecularFormula || '';
  const match = formula.match(/\)2-|-$/);
  const charge = !match ? 0 : match[0] === ')2-' ? 2 : 1;

  let mass = 0;
  const cleanFormula = formula.replace(/\)?2-|-$/, '');
  for (const [el, count] of Object.entries(parseFormula(cleanFormula))) {
    const elMass = ATOMIC_MASSES[el];
    if (!elMass) showAlert(`Undefined element: ${el}`);
    mass += elMass * count;
  }

  const massCeil = Math.ceil(mass);
  const maxSecond = await getMaxSecondDigit(massCeil);
  const secondDigit = maxSecond + 1;

  handleUpdate('compoundId', `${massCeil}.${secondDigit}.${charge}`);
};

const handleFragmentsUpdate = (value: Fragment[]) => {
  emit('update:form', { ...props.form, fragments: value });
};

const handleTypesUpdate = (value: CompoundType[]) => {
  emit('update:form', { ...props.form, types: value });
};

// Hoox
onMounted(async () => {
  const fragmentsRes = await fetch('/api/compound/fragments');
  if (fragmentsRes.ok) {
    availableFragments.value = await fragmentsRes.json();
  }

  const typeRes = await fetch('/api/compound/types');
  if (typeRes.ok) {
    allTypes.value = await typeRes.json();
  }
});
</script>

<template>
  <AtomSnackbar v-model="showSnackbar" :message="snackbarMessage" color="error" :timeout="5000" />
  <v-row class="row-main">
    <v-col cols="12" md="6">
      <AtomTextField
        label="Abbreviation"
        :model-value="form.abbr"
        @update:modelValue="(val: any) => handleUpdate('abbr', val)"
      />
      <AtomTextField
        label="Name"
        :model-value="form.name"
        @update:modelValue="(val: any) => handleUpdate('name', val)"
      />
      <AtomTextField
        label="Molecular formula"
        :model-value="form.molecularFormula"
        @input="generateCompoundID"
        @update:modelValue="(val: any) => handleUpdate('molecularFormula', val)"
      />
      <AtomTextField
        label="ID"
        :model-value="form.compoundId"
        @update:modelValue="(val: any) => handleUpdate('compoundId', val)"
      />
      <AtomTextField
        label="Canonical SMILES"
        :model-value="form.canonicalSmiles"
        @update:modelValue="(val: any) => handleUpdate('canonicalSmiles', val)"
      />
      <AtomTextField
        label="Instrument"
        :model-value="form.instrument"
        @update:modelValue="(val: any) => handleUpdate('instrument', val)"
      />
      <AtomTextField
        label="Scan type"
        :model-value="form.scanType"
        @update:modelValue="(val: any) => handleUpdate('scanType', val)"
      />
      <AtomTextField
        label="Ionization"
        :model-value="form.ionization"
        @update:modelValue="(val: any) => handleUpdate('ionization', val)"
      />
      <AtomTextField
        label="Declustering potential"
        :model-value="form.declusteringPotential"
        @update:modelValue="(val: any) => handleUpdate('declusteringPotential', val)"
      />
      <AtomTextField
        label="Collision energy"
        :model-value="form.collisionEnergy"
        @update:modelValue="(val: any) => handleUpdate('collisionEnergy', val)"
      />
      <v-autocomplete
        v-model="props.form.fragments"
        :items="availableFragments"
        label="Fragments"
        item-title="name"
        item-value="id"
        chips
        closable-chips
        multiple
        clearable
        persistent-placeholder
        return-object
        hide-details="auto"
        variant="outlined"
        density="comfortable"
        item-color="info"
        @update:modelValue="handleFragmentsUpdate"
      >
        <template v-slot:chip="{ props, item }">
          <v-chip v-bind="props" color="info" variant="outlined">
            <strong>
              {{ item.raw.name }}
            </strong>
            &nbsp;
            <span>(<i>m/z:</i>&nbsp;{{ item.raw.molecularMass }})</span>
          </v-chip>
        </template>

        <template v-slot:item="{ props, item }">
          <v-list-item v-bind="props" :key="item.raw.id">
            <template #prepend>
              <v-avatar size="96" rounded="0">
                <v-img :src="item.raw.picture" />
              </v-avatar>
            </template>
            <v-list-item-subtitle
              ><i>m/z:</i>&nbsp;{{ item.raw.molecularMass }}
            </v-list-item-subtitle>
          </v-list-item>
        </template>
      </v-autocomplete>
    </v-col>

    <v-col cols="12" md="6">
      <AtomFileInput
        label="SDF file"
        accept=".sdf"
        :prepend-icon="mdiFileUpload"
        @change="(event) => emit('sdfUpload', event)"
      />
      <AtomButton
        v-if="form.sdf"
        class="mb-4"
        color="secondary"
        :prepend-icon="mdiDownload"
        :href="form.sdf"
        :download="`comp_${form.abbr}.sdf`"
      >
        Download SDF file
      </AtomButton>
      <AtomFileInput
        label="Picture"
        accept="image/*"
        :prepend-icon="mdiFileImage"
        @change="(event) => emit('pictureUpload', event)"
      />

      <div class="compound-picture">
        <v-img :src="form.picture" alt="Compound picture" />
      </div>

      <v-autocomplete
        v-model="props.form.types"
        :items="groupedTypes"
        label="Types"
        item-title="name"
        item-value="id"
        return-object
        multiple
        chips
        closable-chips
        clearable
        density="comfortable"
        variant="outlined"
        hide-details="auto"
        item-color="info"
        @update:modelValue="handleTypesUpdate"
      >
        <template v-slot:chip="{ props, item }">
          <v-chip v-bind="props" color="info" variant="outlined">
            <strong>
              {{ item.raw.name }}
            </strong>
          </v-chip>
        </template>
        <template v-slot:subheader="{ props }">
          <v-list-subheader>
            <strong>{{ props.name }}</strong>
          </v-list-subheader>
        </template>
      </v-autocomplete>
    </v-col>
  </v-row>
  <v-row>
    <v-col cols="12" md="12" class="mt-4">
      <AtomFileInput
        label="The mass spectrometry table"
        accept=".txt"
        :prepend-icon="mdiFileChart"
        hide-details="auto"
        @change="(event) => emit('msUpload', event)"
      />
    </v-col>
  </v-row>
</template>

<style lang="scss" scoped>
.row-main {
  .v-col-md-6:first-child {
    border-right: 1px solid #ccc;
    padding-right: 20px;
  }

  .v-col-md-6:last-child {
    padding-left: 20px;

    .compound-picture {
      display: flex;
      justify-content: center;
      padding-bottom: 40px;
    }
  }
}
</style>
