# HealthyFood App - Available Translation Keys

## How to Use Translation Keys

```typescript
import { useLanguageStore } from '@/store/languageStore';

const { t } = useLanguageStore();

// Usage
<Text>{t('login')}</Text>
<Text>{t('addToCart')}</Text>
```

## Complete List of Translation Keys

### Authentication
- `login` - Login
- `register` - Register
- `email` - Email
- `password` - Password
- `passwordConfirmation` - Confirm Password
- `firstName` - First Name
- `lastName` - Last Name
- `phone` - Phone
- `language` - Language
- `selectLanguage` - Select Language
- `verifyEmail` - Verify Email
- `verifyPhone` - Verify Phone
- `verificationCode` - Verification Code
- `send` - Send
- `verify` - Verify
- `resendCode` - Resend Code
- `loginSuccess` - Login successful
- `registerSuccess` - Registration successful
- `logout` - Logout
- `logoutConfirm` - Are you sure you want to logout?
- `cancel` - Cancel

### Home & Products
- `home` - Home
- `featuredProducts` - Featured Products
- `allProducts` - All Products
- `search` - Search products...
- `addToCart` - Add to Cart
- `price` - Price
- `stock` - Stock
- `availableStock` - Available Stock

### Categories
- `categories` - Categories
- `browseCategories` - Browse Categories

### Shopping Cart
- `cart` - Cart
- `shoppingCart` - Shopping Cart
- `yourCartIsEmpty` - Your cart is empty
- `continueShopping` - Continue Shopping
- `quantity` - Quantity
- `subtotal` - Subtotal
- `total` - Total
- `proceedToCheckout` - Proceed to Checkout
- `removeItem` - Remove

### Checkout & Orders
- `checkout` - Checkout
- `orderSummary` - Order Summary
- `deliveryMethod` - Delivery Method
- `delivery` - Delivery
- `handDelivered` - Hand Delivered
- `shipping` - Shipping
- `deliveryAddress` - Delivery Address
- `enterDeliveryAddress` - Enter delivery address
- `getCurrentLocation` - Get Current Location
- `location` - Location
- `placeOrder` - Place Order
- `orderPlaced` - Order placed successfully
- `items` - items
- `orders` - Orders
- `myOrders` - My Orders
- `noOrders` - No orders yet
- `startShopping` - Start Shopping
- `orderNumber` - Order #
- `orderDate` - Date
- `orderStatus` - Status
- `pending` - Pending
- `confirmed` - Confirmed
- `shipped` - Shipped
- `delivered` - Delivered
- `canceled` - Canceled
- `done` - Done

### Profile
- `profile` - Profile
- `userProfile` - User Profile
- `contactInformation` - Contact Information
- `preferences` - Preferences
- `editProfile` - Edit Profile
- `updateProfile` - Update Profile
- `emailVerified` - Email Verified
- `phoneVerified` - Phone Verified
- `yes` - Yes
- `no` - No
- `promoConsent` - Promo Consent
- `languagePreference` - Language Preference
- `arabic` - العربية
- `english` - English

### Product & Category Details
- `productDetails` - Product Details
- `description` - Description
- `categoryProducts` - Category Products
- `noProductsInCategory` - No products in this category

### Errors & Messages
- `error` - Error
- `success` - Success
- `loading` - Loading...
- `tryAgain` - Try Again
- `fillAllFields` - Please fill in all fields
- `invalidEmail` - Invalid email address
- `passwordMismatch` - Passwords do not match
- `networkError` - Network error. Please check your connection.
- `serverError` - Server error. Please try again later.
- `failedToLoadProfile` - Failed to load profile
- `failedToUpdateProfile` - Failed to update profile
- `failedToLoadProducts` - Failed to load products
- `failedToLoadCategories` - Failed to load categories
- `failedToPlaceOrder` - Failed to place order
- `failedToLoadOrders` - Failed to load orders

### Common Actions
- `back` - Back
- `next` - Next
- `save` - Save
- `delete` - Delete
- `edit` - Edit
- `view` - View
- `close` - Close
- `confirm` - Confirm
- `name` - Name
- `date` - Date
- `amount` - Amount
- `status` - Status

## Adding New Translations

1. Open `constants/translations.ts`
2. Add key to both `en` and `ar` objects:
   ```typescript
   export const translations = {
     en: {
       myNewKey: 'English text',
     },
     ar: {
       myNewKey: 'النص العربي',
     },
   };
   ```
3. Use in component: `t('myNewKey')`

## Language Codes

- `en` - English
- `ar` - Arabic (العربية)

