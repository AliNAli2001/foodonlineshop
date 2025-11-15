import { apiClient } from './api';
import { API_ENDPOINTS } from '@/config/api';

export interface OrderItem {
  id: number;
  product_id: number;
  product_name: string;
  quantity: number;
  unit_price: number;
  subtotal: number;
  status: 'normal' | 'returned';
}

export interface Order {
  id: number;
  status: string;
  total_amount: number;
  delivery_method: string;
  delivery_address: string;
  latitude: number | null;
  longitude: number | null;
  created_at: string;
  items: OrderItem[];
  delivery: { id: number; name: string; phone: string } | null;
}

export interface CreateOrderData {
  order_source: string;
  delivery_method: 'delivery' | 'hand_delivered' | 'shipping';
  address_details: string;
  latitude?: number;
  longitude?: number;
  shipping_notes?: string;
  general_notes?: string;
  cart_data: string; // JSON string
}

export const ordersService = {
  async createOrder(data: CreateOrderData) {
    const response = await apiClient.post(API_ENDPOINTS.ORDERS.CREATE, data);
    return response.data;
  },

  async getOrders(params?: { per_page?: number; page?: number }) {
    const response = await apiClient.get(API_ENDPOINTS.ORDERS.LIST, { params });
    return response.data;
  },

  async getOrder(id: number) {
    const response = await apiClient.get(API_ENDPOINTS.ORDERS.SHOW(id));
    return response.data;
  },
};

