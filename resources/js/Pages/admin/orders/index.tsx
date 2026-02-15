import React from 'react';
import { Link, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { useI18n } from '../../../i18n';

const statusClass = {
    pending: 'bg-amber-400/20 text-amber-200 ring-amber-300/30',
    confirmed: 'bg-sky-400/20 text-sky-200 ring-sky-300/30',
    shipped: 'bg-blue-400/20 text-blue-200 ring-blue-300/30',
    delivered: 'bg-indigo-400/20 text-indigo-200 ring-indigo-300/30',
    done: 'bg-emerald-400/20 text-emerald-200 ring-emerald-300/30',
    canceled: 'bg-rose-400/20 text-rose-200 ring-rose-300/30',
    returned: 'bg-slate-300/20 text-slate-200 ring-slate-300/30',
    rejected: 'bg-rose-400/20 text-rose-200 ring-rose-300/30',
};

const sourceLabel = {
    inside_city: 'Inside City | ???? ???????',
    outside_city: 'Outside City | ???? ???????',
};

function statusLabel(status) {
    if (!status) return '-';
    return status.replaceAll('_', ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

function customerName(order) {
    if (order.client_id) {
        return `${order.client?.first_name ?? ''} ${order.client?.last_name ?? ''}`.trim() || '-';
    }
    return order.client_name || '-';
}

export default function OrdersIndex() {
  const { t } = useI18n();
    const { orders } = usePage().props;
    const list = orders?.data ?? [];

    return (
        <AdminLayout title={t('admin.pages.orders.index.title')}>
            <div className="mx-auto max-w-7xl space-y-6">
                <section className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                    <div>
                        <h1 className="text-2xl font-bold text-white">Orders | ???????</h1>
                        <p className="text-sm text-slate-300">Manage and track order lifecycle.</p>
                    </div>
                    <Link
                        href="/admin/orders/create"
                        className="rounded-xl bg-cyan-400 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-cyan-300"
                    >
                        + Create Order | ????? ???
                    </Link>
                </section>

                <section className="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.04]">
                    <div className="overflow-x-auto">
                        <table className="min-w-full">
                            <thead className="bg-white/[0.03]">
                                <tr>
                                    {['Order', 'Source', 'Type', 'Customer', 'Total', 'Status', 'Date', 'Action'].map((h) => (
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
                                            No orders yet | ?? ???? ?????.
                                        </td>
                                    </tr>
                                ) : (
                                    list.map((order) => (
                                        <tr key={order.id} className="border-t border-white/10">
                                            <td className="px-4 py-3 text-sm text-slate-100">#{order.id}</td>
                                            <td className="px-4 py-3 text-sm text-slate-200">{sourceLabel[order.order_source] ?? order.order_source}</td>
                                            <td className="px-4 py-3 text-sm text-slate-200">{order.client_id ? 'Client | ????' : 'Manual | ????'}</td>
                                            <td className="px-4 py-3 text-sm text-slate-200">{customerName(order)}</td>
                                            <td className="px-4 py-3 text-sm text-slate-200">${Number(order.total_amount ?? 0).toFixed(2)}</td>
                                            <td className="px-4 py-3 text-sm">
                                                <span className={`inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ${statusClass[order.status] ?? 'bg-slate-300/20 text-slate-200 ring-slate-300/30'}`}>
                                                    {statusLabel(order.status)}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3 text-sm text-slate-300">{order.order_date ?? order.created_at ?? '-'}</td>
                                            <td className="px-4 py-3 text-sm">
                                                <Link
                                                    href={`/admin/orders/${order.id}`}
                                                    className="rounded-lg border border-cyan-300/30 bg-cyan-400/10 px-2.5 py-1 text-xs font-medium text-cyan-200 transition hover:bg-cyan-400/20"
                                                >
                                                    View | ???
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
                            {orders.links.map((link, idx) => (
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


