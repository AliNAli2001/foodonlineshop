import '../css/app.css';
import './bootstrap';
import React from 'react';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { I18nProvider } from './i18n';
import GlobalLanguageSwitcher from './components/GlobalLanguageSwitcher';
import 'react-toastify/dist/ReactToastify.css';

const pages = import.meta.glob('./Pages/**/*.tsx', { eager: true });

createInertiaApp({
    resolve: (name) => {
        const normalized = name.replaceAll('.', '/');
        const page = pages[`./Pages/${normalized}.tsx`];

        if (page) {
            return page.default;
        }

        return pages['./Pages/Shared/MissingPage.tsx'].default;
    },
    setup({ el, App, props }) {
        const initialLocale = props?.initialPage?.props?.locale ?? 'en';

        createRoot(el).render(
            <I18nProvider initialLocale={initialLocale}>
                <App {...props} />
                <GlobalLanguageSwitcher />
            </I18nProvider>,
        );
    },
});


