<script setup lang="ts">
// Core
import { useRoute } from 'vue-router';

// Components
import OrganismHeader from '@/components/organisms/OrganismHeader.vue';
import OrganismFragmentView from '@/components/organisms/OrganismFragmentView.vue';

// Data
const route = useRoute();
</script>

<template>
  <v-layout>
    <OrganismHeader />
    <OrganismFragmentView :fragmentId="route.params.id as string" />
  </v-layout>
</template>
