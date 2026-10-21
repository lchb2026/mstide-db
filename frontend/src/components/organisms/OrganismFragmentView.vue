<script setup lang="ts">
// Core
import { ref, reactive, onMounted } from 'vue';

// Modules
import { useRouter } from 'vue-router';
import { mdiPencil, mdiAlert, mdiTrashCan } from '@mdi/js';

// Components
import { appState } from '@/db/auth.ts';
import AtomSnackbar from '@/components/atoms/AtomSnackbar.vue';
import AtomChip from '@/components/atoms/AtomChip.vue';
import AtomButton from '@/components/atoms/AtomButton.vue';

// Interfaces
import type { Fragment } from '@/interfaces/Fragment';

// Props
const props = defineProps<{
  fragmentId?: number | string;
}>();

// Router
const router = useRouter();

// Data
const deleteDialog = ref(false);
const showSnackbar = ref(false);
const snackbarMessage = ref('');

const form = reactive<Fragment>({
  id: 0,
  name: '',
  canonicalSmiles: '',
  molecularMass: 0,
  compounds: [],
  picture: '',
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
  if (!props.fragmentId) return;

  try {
    const response = await fetch(`/api/fragment/${props.fragmentId}`, {
      method: 'DELETE',
    });

    if (!response.ok) {
      showAlert(`Failed to delete fragment: ${response.status}`);
    }

    router.push('/fragment/list');
  } catch (err: any) {
    showAlert(err.message || 'Error deleting fragment');
  } finally {
    deleteDialog.value = false;
  }
};

const cancelDelete = () => {
  deleteDialog.value = false;
};

// Hooks
onMounted(async () => {
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

      <v-dialog v-model="deleteDialog" max-width="500">
        <v-card>
          <v-card-title>
            <v-icon :icon="mdiAlert" color="error"></v-icon> Confirm Deletion
          </v-card-title>
          <v-card-text>
            Are you sure you want to delete fragment <strong>{{ form.name }}</strong
            >?
          </v-card-text>
        </v-card>
        <AtomButton color="secondary" size="large" @click="cancelDelete">Cancel</AtomButton>
        <AtomButton color="error" size="large" @click="confirmDelete">Delete</AtomButton>
      </v-dialog>

      <div class="mb-4 d-flex justify-space-between align-center">
        <div>
          <AtomChip class="pa-0" variant="text" size="x-large">
            <strong>View Fragment</strong>
          </AtomChip>
          <AtomChip class="mx-2" color="primary" size="x-large" :label="true">
            <strong>{{ form.name }}</strong>
          </AtomChip>
          <AtomButton
            v-if="appState.isLoggedIn"
            :prepend-icon="mdiPencil"
            color="primary"
            @click="router.push(`/fragment/edit/${props.fragmentId}`)"
          >
            Edit
          </AtomButton>
        </div>
        <div>
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
              <v-img :src="form.picture" alt="Fragment picture" height="200" />
            </p>
          </v-col>
          <v-col cols="12" md="6">
            <p>
              <AtomChip variant="text" size="x-large">
                <strong><i>m/z:</i></strong>
              </AtomChip>
              <AtomChip class="mx-2" color="info" :label="true">
                <strong>{{ form.molecularMass }}</strong>
              </AtomChip>
            </p>
          </v-col>
        </v-row>
        <v-row>
          <v-col class="py-0">
            <p>
              <AtomChip class="px-0" variant="text" size="x-large">
                <strong>Compounds:</strong>
              </AtomChip>
            </p>
          </v-col>
        </v-row>
        <v-row>
          <v-col
            class="pa-2"
            v-for="compound in form.compounds"
            :key="compound.id"
            cols="12"
            sm="6"
            md="4"
            lg="3"
          >
            <v-card
              class="d-flex flex-column align-center pa-3"
              :href="`/compound/view/${compound.id}`"
              target="_blank"
            >
              <h3 class="mb-1">
                <b>{{ compound.abbr }}</b>
              </h3>
              <v-img
                :src="compound.picture"
                alt="Fragment picture"
                width="50%"
                height="150"
                class="mb-2"
              />
              <h3>ID: {{ compound.compoundId }}</h3>
            </v-card>
          </v-col>
        </v-row>
      </v-form>
    </v-container>
  </v-main>
</template>
