import React, { useMemo, useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { useI18n } from '../i18n';

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
    const { flash = {} } = props;
    const { t } = useI18n();
    const [sidebarOpen, setSidebarOpen] = useState(false);

    const currentPath = useMemo(() => url.split('?')[0], [url]);
    const isActive = useMemo(
        () => (href) => currentPath === href || currentPath.startsWith(`${href}/`),
        [currentPath],
    );

    return (
        <div className="min-h-screen bg-slate-950 text-slate-100">
            <Head title={title} />

            <div className="pointer-events-none fixed inset-0 overflow-hidden">
                <div className="absolute -top-44 left-1/2 h-96 w-96 -translate-x-1/2 rounded-full bg-cyan-400/20 blur-3xl" />
                <div className="absolute bottom-0 right-0 h-80 w-80 rounded-full bg-blue-500/20 blur-3xl" />
            </div>

            <header className="sticky top-0 z-40 border-b border-white/10 bg-slate-950/80 backdrop-blur-xl">
                <div className="mx-auto flex h-16 max-w-[1700px] items-center justify-between gap-3 px-4 md:px-6">
                    <div className="flex items-center gap-3">
                        <button
                            type="button"
                            onClick={() => setSidebarOpen((prev) => !prev)}
                            className="rounded-xl border border-white/15 bg-white/5 px-3 py-2 text-sm font-medium text-slate-100 transition hover:bg-white/10 lg:hidden"
                        >
                            {t('common.menu')}
                        </button>
                        <div className="flex items-center gap-2">
                            <div className="grid h-8 w-8 place-items-center rounded-lg bg-gradient-to-br from-cyan-400 to-blue-500 text-xs font-black text-slate-950">
                                AD
                            </div>
                            <p className="text-sm font-semibold tracking-wide text-slate-200">{t('common.adminControl')}</p>
                        </div>
                    </div>

                    <div className="flex items-center gap-2">
                        <Link
                            href="/admin/settings"
                            className="rounded-xl border border-white/15 bg-white/5 px-3 py-2 text-sm font-medium text-slate-100 transition hover:bg-white/10"
                        >
                            {t('common.settings')}
                        </Link>
                        <button
                            type="button"
                            onClick={() => router.post('/admin/logout')}
                            className="rounded-xl border border-rose-400/25 bg-rose-500/10 px-3 py-2 text-sm font-medium text-rose-200 transition hover:bg-rose-500/20"
                        >
                            {t('common.logout')}
                        </button>
                    </div>
                </div>
            </header>

            <div className="mx-auto flex max-w-[1700px]">
                <div
                    className={`fixed inset-0 z-30 bg-slate-900/70 backdrop-blur-sm transition-opacity lg:hidden ${
                        sidebarOpen ? 'opacity-100' : 'pointer-events-none opacity-0'
                    }`}
                    onClick={() => setSidebarOpen(false)}
                />

                <aside
                    className={`fixed left-0 top-16 z-40 h-[calc(100vh-4rem)] w-80 border-r border-white/10 bg-slate-900/95 p-4 backdrop-blur-xl transition-transform lg:static lg:h-auto lg:w-72 lg:translate-x-0 lg:bg-transparent ${
                        sidebarOpen ? 'translate-x-0' : '-translate-x-full'
                    }`}
                >
                    <div className="mb-4 rounded-2xl border border-white/10 bg-white/5 p-4">
                        <p className="text-xs uppercase tracking-[0.2em] text-slate-400">{t('common.workspace')}</p>
                        <p className="mt-1 text-sm font-semibold text-slate-200">{t('common.appName')}</p>
                    </div>

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
                                            ? 'border-cyan-400/40 bg-cyan-400/15 text-cyan-100'
                                            : 'border-white/10 bg-white/5 text-slate-300 hover:border-white/20 hover:bg-white/10 hover:text-slate-100'
                                    }`}
                                >
                                    <span
                                        className={`grid h-7 w-7 place-items-center rounded-lg ${
                                            active
                                                ? 'bg-cyan-300 text-slate-950'
                                                : 'bg-slate-800 text-slate-300 group-hover:bg-slate-700'
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

                <main className="relative z-10 w-full p-4 md:p-6 lg:p-8">
                    {flash.success && (
                        <div className="mb-4 rounded-2xl border border-emerald-400/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                            {flash.success}
                        </div>
                    )}
                    {flash.error && (
                        <div className="mb-4 rounded-2xl border border-rose-400/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                            {flash.error}
                        </div>
                    )}
                    {children}
                </main>
            </div>
        </div>
    );
}
