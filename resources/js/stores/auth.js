import { defineStore } from 'pinia';
import http, { setAuthToken } from '../services/http';

const STORAGE_KEY = 'esteticaerp_token';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem(STORAGE_KEY) ?? '',
    user: null,
    initialized: false,
    loading: false,
    error: null,
  }),
  getters: {
    isAuthenticated: (state) => Boolean(state.token),
  },
  actions: {
    async bootstrap() {
      if (this.initialized) {
        return;
      }

      if (this.token) {
        setAuthToken(this.token);
        try {
          await this.fetchUser();
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
        this.setToken(data.access_token);
        this.user = data.user;
        return true;
      } catch (error) {
        this.error = error.response?.data?.message ?? 'Falha na autenticação';
        this.clear();
        return false;
      } finally {
        this.loading = false;
      }
    },
    async fetchUser() {
      const { data } = await http.get('/auth/me');
      this.user = data;
      return data;
    },
    async logout() {
      try {
        await http.post('/auth/logout');
      } catch (error) {
        // ignore
      } finally {
        this.clear();
      }
    },
    setToken(token) {
      this.token = token;
      if (token) {
        localStorage.setItem(STORAGE_KEY, token);
      }
      setAuthToken(token);
    },
    clear() {
      localStorage.removeItem(STORAGE_KEY);
      this.token = '';
      this.user = null;
      setAuthToken(null);
    },
  },
});
