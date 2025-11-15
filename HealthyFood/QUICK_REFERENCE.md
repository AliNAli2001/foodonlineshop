# HealthyFood Expo App - Quick Reference Guide

## 🚀 Quick Start (3 Steps)

```bash
# 1. Install dependencies
cd HealthyFood && npm install

# 2. Update API URL in config/api.ts
# Change BASE_URL to your backend IP

# 3. Start development server
npm start
```

Then scan QR code with Expo Go app on your phone!

---

## 📱 Screen Navigation Map

```
Login/Register
    ↓
Home (Featured & All Products)
├── Product Details [id]
├── Categories
│   └── Category Products [id]
├── Shopping Cart
│   └── Checkout
│       └── Orders List
│           └── Order Details [id]
└── Profile
    └── My Orders
        └── Order Details [id]
```

---

## 🔑 Key Files

| File | Purpose |
|------|---------|
| `config/api.ts` | API endpoints & base URL |
| `services/api.ts` | HTTP client with auth |
| `store/authStore.ts` | User authentication state |
| `store/cartStore.ts` | Shopping cart state |
| `app/_layout.tsx` | Root navigation |
| `app/(tabs)/_layout.tsx` | Tab navigation |

---

## 🎯 Common Tasks

### Add Product to Cart
```typescript
const { addItem } = useCartStore();
addItem({
  product_id: 1,
  name_en: "Product Name",
  name_ar: "اسم المنتج",
  price: 29.99,
  quantity: 1,
});
```

### Fetch Products
```typescript
const { products, fetchProducts } = useProductsStore();
useEffect(() => {
  fetchProducts();
}, []);
```

### Create Order
```typescript
const cartData = JSON.stringify({
  1: { quantity: 2, price: 29.99 },
  2: { quantity: 1, price: 19.99 },
});

await ordersService.createOrder({
  order_source: 'inside_city',
  delivery_method: 'delivery',
  address_details: '123 Main St',
  cart_data: cartData,
});
```

### Get User Location
```typescript
import * as Location from 'expo-location';

const location = await Location.getCurrentPositionAsync({});
const { latitude, longitude } = location.coords;
```

---

## 🎨 Color Scheme

| Color | Usage | Hex |
|-------|-------|-----|
| Green | Primary/Success | #2ecc71 |
| Blue | Secondary/Info | #3498db |
| Red | Danger/Error | #e74c3c |
| Orange | Warning/Pending | #f39c12 |
| Purple | Shipped | #9b59b6 |
| Gray | Disabled/Inactive | #95a5a6 |

---

## 📊 API Endpoints Summary

### Auth
- `POST /auth/register` - Register user
- `POST /auth/login` - Login user
- `POST /auth/logout` - Logout user
- `POST /auth/verify-email` - Verify email
- `POST /auth/verify-phone` - Verify phone

### Products
- `GET /products` - List all products
- `GET /products/{id}` - Get product details
- `GET /products?category={id}` - Filter by category

### Categories
- `GET /categories` - List all categories
- `GET /categories/{id}` - Get category details

### Orders
- `POST /orders` - Create order
- `GET /orders` - List user orders
- `GET /orders/{id}` - Get order details

### Profile
- `GET /profile` - Get user profile
- `PUT /profile` - Update profile

---

## 🔐 Authentication Flow

1. User registers with email/phone
2. Email verification code sent
3. User verifies email
4. Phone verification code sent
5. User verifies phone
6. User can now login
7. Token stored in secure storage
8. Token sent with every API request
9. Token auto-refreshed on expiry

---

## 📦 State Management Pattern

```typescript
// Create store
export const useMyStore = create((set) => ({
  data: [],
  loading: false,
  
  fetchData: async () => {
    set({ loading: true });
    try {
      const response = await api.get('/endpoint');
      set({ data: response.data });
    } finally {
      set({ loading: false });
    }
  },
}));

// Use in component
const { data, loading, fetchData } = useMyStore();
useEffect(() => {
  fetchData();
}, []);
```

---

## 🐛 Debug Tips

### Check Network Requests
```bash
npm start
# Press 'd' to open debugger
# Check Network tab
```

### View State
```typescript
// In any component
const state = useAuthStore.getState();
console.log(state);
```

### Clear Cache
```bash
expo start --clear
```

### Check Logs
```bash
# Terminal shows all console.log output
# Check for errors and warnings
```

---

## 📱 Testing on Physical Device

1. Install Expo Go app
2. Connect phone to same WiFi as computer
3. Run `npm start`
4. Scan QR code with Expo Go
5. App loads on your phone

**Important:** Update API_CONFIG.BASE_URL to your computer's IP address (not localhost)

---

## 🚨 Common Issues & Fixes

| Issue | Fix |
|-------|-----|
| Can't connect to API | Update BASE_URL to your IP |
| Token not persisting | Check expo-secure-store |
| Images not loading | Verify image URLs in DB |
| Location permission denied | Grant permission in settings |
| App crashes on startup | Run `npm install` again |
| TypeScript errors | Run `npm run type-check` |

---

## 📚 Documentation Files

- **SETUP.md** - Detailed setup guide
- **SCREENS_SUMMARY.md** - All screens explained
- **INSTALLATION.md** - Installation & testing
- **PROJECT_COMPLETION_SUMMARY.md** - Project overview
- **QUICK_REFERENCE.md** - This file

---

## 🎓 Learning Resources

- [Expo Documentation](https://docs.expo.dev)
- [React Native Docs](https://reactnative.dev)
- [Zustand Guide](https://github.com/pmndrs/zustand)
- [Axios Documentation](https://axios-http.com)
- [Expo Router](https://docs.expo.dev/routing/introduction/)

---

## ✅ Pre-Launch Checklist

- [ ] Update API_CONFIG.BASE_URL
- [ ] Run `npm install`
- [ ] Test authentication flow
- [ ] Test product browsing
- [ ] Test cart functionality
- [ ] Test order placement
- [ ] Test on physical device
- [ ] Check all images load
- [ ] Verify location permission
- [ ] Test error scenarios

---

**Ready to launch! 🚀**

