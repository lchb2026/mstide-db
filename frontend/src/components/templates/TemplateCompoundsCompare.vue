<script setup lang="ts">
// Core
import { useRoute } from 'vue-router';

// Components
import OrganismHeader from '@/components/organisms/OrganismHeader.vue';
import OrganismCompoundsCompare from '@/components/organisms/OrganismCompoundsCompare.vue';

// Data
const route = useRoute();
</script>

<template>
  <v-layout>
    <OrganismHeader />
    <OrganismCompoundsCompare :compoundIds="route.query.ids as string" />
  </v-layout>
</template>
