<script setup lang="ts">
// Core
import { shallowRef, ref, watch } from 'vue';

// Modules
import {
  mdiThemeLightDark,
  mdiInformationVariantCircleOutline,
  mdiMolecule,
  mdiAtom,
  mdiAccount,
} from '@mdi/js';
import { useTheme } from 'vuetify';

// Components
import { appState, loginInput, passwordInput, signIn, signOut } from '@/db/auth.ts';
import AtomTextField from '@/components/atoms/AtomTextField.vue';
import AtomButton from '@/components/atoms/AtomButton.vue';

// Data
const profileMenuVisible = ref(false);
const theme = useTheme();
const drawer = shallowRef(false);
const links = [
  { title: 'Compounds', icon: mdiMolecule, link: '/compound/list' },
  { title: 'Fragments', icon: mdiAtom, link: '/fragment/list' },
];

// Methods
const toggleTheme = () => {
  const newTheme = theme.global.current.value.dark ? 'light' : 'dark';
  theme.change(newTheme);
  localStorage.setItem('theme', newTheme);
};

// Watchers
watch(profileMenuVisible, (val) => {
  if (!val) {
    loginInput.value = '';
    passwordInput.value = '';
  }
});
</script>

<template>
  <v-navigation-drawer v-model="drawer" disable-resize-watcher>
    <v-list nav>
      <v-list-item
        v-for="(item, i) in links"
        link
        :key="i"
        :href="item.link"
        :title="item.title"
        :append-icon="item.icon"
      />
      <v-list-item
        link
        :key="2"
        href="/about"
        title="About"
        :append-icon="mdiInformationVariantCircleOutline"
      />
    </v-list>
  </v-navigation-drawer>

  <v-app-bar class="px-md-4">
    <template #prepend>
      <v-app-bar-nav-icon class="d-flex d-sm-none" @click="drawer = !drawer" />
    </template>

    <a class="d-flex align-center text-decoration-none" href="/">
      <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32">
        <defs>
          <linearGradient id="grad5" x1="0" x2="1" y1="0" y2="1">
            <stop offset="0" stop-color="#14b8a6" />
            <stop offset="1" stop-color="#3b82f6" />
          </linearGradient>
        </defs>
        <circle cx="16" cy="16" r="15" fill="url(#grad5)" />
        <ellipse
          cx="16"
          cy="16"
          rx="12"
          ry="6"
          fill="none"
          stroke="white"
          stroke-width="0.9"
          opacity="0.6"
          transform="rotate(-20 16 16)"
        />
        <circle cx="16" cy="12" r="4" fill="white" />
        <line x1="16" y1="8" x2="16" y2="5" stroke="white" stroke-width="0.9" />
        <circle cx="16" cy="4" r="1.3" fill="white" />
        <line x1="13" y1="14" x2="10" y2="16" stroke="white" stroke-width="0.9" />
        <circle cx="9" cy="16.5" r="1.3" fill="white" />
        <line x1="19" y1="14" x2="22" y2="16" stroke="white" stroke-width="0.9" />
        <circle cx="23" cy="16.5" r="1.3" fill="white" />
        <line x1="9" y1="27" x2="9" y2="21" stroke="white" stroke-width="0.9" />
        <line x1="12.5" y1="27" x2="12.5" y2="19" stroke="white" stroke-width="0.9" />
        <line x1="16" y1="27" x2="16" y2="23" stroke="white" stroke-width="0.9" />
        <line x1="19.5" y1="27" x2="19.5" y2="20" stroke="white" stroke-width="0.9" />
        <line x1="23" y1="27" x2="23" y2="22" stroke="white" stroke-width="0.9" />
      </svg>

      <v-chip variant="text" size="x-large" color="primary">MSTIDE DB 2.0</v-chip>
    </a>

    <template v-if="!$display.xs">
      <v-btn
        v-for="(item, i) in links"
        :key="i"
        :href="item.link"
        :text="item.title"
        :prepend-icon="item.icon"
        class="text-none mx-2"
        size="large"
        slim
      />
    </template>

    <v-spacer class="d-none d-sm-flex" />

    <template #append>
      <v-btn size="large" slim>
        <v-icon :icon="mdiAccount" /> Profile
        <v-menu
          activator="parent"
          origin="top"
          :close-on-content-click="false"
          v-model="profileMenuVisible"
        >
          <v-card min-width="280">
            <template v-if="appState.isLoggedIn">
              <v-card-text>
                <strong>{{ appState.user?.login }}</strong>
              </v-card-text>
              <AtomButton :block="true" color="error" @click="signOut"> Sign out </AtomButton>
            </template>

            <template v-else>
              <v-card-text>
                <AtomTextField
                  label="Login"
                  :model-value="loginInput"
                  @update:modelValue="(val: any) => (loginInput = val)"
                />
                <AtomTextField
                  label="Password"
                  :model-value="passwordInput"
                  type="password"
                  @update:modelValue="(val: any) => (passwordInput = val)"
                />
              </v-card-text>
              <AtomButton :block="true" size="large" color="primary" @click="signIn">
                Sign in
              </AtomButton>
            </template>
          </v-card>
        </v-menu>
      </v-btn>

      <v-btn
        v-if="!$display.xs"
        :icon="mdiThemeLightDark"
        size="large"
        slim
        @click="toggleTheme"
        title="Toggle theme"
      />
      <v-btn
        v-if="!$display.xs"
        :icon="mdiInformationVariantCircleOutline"
        size="large"
        slim
        href="/about"
        title="About"
      />
    </template>
  </v-app-bar>
</template>
