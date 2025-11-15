import { create } from 'zustand';

export interface CartItem {
  product_id: number;
  name_en: string;
  name_ar: string;
  price: number;
  quantity: number;
}

export interface CartState {
  items: CartItem[];
  totalPrice: number;
  
  // Actions
  addItem: (item: CartItem) => void;
  removeItem: (productId: number) => void;
  updateQuantity: (productId: number, quantity: number) => void;
  clearCart: () => void;
  calculateTotal: () => number;
}

export const useCartStore = create<CartState>((set, get) => ({
  items: [],
  totalPrice: 0,

  addItem: (item: CartItem) => {
    set((state) => {
      const existingItem = state.items.find((i) => i.product_id === item.product_id);
      
      let newItems;
      if (existingItem) {
        newItems = state.items.map((i) =>
          i.product_id === item.product_id
            ? { ...i, quantity: i.quantity + item.quantity }
            : i
        );
      } else {
        newItems = [...state.items, item];
      }

      const totalPrice = newItems.reduce(
        (sum, i) => sum + i.price * i.quantity,
        0
      );

      return { items: newItems, totalPrice };
    });
  },

  removeItem: (productId: number) => {
    set((state) => {
      const newItems = state.items.filter((i) => i.product_id !== productId);
      const totalPrice = newItems.reduce(
        (sum, i) => sum + i.price * i.quantity,
        0
      );
      return { items: newItems, totalPrice };
    });
  },

  updateQuantity: (productId: number, quantity: number) => {
    set((state) => {
      let newItems;
      if (quantity <= 0) {
        newItems = state.items.filter((i) => i.product_id !== productId);
      } else {
        newItems = state.items.map((i) =>
          i.product_id === productId ? { ...i, quantity } : i
        );
      }

      const totalPrice = newItems.reduce(
        (sum, i) => sum + i.price * i.quantity,
        0
      );

      return { items: newItems, totalPrice };
    });
  },

  clearCart: () => {
    set({ items: [], totalPrice: 0 });
  },

  calculateTotal: () => {
    const state = get();
    return state.items.reduce((sum, item) => sum + item.price * item.quantity, 0);
  },
}));

