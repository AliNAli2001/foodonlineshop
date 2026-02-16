import React from 'react';
import { Link, usePage } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import { useI18n } from '../../i18n';

const statusBadge = {
    pending: 'bg-amber-400/20 text-amber-200 ring-amber-300/30',
    confirmed: 'bg-sky-400/20 text-sky-200 ring-sky-300/30',
    shipped: 'bg-blue-400/20 text-blue-200 ring-blue-300/30',
    delivered: 'bg-indigo-400/20 text-indigo-200 ring-indigo-300/30',
    done: 'bg-emerald-400/20 text-emerald-200 ring-emerald-300/30',
    canceled: 'bg-rose-400/20 text-rose-200 ring-rose-300/30',
    returned: 'bg-slate-300/20 text-slate-200 ring-slate-300/30',
};

function statusLabel(status, t) {
    if (!status) return '-';
    const map = {
        pending: 'admin.pages.dashboard.status.pending',
        confirmed: 'admin.pages.dashboard.status.confirmed',
        shipped: 'admin.pages.dashboard.status.shipped',
        delivered: 'admin.pages.dashboard.status.delivered',
        done: 'admin.pages.dashboard.status.done',
        canceled: 'admin.pages.dashboard.status.canceled',
        returned: 'admin.pages.dashboard.status.returned',
    };
    return t(map[status] || '', status.replaceAll('_', ' ').replace(/\b\w/g, (c) => c.toUpperCase()));
}

function StatCard({ title, value, hint, tone }) {
    return (
        <article className="rounded-2xl border border-white/10 bg-white/[0.04] p-5 backdrop-blur-xl">
            <div className="mb-4 flex items-start justify-between gap-3">
                <p className="text-sm font-medium text-slate-300">{title}</p>
                <span className={`h-2.5 w-2.5 rounded-full ${tone}`} />
            </div>
            <p className="text-3xl font-bold tracking-tight text-white">{value ?? 0}</p>
            <p className="mt-2 text-xs text-slate-400">{hint}</p>
        </article>
    );
}

function Card({ title, action, children }) {
    return (
        <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-5 backdrop-blur-xl">
            <div className="mb-4 flex items-center justify-between gap-3">
                <h2 className="text-lg font-semibold text-white">{title}</h2>
                {action}
            </div>
            {children}
        </section>
    );
}

function TableHead({ children, end = false, isRtl = false }) {
    return (
        <th
            className={`pb-3 text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400 ${
                isRtl ? 'text-right' : 'text-left'
            } ${end ? '' : isRtl ? 'pl-4' : 'pr-4'}`}
        >
            {children}
        </th>
    );
}

function TableCell({ children, end = false, isRtl = false }) {
    return (
        <td className={`py-3 text-sm text-slate-200 ${isRtl ? 'text-right' : 'text-left'} ${end ? '' : isRtl ? 'pl-4' : 'pr-4'}`}>
            {children}
        </td>
    );
}

