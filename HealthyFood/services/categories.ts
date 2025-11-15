import { apiClient } from './api';
import { API_ENDPOINTS } from '@/config/api';

export interface Category {
  id: number;
  name_ar: string;
  name_en: string;
  type: string;
  featured: boolean;
  image: string | null;
}

export interface CategoriesListParams {
  lang?: 'ar' | 'en';
  per_page?: number;
  page?: number;
}

export const categoriesService = {
  async getCategories(params?: CategoriesListParams) {
    const response = await apiClient.get(API_ENDPOINTS.CATEGORIES.LIST, { params });
    return response.data;
  },

  async getCategory(id: number, lang: 'ar' | 'en' = 'en') {
    const response = await apiClient.get(API_ENDPOINTS.CATEGORIES.SHOW(id), {
      params: { lang },
    });
    return response.data;
  },

  async getFeaturedCategories(lang: 'ar' | 'en' = 'en') {
    const response = await apiClient.get(API_ENDPOINTS.CATEGORIES.FEATURED, {
      params: { lang },
    });
    return response.data;
  },
};

