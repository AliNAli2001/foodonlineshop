# i18n Guide

This project now supports two locales:
- `en` (default)
- `ar`

## Files
- `resources/js/i18n/en.json`: source English dictionary.
- `resources/js/i18n/ar.json`: Arabic dictionary template (currently empty values).
- `resources/js/i18n/index.tsx`: i18n provider, `useI18n()` hook, locale persistence.

## How to complete Arabic translation
1. Open `resources/js/i18n/ar.json`.
2. Keep keys exactly the same as `en.json`.
3. Replace each empty value with Arabic text.
4. Save and refresh the app.

## How language switching works
- A global language switcher is rendered on every page.
- Selected locale is saved to:
  - browser `localStorage`
  - Laravel session via `POST /locale`
- RTL/LTR direction is applied automatically:
  - `ar` => `rtl`
  - `en` => `ltr`

## Using translations in React pages
```tsx
import { useI18n } from '../../i18n';

const { t } = useI18n();

<h1>{t('admin.pages.dashboard.title')}</h1>
```

If an Arabic value is empty, it automatically falls back to English.
