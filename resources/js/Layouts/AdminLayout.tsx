import React, { useEffect, useMemo, useRef, useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { useI18n } from '../i18n';
import { ToastContainer, toast } from 'react-toastify';

const navItems = [
    { key: 'dashboard', href: '/admin/dashboard' },
    { key: 'statistics', href: '/admin/statistics' },
    { key: 'orders', href: '/admin/orders' },
    { key: 'products', href: '/admin/products' },
    { key: 'inventory', href: '/admin/inventory' },
    { key: 'categories', href: '/admin/categories' },
    { key: 'companies', href: '/admin/companies' },
    { key: 'tags', href: '/admin/tags' },
    { key: 'damagedGoods', href: '/admin/damaged-goods' },
    { key: 'adjustments', href: '/admin/adjustments' },
    { key: 'delivery', href: '/admin/delivery' },
];

function NavIcon({ itemKey }: { itemKey: string }) {
    const cls = 'h-4 w-4';

    switch (itemKey) {
        case 'dashboard':
            return <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" className={cls}><path d="M3 13h8V3H3v10Zm10 8h8V11h-8v10ZM3 21h8v-6H3v6Zm10-10h8V3h-8v8Z" /></svg>;
        case 'statistics':
            return <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" className={cls}><path d="M4 20V10m6 10V4m6 16v-7m6 7v-4" /><path d="M3 20h18" /></svg>;
        case 'orders':
            return <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" className={cls}><path d="M4 6h16v12H4z" /><path d="M4 10h16M8 14h3" /></svg>;
        case 'products':
            return <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" className={cls}><path d="m12 3 8 4.5-8 4.5-8-4.5L12 3Z" /><path d="M4 7.5V16.5L12 21l8-4.5V7.5" /></svg>;
        case 'inventory':
            return <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" className={cls}><path d="M3 7h18v13H3z" /><path d="M8 7V3h8v4M8 13h8" /></svg>;
        case 'categories':
            return <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" className={cls}><path d="M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z" /></svg>;
        case 'companies':
            return <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" className={cls}><path d="M4 21V7l8-4 8 4v14" /><path d="M9 21V11h6v10M7 9h0m10 0h0" /></svg>;
        case 'tags':
            return <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" className={cls}><path d="M20 10 11 19 3 11l8-8h6l3 3v4Z" /><circle cx="14" cy="7" r="1.2" fill="currentColor" stroke="none" /></svg>;
        case 'damagedGoods':
            return <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" className={cls}><path d="M5 5h14l-1.5 14h-11L5 5Zm4-2h6M9 10h6m-6 4h4" /></svg>;
        case 'adjustments':
            return <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" className={cls}><path d="M4 17h4m-2-2v4M20 7h-4m2-2v4" /><path d="M8 17h8a4 4 0 0 0 0-8H8" /></svg>;
        case 'delivery':
            return <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" className={cls}><path d="M3 7h11v10H3zM14 10h3l4 3v4h-7z" /><circle cx="7.5" cy="18.5" r="1.5" /><circle cx="18.5" cy="18.5" r="1.5" /></svg>;
        default:
            return <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" className={cls}><circle cx="12" cy="12" r="8" /></svg>;
    }
}

export default function AdminLayout({ title = 'Admin', children }) {
    const { url, props } = usePage();
    const { flash = {}, errors = {} } = props as any;
    const { t, isRtl } = useI18n();
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [theme, setTheme] = useState<'dark' | 'light'>('dark');
    const lastToastRef = useRef<{ success?: string; error?: string }>({});

    const currentPath = useMemo(() => url.split('?')[0], [url]);
    const isActive = useMemo(
        () => (href) => currentPath === href || currentPath.startsWith(`${href}/`),
        [currentPath],
    );
    const isDark = theme === 'dark';
    const sidebarClosedClass = isRtl ? 'translate-x-full' : '-translate-x-full';

    useEffect(() => {
        if (typeof window === 'undefined') {
            return;
        }

        const savedTheme = window.localStorage.getItem('admin-theme');
        if (savedTheme === 'light' || savedTheme === 'dark') {
            setTheme(savedTheme);
        }

        if (window.matchMedia('(min-width: 1024px)').matches) {
            setSidebarOpen(true);
        }
    }, []);

    useEffect(() => {
        if (flash?.success && lastToastRef.current.success !== flash.success) {
            toast.success(flash.success);
            lastToastRef.current.success = flash.success;
        }
    }, [flash?.success]);

    useEffect(() => {
        const fieldError =
            Object.values(errors || {}).find((value) => typeof value === 'string' && value.length > 0) as string | undefined;
        const errorMessage = flash?.error || errors?.error || fieldError;
        if (errorMessage && lastToastRef.current.error !== errorMessage) {
            toast.error(errorMessage);
            lastToastRef.current.error = errorMessage;
        }
    }, [flash?.error, errors?.error, errors]);

    const toggleTheme = () => {
        setTheme((prevTheme) => {
            const nextTheme = prevTheme === 'dark' ? 'light' : 'dark';
            if (typeof window !== 'undefined') {
                window.localStorage.setItem('admin-theme', nextTheme);
            }
            return nextTheme;
        });
    };

    return (
        <div
            data-admin-theme={isDark ? 'dark' : 'light'}
            className={`min-h-screen ${isDark ? 'bg-slate-950 text-slate-100' : 'bg-slate-50 text-slate-900'}`}
        >
            <Head title={title} />

            <div className="pointer-events-none fixed inset-0 overflow-hidden">
                <div className={`absolute -top-44 left-1/2 h-96 w-96 -translate-x-1/2 rounded-full blur-3xl ${isDark ? 'bg-cyan-400/15' : 'bg-cyan-300/25'}`} />
                <div className={`absolute bottom-0 right-0 h-80 w-80 rounded-full blur-3xl ${isDark ? 'bg-blue-500/15' : 'bg-blue-300/25'}`} />
                <div
                    className={`absolute inset-0 ${
                        isDark
                            ? 'bg-[radial-gradient(circle_at_20%_0%,rgba(34,211,238,0.08),transparent_35%),radial-gradient(circle_at_90%_90%,rgba(59,130,246,0.08),transparent_30%)]'
                            : 'bg-[radial-gradient(circle_at_20%_0%,rgba(14,165,233,0.12),transparent_38%),radial-gradient(circle_at_90%_90%,rgba(59,130,246,0.12),transparent_35%)]'
                    }`}
                />
            </div>

            <header
                className={`sticky top-0 z-40 border-b backdrop-blur-2xl ${
                    isDark ? 'border-white/10 bg-slate-950/70' : 'border-slate-200/80 bg-white/75'
                }`}
            >
                <div className="mx-auto flex h-16 max-w-[1700px] items-center justify-between gap-3 px-4 md:px-6">
                    <div className="flex items-center gap-3">
                        <button
                            type="button"
                            onClick={() => setSidebarOpen((prev) => !prev)}
                            className={`rounded-xl border p-2 transition ${
                                isDark
                                    ? 'border-white/15 bg-white/5 text-slate-100 hover:bg-white/10'
                                    : 'border-slate-300 bg-white/80 text-slate-700 shadow-sm hover:bg-white'
                            }`}
                            aria-label={sidebarOpen ? 'Close menu' : t('common.menu')}
                        >
                            {sidebarOpen ? (
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="h-5 w-5">
                                    <path d="M6 6l12 12M18 6l-12 12" />
                                </svg>
                            ) : (
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.9" className="h-5 w-5">
                                    <path d="M4 7h16M4 12h16M4 17h16" />
                                </svg>
                            )}
                        </button>
                        <div
                            className={`flex items-center gap-2 rounded-2xl border px-2.5 py-1.5 ${
                                isDark ? 'border-white/10 bg-white/[0.03]' : 'border-slate-200 bg-white/70 shadow-sm'
                            }`}
                        >
                            <div className="grid h-8 w-8 place-items-center rounded-lg bg-gradient-to-br from-cyan-400 to-blue-500 text-xs font-black text-slate-950 shadow-[0_0_20px_rgba(34,211,238,0.35)]">
                                AD
                            </div>
                            <div>
                                <p className={`text-[11px] uppercase tracking-[0.2em] ${isDark ? 'text-cyan-200/80' : 'text-cyan-700'}`}>{t('common.workspace')}</p>
                                <p className={`text-sm font-semibold tracking-wide ${isDark ? 'text-slate-200' : 'text-slate-700'}`}>{t('common.adminControl')}</p>
                            </div>
                        </div>
                    </div>

                    <div className="flex items-center gap-2">
                        <button
                            type="button"
                            onClick={toggleTheme}
                            className={`rounded-xl border p-2 transition ${
                                isDark
                                    ? 'border-white/15 bg-white/5 text-slate-100 hover:bg-white/10'
                                    : 'border-slate-300 bg-white/80 text-slate-700 shadow-sm hover:bg-white'
                            }`}
                            aria-label={isDark ? 'Switch to light mode' : 'Switch to dark mode'}
                        >
                            {isDark ? (
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" className="h-5 w-5">
                                    <path d="M12 3v2.2M12 18.8V21M4.9 4.9l1.6 1.6m11 11 1.6 1.6M3 12h2.2m13.6 0H21M4.9 19.1l1.6-1.6m11-11 1.6-1.6" />
                                    <circle cx="12" cy="12" r="4.2" />
                                </svg>
                            ) : (
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" className="h-5 w-5">
                                    <path d="M21 14.7A9 9 0 1 1 9.3 3a7.2 7.2 0 0 0 11.7 11.7Z" />
                                </svg>
                            )}
                        </button>
                        <Link
                            href="/admin/settings"
                            className={`rounded-xl border px-3 py-2 text-sm font-medium transition ${
                                isDark
                                    ? 'border-white/15 bg-white/5 text-slate-100 hover:border-cyan-300/30 hover:bg-white/10'
                                    : 'border-slate-300 bg-white/80 text-slate-700 shadow-sm hover:border-cyan-300 hover:bg-white'
                            }`}
                        >
                            {t('common.settings')}
                        </Link>
                        <button
                            type="button"
                            onClick={() => router.post('/admin/logout')}
                            className={`rounded-xl border px-3 py-2 text-sm font-medium transition ${
                                isDark
                                    ? 'border-rose-400/25 bg-rose-500/10 text-rose-200 hover:border-rose-300/40 hover:bg-rose-500/20'
                                    : 'border-rose-300 bg-rose-50 text-rose-600 hover:border-rose-400 hover:bg-rose-100'
                            }`}
                        >
                            {t('common.logout')}
                        </button>
                    </div>
                </div>
            </header>

            <div className="mx-auto flex max-w-[1700px]">
                <div
                    className={`fixed inset-0 z-30 backdrop-blur-sm transition-opacity lg:hidden ${
                        isDark ? 'bg-slate-900/70' : 'bg-slate-900/30'
                    } ${
                        sidebarOpen ? 'opacity-100' : 'pointer-events-none opacity-0'
                    }`}
                    onClick={() => setSidebarOpen(false)}
                />

                <aside
                    className={`fixed top-16 z-40 h-[calc(100vh-4rem)] w-80 p-4 backdrop-blur-xl transition-transform lg:w-72 ${
                        isRtl ? 'right-0 border-l' : 'left-0 border-r'
                    } ${
                        isDark
                            ? 'border-white/10 bg-slate-900/95 lg:bg-slate-900/80'
                            : 'border-slate-200 bg-white/95 lg:bg-white/90'
                    } ${
                        sidebarOpen ? 'translate-x-0' : sidebarClosedClass
                    }`}
                >
                    <nav className="grid gap-2">
                        {navItems.map((item) => {
                            const active = isActive(item.href);

                            return (
                                <Link
                                    key={item.href}
                                    href={item.href}
                                    onClick={() => setSidebarOpen(false)}
                                    className={`group flex items-center gap-3 rounded-2xl border px-3 py-2.5 text-sm font-medium transition ${
                                        active
                                            ? isDark
                                                ? 'border-cyan-400/40 bg-gradient-to-r from-cyan-500/20 to-blue-500/10 text-cyan-100 shadow-[0_0_16px_rgba(34,211,238,0.18)]'
                                                : 'border-cyan-300 bg-gradient-to-r from-cyan-100 to-blue-50 text-cyan-800 shadow-[0_8px_20px_rgba(56,189,248,0.18)]'
                                            : isDark
                                                ? 'border-white/10 bg-white/5 text-slate-300 hover:border-cyan-300/25 hover:bg-white/10 hover:text-slate-100'
                                                : 'border-slate-200 bg-white/60 text-slate-700 hover:border-cyan-300 hover:bg-white'
                                    }`}
                                >
                                    <span
                                        className={`grid h-7 w-7 place-items-center rounded-lg ${
                                            active
                                                ? isDark
                                                    ? 'bg-cyan-300 text-slate-950'
                                                    : 'bg-cyan-200 text-cyan-800'
                                                : isDark
                                                    ? 'bg-slate-800 text-slate-300 group-hover:bg-slate-700'
                                                    : 'bg-slate-100 text-slate-600 group-hover:bg-slate-200'
                                        }`}
                                    >
                                        <NavIcon itemKey={item.key} />
                                    </span>
                                    <span>{t(`admin.nav.${item.key}`)}</span>
                                </Link>
                            );
                        })}
                    </nav>
                </aside>

                <main
                    className={`admin-fade-in relative z-10 w-full p-4 transition-all duration-300 md:p-6 lg:p-8 ${
                        sidebarOpen ? (isRtl ? 'lg:pr-[20rem]' : 'lg:pl-[20rem]') : ''
                    }`}
                >
                    {children}
                </main>
            </div>
            <ToastContainer
                position="top-right"
                autoClose={3500}
                hideProgressBar={false}
                newestOnTop
                closeOnClick
                pauseOnHover
                draggable
                theme={isDark ? 'dark' : 'light'}
                toastClassName={
                    isDark
                        ? '!rounded-2xl !border !border-white/15 !bg-slate-900/95 !text-slate-100'
                        : '!rounded-2xl !border !border-slate-200 !bg-white !text-slate-800'
                }
                bodyClassName="!text-sm"
            />
        </div>
    );
}
