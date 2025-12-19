import { apiClient } from './api';
import { API_ENDPOINTS } from '@/config/api';

export interface ClientProfile {
  id: number;
  first_name: string;
  last_name: string;
  email: string;
  phone: string;
  email_verified: boolean;
  phone_verified: boolean;
  address_details: string | null;
  language_preference: 'ar' | 'en';
  promo_consent: boolean;
  created_at: string;
}

export interface UpdateProfileData {
  first_name?: string;
  last_name?: string;
  phone?: string;
  address_details?: string;
  language_preference?: 'ar' | 'en';
  promo_consent?: boolean;
}

export const profileService = {
  async getProfile() {
    const response = await apiClient.get(API_ENDPOINTS.PROFILE.GET);
    console.log(response.data);
    return response.data;
  },

  async updateProfile(data: UpdateProfileData) {
    const response = await apiClient.put(API_ENDPOINTS.PROFILE.UPDATE, data);
    return response.data;
  },
};

