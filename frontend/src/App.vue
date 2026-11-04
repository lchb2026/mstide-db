<script setup lang="ts">
// Core
import { onMounted } from 'vue';

// Modules
import { RouterView } from 'vue-router';

// Components
import { checkSession } from '@/db/auth.ts';

// Hoox
onMounted(async () => await checkSession());
</script>

<template>
  <v-app>
    <RouterView />
  </v-app>
</template>
