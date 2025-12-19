# HealthyFood App - Multilingual Implementation

## Overview
The HealthyFood Expo app now supports **Arabic (العربية)** and **English** languages with full RTL (Right-to-Left) support for Arabic.

## What Was Implemented

### 1. **Translation System**
- **File**: `constants/translations.ts`
- Contains all UI text in both English and Arabic
- 100+ translation keys covering all screens
- Easy to add new translations

### 2. **Language Store (Zustand)**
- **File**: `store/languageStore.ts`
- Manages current language preference
- Persists language choice to AsyncStorage
- Provides RTL flag for Arabic
- Translation helper function `t(key)`

### 3. **Edit Profile Screen**
- **File**: `app/edit-profile.tsx`
- Complete form to edit user profile
- Fields: First Name, Last Name, Phone, Language Preference, Promo Consent
- Language switcher integrated
- Form validation and error handling
- Syncs language preference with backend

### 4. **Updated Screens with Multilingual Support**

#### Profile Screen (`app/(tabs)/profile.tsx`)
- Language toggle buttons (English/العربية)
- All text translated
- Edit Profile button now navigates to edit-profile screen
- RTL support

#### Home Screen (`app/(tabs)/index.tsx`)
- Product names in selected language
- All UI text translated
- RTL support

#### Cart Screen (`app/(tabs)/cart.tsx`)
- Product names in selected language
- All buttons and labels translated
- RTL support

#### Categories Screen (`app/(tabs)/categories.tsx`)
- Category names in selected language
- All text translated
- RTL support

#### Login Screen (`app/(auth)/login.tsx`)
- All form labels and buttons translated
- RTL support

### 5. **Root Layout Updates**
- **File**: `app/_layout.tsx`
- Initializes language on app startup
- Added edit-profile route

### 6. **Dependencies Added**
- `@react-native-async-storage/async-storage@^1.23.1` - For persisting language preference

## How to Use

### For Users
1. **Change Language**: Go to Profile → Click English/العربية button
2. **Edit Profile**: Go to Profile → Click "Edit Profile" button
3. Language preference is saved and persists across app sessions

### For Developers
1. **Add New Translation**:
   ```typescript
   // In constants/translations.ts
   export const translations = {
     en: {
       myNewKey: 'English text',
     },
     ar: {
       myNewKey: 'النص العربي',
     },
   };
   ```

2. **Use Translation in Component**:
   ```typescript
   import { useLanguageStore } from '@/store/languageStore';
   
   const { t, language, isRTL } = useLanguageStore();
   
   <Text>{t('myNewKey')}</Text>
   <View style={[styles.container, isRTL && styles.rtl]}>
   ```

## Features

✅ **Full Arabic Support** - All text in Arabic
✅ **RTL Layout** - Proper right-to-left layout for Arabic
✅ **Language Persistence** - Language choice saved locally
✅ **Backend Sync** - Language preference synced with server
✅ **Edit Profile** - Complete profile editing functionality
✅ **Easy to Extend** - Simple translation system

## Testing Checklist

- [ ] Switch between English and Arabic
- [ ] Verify RTL layout in Arabic mode
- [ ] Edit profile and change language preference
- [ ] Verify language persists after app restart
- [ ] Check all screens display correct language
- [ ] Test product names in both languages
- [ ] Verify category names in both languages
- [ ] Test form validation in both languages
- [ ] Check error messages in both languages

## Next Steps

1. Update remaining screens (checkout, orders, product details, etc.)
2. Add more languages if needed
3. Consider adding language selection on first app launch
4. Add language preference to user settings

