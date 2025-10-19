import { defineStore } from 'pinia';
import http, { setAuthToken } from '../services/http';

const STORAGE_TOKEN_KEY = 'esteticaerp_client_token';
const STORAGE_CLIENT_KEY = 'esteticaerp_client';

function loadStoredClient() {
  try {
    const raw = localStorage.getItem(STORAGE_CLIENT_KEY);
    return raw ? JSON.parse(raw) : null;
  } catch (error) {
    console.error('Failed to parse stored client session', error);
    return null;
  }
}

export const useClientAuthStore = defineStore('client-auth', {
  state: () => ({
    token: localStorage.getItem(STORAGE_TOKEN_KEY) ?? '',
    client: loadStoredClient(),
    initialized: false,
    loading: false,
    error: null,
  }),
  getters: {
    isAuthenticated: (state) => Boolean(state.token),
    firstName: (state) => {
      const name = state.client?.full_name ?? '';
      return name.split(' ')[0] ?? '';
    },
  },
  actions: {
    async bootstrap() {
      if (this.initialized) {
        return;
      }

      if (this.token) {
        setAuthToken(this.token);
        try {
          await this.fetchClient();
        } catch (error) {
          this.clear();
        }
      }

      this.initialized = true;
    },
    async login(credentials) {
      this.loading = true;
      this.error = null;
      try {
        const { data } = await http.post('/auth/login', credentials);
        this.setSession(data.access_token, data.client);
        return true;
      } catch (error) {
        this.error = error.response?.data?.message ?? 'Falha ao autenticar. Verifique os dados e tente novamente.';
        this.clear();
        return false;
      } finally {
        this.loading = false;
      }
    },
    async fetchClient() {
      const { data } = await http.get('/auth/me');
      this.setSession(this.token, data.client);
      return data.client;
    },
    async logout() {
      try {
        await http.post('/auth/logout');
      } catch (error) {
        // Ignorar erro de logout
      } finally {
        this.clear();
      }
    },
    setSession(token, client) {
      this.token = token ?? '';
      this.client = client ?? null;

      if (this.token) {
        localStorage.setItem(STORAGE_TOKEN_KEY, this.token);
        setAuthToken(this.token);
      } else {
        localStorage.removeItem(STORAGE_TOKEN_KEY);
        setAuthToken(null);
      }

      if (this.client) {
        localStorage.setItem(STORAGE_CLIENT_KEY, JSON.stringify(this.client));
      } else {
        localStorage.removeItem(STORAGE_CLIENT_KEY);
      }
    },
    clear() {
      this.setSession(null, null);
      this.error = null;
    },
  },
});
