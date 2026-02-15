import '../css/app.css';
import './bootstrap';
import React from 'react';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';

const pages = import.meta.glob('./Pages/**/*.jsx', { eager: true });

createInertiaApp({
    resolve: (name) => {
        const normalized = name.replaceAll('.', '/');
        const page = pages[`./Pages/${normalized}.jsx`];

        if (page) {
            return page.default;
        }

        return pages['./Pages/Shared/MissingPage.jsx'].default;
    },
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },
});
