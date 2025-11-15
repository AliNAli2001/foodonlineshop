# HealthyFood Expo App - Setup Guide

## Overview
This is a React Native Expo application for the Healthy Food online shop. It provides a complete mobile experience for browsing products, managing cart, placing orders, and tracking deliveries.

## Prerequisites
- Node.js (v16 or higher)
- npm or yarn
- Expo CLI: `npm install -g expo-cli`
- Expo Go app on your mobile device (for testing)

## Installation

1. **Install dependencies:**
```bash
cd HealthyFood
npm install
```

2. **Configure API Base URL:**
Edit `config/api.ts` and update the `BASE_URL` to match your Laravel backend:
```typescript
export const API_CONFIG = {
  BASE_URL: 'http://YOUR_BACKEND_URL/api',
  // ...
};
```

## Running the App

### Development Mode
```bash
npm start
```

This will start the Expo development server. You can then:
- Press `i` to open in iOS simulator
- Press `a` to open in Android emulator
- Scan QR code with Expo Go app on your phone

### Web
```bash
npm run web
```

### Android
```bash
npm run android
```

### iOS
```bash
npm run ios
```

## Project Structure

```
HealthyFood/
├── app/                          # App screens and navigation
│   ├── (auth)/                   # Authentication screens
│   │   ├── login.tsx
│   │   ├── register.tsx
│   │   ├── verify-email.tsx
│   │   └── verify-phone.tsx
│   ├── (tabs)/                   # Main app tabs
│   │   ├── index.tsx             # Home screen
│   │   ├── categories.tsx        # Categories screen
│   │   ├── cart.tsx              # Shopping cart
│   │   └── profile.tsx           # User profile
│   ├── product/[id].tsx          # Product details
│   ├── category/[id].tsx         # Category products
│   ├── checkout.tsx              # Checkout screen
│   ├── orders.tsx                # Orders list
│   ├── order/[id].tsx            # Order details
│   └── _layout.tsx               # Root layout
├── config/
│   └── api.ts                    # API configuration
├── services/                     # API services
│   ├── api.ts                    # API client
│   ├── auth.ts                   # Authentication
│   ├── products.ts               # Products API
│   ├── categories.ts             # Categories API
│   ├── orders.ts                 # Orders API
│   └── profile.ts                # Profile API
├── store/                        # Zustand stores
│   ├── authStore.ts              # Auth state
│   ├── cartStore.ts              # Cart state
│   ├── productsStore.ts          # Products state
│   └── categoriesStore.ts        # Categories state
├── components/                   # Reusable components
├── constants/                    # App constants
├── hooks/                        # Custom hooks
└── assets/                       # Images and icons
```

## Features

### Authentication
- User registration with email and phone
- Email and phone verification
- Secure token storage
- Session management

### Products & Categories
- Browse all products
- View featured products
- Filter by categories
- Search functionality
- Product details with inventory info

### Shopping Cart
- Add/remove items
- Update quantities
- Persistent cart state
- Real-time total calculation

### Orders
- Place orders with delivery options
- Location-based delivery
- Order tracking
- Order history
- Order details view

### User Profile
- View profile information
- Update profile details
- Language preferences
- Promo consent management

## API Integration

The app uses the following API endpoints:

### Authentication
- `POST /auth/register` - Register new user
- `POST /auth/login` - Login user
- `POST /auth/logout` - Logout user
- `POST /auth/verify-email` - Verify email
- `POST /auth/verify-phone` - Verify phone

### Products
- `GET /products` - List products
- `GET /products/{id}` - Get product details
- `GET /products/featured` - Get featured products
- `GET /products/category/{id}` - Get products by category

### Categories
- `GET /categories` - List categories
- `GET /categories/{id}` - Get category details
- `GET /categories/featured` - Get featured categories

### Orders
- `POST /orders` - Create order
- `GET /orders` - List user orders
- `GET /orders/{id}` - Get order details

### Profile
- `GET /profile` - Get user profile
- `PUT /profile` - Update user profile

## State Management

The app uses **Zustand** for state management:

- **authStore**: Authentication state and actions
- **cartStore**: Shopping cart state and actions
- **productsStore**: Products data and loading states
- **categoriesStore**: Categories data and loading states

## Styling

The app uses React Native's built-in `StyleSheet` for styling with a consistent color scheme:
- Primary Green: `#2ecc71`
- Secondary Blue: `#3498db`
- Danger Red: `#e74c3c`
- Warning Orange: `#f39c12`

## Environment Variables

Create a `.env` file in the HealthyFood directory:
```
EXPO_PUBLIC_API_URL=http://YOUR_BACKEND_URL/api
```

## Troubleshooting

### Connection Issues
- Ensure your backend is running
- Check that the API_BASE_URL is correct
- Verify network connectivity

### Authentication Issues
- Clear app cache: `expo start --clear`
- Check token storage in SecureStore
- Verify backend authentication endpoints

### Build Issues
- Clear node_modules: `rm -rf node_modules && npm install`
- Clear Expo cache: `expo start --clear`
- Update Expo CLI: `npm install -g expo-cli@latest`

## Dependencies

Key dependencies:
- `expo` - Expo framework
- `expo-router` - File-based routing
- `react-native` - React Native framework
- `axios` - HTTP client
- `zustand` - State management
- `expo-location` - Location services
- `expo-secure-store` - Secure token storage

## License

This project is part of the Healthy Food online shop system.

