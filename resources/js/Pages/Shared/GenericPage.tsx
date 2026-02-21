import React from 'react';
import { useI18n } from '../../i18n';

export type GenericPageProps = Record<string, unknown>;

type Props = {
    viewName: string;
    propsData: GenericPageProps;
};

export default function GenericPage({ viewName, propsData }: Props) {
    const { t } = useI18n();

    return (
        <main style={{ padding: '24px' }}>
            <h1 style={{ marginTop: 0 }}>{viewName}</h1>
            <p>{t('shared.genericPage.description')}</p>
            <pre style={{ background: '#fff', padding: '12px', border: '1px solid #ddd', overflowX: 'auto' }}>
                {JSON.stringify(propsData, null, 2)}
            </pre>
        </main>
    );
}

