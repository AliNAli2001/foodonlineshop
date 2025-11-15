import { create } from 'zustand';
import { authService, RegisterData, LoginData } from '@/services/auth';

export interface AuthState {
  isAuthenticated: boolean;
  user: any | null;
  loading: boolean;
  error: string | null;
  
  // Actions
  register: (data: RegisterData) => Promise<void>;
  login: (data: LoginData) => Promise<void>;
  logout: () => Promise<void>;
  verifyEmail: (code: string) => Promise<void>;
  verifyPhone: (code: string) => Promise<void>;
  checkAuth: () => Promise<void>;
  clearError: () => void;
}

export const useAuthStore = create<AuthState>((set) => ({
  isAuthenticated: false,
  user: null,
  loading: false,
  error: null,

  register: async (data: RegisterData) => {
    set({ loading: true, error: null });
    try {
      const response = await authService.register(data);
      set({
        isAuthenticated: true,
        user: response.client,
        loading: false,
      });
    } catch (error: any) {
      set({
        error: error.response?.data?.message || 'Registration failed',
        loading: false,
      });
      throw error;
    }
  },

  login: async (data: LoginData) => {
    set({ loading: true, error: null });
    try {
      const response = await authService.login(data);
      set({
        isAuthenticated: true,
        user: response.client,
        loading: false,
      });
    } catch (error: any) {
      set({
        error: error.response?.data?.message || 'Login failed',
        loading: false,
      });
      throw error;
    }
  },

  logout: async () => {
    set({ loading: true });
    try {
      await authService.logout();
      set({
        isAuthenticated: false,
        user: null,
        loading: false,
      });
    } catch (error: any) {
      set({
        error: error.response?.data?.message || 'Logout failed',
        loading: false,
      });
    }
  },

  verifyEmail: async (code: string) => {
    set({ loading: true, error: null });
    try {
      await authService.verifyEmail({ code });
      set({ loading: false });
    } catch (error: any) {
      set({
        error: error.response?.data?.message || 'Email verification failed',
        loading: false,
      });
      throw error;
    }
  },

  verifyPhone: async (code: string) => {
    set({ loading: true, error: null });
    try {
      await authService.verifyPhone({ code });
      set({ loading: false });
    } catch (error: any) {
      set({
        error: error.response?.data?.message || 'Phone verification failed',
        loading: false,
      });
      throw error;
    }
  },

  checkAuth: async () => {
    const token = await authService.getToken();
    set({ isAuthenticated: !!token });
  },

  clearError: () => set({ error: null }),
}));

