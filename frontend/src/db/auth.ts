// Core
import { reactive, ref } from 'vue';

// Interfaces
import type { User } from '@/interfaces/User';

// Data
export const appState = reactive({
  isLoggedIn: false,
  user: null as User | null,
});

export const loginInput = ref('');
export const passwordInput = ref('');

// Methods
export const checkSession = async () => {
  try {
    const res = await fetch('/api/user/me', {
      credentials: 'include',
    });

    if (!res.ok) throw new Error();

    const data = await res.json();
    if (data.loggedIn) {
      appState.isLoggedIn = true;
      appState.user = { id: data.id, login: data.login };
    } else {
      appState.isLoggedIn = false;
      appState.user = null;
    }
  } catch {
    appState.isLoggedIn = false;
    appState.user = null;
  }
};

export const signIn = async () => {
  try {
    const res = await fetch('/api/user/login', {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        login: loginInput.value,
        password: passwordInput.value,
      }),
    });

    if (!res.ok) throw new Error('Login failed');

    await checkSession();
  } catch {
    alert('Invalid login or password');
    throw new Error('Invalid login or password');
  }
};

export const signOut = async () => {
  try {
    await fetch('/api/user/logout', {
      method: 'POST',
      credentials: 'include',
    });
  } finally {
    appState.user = null;
    appState.isLoggedIn = false;
  }
};
