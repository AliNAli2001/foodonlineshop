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

function Row({ label, value, className = 'text-slate-100' }) {
    return <div className="flex items-center justify-between text-sm"><span className="text-slate-400">{label}</span><span className={className}>{value}</span></div>;
}

export default function StatisticsEarnings() {
    const { t } = useI18n();
    const { earningsStats = {}, adjustmentsStats = {}, salesStats = {}, startDate, endDate } = usePage().props;

    return (
        <AdminLayout title={t('admin.pages.statistics.earnings.title')}>
            <div className="mx-auto max-w-7xl space-y-6">
                <section className="flex items-center justify-between rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                    <div>
                        <h1 className="text-2xl font-bold text-white">{t('admin.pages.statistics.earnings.title')}</h1>
                        <p className="text-sm text-slate-300">{t('admin.pages.statistics.earnings.subtitle')}</p>
                    </div>
                    <Link href="/admin/statistics" className="rounded-xl border border-white/15 bg-white/5 px-3 py-2 text-sm text-slate-200 hover:bg-white/10">{t('common.back')}</Link>
                </section>

                <DateFilter action="/admin/statistics/earnings" startDate={startDate} endDate={endDate} t={t} />

                <section className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <Card label={t('admin.pages.statistics.earnings.cards.profitFromSales')} value={`$${money(earningsStats.profit_from_sales)}`} tone="text-emerald-300" />
                    <Card label={t('admin.pages.statistics.earnings.cards.gainsFromAdjustments')} value={`$${money(earningsStats.gains_from_adjustments)}`} tone="text-sky-300" />
                    <Card label={t('admin.pages.statistics.earnings.cards.lossesFromAdjustments')} value={`$${money(earningsStats.losses_from_adjustments)}`} tone="text-rose-300" />
                    <Card label={t('admin.pages.statistics.earnings.cards.netEarnings')} value={`$${money(earningsStats.net_earnings)}`} tone={Number(earningsStats.net_earnings || 0) >= 0 ? 'text-emerald-300' : 'text-rose-300'} />
                </section>

                <section className="grid gap-4 lg:grid-cols-2">
                    <div className="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
                        <h2 className="mb-3 text-lg font-semibold text-white">{t('admin.pages.statistics.earnings.salesBreakdown.title')}</h2>
                        <div className="space-y-2">
                            <Row label={t('admin.pages.statistics.sales.cards.completedOrders')} value={salesStats.total_orders ?? 0} />
                            <Row label={t('admin.pages.statistics.sales.cards.totalRevenue')} value={`$${money(salesStats.total_revenue)}`} className="text-emerald-300" />
                            <Row label={t('admin.pages.statistics.sales.cards.totalCost')} value={`$${money(salesStats.total_cost)}`} className="text-rose-300" />
                            <Row label={t('admin.pages.statistics.earnings.cards.profitFromSales')} value={`$${money(salesStats.total_profit)}`} className="text-emerald-300" />
                            <Row label={t('admin.pages.statistics.sales.cards.profitMargin')} value={`${money(salesStats.profit_margin)}%`} />
                        </div>
                    </div>

                    <div className="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
                        <h2 className="mb-3 text-lg font-semibold text-white">{t('admin.pages.statistics.earnings.adjustmentsBreakdown.title')}</h2>
                        <div className="space-y-2">
                            <Row label={t('admin.pages.statistics.earnings.adjustmentsBreakdown.gainRecords')} value={adjustmentsStats.gains_count ?? 0} />
                            <Row label={t('admin.pages.statistics.index.adjustmentStats.totalGains')} value={`$${money(adjustmentsStats.total_gains)}`} className="text-emerald-300" />
                            <Row label={t('admin.pages.statistics.earnings.adjustmentsBreakdown.lossRecords')} value={adjustmentsStats.losses_count ?? 0} />
                            <Row label={t('admin.pages.statistics.index.adjustmentStats.totalLosses')} value={`$${money(adjustmentsStats.total_losses)}`} className="text-rose-300" />
                            <Row label={t('admin.pages.statistics.index.adjustmentStats.netAdjustment')} value={`$${money(adjustmentsStats.net_adjustment)}`} className={Number(adjustmentsStats.net_adjustment || 0) >= 0 ? 'text-emerald-300' : 'text-rose-300'} />
                        </div>
                    </div>
                </section>

                <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                    <h2 className="text-lg font-semibold text-white">{t('admin.pages.statistics.earnings.finalNetEarnings')}</h2>
                    <p className={`mt-2 text-4xl font-bold ${Number(earningsStats.net_earnings || 0) >= 0 ? 'text-emerald-300' : 'text-rose-300'}`}>
                        ${money(earningsStats.net_earnings)}
                    </p>
                </section>

                <SimpleComparisonChart
                    title={t('admin.pages.statistics.earnings.charts.earningsBreakdown', 'Earnings Breakdown')}
                    rows={[
                        { label: t('admin.pages.statistics.earnings.cards.profitFromSales'), value: Number(earningsStats.profit_from_sales || 0), tone: 'bg-emerald-400' },
                        { label: t('admin.pages.statistics.earnings.cards.gainsFromAdjustments'), value: Number(earningsStats.gains_from_adjustments || 0), tone: 'bg-sky-400' },
                        { label: t('admin.pages.statistics.earnings.cards.lossesFromAdjustments'), value: Number(earningsStats.losses_from_adjustments || 0), tone: 'bg-rose-400' },
                        { label: t('admin.pages.statistics.earnings.cards.netEarnings'), value: Number(earningsStats.net_earnings || 0), tone: 'bg-cyan-400' },
                    ]}
                />
            </div>
        </AdminLayout>
    );
}

function Card({ label, value, tone }) {
    return <div className="rounded-2xl border border-white/10 bg-white/[0.04] p-4"><p className="text-sm text-slate-400">{label}</p><p className={`mt-1 text-2xl font-bold ${tone}`}>{value}</p></div>;
}

function SimpleComparisonChart({ title, rows }) {
    const maxValue = Math.max(1, ...rows.map((r) => Math.abs(Number(r.value || 0))));
    return (
        <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
            <h2 className="mb-3 text-lg font-semibold text-white">{title}</h2>
            <div className="space-y-2">
                {rows.map((row, idx) => {
                    const width = `${Math.max(2, (Math.abs(Number(row.value || 0)) / maxValue) * 100)}%`;
                    return (
                        <div key={`${row.label}-${idx}`} className="grid grid-cols-[220px_1fr_auto] items-center gap-2 text-xs">
                            <span className="truncate text-slate-400">{row.label}</span>
                            <div className="h-2.5 rounded bg-slate-800">
                                <div className={`h-2.5 rounded ${row.tone}`} style={{ width }} />
                            </div>
                            <span className="text-slate-200">{money(row.value)}</span>
                        </div>
                    );
                })}
            </div>
        </section>
    );
}
