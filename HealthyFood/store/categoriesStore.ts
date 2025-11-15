import { create } from 'zustand';
import { categoriesService, Category, CategoriesListParams } from '@/services/categories';

export interface CategoriesState {
  categories: Category[];
  featuredCategories: Category[];
  currentCategory: Category | null;
  loading: boolean;
  error: string | null;
  
  // Actions
  fetchCategories: (params?: CategoriesListParams) => Promise<void>;
  fetchFeaturedCategories: (lang?: 'ar' | 'en') => Promise<void>;
  fetchCategory: (id: number, lang?: 'ar' | 'en') => Promise<void>;
  clearError: () => void;
}

export const useCategoriesStore = create<CategoriesState>((set) => ({
  categories: [],
  featuredCategories: [],
  currentCategory: null,
  loading: false,
  error: null,

  fetchCategories: async (params?: CategoriesListParams) => {
    set({ loading: true, error: null });
    try {
      const response = await categoriesService.getCategories(params);
      set({
        categories: response.data || [],
        loading: false,
      });
    } catch (error: any) {
      set({
        error: error.response?.data?.message || 'Failed to fetch categories',
        loading: false,
      });
    }
  },

  fetchFeaturedCategories: async (lang = 'en') => {
    set({ loading: true, error: null });
    try {
      const response = await categoriesService.getFeaturedCategories(lang);
      set({
        featuredCategories: response.data || [],
        loading: false,
      });
    } catch (error: any) {
      set({
        error: error.response?.data?.message || 'Failed to fetch featured categories',
        loading: false,
      });
    }
  },

  fetchCategory: async (id: number, lang = 'en') => {
    set({ loading: true, error: null });
    try {
      const response = await categoriesService.getCategory(id, lang);
      set({
        currentCategory: response.data,
        loading: false,
      });
    } catch (error: any) {
      set({
        error: error.response?.data?.message || 'Failed to fetch category',
        loading: false,
      });
    }
  },

  clearError: () => set({ error: null }),
}));

