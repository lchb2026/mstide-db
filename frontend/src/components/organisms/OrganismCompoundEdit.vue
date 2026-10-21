<script setup lang="ts">
// Core
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';

// Modules
import { mdiContentSave, mdiClose } from '@mdi/js';

// Interfaces
import type { Compound } from '@/interfaces/Compound';
import type { MassTable } from '@/interfaces/MassTable';
import type { Fragment } from '@/interfaces/Fragment';

// Components
import { appState } from '@/db/auth.ts';
import OrganismCompoundFormSection from '@/components/organisms/OrganismCompoundFormSection.vue';
import OrganismCompoundMassSpectraSection from '@/components/organisms/OrganismCompoundMassSpectraSection.vue';
import AtomChip from '@/components/atoms/AtomChip.vue';
import AtomButton from '@/components/atoms/AtomButton.vue';
import AtomSnackbar from '@/components/atoms/AtomSnackbar.vue';

// Props
const props = defineProps<{
  compoundId?: string;
}>();

// Router
const router = useRouter();

// Data
const pageTite = ref<string>('');

const form = reactive<Compound>({
  id: 0,
  abbr: '',
  name: '',
  molecularFormula: '',
  canonicalSmiles: '',
  compoundId: '',
  instrument: 'QTRAP 3200',
  scanType: 'MS2',
  ionization: 'ESI (-)',
  declusteringPotential: '',
  collisionEnergy: '',
  massTable: [],
  fragments: [],
  picture: '',
  sdf: '',
  types: [],
  typesText: '',
});

const massSpectra: MassTable[] = reactive([]);
const sdfFile = ref<File | null>(null);
const pictureFile = ref<File | null>(null);
const msFile = ref<File | null>(null);
const availableFragments = ref<Fragment[]>([]);

const showSnackbar = ref(false);
const snackbarMessage = ref('');

// Methods
const showAlert = (msg: string) => {
  snackbarMessage.value = msg;
  showSnackbar.value = true;
};

const saveCompound = async (): Promise<void> => {
  const compound: Compound = {
    id: form.id ? Number(form.id) : undefined,
    abbr: form.abbr,
    name: form.name,
    molecularFormula: form.molecularFormula,
    compoundId: form.compoundId,
    instrument: form.instrument,
    scanType: form.scanType,
    ionization: form.ionization,
    declusteringPotential: form.declusteringPotential,
    collisionEnergy: form.collisionEnergy,
    canonicalSmiles: form.canonicalSmiles,
    massTable: massSpectra,
    fragments: form.fragments,
    types: form.types,
    typesText: form.typesText,
  };

  try {
    const url = compound.id ? `/api/compound/${compound.id}/save` : `/api/compound/save`;
    const method = compound.id ? 'PUT' : 'POST';

    const response = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        ...compound,
        types: compound.types.map((t) => t.id),
        fragments: compound.fragments.map((f) => f.id),
      }),
    });

    if (!response.ok) showAlert(`Error: ${response.status}`);

    const savedCompound = await response.json();

    if (pictureFile.value && savedCompound.id) {
      const formData = new FormData();
      formData.append('type', 'picture');
      formData.append('file', pictureFile.value);
      await uploadFile(formData, savedCompound.id);
    }

    if (sdfFile.value && savedCompound.id) {
      const formData = new FormData();
      formData.append('type', 'sdf');
      formData.append('file', sdfFile.value);
      await uploadFile(formData, savedCompound.id);
    }

    if (savedCompound.id) {
      router.push(`/compound/view/${savedCompound.id}`);
    }
  } catch (err: any) {
    showAlert(err.message || 'Saving error');
  }
};

const uploadFile = async (formData: FormData, id: string) => {
  const fileResponse = await fetch(`/api/compound/${id}/upload-file`, {
    method: 'POST',
    body: formData,
  });

  if (!fileResponse.ok) showAlert(`Picture upload failed: ${fileResponse.status}`);
};

const handleSdfUpload = (event: Event) => {
  const files = (event.target as HTMLInputElement).files;
  if (files && files.length > 0) {
    if (!files[0].name.endsWith('.sdf')) {
      showAlert('Only .sdf files are allowed');
      return;
    }
    sdfFile.value = files[0];
  }
};

const handlePictureUpload = (event: Event) => {
  const files = (event.target as HTMLInputElement).files;
  if (files && files.length > 0) {
    if (!['.png', '.jpg', '.jpeg', '.gif'].some((ext) => files[0].name.endsWith(ext))) {
      showAlert('Only graphic formats are allowed');
      return;
    }
    pictureFile.value = files[0];
  }
};

