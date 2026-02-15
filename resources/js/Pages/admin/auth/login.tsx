import React from 'react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';

export default function AdminLogin() {
    const { flash = {} } = usePage().props;
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
    });

    const submit = (event) => {
        event.preventDefault();
        post('/admin/login');
    };

    return (
        <>
            <Head title="Admin Login" />

            <main className="grid min-h-screen place-items-center bg-gradient-to-b from-blue-50 to-slate-100 px-4">
                <section className="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-lg">
                    <h1 className="text-2xl font-bold text-slate-900">Admin Login</h1>
                    <p className="mt-1 text-sm text-slate-500">Sign in to access the dashboard.</p>

                    {flash.success && (
                        <div className="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
                            {flash.success}
                        </div>
                    )}

                    {errors.email && (
                        <div className="mt-4 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">{errors.email}</div>
                    )}
                    {errors.password && (
                        <div className="mt-2 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">{errors.password}</div>
                    )}

                    <form onSubmit={submit} className="mt-5 space-y-4">
                        <div>
                            <label htmlFor="email" className="mb-1.5 block text-sm font-semibold text-slate-700">
                                Email
                            </label>
                            <input
                                id="email"
                                type="email"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                autoComplete="email"
                                required
                                className="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            />
                        </div>

                        <div>
                            <label htmlFor="password" className="mb-1.5 block text-sm font-semibold text-slate-700">
                                Password
                            </label>
                            <input
                                id="password"
                                type="password"
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                autoComplete="current-password"
                                required
                                className="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            />
                        </div>

                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-70"
                        >
                            {processing ? 'Signing in...' : 'Login'}
                        </button>
                    </form>

                    <p className="mt-5 text-center text-sm text-slate-600">
                        <Link href="/" className="font-medium text-blue-600 hover:underline">
                            Back to Home
                        </Link>
                    </p>
                </section>
            </main>
        </>
    );
}
