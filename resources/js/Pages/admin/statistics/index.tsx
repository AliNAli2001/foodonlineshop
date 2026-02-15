import React from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { useI18n } from '../../../i18n';

function money(value) {
    return Number(value ?? 0).toFixed(2);
}

function DateFilter({ action, startDate, endDate, t }) {
    const submit = (e) => {
        e.preventDefault();
        const form = new FormData(e.currentTarget);
        router.get(action, Object.fromEntries(form.entries()), { preserveState: true, preserveScroll: true });
    };

    return (
        <form onSubmit={submit} className="grid gap-3 rounded-2xl border border-white/10 bg-white/[0.04] p-4 md:grid-cols-3">
            <input type="date" name="start_date" defaultValue={startDate} className="rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white" />
            <input type="date" name="end_date" defaultValue={endDate} className="rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white" />
            <button className="rounded-xl bg-cyan-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-cyan-300">{t('admin.pages.statistics.common.filter')}</button>
        </form>
    );
}

function StatCard({ label, value }) {
    return (
        <div className="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
            <p className="text-sm text-slate-400">{label}</p>
            <p className="mt-1 text-2xl font-bold text-white">{value}</p>
        </div>
    );
}

export default function StatisticsIndex() {
    const { t } = useI18n();
    const { statistics = {}, topProducts = [], dailySales = [], startDate, endDate } = usePage().props;
    const summary = statistics.summary || {};
    const sales = statistics.sales || {};
    const adjustments = statistics.adjustments || {};

    const totals = {
        orders: dailySales.reduce((sum, d) => sum + Number(d.orders || 0), 0),
        revenue: dailySales.reduce((sum, d) => sum + Number(d.revenue || 0), 0),
        cost: dailySales.reduce((sum, d) => sum + Number(d.cost || 0), 0),
        profit: dailySales.reduce((sum, d) => sum + Number(d.profit || 0), 0),
    };

    return (
        <AdminLayout title={t('admin.pages.statistics.index.title')}>
            <div className="mx-auto max-w-7xl space-y-6">
                <section className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                    <div>
                        <h1 className="text-2xl font-bold text-white">{t('admin.pages.statistics.index.title')}</h1>
                        <p className="text-sm text-slate-300">{t('admin.pages.statistics.index.subtitle')}</p>
                    </div>
                    <div className="flex gap-2">
                        <Link href="/admin/statistics/sales" className="rounded-xl border border-white/15 bg-white/5 px-3 py-2 text-sm text-slate-200 hover:bg-white/10">{t('admin.pages.statistics.common.sales')}</Link>
                        <Link href="/admin/statistics/earnings" className="rounded-xl border border-white/15 bg-white/5 px-3 py-2 text-sm text-slate-200 hover:bg-white/10">{t('admin.pages.statistics.common.earnings')}</Link>
                    </div>
                </section>

                <DateFilter action="/admin/statistics" startDate={startDate} endDate={endDate} t={t} />

                <section className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <StatCard label={t('admin.pages.statistics.index.cards.totalRevenue')} value={`$${money(summary.total_revenue)}`} />
                    <StatCard label={t('admin.pages.statistics.index.cards.totalCost')} value={`$${money(summary.total_cost)}`} />
                    <StatCard label={t('admin.pages.statistics.index.cards.grossProfit')} value={`$${money(summary.gross_profit)}`} />
                    <StatCard label={t('admin.pages.statistics.index.cards.netProfit')} value={`$${money(summary.net_profit)}`} />
                </section>

                <section className="grid gap-4 lg:grid-cols-2">
                    <InfoTable title={t('admin.pages.statistics.index.salesStats.title')} rows={[
                        [t('admin.pages.statistics.index.salesStats.completedOrders'), sales.total_orders],
                        [t('admin.pages.statistics.index.salesStats.averageOrderValue'), `$${money(sales.average_order_value)}`],
                        [t('admin.pages.statistics.index.salesStats.profitMargin'), `${money(sales.profit_margin)}%`],
                    ]} />

                    <InfoTable title={t('admin.pages.statistics.index.adjustmentStats.title')} rows={[
                        [t('admin.pages.statistics.index.adjustmentStats.totalGains'), `$${money(adjustments.total_gains)}`],
                        [t('admin.pages.statistics.index.adjustmentStats.totalLosses'), `$${money(adjustments.total_losses)}`],
                        [t('admin.pages.statistics.index.adjustmentStats.netAdjustment'), `$${money(adjustments.net_adjustment)}`],
                    ]} />
                </section>

                <DataTable
                    title={t('admin.pages.statistics.index.topSellingProducts.title')}
                    headers={['#', t('admin.pages.statistics.common.product'), t('admin.pages.statistics.common.quantity'), t('admin.pages.statistics.common.revenue')]}
                    rows={topProducts.map((p, i) => [i + 1, p.product_name, p.total_quantity, `$${money(p.total_revenue)}`])}
                    emptyText={t('admin.pages.statistics.common.noData')}
                />

                <DataTable
                    title={t('admin.pages.statistics.index.dailySales.title')}
                    headers={[t('admin.pages.statistics.common.date'), t('admin.pages.statistics.common.orders'), t('admin.pages.statistics.common.revenue'), t('admin.pages.statistics.common.cost'), t('admin.pages.statistics.common.profit')]}
                    rows={dailySales.map((d) => [d.date, d.orders, `$${money(d.revenue)}`, `$${money(d.cost)}`, `$${money(d.profit)}`])}
                    footer={[t('admin.pages.statistics.common.total'), totals.orders, `$${money(totals.revenue)}`, `$${money(totals.cost)}`, `$${money(totals.profit)}`]}
                    emptyText={t('admin.pages.statistics.common.noData')}
                />
            </div>
        </AdminLayout>
    );
}

function InfoTable({ title, rows }) {
    return (
        <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
            <h2 className="mb-3 text-lg font-semibold text-white">{title}</h2>
            <div className="space-y-2">
                {rows.map(([k, v]) => (
                    <div key={k} className="flex items-center justify-between text-sm">
                        <span className="text-slate-400">{k}</span>
                        <span className="text-slate-100">{v}</span>
                    </div>
                ))}
            </div>
        </section>
    );
}

function DataTable({ title, headers, rows, footer, emptyText }) {
    return (
        <section className="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.04]">
            <h2 className="border-b border-white/10 px-4 py-3 text-lg font-semibold text-white">{title}</h2>
            <div className="overflow-x-auto">
                <table className="min-w-full">
                    <thead className="bg-white/[0.03]">
                        <tr>
                            {headers.map((h) => <th key={h} className="px-4 py-3 text-left text-xs uppercase tracking-[0.12em] text-slate-400">{h}</th>)}
                        </tr>
                    </thead>
                    <tbody>
                        {rows.length === 0 ? (
                            <tr><td colSpan={headers.length} className="px-4 py-8 text-center text-sm text-slate-400">{emptyText}</td></tr>
                        ) : rows.map((r, idx) => (
                            <tr key={idx} className="border-t border-white/10">
                                {r.map((cell, i) => <td key={i} className="px-4 py-3 text-sm text-slate-200">{cell}</td>)}
                            </tr>
                        ))}
                    </tbody>
                    {footer ? (
                        <tfoot className="border-t border-white/10 bg-white/[0.03]">
                            <tr>{footer.map((cell, i) => <td key={i} className="px-4 py-3 text-sm font-semibold text-slate-100">{cell}</td>)}</tr>
                        </tfoot>
                    ) : null}
                </table>
            </div>
        </section>
    );
}
