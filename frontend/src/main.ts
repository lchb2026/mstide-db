import { createApp } from 'vue';
import App from '@/App.vue';
import axios from 'axios';
import VueAxios from 'vue-axios';
import router from '@/router';

import { createVuetify } from 'vuetify';
import 'vuetify/styles';
import { aliases, mdi } from 'vuetify/iconsets/mdi-svg';

import '@/assets/scss/main.scss';

const savedTheme = localStorage.getItem('theme') || 'light';
const vuetify = createVuetify({
  theme: {
    defaultTheme: savedTheme,
  },
  icons: {
    defaultSet: 'mdi',
    aliases,
    sets: { mdi },
  },
});

const app = createApp(App);

app.use(vuetify);
app.mixin({
  computed: {
    $display() {
      return this.$vuetify.display;
    },
  },
});

app.use(VueAxios, axios);
app.use(router);
app.mount('#app');
