import React, { FormEvent, useEffect, useMemo, useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { useI18n } from '../../../i18n';

const statusClass: Record<string, string> = {
    pending: 'bg-amber-400/20 text-amber-200 ring-amber-300/30',
    confirmed: 'bg-sky-400/20 text-sky-200 ring-sky-300/30',
    shipped: 'bg-blue-400/20 text-blue-200 ring-blue-300/30',
    delivered: 'bg-indigo-400/20 text-indigo-200 ring-indigo-300/30',
    done: 'bg-emerald-400/20 text-emerald-200 ring-emerald-300/30',
    canceled: 'bg-rose-400/20 text-rose-200 ring-rose-300/30',
    returned: 'bg-slate-300/20 text-slate-200 ring-slate-300/30',
    rejected: 'bg-rose-400/20 text-rose-200 ring-rose-300/30',
};

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

    useEffect(() => {
        setStatus(filters.status ?? '');
        setMinPrice(filters.min_price ?? '');
        setMaxPrice(filters.max_price ?? '');
        setTotalPrice(filters.total_price ?? '');
    }, [filters.status, filters.min_price, filters.max_price, filters.total_price]);

    const hasActiveFilters = useMemo(
        () => Boolean((filters.status ?? '').trim() || (filters.min_price ?? '').trim() || (filters.max_price ?? '').trim() || (filters.total_price ?? '').trim()),
        [filters.status, filters.min_price, filters.max_price, filters.total_price],
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
            },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    const clearFilters = () => {
        setStatus('');
        setMinPrice('');
        setMaxPrice('');
        setTotalPrice('');
        router.get('/admin/orders', {}, { preserveState: true, preserveScroll: true, replace: true });
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
                        <button
                            type="button"
                            onClick={() =>
                                router.reload({
                                    preserveScroll: true,
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

                <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
                    <div className="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <h2 className="text-sm font-semibold text-white">{t('admin.pages.orders.index.filters.title')}</h2>
                            <p className="text-xs text-slate-400">{t('admin.pages.orders.index.filters.subtitle')}</p>
                        </div>
                        <div className="flex items-center gap-2">
                            <button
                                type="button"
                                onClick={clearFilters}
                                className={`rounded-lg border px-3 py-1.5 text-xs font-medium transition ${
                                    hasActiveFilters
                                        ? 'border-rose-300/30 bg-rose-500/10 text-rose-200 hover:bg-rose-500/20'
                                        : 'cursor-not-allowed border-white/10 bg-white/5 text-slate-500'
                                }`}
                                disabled={!hasActiveFilters}
                            >
                                {t('admin.pages.orders.index.filters.clear')}
                            </button>
                            <button
                                type="button"
                                onClick={() => setFiltersOpen((prev) => !prev)}
                                className="rounded-lg border border-white/15 bg-white/5 px-3 py-1.5 text-xs font-medium text-slate-200 transition hover:bg-white/10"
                            >
                                {filtersOpen ? t('admin.pages.orders.index.filters.collapse') : t('admin.pages.orders.index.filters.expand')}
                            </button>
                        </div>
                    </div>

                    {filtersOpen && (
                        <form onSubmit={applyFilters} className="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-5">
                            <label className="block">
                                <span className="mb-1 block text-xs font-medium text-slate-300">{t('common.status')}</span>
                                <select
                                    value={status}
                                    onChange={(e) => setStatus(e.target.value)}
                                    className="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white outline-none focus:border-cyan-300/40"
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
                                <span className="mb-1 block text-xs font-medium text-slate-300">{t('admin.pages.orders.index.filters.minPrice')}</span>
                                <input
                                    type="number"
                                    step="0.01"
                                    value={minPrice}
                                    onChange={(e) => setMinPrice(e.target.value)}
                                    className="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white outline-none focus:border-cyan-300/40"
                                />
                            </label>

                            <label className="block">
                                <span className="mb-1 block text-xs font-medium text-slate-300">{t('admin.pages.orders.index.filters.maxPrice')}</span>
                                <input
                                    type="number"
                                    step="0.01"
                                    value={maxPrice}
                                    onChange={(e) => setMaxPrice(e.target.value)}
                                    className="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white outline-none focus:border-cyan-300/40"
                                />
                            </label>

                            <label className="block">
                                <span className="mb-1 block text-xs font-medium text-slate-300">{t('admin.pages.orders.index.filters.totalPrice')}</span>
                                <input
                                    type="number"
                                    step="0.01"
                                    value={totalPrice}
                                    onChange={(e) => setTotalPrice(e.target.value)}
                                    className="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white outline-none focus:border-cyan-300/40"
                                />
                            </label>

                            <div className={`flex items-end ${isRtl ? 'justify-start' : 'justify-end'}`}>
                                <button
                                    type="submit"
                                    className="w-full rounded-xl bg-cyan-400 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-cyan-300 xl:w-auto"
                                >
                                    {t('admin.pages.orders.index.filters.apply')}
                                </button>
                            </div>
                        </form>
                    )}
                </section>

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
                                                <span className={`inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ${statusClass[order.status] ?? 'bg-slate-300/20 text-slate-200 ring-slate-300/30'}`}>
                                                    {statusLabel(order.status)}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3 text-sm text-slate-300">{formatDateTime(order.order_date ?? order.created_at)}</td>
                                            <td className="px-4 py-3 text-sm">
                                                <Link
                                                    href={`/admin/orders/${order.id}`}
                                                    className="rounded-lg border border-cyan-300/30 bg-cyan-400/10 px-2.5 py-1 text-xs font-medium text-cyan-200 transition hover:bg-cyan-400/20"
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
