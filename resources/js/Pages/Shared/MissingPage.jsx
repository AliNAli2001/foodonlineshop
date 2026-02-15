import React from 'react';

export default function MissingPage(props) {
    return (
        <main style={{ padding: '24px' }}>
            <h1 style={{ marginTop: 0 }}>Missing Inertia Page</h1>
            <p>No React page component exists for this route yet.</p>
            <pre style={{ background: '#fff', padding: '12px', border: '1px solid #ddd', overflowX: 'auto' }}>
                {JSON.stringify(props, null, 2)}
            </pre>
        </main>
    );
}

