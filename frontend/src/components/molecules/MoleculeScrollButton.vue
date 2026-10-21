<script setup lang="ts">
// Core
import { shallowRef, onMounted, onUnmounted } from 'vue';

// Modules
import { useGoTo } from 'vuetify';
import { mdiArrowUp } from '@mdi/js';

// Components
import AtomButton from '@/components/atoms/AtomButton.vue';

// Data
const goTo = useGoTo();
const showScrollBtn = shallowRef(false);

// Methods
const handleScroll = () => {
  showScrollBtn.value = window.scrollY > 200;
};

// Hoox
onMounted(() => {
  window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
  <v-slide-y-reverse-transition>
    <AtomButton
      v-if="showScrollBtn"
      class="position-fixed scroll-btn"
      :icon="mdiArrowUp"
      color="secondary"
      size="x-large"
      @click="goTo(0)"
    >
    </AtomButton>
  </v-slide-y-reverse-transition>
</template>

<style lang="scss" scoped>
.scroll-btn {
  bottom: 72px;
  right: 24px;
  z-index: 1;
}
</style>
