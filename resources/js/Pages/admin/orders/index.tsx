import React, { FormEvent, useEffect, useMemo, useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { useI18n } from '../../../i18n';
import { statusBadge } from '../../../constants/statusBadge';

const orderStatuses = ['pending', 'confirmed', 'shipped', 'delivered', 'done', 'canceled', 'returned', 'rejected'] as const;

function formatDateTime(value?: string | null): string {
    if (!value) return '-';
    const normalized = value.replace(/(\.\d{3})\d+Z$/, '$1Z');
    const date = new Date(normalized);
    if (Number.isNaN(date.getTime())) return value;
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'numeric',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        second: '2-digit',
        hour12: true,
    });
}

export default function OrdersIndex() {
    const { t, isRtl } = useI18n();
    const { orders, statusSummary = {}, filters = {} } = usePage<any>().props;
    const list = orders?.data ?? [];
    const [summaryOpen, setSummaryOpen] = useState(false);
    const [filtersOpen, setFiltersOpen] = useState(false);
    const [status, setStatus] = useState(filters.status ?? '');
    const [minPrice, setMinPrice] = useState(filters.min_price ?? '');
    const [maxPrice, setMaxPrice] = useState(filters.max_price ?? '');
    const [totalPrice, setTotalPrice] = useState(filters.total_price ?? '');
    const [startDate, setStartDate] = useState(filters.start_date ?? '');
    const [endDate, setEndDate] = useState(filters.end_date ?? '');

    useEffect(() => {
        setStatus(filters.status ?? '');
        setMinPrice(filters.min_price ?? '');
        setMaxPrice(filters.max_price ?? '');
        setTotalPrice(filters.total_price ?? '');
        setStartDate(filters.start_date ?? '');
        setEndDate(filters.end_date ?? '');
    }, [filters.status, filters.min_price, filters.max_price, filters.total_price, filters.start_date, filters.end_date]);

    const hasActiveFilters = useMemo(
        () =>
            Boolean(
                (filters.status ?? '').trim() ||
                    (filters.min_price ?? '').trim() ||
                    (filters.max_price ?? '').trim() ||
                    (filters.total_price ?? '').trim() ||
                    (filters.start_date ?? '').trim() ||
                    (filters.end_date ?? '').trim(),
            ),
        [filters.status, filters.min_price, filters.max_price, filters.total_price, filters.start_date, filters.end_date],
    );

    const sourceLabel = (source: string) => {
        if (source === 'inside_city') return t('admin.pages.orders.index.sourceInside');
        if (source === 'outside_city') return t('admin.pages.orders.index.sourceOutside');
        return source || '-';
    };

    const statusLabel = (status: string) => {
        const key = `admin.pages.orders.status.${status}`;
        return t(key, status || '-');
    };

    const customerName = (order: any) => {
        if (order.client_id) {
            return `${order.client?.first_name ?? ''} ${order.client?.last_name ?? ''}`.trim() || '-';
        }
        return order.client_name || '-';
    };

    const applyFilters = (e: FormEvent) => {
        e.preventDefault();
        router.get(
            '/admin/orders',
            {
                status: status || undefined,
                min_price: minPrice || undefined,
                max_price: maxPrice || undefined,
                total_price: totalPrice || undefined,
                start_date: startDate || undefined,
                end_date: endDate || undefined,
            },
            { preserveState: true, replace: true },
        );
    };

    const clearFilters = () => {
        setStatus('');
        setMinPrice('');
        setMaxPrice('');
        setTotalPrice('');
        setStartDate('');
        setEndDate('');
        router.get('/admin/orders', {}, { preserveState: true, replace: true });
    };

    return (
        <AdminLayout title={t('admin.pages.orders.index.title')}>
            <div className="mx-auto max-w-7xl space-y-6">
                <section className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                    <div>
                        <h1 className="text-2xl font-bold text-white">{t('admin.pages.orders.index.title')}</h1>
                        <p className="text-sm text-slate-300">{t('admin.pages.orders.index.subtitle')}</p>
                    </div>
                    <div className="flex items-center gap-2">
                        <Link
                            href="/admin/orders/work"
                            className="rounded-xl border border-white/15 bg-white/5 px-4 py-2 text-sm font-medium text-slate-100 transition hover:bg-white/10"
                        >
                            {t('admin.pages.orders.work.title', 'Orders Work')}
                        </Link>
                        <button
                            type="button"
                            onClick={() =>
                                router.reload({
                                    only: ['orders', 'statusSummary', 'filters'],
                                })
                            }
                            className="rounded-xl border border-white/15 bg-white/5 px-4 py-2 text-sm font-medium text-slate-100 transition hover:bg-white/10"
                        >
                            {t('admin.pages.orders.index.refresh', 'Refresh')}
                        </button>
                        <Link
                            href="/admin/orders/create"
                            className="rounded-xl bg-cyan-400 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-cyan-300"
                        >
                            + {t('admin.pages.orders.create.title')}
                        </Link>
                    </div>
                </section>

                <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
                    <div className="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <h2 className="text-sm font-semibold text-white">{t('admin.pages.orders.index.cards.title', 'Order Status Summary')}</h2>
                            <p className="text-xs text-slate-400">{t('admin.pages.orders.index.cards.subtitle', 'Counts and totals by status.')}</p>
                        </div>
                        <button
                            type="button"
                            onClick={() => setSummaryOpen((prev) => !prev)}
                            className="rounded-lg border border-white/15 bg-white/5 px-3 py-1.5 text-xs font-medium text-slate-200 transition hover:bg-white/10"
                        >
                            {summaryOpen ? t('admin.pages.orders.index.filters.collapse') : t('admin.pages.orders.index.filters.expand')}
                        </button>
                    </div>

                    {summaryOpen && (
                        <div className="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                            {orderStatuses.map((currentStatus) => {
                                const summary = statusSummary?.[currentStatus] ?? { count: 0, total: 0 };
                                return (
                                    <article key={currentStatus} className="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
                                        <p className="text-xs uppercase tracking-[0.14em] text-slate-400">{statusLabel(currentStatus)}</p>
                                        <p className="mt-2 text-2xl font-bold text-white">{summary.count}</p>
                                        <p className="mt-1 text-sm text-slate-300">
                                            {t('admin.pages.orders.index.cards.total')}: ${Number(summary.total ?? 0).toFixed(2)}
                                        </p>
                                    </article>
                                );
                            })}
                        </div>
                    )}
                </section>

                <button
                    type="button"
                    onClick={() => setFiltersOpen((prev) => !prev)}
                    className={`fixed top-1/2 z-[60] -translate-y-1/2 rounded-full border border-cyan-300/60 bg-white/95 p-3 text-cyan-700 shadow-lg backdrop-blur transition hover:bg-cyan-50 dark:border-cyan-300/30 dark:bg-slate-900/95 dark:text-cyan-200 dark:hover:bg-slate-800 ${
                        isRtl ? 'left-3' : 'right-3'
                    }`}
                    aria-label={filtersOpen ? t('admin.pages.orders.index.filters.collapse') : t('admin.pages.orders.index.filters.expand')}
                    title={filtersOpen ? t('admin.pages.orders.index.filters.collapse') : t('admin.pages.orders.index.filters.expand')}
                >
                    {filtersOpen ? (
                        <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    ) : (
                        <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                        </svg>
                    )}
                </button>
                {hasActiveFilters && (
                    <button
                        type="button"
                        onClick={clearFilters}
                        className={`fixed top-[calc(50%+3.75rem)] z-[60] rounded-xl border border-rose-300/50 bg-white/95 px-3 py-2 text-xs font-medium text-rose-700 shadow-lg transition hover:bg-rose-50 dark:border-rose-300/30 dark:bg-rose-500/10 dark:text-rose-200 dark:hover:bg-rose-500/20 ${
                            isRtl ? 'left-3' : 'right-3'
                        }`}
                    >
                        {t('admin.pages.orders.index.filters.clear')}
                    </button>
                )}

                <div
                    className={`fixed inset-0 z-40 bg-slate-950/40 transition-opacity duration-300 dark:bg-slate-950/65 ${
                        filtersOpen ? 'opacity-100' : 'pointer-events-none opacity-0'
                    }`}
                    onClick={() => setFiltersOpen(false)}
                />
                <aside
                    className={`fixed inset-y-0 z-50 w-full max-w-md border-slate-200/90 bg-white/95 p-5 shadow-2xl backdrop-blur transition-transform duration-300 dark:border-white/10 dark:bg-slate-900/95 sm:w-[28rem] ${
                        isRtl ? 'left-0 border-r' : 'right-0 border-l'
                    } ${filtersOpen ? 'translate-x-0' : isRtl ? '-translate-x-full' : 'translate-x-full'}`}
                >
                    <form onSubmit={applyFilters} className="flex h-full flex-col">
                        <div className="flex items-center justify-between gap-2 border-b border-slate-200 pb-3 dark:border-white/10">
                            <div>
                                <h3 className="text-base font-semibold text-slate-900 dark:text-white">{t('admin.pages.orders.index.filters.title')}</h3>
                                <p className="text-xs text-slate-500 dark:text-slate-400">{t('admin.pages.orders.index.filters.subtitle')}</p>
                            </div>
                            <button
                                type="button"
                                onClick={() => setFiltersOpen(false)}
                                className="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-50 dark:border-white/15 dark:bg-white/5 dark:text-slate-200 dark:hover:bg-white/10"
                            >
                                {t('common.close', 'Close')}
                            </button>
                        </div>

                        <div className="mt-4 grid gap-3">
                            <label className="block">
                                <span className="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">{t('common.status')}</span>
                                <select
                                    value={status}
                                    onChange={(e) => setStatus(e.target.value)}
                                    className="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-cyan-400/60 dark:border-white/15 dark:bg-slate-900/70 dark:text-white dark:focus:border-cyan-300/40"
                                >
                                    <option value="">{t('admin.pages.orders.index.filters.allStatuses')}</option>
                                    {orderStatuses.map((s) => (
                                        <option key={s} value={s}>
                                            {statusLabel(s)}
                                        </option>
                                    ))}
                                </select>
                            </label>

                            <label className="block">
                                <span className="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">{t('admin.pages.orders.index.filters.startDate')}</span>
                                <input
                                    type="date"
                                    value={startDate}
                                    onChange={(e) => setStartDate(e.target.value)}
                                    className="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-cyan-400/60 dark:border-white/15 dark:bg-slate-900/70 dark:text-white dark:focus:border-cyan-300/40"
                                />
                            </label>

                            <label className="block">
                                <span className="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">{t('admin.pages.orders.index.filters.endDate')}</span>
                                <input
                                    type="date"
                                    value={endDate}
                                    onChange={(e) => setEndDate(e.target.value)}
                                    className="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-cyan-400/60 dark:border-white/15 dark:bg-slate-900/70 dark:text-white dark:focus:border-cyan-300/40"
                                />
                            </label>

                            <label className="block">
                                <span className="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">{t('admin.pages.orders.index.filters.minPrice')}</span>
                                <input
                                    type="number"
                                    step="0.01"
                                    value={minPrice}
                                    onChange={(e) => setMinPrice(e.target.value)}
                                    className="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-cyan-400/60 dark:border-white/15 dark:bg-slate-900/70 dark:text-white dark:focus:border-cyan-300/40"
                                />
                            </label>

                            <label className="block">
                                <span className="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">{t('admin.pages.orders.index.filters.maxPrice')}</span>
                                <input
                                    type="number"
                                    step="0.01"
                                    value={maxPrice}
                                    onChange={(e) => setMaxPrice(e.target.value)}
                                    className="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-cyan-400/60 dark:border-white/15 dark:bg-slate-900/70 dark:text-white dark:focus:border-cyan-300/40"
                                />
                            </label>

                            <label className="block">
                                <span className="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">{t('admin.pages.orders.index.filters.totalPrice')}</span>
                                <input
                                    type="number"
                                    step="0.01"
                                    value={totalPrice}
                                    onChange={(e) => setTotalPrice(e.target.value)}
                                    className="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-cyan-400/60 dark:border-white/15 dark:bg-slate-900/70 dark:text-white dark:focus:border-cyan-300/40"
                                />
                            </label>
                        </div>

                        <div className={`mt-auto flex gap-2 border-t border-slate-200 pt-4 dark:border-white/10 ${isRtl ? 'flex-row-reverse' : ''}`}>
                            <button
                                type="button"
                                onClick={clearFilters}
                                className="w-full rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-white/15 dark:bg-white/5 dark:text-slate-100 dark:hover:bg-white/10"
                            >
                                {t('admin.pages.orders.index.filters.clear')}
                            </button>
                            <button type="submit" className="w-full rounded-xl bg-cyan-400 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-cyan-300">
                                {t('admin.pages.orders.index.filters.apply')}
                            </button>
                        </div>
                    </form>
                </aside>

                <section className="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.04]">
                    <div className="overflow-x-auto">
                        <table className="min-w-full">
                            <thead className="bg-white/[0.03]">
                                <tr>
                                    {[
                                        t('admin.pages.orders.index.columns.order'),
                                        t('admin.pages.orders.index.columns.source'),
                                        t('admin.pages.orders.index.columns.type'),
                                        t('admin.pages.orders.index.columns.customer'),
                                        t('admin.pages.orders.index.columns.total'),
                                        t('common.status'),
                                        t('admin.pages.orders.index.columns.date'),
                                        t('common.actions'),
                                    ].map((h) => (
                                        <th key={h} className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                            {h}
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody>
                                {list.length === 0 ? (
                                    <tr>
                                        <td colSpan={8} className="px-4 py-10 text-center text-sm text-slate-400">
                                            {t('admin.pages.orders.index.empty')}
                                        </td>
                                    </tr>
                                ) : (
                                    list.map((order: any) => (
                                        <tr key={order.id} className="border-t border-white/10">
                                            <td className="px-4 py-3 text-sm text-slate-100">#{order.id}</td>
                                            <td className="px-4 py-3 text-sm text-slate-200">{sourceLabel(order.order_source)}</td>
                                            <td className="px-4 py-3 text-sm text-slate-200">{order.client_id ? t('admin.pages.orders.index.typeClient') : t('admin.pages.orders.index.typeManual')}</td>
                                            <td className="px-4 py-3 text-sm text-slate-200">{customerName(order)}</td>
                                            <td className="px-4 py-3 text-sm text-slate-200">${Number(order.total_amount ?? 0).toFixed(2)}</td>
                                            <td className="px-4 py-3 text-sm">
                                                <span className={`inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ${statusBadge[order.status] ?? 'bg-slate-300/20 text-slate-200 ring-slate-300/30'}`}>
                                                    {statusLabel(order.status)}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3 text-sm text-slate-300">{formatDateTime(order.order_date ?? order.created_at)}</td>
                                            <td className="px-4 py-3 text-sm">
                                                <Link
                                                    href={`/admin/orders/${order.id}`}
                                                    className="rounded-lg border  dark:border-cyan-300/30 border-cyan-400/40 bg-cyan-400/10 px-2.5 py-1 text-xs font-medium dark:text-cyan-200 transition hover:bg-cyan-400/20"
                                                >
                                                    {t('common.view')}
                                                </Link>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>

                    {orders?.links && (
                        <div className="flex flex-wrap gap-2 border-t border-white/10 p-4">
                            {orders.links.map((link: any, idx: number) => (
                                <Link
                                    key={`${link.label}-${idx}`}
                                    href={link.url || '#'}
                                    preserveScroll
                                    className={`rounded-lg px-3 py-1.5 text-sm ${
                                        link.active
                                            ? 'bg-cyan-400 text-slate-950'
                                            : link.url
                                            ? 'bg-white/5 text-slate-200 hover:bg-white/10'
                                            : 'cursor-not-allowed bg-white/5 text-slate-500'
                                    }`}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ))}
                        </div>
                    )}
                </section>
            </div>
        </AdminLayout>
    );
}
