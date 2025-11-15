import { create } from 'zustand';
import { productsService, Product, ProductsListParams } from '@/services/products';

export interface ProductsState {
  products: Product[];
  featuredProducts: Product[];
  currentProduct: Product | null;
  loading: boolean;
  error: string | null;
  
  // Actions
  fetchProducts: (params?: ProductsListParams) => Promise<void>;
  fetchFeaturedProducts: (lang?: 'ar' | 'en') => Promise<void>;
  fetchProduct: (id: number, lang?: 'ar' | 'en') => Promise<void>;
  fetchProductsByCategory: (categoryId: number, lang?: 'ar' | 'en') => Promise<void>;
  clearError: () => void;
}

export const useProductsStore = create<ProductsState>((set) => ({
  products: [],
  featuredProducts: [],
  currentProduct: null,
  loading: false,
  error: null,

  fetchProducts: async (params?: ProductsListParams) => {
    set({ loading: true, error: null });
    try {
      const response = await productsService.getProducts(params);
      set({
        products: response.data || [],
        loading: false,
      });
    } catch (error: any) {
      set({
        error: error.response?.data?.message || 'Failed to fetch products',
        loading: false,
      });
    }
  },

  fetchFeaturedProducts: async (lang = 'en') => {
    set({ loading: true, error: null });
    try {
      const response = await productsService.getFeaturedProducts(lang);
      set({
        featuredProducts: response.data || [],
        loading: false,
      });
    } catch (error: any) {
      set({
        error: error.response?.data?.message || 'Failed to fetch featured products',
        loading: false,
      });
    }
  },

  fetchProduct: async (id: number, lang = 'en') => {
    set({ loading: true, error: null });
    try {
      const response = await productsService.getProduct(id, lang);
      set({
        currentProduct: response.data,
        loading: false,
      });
    } catch (error: any) {
      set({
        error: error.response?.data?.message || 'Failed to fetch product',
        loading: false,
      });
    }
  },

  fetchProductsByCategory: async (categoryId: number, lang = 'en') => {
    set({ loading: true, error: null });
    try {
      const response = await productsService.getProductsByCategory(categoryId, lang);
      set({
        products: response.data || [],
        loading: false,
      });
    } catch (error: any) {
      set({
        error: error.response?.data?.message || 'Failed to fetch products',
        loading: false,
      });
    }
  },

  clearError: () => set({ error: null }),
}));