export default function Dashboard() {
    const { t, isRtl } = useI18n();
    const {
        pendingOrders = 0,
        confirmedOrders = 0,
        lowStockProductsCount = 0,
        totalClients = 0,
        lowStockProducts = [],
        recentOrders = [],
        expiredSoonInventories = [],
    } = usePage().props;

    return (
        <AdminLayout title={t('admin.pages.dashboard.title')}>
            <div className="mx-auto max-w-7xl space-y-6">
                <section className="relative overflow-hidden rounded-3xl border border-white/10 bg-gradient-to-br from-slate-900/80 via-slate-900/50 to-cyan-950/40 p-6 md:p-8">
                    <div className="pointer-events-none absolute -right-16 -top-20 h-52 w-52 rounded-full bg-cyan-400/20 blur-3xl" />
                    <div className="pointer-events-none absolute -bottom-20 left-1/3 h-52 w-52 rounded-full bg-blue-500/20 blur-3xl" />

                    <div className="relative z-10 flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <p className="text-xs font-semibold uppercase tracking-[0.16em] text-cyan-200">{t('admin.pages.dashboard.hero.overview')}</p>
                            <h1 className="mt-2 text-3xl font-bold tracking-tight text-white md:text-4xl">{t('admin.pages.dashboard.hero.heading')}</h1>
                            <p className="mt-2 text-sm text-slate-300">{t('admin.pages.dashboard.hero.description')}</p>
                        </div>
                        <div className="flex gap-2">
                            <Link
                                href="/admin/orders"
                                className="rounded-xl border border-white/15 bg-white/5 px-4 py-2 text-sm font-medium text-slate-100 transition hover:bg-white/10"
                            >
                                {t('admin.pages.dashboard.hero.manageOrders')}
                            </Link>
                            <Link
                                href="/admin/inventory"
                                className="rounded-xl bg-cyan-400 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-cyan-300"
                            >
                                {t('admin.pages.dashboard.hero.openInventory')}
                            </Link>
                        </div>
                    </div>
                </section>

                <section className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <StatCard title={t('admin.pages.dashboard.stats.pendingOrders.title')} value={pendingOrders} hint={t('admin.pages.dashboard.stats.pendingOrders.hint')} tone="bg-amber-300" />
                    <StatCard title={t('admin.pages.dashboard.stats.confirmedOrders.title')} value={confirmedOrders} hint={t('admin.pages.dashboard.stats.confirmedOrders.hint')} tone="bg-sky-300" />
                    <StatCard title={t('admin.pages.dashboard.stats.lowStockAlerts.title')} value={lowStockProductsCount} hint={t('admin.pages.dashboard.stats.lowStockAlerts.hint')} tone="bg-rose-300" />
                    <StatCard title={t('admin.pages.dashboard.stats.totalClients.title')} value={totalClients} hint={t('admin.pages.dashboard.stats.totalClients.hint')} tone="bg-emerald-300" />
                </section>

                <section className="grid gap-4 xl:grid-cols-2">
                    <Card title={t('admin.pages.dashboard.lowStock.title')}>
                        {lowStockProducts.length === 0 ? (
                            <p className="rounded-xl border border-dashed border-white/15 bg-white/[0.03] p-4 text-sm text-slate-400">{t('admin.pages.dashboard.lowStock.empty')}</p>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="min-w-full">
                                    <thead>
                                        <tr>
                                            <TableHead isRtl={isRtl}>{t('admin.pages.dashboard.table.product')}</TableHead>
                                            <TableHead isRtl={isRtl}>{t('admin.pages.dashboard.table.available')}</TableHead>
                                            <TableHead isRtl={isRtl}>{t('admin.pages.dashboard.table.alert')}</TableHead>
                                            <TableHead end isRtl={isRtl}>{t('common.actions')}</TableHead>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {lowStockProducts.map((product) => (
                                            <tr key={product.id} className="border-t border-white/10">
                                                <TableCell isRtl={isRtl}>{product.name_en}</TableCell>
                                                <TableCell isRtl={isRtl}>{product.stock_available_quantity}</TableCell>
                                                <TableCell isRtl={isRtl}>{product.minimum_alert_quantity}</TableCell>
                                                <TableCell end isRtl={isRtl}>
                                                    <Link
                                                        href={`/admin/inventory/${product.id}/batches`}
                                                        className="rounded-lg border border-cyan-300/30 bg-cyan-400/10 px-2.5 py-1 text-xs font-medium text-cyan-200 transition hover:bg-cyan-400/20"
                                                    >
                                                        {t('common.view')}
                                                    </Link>
                                                </TableCell>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </Card>

                    <Card title={t('admin.pages.dashboard.expiringSoon.title')}>
                        {expiredSoonInventories.length === 0 ? (
                            <p className="rounded-xl border border-dashed border-white/15 bg-white/[0.03] p-4 text-sm text-slate-400">{t('admin.pages.dashboard.expiringSoon.empty')}</p>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="min-w-full">
                                    <thead>
                                        <tr>
                                            <TableHead isRtl={isRtl}>{t('admin.pages.dashboard.table.product')}</TableHead>
                                            <TableHead isRtl={isRtl}>{t('admin.pages.dashboard.table.batch')}</TableHead>
                                            <TableHead isRtl={isRtl}>{t('admin.pages.dashboard.table.qty')}</TableHead>
                                            <TableHead isRtl={isRtl}>{t('admin.pages.dashboard.table.expiry')}</TableHead>
                                            <TableHead end isRtl={isRtl}>{t('common.actions')}</TableHead>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {expiredSoonInventories.map((batch) => (
                                            <tr key={batch.id} className="border-t border-white/10">
                                                <TableCell isRtl={isRtl}>{batch.product?.name_en ?? '-'}</TableCell>
                                                <TableCell isRtl={isRtl}>{batch.batch_number}</TableCell>
                                                <TableCell isRtl={isRtl}>{batch.available_quantity}</TableCell>
                                                <TableCell isRtl={isRtl}>{batch.expiry_date}</TableCell>
                                                <TableCell end isRtl={isRtl}>
                                                    <Link
                                                        href={`/admin/inventory/${batch.product_id}/batches`}
                                                        className="rounded-lg border border-cyan-300/30 bg-cyan-400/10 px-2.5 py-1 text-xs font-medium text-cyan-200 transition hover:bg-cyan-400/20"
                                                    >
                                                        {t('common.view')}
                                                    </Link>
                                                </TableCell>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </Card>
                </section>

                <Card title={t('admin.pages.dashboard.recentOrders.title')} action={<Link href="/admin/orders" className="text-sm font-medium text-cyan-200 hover:text-cyan-100">{t('admin.pages.dashboard.recentOrders.allOrders')}</Link>}>
                    {recentOrders.length === 0 ? (
                        <p className="rounded-xl border border-dashed border-white/15 bg-white/[0.03] p-4 text-sm text-slate-400">{t('admin.pages.dashboard.recentOrders.empty')}</p>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="min-w-full">
                                <thead>
                                    <tr>
                                        <TableHead isRtl={isRtl}>{t('admin.pages.dashboard.table.order')}</TableHead>
                                        <TableHead isRtl={isRtl}>{t('admin.pages.dashboard.table.type')}</TableHead>
                                        <TableHead isRtl={isRtl}>{t('admin.pages.dashboard.table.customer')}</TableHead>
                                        <TableHead isRtl={isRtl}>{t('admin.pages.dashboard.table.total')}</TableHead>
                                        <TableHead isRtl={isRtl}>{t('common.status')}</TableHead>
                                        <TableHead end isRtl={isRtl}>{t('common.actions')}</TableHead>
                                    </tr>
                                </thead>
                                <tbody>
                                    {recentOrders.map((order) => (
                                        <tr key={order.id} className="border-t border-white/10">
                                            <TableCell isRtl={isRtl}>#{order.id}</TableCell>
                                            <TableCell isRtl={isRtl}>{order.client_id ? t('admin.pages.dashboard.recentOrders.typeClient') : t('admin.pages.dashboard.recentOrders.typeManual')}</TableCell>
                                            <TableCell isRtl={isRtl}>
                                                {order.client_id
                                                    ? `${order.client?.first_name ?? ''} ${order.client?.last_name ?? ''}`.trim()
                                                    : order.client_name}
                                            </TableCell>
                                            <TableCell isRtl={isRtl}>${Number(order.total_amount ?? 0).toFixed(2)}</TableCell>
                                            <TableCell isRtl={isRtl}>
                                                <span
                                                    className={`inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ${
                                                        statusBadge[order.status] ?? 'bg-slate-300/20 text-slate-200 ring-slate-300/30'
                                                    }`}
                                                >
                                                    {statusLabel(order.status, t)}
                                                </span>
                                            </TableCell>
                                            <TableCell end isRtl={isRtl}>
                                                <Link
                                                    href={`/admin/orders/${order.id}`}
                                                    className="rounded-lg border border-cyan-300/30 bg-cyan-400/10 px-2.5 py-1 text-xs font-medium text-cyan-200 transition hover:bg-cyan-400/20"
                                                >
                                                    {t('common.view')}
                                                </Link>
                                            </TableCell>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                </Card>
            </div>
        </AdminLayout>
    );
}
