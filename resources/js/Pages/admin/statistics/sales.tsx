import React from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { useI18n } from '../../../i18n';

const money = (v) => Number(v ?? 0).toFixed(2);

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

export default function StatisticsSales() {
    const { t } = useI18n();
    const { salesStats = {}, topProducts = [], dailySales = [], startDate, endDate } = usePage().props;

    return (
        <AdminLayout title={t('admin.pages.statistics.sales.title')}>
            <div className="mx-auto max-w-7xl space-y-6">
                <section className="flex items-center justify-between rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                    <div>
                        <h1 className="text-2xl font-bold text-white">{t('admin.pages.statistics.sales.title')}</h1>
                        <p className="text-sm text-slate-300">{t('admin.pages.statistics.sales.subtitle')}</p>
                    </div>
                    <Link href="/admin/statistics" className="rounded-xl border border-white/15 bg-white/5 px-3 py-2 text-sm text-slate-200 hover:bg-white/10">{t('common.back')}</Link>
                </section>

                <DateFilter action="/admin/statistics/sales" startDate={startDate} endDate={endDate} t={t} />

                <section className="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    <Stat title={t('admin.pages.statistics.sales.cards.completedOrders')} value={salesStats.total_orders ?? 0} />
                    <Stat title={t('admin.pages.statistics.sales.cards.totalRevenue')} value={`$${money(salesStats.total_revenue)}`} />
                    <Stat title={t('admin.pages.statistics.sales.cards.averageOrderValue')} value={`$${money(salesStats.average_order_value)}`} />
                    <Stat title={t('admin.pages.statistics.sales.cards.totalCost')} value={`$${money(salesStats.total_cost)}`} />
                    <Stat title={t('admin.pages.statistics.sales.cards.totalProfit')} value={`$${money(salesStats.total_profit)}`} />
                    <Stat title={t('admin.pages.statistics.sales.cards.profitMargin')} value={`${money(salesStats.profit_margin)}%`} />
                </section>

                <Table title={t('admin.pages.statistics.sales.topSellingProducts.title')} headers={['#', t('admin.pages.statistics.common.product'), t('admin.pages.statistics.common.quantity'), t('admin.pages.statistics.common.revenue')]} rows={topProducts.map((p, i) => [i + 1, p.product_name, p.total_quantity, `$${money(p.total_revenue)}`])} emptyText={t('admin.pages.statistics.common.noData')} />
                <Table title={t('admin.pages.statistics.sales.dailySales.title')} headers={[t('admin.pages.statistics.common.date'), t('admin.pages.statistics.common.orders'), t('admin.pages.statistics.common.revenue'), t('admin.pages.statistics.common.cost'), t('admin.pages.statistics.common.profit')]} rows={dailySales.map((d) => [d.date, d.orders, `$${money(d.revenue)}`, `$${money(d.cost)}`, `$${money(d.profit)}`])} emptyText={t('admin.pages.statistics.common.noData')} />
                <SimpleBarChart
                    title={t('admin.pages.statistics.sales.charts.ordersTrend', 'Orders Trend')}
                    labels={dailySales.map((d) => d.date)}
                    values={dailySales.map((d) => Number(d.orders || 0))}
                />
                <SimpleBarChart
                    title={t('admin.pages.statistics.sales.charts.revenueTrend', 'Revenue Trend')}
                    labels={dailySales.map((d) => d.date)}
                    values={dailySales.map((d) => Number(d.revenue || 0))}
                />
            </div>
        </AdminLayout>
    );
}

function Stat({ title, value }) {
    return <div className="rounded-2xl border border-white/10 bg-white/[0.04] p-4"><p className="text-sm text-slate-400">{title}</p><p className="mt-1 text-2xl font-bold text-white">{value}</p></div>;
}

function Table({ title, headers, rows, emptyText }) {
    return (
        <section className="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.04]">
            <h2 className="border-b border-white/10 px-4 py-3 text-lg font-semibold text-white">{title}</h2>
            <div className="overflow-x-auto">
                <table className="min-w-full">
                    <thead className="bg-white/[0.03]"><tr>{headers.map((h) => <th key={h} className="px-4 py-3 text-left text-xs uppercase tracking-[0.12em] text-slate-400">{h}</th>)}</tr></thead>
                    <tbody>
                        {rows.length === 0 ? <tr><td colSpan={headers.length} className="px-4 py-8 text-center text-sm text-slate-400">{emptyText}</td></tr> : rows.map((r, idx) => <tr key={idx} className="border-t border-white/10">{r.map((c, i) => <td key={i} className="px-4 py-3 text-sm text-slate-200">{c}</td>)}</tr>)}
                    </tbody>
                </table>
            </div>
        </section>
    );
}

function SimpleBarChart({ title, labels, values }) {
    const maxValue = Math.max(1, ...values.map((v) => Number(v || 0)));
    return (
        <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
            <h2 className="mb-3 text-lg font-semibold text-white">{title}</h2>
            {values.length === 0 ? (
                <p className="text-sm text-slate-400">No chart data.</p>
            ) : (
                <div className="space-y-2">
                    {values.map((value, idx) => {
                        const width = `${Math.max(2, (Number(value || 0) / maxValue) * 100)}%`;
                        return (
                            <div key={`${labels[idx]}-${idx}`} className="grid grid-cols-[150px_1fr_auto] items-center gap-2 text-xs">
                                <span className="truncate text-slate-400">{labels[idx]}</span>
                                <div className="h-2.5 rounded bg-slate-800">
                                    <div className="h-2.5 rounded bg-cyan-400" style={{ width }} />
                                </div>
                                <span className="text-slate-200">{money(value)}</span>
                            </div>
                        );
                    })}
                </div>
            )}
        </section>
    );
}
