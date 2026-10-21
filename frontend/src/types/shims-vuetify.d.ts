import 'vue';
import type { DisplayInstance } from 'vuetify';

declare module '@vue/runtime-core' {
  interface ComponentCustomProperties {
    $display: DisplayInstance;
  }
}
