import { apiClient } from './api';
import { API_ENDPOINTS } from '@/config/api';

export interface Product {
  id: number;
  name_ar: string;
  name_en: string;
  description_ar: string;
  description_en: string;
  price: number;
  max_order_item: number;
  featured: boolean;
  total_stock: number;
  available_stock: number;
  images: Array<{ id: number; url: string }>;
  categories: Array<{ id: number; name_ar: string; name_en: string }>;
}

export interface ProductsListParams {
  search?: string;
  categories?: string;
  featured?: boolean;
  lang?: 'ar' | 'en';
  per_page?: number;
  page?: number;
}

export const productsService = {
  async getProducts(params?: ProductsListParams) {
    const response = await apiClient.get(API_ENDPOINTS.PRODUCTS.LIST, { params });
    
   
    return response.data;
  },

  async getProduct(id: number, lang: 'ar' | 'en' = 'en') {
    const response = await apiClient.get(API_ENDPOINTS.PRODUCTS.SHOW(id), {
      params: { lang },
    });
    return response.data;
  },

  async getFeaturedProducts(lang: 'ar' | 'en' = 'en') {
    const response = await apiClient.get(API_ENDPOINTS.PRODUCTS.FEATURED, {
      params: { lang },
    });
    return response.data;
  },

  async getProductsByCategory(categoryId: number, lang: 'ar' | 'en' = 'en') {
    const response = await apiClient.get(
      API_ENDPOINTS.PRODUCTS.BY_CATEGORY(categoryId),
      { params: { lang } }
    );
    return response.data;
  },
};

