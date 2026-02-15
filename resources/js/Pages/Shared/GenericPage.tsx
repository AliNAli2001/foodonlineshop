import React from 'react';

export default function GenericPage({ viewName, propsData }) {
    return (
        <main style={{ padding: '24px' }}>
            <h1 style={{ marginTop: 0 }}>{viewName}</h1>
            <p>This page was migrated to Inertia React. Build UI details for this screen here.</p>
            <pre style={{ background: '#fff', padding: '12px', border: '1px solid #ddd', overflowX: 'auto' }}>
                {JSON.stringify(propsData, null, 2)}
            </pre>
        </main>
    );
}