const handleMsUpload = async (event: Event) => {
  const files = (event.target as HTMLInputElement).files;
  if (files && files.length > 0) {
    if (!files[0].name.endsWith('.txt')) {
      showAlert('Only .txt files are allowed');
      return;
    }

    msFile.value = files[0];
    const text = await files[0].text();

    massSpectra.splice(0, massSpectra.length);

    const mzValues: number[] = [];

    text.split('\n').forEach((line) => {
      const [mzStr, intensityStr] = line.trim().split(/\s+/);
      const mz = parseFloat(mzStr);
      const intensity = parseFloat(intensityStr);
      const visible = true;
      if (!isNaN(mz) && !isNaN(intensity)) {
        massSpectra.push({ mz, intensity, visible });
        mzValues.push(mz);
      }
    });

    const matchedFragments = availableFragments.value.filter((f) =>
      mzValues.includes(Number(f.molecularMass))
    );

    const uniqueFragments = [
      ...form.fragments,
      ...matchedFragments.filter((f) => !form.fragments.some((sel) => sel.id === f.id)),
    ];

    form.fragments = uniqueFragments;
  }
};

const updateMassSpectra = (newValue: MassTable[][]) => {
  massSpectra.splice(0, massSpectra.length, ...newValue[0]);
};

// Hoox
onMounted(async () => {
  pageTite.value = router.currentRoute.value.meta.title as string;

  const fragmentsRes = await fetch('/api/compound/fragments');
  if (fragmentsRes.ok) {
    availableFragments.value = await fragmentsRes.json();
  }

  if (props.compoundId) {
    try {
      const response = await fetch(`/api/compound/${props.compoundId}`);
      if (!response.ok) showAlert(`Error: ${response.status}`);
      const data: Compound = await response.json();

      form.id = data.id || 0;
      form.abbr = data.abbr || '';
      form.name = data.name || '';
      form.molecularFormula = data.molecularFormula || '';
      form.compoundId = data.compoundId || '';
      form.instrument = data.instrument || 'QTRAP 3200';
      form.scanType = data.scanType || 'MS2';
      form.ionization = data.ionization || 'ESI (-)';
      form.declusteringPotential = data.declusteringPotential || '';
      form.collisionEnergy = data.collisionEnergy || '';
      form.canonicalSmiles = data.canonicalSmiles || '';
      form.picture = data.picture || '';
      form.sdf = data.sdf || '';
      form.types = data.types || [];

      const massTable = data.massTable || [];
      massTable.map((i) => {
        if (i.visible === undefined) i.visible = true;
      });
      massSpectra.splice(0, massSpectra.length, ...massTable);

      form.fragments = data.fragments || [];
    } catch (err: any) {
      showAlert(err.message || 'Failed to load compound data.');
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

      <div class="mb-4">
        <AtomChip class="pa-0" variant="text" size="x-large">
          <strong>{{ pageTite }}</strong>
        </AtomChip>
        <AtomChip v-if="form.id" class="mx-2" color="primary" size="x-large" :label="true">
          <strong>{{ form.abbr }} ({{ form.name }})</strong>
        </AtomChip>
      </div>
      <v-form>
        <OrganismCompoundFormSection
          :form="form"
          @update:form="(val) => Object.assign(form, val)"
          @sdfUpload="handleSdfUpload"
          @pictureUpload="handlePictureUpload"
          @msUpload="handleMsUpload"
        />
        <v-row>
          <v-col cols="12" md="12">
            <OrganismCompoundMassSpectraSection
              :massSpectra="[massSpectra]"
              :compare="false"
              @update:massSpectra="updateMassSpectra"
            />
          </v-col>
        </v-row>
        <v-row>
          <v-col cols="12" md="6">
            <AtomButton
              v-if="appState.isLoggedIn"
              :block="true"
              :prepend-icon="mdiClose"
              color="error"
              size="large"
              @click="router.push(`/compound/view/${props.compoundId}`)"
              >Cancel
            </AtomButton>
          </v-col>
          <v-col cols="12" md="6">
            <AtomButton
              v-if="appState.isLoggedIn"
              :block="true"
              :prepend-icon="mdiContentSave"
              color="primary"
              size="large"
              @click="saveCompound"
              >Save</AtomButton
            >
          </v-col>
        </v-row>
      </v-form>
    </v-container>
  </v-main>
</template>
