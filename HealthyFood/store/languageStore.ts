import { create } from 'zustand';
import { translations } from '@/constants/translations';
import AsyncStorage from '@react-native-async-storage/async-storage';

export type Language = 'en' | 'ar';

export interface LanguageState {
  language: Language;
  isRTL: boolean;
  
  // Actions
  setLanguage: (lang: Language) => Promise<void>;
  getLanguage: () => Promise<void>;
  t: (key: string) => string;
}

export const useLanguageStore = create<LanguageState>((set, get) => ({
  language: 'en',
  isRTL: false,

  setLanguage: async (lang: Language) => {
    try {
      await AsyncStorage.setItem('language', lang);
      set({
        language: lang,
        isRTL: lang === 'ar',
      });
    } catch (error) {
      console.error('Failed to set language:', error);
    }
  },

  getLanguage: async () => {
    try {
      const savedLanguage = await AsyncStorage.getItem('language');
      const lang = (savedLanguage as Language) || 'en';
      set({
        language: lang,
        isRTL: lang === 'ar',
      });
    } catch (error) {
      console.error('Failed to get language:', error);
    }
  },

  t: (key: string) => {
    const { language } = get();
    const keys = key.split('.');
    let value: any = translations[language];

    for (const k of keys) {
      value = value?.[k];
    }

    return value || key;
  },
}));

