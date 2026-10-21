<script setup lang="ts">
// Core
import { useRoute } from 'vue-router';

// Components
import OrganismHeader from '@/components/organisms/OrganismHeader.vue';
import OrganismFragmentEdit from '@/components/organisms/OrganismFragmentEdit.vue';

// Data
const route = useRoute();
</script>

<template>
  <v-layout>
    <OrganismHeader />
    <OrganismFragmentEdit :fragmentId="route.params.id as string" />
  </v-layout>
</template>
