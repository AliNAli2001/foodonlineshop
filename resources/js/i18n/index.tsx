import React, { createContext, useContext, useEffect, useMemo, useState } from 'react';
import en from './en.json';
import ar from './ar.json';

export type Locale = 'en' | 'ar';

type I18nContextValue = {
  locale: Locale;
  isRtl: boolean;
  setLocale: (locale: Locale) => void;
  t: (key: string, fallback?: string) => string;
};

const dictionaries: Record<Locale, Record<string, any>> = {
  en,
  ar,
};

const I18nContext = createContext<I18nContextValue | null>(null);

function getByPath(object: Record<string, any>, path: string): string | undefined {
  const segments = path.split('.');
  let current: any = object;

  for (const segment of segments) {
    if (!current || typeof current !== 'object' || !(segment in current)) {
      return undefined;
    }
    current = current[segment];
  }

  return typeof current === 'string' ? current : undefined;
}

function applyDocumentLocale(locale: Locale) {
  if (typeof document === 'undefined') {
    return;
  }

  const dir = locale === 'ar' ? 'rtl' : 'ltr';
  document.documentElement.lang = locale;
  document.documentElement.dir = dir;
}

function persistLocale(locale: Locale) {
  if (typeof window !== 'undefined') {
    window.localStorage.setItem('locale', locale);
  }

  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

  if (!token) {
    return;
  }

  fetch('/locale', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': token,
      Accept: 'application/json',
    },
    body: JSON.stringify({ locale }),
  }).catch(() => {
    // Ignore locale persistence failures; local state is still applied.
  });
}

function resolveInitialLocale(initialLocale?: string | null): Locale {
  const fromServer = initialLocale === 'ar' ? 'ar' : initialLocale === 'en' ? 'en' : null;

  if (fromServer) {
    return fromServer;
  }

  if (typeof window !== 'undefined') {
    const fromStorage = window.localStorage.getItem('locale');
    if (fromStorage === 'ar' || fromStorage === 'en') {
      return fromStorage;
    }
  }

  return 'en';
}

export function I18nProvider({ initialLocale, children }: { initialLocale?: string | null; children: React.ReactNode }) {
  const [locale, setLocaleState] = useState<Locale>(() => resolveInitialLocale(initialLocale));

  useEffect(() => {
    applyDocumentLocale(locale);
  }, [locale]);

  const value = useMemo<I18nContextValue>(() => {
    const setLocale = (nextLocale: Locale) => {
      setLocaleState(nextLocale);
      applyDocumentLocale(nextLocale);
      persistLocale(nextLocale);
    };

    const t = (key: string, fallback?: string) => {
      const currentValue = getByPath(dictionaries[locale], key);
      if (currentValue && currentValue.trim().length > 0) {
        return currentValue;
      }

      const englishValue = getByPath(dictionaries.en, key);
      if (englishValue && englishValue.trim().length > 0) {
        return englishValue;
      }

      return fallback || key;
    };

    return {
      locale,
      isRtl: locale === 'ar',
      setLocale,
      t,
    };
  }, [locale]);

  return <I18nContext.Provider value={value}>{children}</I18nContext.Provider>;
}

export function useI18n() {
  const context = useContext(I18nContext);

  if (!context) {
    throw new Error('useI18n must be used within I18nProvider');
  }

  return context;
}
