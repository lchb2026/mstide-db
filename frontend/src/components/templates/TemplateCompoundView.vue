<script setup lang="ts">
// Core
import { useRoute } from 'vue-router';

// Components
import OrganismHeader from '@/components/organisms/OrganismHeader.vue';
import OrganismCompoundView from '@/components/organisms/OrganismCompoundView.vue';

// Data
const route = useRoute();
</script>

<template>
  <v-layout>
    <OrganismHeader />
    <OrganismCompoundView :compoundId="route.params.id as string" />
  </v-layout>
</template>
