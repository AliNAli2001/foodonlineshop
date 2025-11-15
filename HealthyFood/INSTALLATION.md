# HealthyFood Expo App - Installation & Testing Guide

## Quick Start

### 1. Install Dependencies
```bash
cd HealthyFood
npm install
```

### 2. Configure Backend URL
Edit `HealthyFood/config/api.ts`:
```typescript
export const API_CONFIG = {
  BASE_URL: 'http://YOUR_BACKEND_IP:8000/api',  // Update this
  TIMEOUT: 10000,
  HEADERS: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
};
```

**Important:** 
- For local development: Use your machine's IP address (not localhost)
- For production: Use your server's domain/IP

### 3. Start the Development Server
```bash
npm start
```

### 4. Run on Device/Emulator

**Option A: Expo Go App (Easiest)**
- Install Expo Go on your phone
- Scan the QR code from terminal
- App will load on your device

**Option B: Android Emulator**
```bash
npm run android
```

**Option C: iOS Simulator**
```bash
npm run ios
```

**Option D: Web Browser**
```bash
npm run web
```

---

## Testing Checklist

### Authentication Flow
- [ ] Register new account with email and phone
- [ ] Verify email with code
- [ ] Verify phone with code
- [ ] Login with credentials
- [ ] Logout from profile screen
- [ ] Login again to verify token persistence

### Home Screen
- [ ] Featured products load and display
- [ ] All products load and display
- [ ] Search functionality works
- [ ] Product cards show correct info
- [ ] "Add to Cart" button works
- [ ] Navigation to product details works

### Categories Screen
- [ ] Categories load and display
- [ ] Category cards show images and names
- [ ] Navigation to category products works
- [ ] Loading state displays correctly

### Product Details
- [ ] Product image loads
- [ ] Product name, price, description display
- [ ] Available stock shows correctly
- [ ] Quantity selector works (+/- buttons)
- [ ] "Add to Cart" button adds item
- [ ] Success alert shows options
- [ ] Navigation works from alert

### Shopping Cart
- [ ] Items display correctly
- [ ] Quantity controls work
- [ ] Subtotals calculate correctly
- [ ] Total price updates
- [ ] Delete button removes items
- [ ] Empty cart state shows
- [ ] "Proceed to Checkout" navigates

### Checkout
- [ ] Order summary displays all items
- [ ] Delivery method selector works
- [ ] Address input accepts text
- [ ] "Get Location" button works
- [ ] Location coordinates display
- [ ] "Place Order" button submits
- [ ] Success alert shows
- [ ] Cart clears after order

### Orders
- [ ] Orders list loads
- [ ] Order cards display correctly
- [ ] Status badges show correct colors
- [ ] Navigation to order details works
- [ ] Empty state shows when no orders

### Order Details
- [ ] Order info displays correctly
- [ ] Items list shows all products
- [ ] Delivery address displays
- [ ] Coordinates show if available
- [ ] Delivery person info shows (if available)

### User Profile
- [ ] User info displays correctly
- [ ] Contact information shows
- [ ] Verification status displays
- [ ] Language preference shows
- [ ] "My Orders" button navigates
- [ ] "Logout" button works

---

## Troubleshooting

### App Won't Connect to Backend
**Problem:** Network error when trying to login
**Solution:**
1. Check backend is running: `php artisan serve`
2. Update API_CONFIG.BASE_URL with correct IP
3. Ensure phone/emulator can reach backend IP
4. Check firewall settings

### Token Not Persisting
**Problem:** Logged out after app restart
**Solution:**
1. Check expo-secure-store is installed
2. Verify token is being saved in authStore
3. Check browser console for errors

### Images Not Loading
**Problem:** Product images show as broken
**Solution:**
1. Verify image URLs in database
2. Check image storage path in Laravel
3. Ensure images are publicly accessible

### Location Permission Denied
**Problem:** "Get Location" button doesn't work
**Solution:**
1. Grant location permission when prompted
2. Check app permissions in device settings
3. Ensure expo-location is installed

### Build Errors
**Problem:** TypeScript or compilation errors
**Solution:**
```bash
# Clear cache and reinstall
rm -rf node_modules package-lock.json
npm install

# Clear Expo cache
expo start --clear
```

---

## Performance Tips

1. **Optimize Images**
   - Use compressed images
   - Implement lazy loading for lists

2. **Pagination**
   - Implement pagination for products/orders
   - Load more on scroll

3. **Caching**
   - Cache product data locally
   - Implement offline support

4. **Network**
   - Use appropriate timeouts
   - Implement retry logic

---

## Development Tools

### Debug Mode
```bash
npm start
# Press 'd' in terminal to open debugger
```

### React Native Debugger
```bash
# Install globally
npm install -g react-native-debugger

# Run app with debugger
npm start
```

### Network Inspection
- Use Expo DevTools to inspect network requests
- Check API responses in browser DevTools

---

## Deployment

### Build APK (Android)
```bash
eas build --platform android
```

### Build IPA (iOS)
```bash
eas build --platform ios
```

### Submit to App Stores
```bash
eas submit --platform android
eas submit --platform ios
```

---

## Support

For issues or questions:
1. Check the SETUP.md file
2. Review SCREENS_SUMMARY.md for feature details
3. Check backend API documentation
4. Review Expo documentation: https://docs.expo.dev

---

## Next Steps

After successful installation and testing:
1. Customize styling and branding
2. Add more features (reviews, wishlist, etc.)
3. Implement push notifications
4. Add analytics
5. Deploy to app stores

