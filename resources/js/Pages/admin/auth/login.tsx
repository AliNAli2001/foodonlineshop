import React, { useState } from 'react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useI18n } from '../../../i18n';

export default function AdminLogin() {
    const { t } = useI18n();
    const { flash = {} } = usePage<{ flash?: { success?: string } }>().props;
    const [showPassword, setShowPassword] = useState(false);
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
    });

    const submit = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        post('/admin/login');
    };

    return (
        <>
            <Head title={t('admin.pages.auth.login.title')} />

            <main className="relative grid min-h-screen place-items-center overflow-hidden bg-slate-950 px-4 py-10">
                <div className="pointer-events-none absolute inset-0">
                    <div className="absolute -left-16 top-[-80px] h-64 w-64 rounded-full bg-cyan-400/20 blur-3xl" />
                    <div className="absolute -right-20 bottom-[-90px] h-72 w-72 rounded-full bg-blue-500/20 blur-3xl" />
                </div>

                <section className="relative w-full max-w-4xl overflow-hidden rounded-3xl border border-white/10 bg-slate-900/80 shadow-2xl backdrop-blur-xl">
                    <div className="grid md:grid-cols-2">
                        <div className="flex flex-col justify-between border-b border-white/10 bg-gradient-to-br from-cyan-400/20 via-blue-500/10 to-transparent p-8 md:border-b-0 md:border-r">
                            <div>
                                <p className="inline-flex rounded-full border border-cyan-300/30 bg-cyan-400/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-cyan-200">
                                    {t('admin.pages.auth.login.welcome')}
                                </p>
                                <h1 className="mt-4 text-3xl font-bold leading-tight text-white">{t('admin.pages.auth.login.title')}</h1>
                                <p className="mt-3 text-sm text-slate-300">{t('admin.pages.auth.login.description')}</p>
                            </div>
                            <p className="mt-6 text-xs text-slate-400">{t('admin.pages.auth.login.secureNote')}</p>
                        </div>

                        <div className="p-8">
                            <p className="text-sm text-slate-300">{t('admin.pages.auth.login.subtitle')}</p>

                            {flash.success && (
                                <div className="mt-4 rounded-xl border border-emerald-300/30 bg-emerald-500/10 px-3 py-2 text-sm text-emerald-200">
                                    {flash.success}
                                </div>
                            )}

                            {(errors.email || errors.password) && (
                                <div className="mt-4 rounded-xl border border-rose-300/30 bg-rose-500/10 px-3 py-2 text-sm text-rose-200">
                                    {errors.email || errors.password}
                                </div>
                            )}

                            <form onSubmit={submit} className="mt-5 space-y-4">
                                <div>
                                    <label htmlFor="email" className="mb-1.5 block text-sm font-semibold text-slate-200">
                                        {t('common.email')}
                                    </label>
                                    <input
                                        id="email"
                                        type="email"
                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value)}
                                        autoComplete="email"
                                        required
                                        placeholder={t('admin.pages.auth.login.emailPlaceholder')}
                                        className="w-full rounded-xl border border-white/15 bg-slate-800 px-3 py-2.5 text-sm text-white outline-none transition placeholder:text-slate-500 focus:border-cyan-300/50 focus:ring-2 focus:ring-cyan-400/20"
                                    />
                                </div>

                                <div>
                                    <label htmlFor="password" className="mb-1.5 block text-sm font-semibold text-slate-200">
                                        {t('common.password')}
                                    </label>
                                    <div className="relative">
                                        <input
                                            id="password"
                                            type={showPassword ? 'text' : 'password'}
                                            value={data.password}
                                            onChange={(e) => setData('password', e.target.value)}
                                            autoComplete="current-password"
                                            required
                                            placeholder={t('admin.pages.auth.login.passwordPlaceholder')}
                                            className="w-full rounded-xl border border-white/15 bg-slate-800 px-3 py-2.5 pr-24 text-sm text-white outline-none transition placeholder:text-slate-500 focus:border-cyan-300/50 focus:ring-2 focus:ring-cyan-400/20"
                                        />
                                        <button
                                            type="button"
                                            onClick={() => setShowPassword((prev) => !prev)}
                                            className="absolute right-1.5 top-1/2 -translate-y-1/2 rounded-lg border border-white/15 bg-white/5 px-2.5 py-1 text-xs font-medium text-slate-200 hover:bg-white/10"
                                        >
                                            {showPassword ? t('admin.pages.auth.login.hidePassword') : t('admin.pages.auth.login.showPassword')}
                                        </button>
                                    </div>
                                </div>

                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="w-full rounded-xl bg-cyan-400 px-4 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-cyan-300 disabled:cursor-not-allowed disabled:opacity-70"
                                >
                                    {processing ? t('common.signingIn') : t('common.signIn')}
                                </button>
                            </form>

                            <p className="mt-5 text-center text-sm text-slate-400">
                                <Link href="/" className="font-medium text-cyan-300 hover:text-cyan-200 hover:underline">
                                    {t('common.back')}
                                </Link>
                            </p>
                        </div>
                    </div>
                </section>
            </main>
        </>
    );
}
