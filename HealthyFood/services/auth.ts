import { apiClient } from './api';
import { API_ENDPOINTS } from '@/config/api';

export interface RegisterData {
  first_name: string;
  last_name: string;
  email: string;
  phone: string;
  password: string;
  password_confirmation: string;
  language_preference: 'ar' | 'en';
}

export interface LoginData {
  email: string;
  password: string;
  device_name?: string;
}

export interface VerificationData {
  code: string;
}

export const authService = {
  async register(data: RegisterData) {
    const response = await apiClient.post(API_ENDPOINTS.AUTH.REGISTER, data);
    if (response.data.token) {
      await apiClient.setToken(response.data.token);
    }
    return response.data;
  },

  async login(data: LoginData) {
    const response = await apiClient.post(API_ENDPOINTS.AUTH.LOGIN, {
      ...data,
      device_name: data.device_name || 'mobile',
    });
    if (response.data.token) {
      await apiClient.setToken(response.data.token);
    }
    return response.data;
  },

  async logout() {
    try {
      await apiClient.post(API_ENDPOINTS.AUTH.LOGOUT);
    } finally {
      apiClient.clearToken();
    }
  },

  async verifyEmail(data: VerificationData) {
    const response = await apiClient.post(API_ENDPOINTS.AUTH.VERIFY_EMAIL, data);
    return response.data;
  },

  async verifyPhone(data: VerificationData) {
    const response = await apiClient.post(API_ENDPOINTS.AUTH.VERIFY_PHONE, data);
    return response.data;
  },

  async getToken() {
    return await apiClient.getToken();
  },

  clearToken() {
    apiClient.clearToken();
  },
};

