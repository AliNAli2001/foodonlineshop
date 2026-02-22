import React, { useEffect, useMemo, useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { useI18n } from '../../../i18n';

const workStatuses = ['pending', 'confirmed', 'shipped', 'delivered'] as const;
type WorkStatus = (typeof workStatuses)[number];

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

export default function OrdersWork() {
    const { t, isRtl } = useI18n();
    const { ordersByStatus = {}, statusCounts = {}, lastUpdatedAt } = usePage<any>().props;
    const [activeTab, setActiveTab] = useState<WorkStatus>('pending');

    const rows = useMemo(() => ordersByStatus?.[activeTab] ?? [], [ordersByStatus, activeTab]);

    useEffect(() => {
        const interval = window.setInterval(() => {
            router.reload({
                only: ['ordersByStatus', 'statusCounts', 'lastUpdatedAt'],
            });
        }, 10000);

        return () => window.clearInterval(interval);
    }, []);

    const statusLabel = (status: string) => t(`admin.pages.orders.status.${status}`, status);
    const tabLabel = (status: WorkStatus) => t(`admin.pages.orders.work.tabs.${status}`, statusLabel(status));

    const customerName = (order: any) => {
        if (order.client_id) {
            return `${order.client?.first_name ?? ''} ${order.client?.last_name ?? ''}`.trim() || '-';
        }
        return order.client_name || '-';
    };

    const sourceLabel = (source: string) => {
        if (source === 'inside_city') return t('admin.pages.orders.index.sourceInside');
        if (source === 'outside_city') return t('admin.pages.orders.index.sourceOutside');
        return source || '-';
    };

    const confirmOrder = (orderId: number) => {
        router.post(`/admin/orders/${orderId}/confirm`);
    };

    const rejectOrder = (orderId: number) => {
        const reason = window.prompt(t('admin.pages.orders.show.reason'), '');
        if (!reason || reason.trim().length === 0) return;
        router.post(`/admin/orders/${orderId}/reject`, { reason });
    };

    return (
        <AdminLayout title={t('admin.pages.orders.work.title', 'Orders Work')}>
            <div className="mx-auto max-w-7xl space-y-5">
                <section className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                    <div>
                        <h1 className="text-2xl font-bold text-white">{t('admin.pages.orders.work.title', 'Orders Work')}</h1>
                        <p className="text-sm text-slate-300">
                            {t('admin.pages.orders.work.subtitle', 'Operational order queue by status, auto-refresh every 10 seconds.')}
                        </p>
                        <p className="mt-1 text-xs text-slate-400">
                            {t('admin.pages.orders.work.lastUpdated', 'Last updated')}: {formatDateTime(lastUpdatedAt)}
                        </p>
                    </div>
                    <div className="flex items-center gap-2">
                        <button
                            type="button"
                            onClick={() =>
                                router.reload({
                                    only: ['ordersByStatus', 'statusCounts', 'lastUpdatedAt'],
                                })
                            }
                            className="rounded-xl border border-white/15 bg-white/5 px-4 py-2 text-sm font-medium text-slate-100 transition hover:bg-white/10"
                        >
                            {t('admin.pages.orders.index.refresh', 'Refresh')}
                        </button>
                        <Link
                            href="/admin/orders"
                            className="rounded-xl border border-white/15 bg-white/5 px-4 py-2 text-sm font-medium text-slate-100 transition hover:bg-white/10"
                        >
                            {t('admin.pages.orders.show.backToOrders')}
                        </Link>
                    </div>
                </section>

                <section className="rounded-2xl border border-slate-200/80 bg-white/80 p-4 dark:border-white/10 dark:bg-white/[0.04]">
                    <div className={`flex flex-wrap gap-2 ${isRtl ? 'justify-end' : 'justify-start'}`}>
                        {workStatuses.map((status) => {
                            const active = status === activeTab;
                            return (
                                <button
                                    key={status}
                                    type="button"
                                    onClick={() => setActiveTab(status)}
                                    className={`inline-flex items-center gap-2 rounded-xl border px-3 py-2 text-sm font-medium transition ${
                                        active
                                            ? 'border-cyan-400/40 bg-cyan-50 text-cyan-800 dark:border-cyan-300/40 dark:bg-cyan-500/15 dark:text-cyan-100'
                                            : 'border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100 dark:border-white/10 dark:bg-white/5 dark:text-slate-200 dark:hover:bg-white/10'
                                    }`}
                                >
                                    <span>{tabLabel(status)}</span>
                                    <span className={`rounded-full px-2 py-0.5 text-xs ${active ? 'bg-cyan-300 text-slate-950 dark:bg-cyan-300 dark:text-slate-950' : 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-200'}`}>
                                        {statusCounts?.[status] ?? 0}
                                    </span>
                                </button>
                            );
                        })}
                    </div>
                </section>

                <section className="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.04]">
                    <div className="flex items-center justify-between border-b border-white/10 px-4 py-3">
                        <h2 className="text-sm font-semibold text-white">{tabLabel(activeTab)}</h2>
                        <p className="text-xs text-slate-400">
                            {t('admin.pages.orders.work.count', 'Count')}: {rows.length}
                        </p>
                    </div>

                    <div className="overflow-x-auto">
                        <table className="min-w-full">
                            <thead className="bg-white/[0.03]">
                                <tr>
                                    {[
                                        t('admin.pages.orders.index.columns.order'),
                                        t('admin.pages.orders.index.columns.customer'),
                                        t('admin.pages.orders.index.columns.source'),
                                        t('admin.pages.orders.index.columns.total'),
                                        t('admin.pages.orders.work.cost', 'Cost'),
                                        t('admin.pages.orders.work.profit', 'Profit'),
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
                                {rows.length === 0 ? (
                                    <tr>
                                        <td colSpan={8} className="px-4 py-10 text-center text-sm text-slate-400">
                                            {t('admin.pages.orders.index.empty')}
                                        </td>
                                    </tr>
                                ) : (
                                    rows.map((order: any) => (
                                        <tr key={order.id} className="border-t border-white/10">
                                            <td className="px-4 py-3 text-sm text-slate-100">#{order.id}</td>
                                            <td className="px-4 py-3 text-sm text-slate-200">{customerName(order)}</td>
                                            <td className="px-4 py-3 text-sm text-slate-200">{sourceLabel(order.order_source)}</td>
                                            <td className="px-4 py-3 text-sm text-slate-200">${Number(order.total_amount ?? 0).toFixed(2)}</td>
                                            <td className="px-4 py-3 text-sm text-slate-200">${Number(order.cost_price ?? 0).toFixed(2)}</td>
                                            <td className="px-4 py-3 text-sm text-slate-100">${Number(order.profit ?? 0).toFixed(2)}</td>
                                            <td className="px-4 py-3 text-sm text-slate-300">{formatDateTime(order.order_date ?? order.created_at)}</td>
                                            <td className="px-4 py-3 text-sm">
                                                <div className="flex flex-wrap gap-2">
                                                    {activeTab === 'pending' && (
                                                        <>
                                                            <button
                                                                type="button"
                                                                onClick={() => confirmOrder(order.id)}
                                                                className="rounded-lg bg-emerald-400 px-2.5 py-1 text-xs font-semibold text-slate-950 hover:bg-emerald-300"
                                                            >
                                                                {t('admin.pages.orders.show.confirm')}
                                                            </button>
                                                            <button
                                                                type="button"
                                                                onClick={() => rejectOrder(order.id)}
                                                                className="rounded-lg bg-rose-500 px-2.5 py-1 text-xs font-semibold text-white hover:bg-rose-400"
                                                            >
                                                                {t('admin.pages.orders.show.reject')}
                                                            </button>
                                                        </>
                                                    )}
                                                    <Link
                                                        href={`/admin/orders/${order.id}`}
                                                        className="rounded-lg border border-cyan-300/30 bg-cyan-400/10 px-2.5 py-1 text-xs font-medium text-cyan-200 transition hover:bg-cyan-400/20"
                                                    >
                                                        {t('common.view')}
                                                    </Link>
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </AdminLayout>
    );
}
