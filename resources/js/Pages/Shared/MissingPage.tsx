import React from 'react';
import { useI18n } from '../../i18n';

export default function MissingPage(props) {
    const { t } = useI18n();

    return (
        <main style={{ padding: '24px' }}>
            <h1 style={{ marginTop: 0 }}>{t('shared.missingPage.title')}</h1>
            <p>{t('shared.missingPage.description')}</p>
            <pre style={{ background: '#fff', padding: '12px', border: '1px solid #ddd', overflowX: 'auto' }}>
                {JSON.stringify(props, null, 2)}
            </pre>
        </main>
    );
}

