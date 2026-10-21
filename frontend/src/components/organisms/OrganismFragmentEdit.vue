<script setup lang="ts">
// Core
import { ref, reactive, onMounted } from 'vue';

// Modules
import { useRouter } from 'vue-router';
import { mdiFileImage, mdiContentSave, mdiClose } from '@mdi/js';

// Interfaces
import type { Fragment } from '@/interfaces/Fragment';
import type { Compound } from '@/interfaces/Compound';

// Components
import { appState } from '@/db/auth.ts';
import AtomSnackbar from '@/components/atoms/AtomSnackbar.vue';
import AtomChip from '@/components/atoms/AtomChip.vue';
import AtomFileInput from '@/components/atoms/AtomFileInput.vue';
import AtomTextField from '@/components/atoms/AtomTextField.vue';
import AtomButton from '@/components/atoms/AtomButton.vue';

// Props
const props = defineProps<{
  fragmentId?: string;
}>();

// Router
const router = useRouter();

// Data
const pageTite = ref<string>('');

const form = reactive<Fragment>({
  id: 0,
  name: '',
  canonicalSmiles: '',
  molecularMass: 0,
  compounds: [],
  picture: '',
});

const pictureFile = ref<File | null>(null);
const allCompounds = ref<Compound[]>([]);

const showSnackbar = ref(false);
const snackbarMessage = ref('');

// Methods
const showAlert = (msg: string) => {
  snackbarMessage.value = msg;
  showSnackbar.value = true;
};

const handleUpdate = (key: keyof Fragment, value: any) => {
  (form as any)[key] = value;
};

const saveFragment = async (): Promise<void> => {
  const fragment: Fragment = {
    id: form.id,
    name: form.name,
    canonicalSmiles: form.canonicalSmiles,
    molecularMass: form.molecularMass,
    compounds: form.compounds,
  };

  try {
    const url = fragment.id ? `/api/fragment/${fragment.id}/save` : `/api/fragment/save`;
    const method = fragment.id ? 'PUT' : 'POST';

    const response = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        ...fragment,
        compounds: fragment.compounds.map((c) => c.id),
      }),
    });

    if (!response.ok) showAlert(`Error: ${response.status}`);

    const savedFragment = await response.json();

    if (pictureFile.value && savedFragment.id) {
      const formData = new FormData();
      formData.append('picture', pictureFile.value);

      const picResponse = await fetch(`/api/fragment/${savedFragment.id}/upload-picture`, {
        method: 'POST',
        body: formData,
      });

      if (!picResponse.ok) showAlert(`Picture upload failed: ${picResponse.status}`);
    }

    if (savedFragment.id) {
      router.push(`/fragment/view/${savedFragment.id}`);
    }
  } catch (err: any) {
    showAlert(err.message || 'Saving error');
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

// Hooks
onMounted(async () => {
  pageTite.value = router.currentRoute.value.meta.title as string;

  const compoundRes = await fetch('/api/fragment/compounds');
  if (compoundRes.ok) {
    allCompounds.value = await compoundRes.json();
  }

  if (props.fragmentId) {
    try {
      const response = await fetch(`/api/fragment/${props.fragmentId}`);
      if (!response.ok) showAlert(`Error: ${response.status}`);
      const data: Fragment = await response.json();
      Object.assign(form, data);
    } catch (err) {
      showAlert('Failed to load fragment data.');
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
          <strong>{{ form.name }}</strong>
        </AtomChip>
      </div>
      <v-form>
        <v-row>
          <v-col cols="12" md="6">
            <AtomFileInput
              label="Picture"
              accept="image/*"
              :prepend-icon="mdiFileImage"
              @change="handlePictureUpload"
            />
            <v-img :src="form.picture" alt="Fragment picture" height="200" />
          </v-col>
          <v-col cols="12" md="6">
            <AtomTextField
              label="Abbreviation"
              :model-value="form.name"
              @update:modelValue="(val: any) => handleUpdate('name', val)"
            />
            <AtomTextField
              label="m/z"
              :model-value="form.molecularMass"
              @update:modelValue="(val: number) => handleUpdate('molecularMass', val)"
            />
            <v-autocomplete
              v-model="form.compounds"
              :items="allCompounds"
              label="Compounds"
              item-title="abbr"
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
            >
              <template v-slot:chip="{ props, item }">
                <v-chip v-bind="props" color="info" variant="outlined">
                  <strong>
                    {{ item.raw.abbr }}
                  </strong>
                  &nbsp;
                  <span>(ID:&nbsp;{{ item.raw.compoundId }})</span>
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
                    >{{ item.raw.name }} (ID:&nbsp;{{ item.raw.compoundId }})
                  </v-list-item-subtitle>
                </v-list-item>
              </template>
            </v-autocomplete>
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
              @click="router.push(`/fragment/view/${props.fragmentId}`)"
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
              @click="saveFragment"
            >
              Save
            </AtomButton>
          </v-col>
        </v-row>
      </v-form>
    </v-container>
  </v-main>
</template>
