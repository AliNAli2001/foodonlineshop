import React, { useMemo, useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { useI18n } from '../i18n';

const navItems = [
    { key: 'dashboard', href: '/admin/dashboard', icon: 'DB' },
    { key: 'statistics', href: '/admin/statistics', icon: 'ST' },
    { key: 'orders', href: '/admin/orders', icon: 'OR' },
    { key: 'products', href: '/admin/products', icon: 'PR' },
    { key: 'inventory', href: '/admin/inventory', icon: 'IN' },
    { key: 'categories', href: '/admin/categories', icon: 'CA' },
    { key: 'companies', href: '/admin/companies', icon: 'CO' },
    { key: 'tags', href: '/admin/tags', icon: 'TG' },
    { key: 'damagedGoods', href: '/admin/damaged-goods', icon: 'DG' },
    { key: 'adjustments', href: '/admin/adjustments', icon: 'AD' },
    { key: 'delivery', href: '/admin/delivery', icon: 'DV' },
];

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
                                        className={`grid h-7 w-7 place-items-center rounded-lg text-[10px] font-bold ${
                                            active
                                                ? 'bg-cyan-300 text-slate-950'
                                                : 'bg-slate-800 text-slate-300 group-hover:bg-slate-700'
                                        }`}
                                    >
                                        {item.icon}
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
