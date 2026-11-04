<script setup lang="ts">
// Core
import { useRoute } from 'vue-router';

// Components
import OrganismHeader from '@/components/organisms/OrganismHeader.vue';
import OrganismCompoundEdit from '@/components/organisms/OrganismCompoundEdit.vue';

// Data
const route = useRoute();
</script>

<template>
  <v-layout>
    <OrganismHeader />
    <OrganismCompoundEdit :compoundId="route.params.id as string" />
  </v-layout>
</template>
