import { createRouter, createWebHistory } from 'vue-router';
import type { RouteRecordRaw } from 'vue-router';
import { appState, checkSession } from '@/db/auth.ts';

import PageHome from '@/pages/PageHome.vue';
import PageAbout from '@/pages/PageAbout.vue';
import PageCompoundEdit from '@/pages/PageCompoundEdit.vue';
import PageCompoundView from '@/pages/PageCompoundView.vue';
import PageCompoundsList from '@/pages/PageCompoundsList.vue';
import PageCompoundsCompare from '@/pages/PageCompoundsCompare.vue';
import PageFragmentEdit from '@/pages/PageFragmentEdit.vue';
import PageFragmentView from '@/pages/PageFragmentView.vue';
import PageFragmentsList from '@/pages/PageFragmentsList.vue';
import PageNotFound from '@/pages/PageNotFound.vue';

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    name: 'Home',
    component: PageHome,
    meta: { title: 'Home' },
  },
  {
    path: '/about',
    name: 'About',
    component: PageAbout,
    meta: { title: 'About' },
  },
  {
    path: '/compound/new',
    name: 'CompoundNew',
    component: PageCompoundEdit,
    meta: { title: 'Add New Compound', requiresAuth: true },
  },
  {
    path: '/compound/edit/:id',
    name: 'CompoundEdit',
    component: PageCompoundEdit,
    meta: { title: 'Edit Compound', requiresAuth: true },
    props: true,
  },
  {
    path: '/compound/view/:id',
    name: 'CompoundView',
    component: PageCompoundView,
    meta: { title: 'View Compound' },
    props: true,
  },
  {
    path: '/compound/list',
    name: 'CompoundList',
    component: PageCompoundsList,
    meta: { title: 'List Of Compounds' },
  },
  {
    path: '/compound/compare',
    name: 'CompoundCompare',
    component: PageCompoundsCompare,
    meta: { title: 'Compare Compounds' },
    props: true,
  },
  {
    path: '/fragment/new',
    name: 'FragmentNew',
    component: PageFragmentEdit,
    meta: { title: 'Add New Fragment', requiresAuth: true },
  },
  {
    path: '/fragment/edit/:id',
    name: 'FragmentEdit',
    component: PageFragmentEdit,
    meta: { title: 'Edit Fragment', requiresAuth: true },
    props: true,
  },
  {
    path: '/fragment/view/:id',
    name: 'FragmentView',
    component: PageFragmentView,
    meta: { title: 'View Fragment' },
    props: true,
  },
  {
    path: '/fragment/list',
    name: 'FragmentList',
    component: PageFragmentsList,
    meta: { title: 'List Of Fragments' },
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: PageNotFound,
    meta: { title: '404 - Not Found' },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach(async (to, from, next) => {
  document.title = 'MSTIDE DB 2.0' + (to.meta?.title ? ' - ' + to.meta.title : '');

  if (to.meta.requiresAuth) {
    if (!appState.isLoggedIn) {
      try {
        await checkSession();

        if (appState.isLoggedIn) {
          next();
        } else {
          next('/');
        }
      } catch {
        next('/');
      }
    } else {
      next();
    }
  } else {
    next();
  }
});

export default router;
