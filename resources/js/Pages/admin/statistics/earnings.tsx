import React from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';

const money = (v) => Number(v ?? 0).toFixed(2);

function DateFilter({ action, startDate, endDate }) {
    const submit = (e) => {
        e.preventDefault();
        const form = new FormData(e.currentTarget);
        router.get(action, Object.fromEntries(form.entries()), { preserveState: true, preserveScroll: true });
    };
    return (
        <form onSubmit={submit} className="grid gap-3 rounded-2xl border border-white/10 bg-white/[0.04] p-4 md:grid-cols-3">
            <input type="date" name="start_date" defaultValue={startDate} className="rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white" />
            <input type="date" name="end_date" defaultValue={endDate} className="rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white" />
            <button className="rounded-xl bg-cyan-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-cyan-300">Filter</button>
        </form>
    );
}

function Row({ label, value, className = 'text-slate-100' }) {
    return <div className="flex items-center justify-between text-sm"><span className="text-slate-400">{label}</span><span className={className}>{value}</span></div>;
}

export default function StatisticsEarnings() {
    const { earningsStats = {}, adjustmentsStats = {}, salesStats = {}, startDate, endDate } = usePage().props;

    return (
        <AdminLayout title="Earnings & Losses">
            <div className="mx-auto max-w-7xl space-y-6">
                <section className="flex items-center justify-between rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                    <div>
                        <h1 className="text-2xl font-bold text-white">Earnings & Losses</h1>
                        <p className="text-sm text-slate-300">Consolidated view of profit and adjustments.</p>
                    </div>
                    <Link href="/admin/statistics" className="rounded-xl border border-white/15 bg-white/5 px-3 py-2 text-sm text-slate-200 hover:bg-white/10">Back</Link>
                </section>

                <DateFilter action="/admin/statistics/earnings" startDate={startDate} endDate={endDate} />

                <section className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <Card label="Profit from Sales" value={`$${money(earningsStats.profit_from_sales)}`} tone="text-emerald-300" />
                    <Card label="Gains from Adjustments" value={`$${money(earningsStats.gains_from_adjustments)}`} tone="text-sky-300" />
                    <Card label="Losses from Adjustments" value={`$${money(earningsStats.losses_from_adjustments)}`} tone="text-rose-300" />
                    <Card label="Net Earnings" value={`$${money(earningsStats.net_earnings)}`} tone={Number(earningsStats.net_earnings || 0) >= 0 ? 'text-emerald-300' : 'text-rose-300'} />
                </section>

                <section className="grid gap-4 lg:grid-cols-2">
                    <div className="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
                        <h2 className="mb-3 text-lg font-semibold text-white">Sales Breakdown</h2>
                        <div className="space-y-2">
                            <Row label="Completed Orders" value={salesStats.total_orders ?? 0} />
                            <Row label="Total Revenue" value={`$${money(salesStats.total_revenue)}`} className="text-emerald-300" />
                            <Row label="Total Cost" value={`$${money(salesStats.total_cost)}`} className="text-rose-300" />
                            <Row label="Profit from Sales" value={`$${money(salesStats.total_profit)}`} className="text-emerald-300" />
                            <Row label="Profit Margin" value={`${money(salesStats.profit_margin)}%`} />
                        </div>
                    </div>

                    <div className="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
                        <h2 className="mb-3 text-lg font-semibold text-white">Adjustments Breakdown</h2>
                        <div className="space-y-2">
                            <Row label="Gain Records" value={adjustmentsStats.gains_count ?? 0} />
                            <Row label="Total Gains" value={`$${money(adjustmentsStats.total_gains)}`} className="text-emerald-300" />
                            <Row label="Loss Records" value={adjustmentsStats.losses_count ?? 0} />
                            <Row label="Total Losses" value={`$${money(adjustmentsStats.total_losses)}`} className="text-rose-300" />
                            <Row label="Net Adjustment" value={`$${money(adjustmentsStats.net_adjustment)}`} className={Number(adjustmentsStats.net_adjustment || 0) >= 0 ? 'text-emerald-300' : 'text-rose-300'} />
                        </div>
                    </div>
                </section>

                <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                    <h2 className="text-lg font-semibold text-white">Final Net Earnings</h2>
                    <p className={`mt-2 text-4xl font-bold ${Number(earningsStats.net_earnings || 0) >= 0 ? 'text-emerald-300' : 'text-rose-300'}`}>
                        ${money(earningsStats.net_earnings)}
                    </p>
                </section>
            </div>
        </AdminLayout>
    );
}

function Card({ label, value, tone }) {
    return <div className="rounded-2xl border border-white/10 bg-white/[0.04] p-4"><p className="text-sm text-slate-400">{label}</p><p className={`mt-1 text-2xl font-bold ${tone}`}>{value}</p></div>;
}
