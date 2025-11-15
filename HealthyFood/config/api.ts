// API Configuration
export const API_CONFIG = {
  BASE_URL: 'http://192.168.1.14:8000/api',
  TIMEOUT: 10000,
  HEADERS: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
};

// API Endpoints
export const API_ENDPOINTS = {
  // Auth
  AUTH: {
    REGISTER: '/auth/register',
    LOGIN: '/auth/login',
    LOGOUT: '/auth/logout',
    VERIFY_EMAIL: '/auth/verify-email',
    VERIFY_PHONE: '/auth/verify-phone',
  },
  // Products
  PRODUCTS: {
    LIST: '/products',
    SHOW: (id: number) => `/products/${id}`,
    FEATURED: '/products/featured',
    BY_CATEGORY: (categoryId: number) => `/products/category/${categoryId}`,
  },
  // Categories
  CATEGORIES: {
    LIST: '/categories',
    SHOW: (id: number) => `/categories/${id}`,
    FEATURED: '/categories/featured',
  },
  // Orders
  ORDERS: {
    CREATE: '/orders',
    LIST: '/orders',
    SHOW: (id: number) => `/orders/${id}`,
  },
  // Profile
  PROFILE: {
    GET: '/profile',
    UPDATE: '/profile',
  },
};

