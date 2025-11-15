import axios, { AxiosInstance, AxiosError } from 'axios';
import { API_CONFIG } from '@/config/api';
import * as SecureStore from 'expo-secure-store';

class ApiClient {
  private client: AxiosInstance;
  private token: string | null = null;

  constructor() {
    this.client = axios.create({
      baseURL: API_CONFIG.BASE_URL,
      timeout: API_CONFIG.TIMEOUT,
      headers: API_CONFIG.HEADERS,
    });

    // Add request interceptor
    this.client.interceptors.request.use(
      async (config) => {
        if (this.token) {
          config.headers.Authorization = `Bearer ${this.token}`;
        }
        return config;
      },
      (error) => Promise.reject(error)
    );

    // Add response interceptor
    this.client.interceptors.response.use(
      (response) => response,
      (error: AxiosError) => {
        if (error.response?.status === 401) {
          // Handle unauthorized - clear token and redirect to login
          this.clearToken();
        }
        return Promise.reject(error);
      }
    );
  }

  async setToken(token: string) {
    this.token = token;
    try {
      await SecureStore.setItemAsync('auth_token', token);
    } catch (error) {
      console.error('Failed to store token:', error);
    }
  }

  async getToken(): Promise<string | null> {
    if (!this.token) {
      try {
        this.token = await SecureStore.getItemAsync('auth_token');
      } catch (error) {
        console.error('Failed to retrieve token:', error);
      }
    }
    return this.token;
  }

  clearToken() {
    this.token = null;
    try {
      SecureStore.deleteItemAsync('auth_token');
    } catch (error) {
      console.error('Failed to clear token:', error);
    }
  }

  get(url: string, config?: any) {
    return this.client.get(url, config);
  }

  post(url: string, data?: any, config?: any) {
    return this.client.post(url, data, config);
  }

  put(url: string, data?: any, config?: any) {
    return this.client.put(url, data, config);
  }

  delete(url: string, config?: any) {
    return this.client.delete(url, config);
  }
}

export const apiClient = new ApiClient();

