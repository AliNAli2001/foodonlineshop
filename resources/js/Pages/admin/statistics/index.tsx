import React from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';

function money(value) {
    return Number(value ?? 0).toFixed(2);
}

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

function StatCard({ label, value }) {
    return (
        <div className="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
            <p className="text-sm text-slate-400">{label}</p>
            <p className="mt-1 text-2xl font-bold text-white">{value}</p>
        </div>
    );
}

export default function StatisticsIndex() {
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
        <AdminLayout title="Statistics Overview">
            <div className="mx-auto max-w-7xl space-y-6">
                <section className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                    <div>
                        <h1 className="text-2xl font-bold text-white">Statistics Overview</h1>
                        <p className="text-sm text-slate-300">Revenue, cost, profit, and performance trends.</p>
                    </div>
                    <div className="flex gap-2">
                        <Link href="/admin/statistics/sales" className="rounded-xl border border-white/15 bg-white/5 px-3 py-2 text-sm text-slate-200 hover:bg-white/10">Sales</Link>
                        <Link href="/admin/statistics/earnings" className="rounded-xl border border-white/15 bg-white/5 px-3 py-2 text-sm text-slate-200 hover:bg-white/10">Earnings</Link>
                    </div>
                </section>

                <DateFilter action="/admin/statistics" startDate={startDate} endDate={endDate} />

                <section className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <StatCard label="Total Revenue" value={`$${money(summary.total_revenue)}`} />
                    <StatCard label="Total Cost" value={`$${money(summary.total_cost)}`} />
                    <StatCard label="Gross Profit" value={`$${money(summary.gross_profit)}`} />
                    <StatCard label="Net Profit" value={`$${money(summary.net_profit)}`} />
                </section>

                <section className="grid gap-4 lg:grid-cols-2">
                    <InfoTable title="Sales Stats" rows={[
                        ['Completed Orders', sales.total_orders],
                        ['Average Order Value', `$${money(sales.average_order_value)}`],
                        ['Profit Margin', `${money(sales.profit_margin)}%`],
                    ]} />

                    <InfoTable title="Adjustment Stats" rows={[
                        ['Total Gains', `$${money(adjustments.total_gains)}`],
                        ['Total Losses', `$${money(adjustments.total_losses)}`],
                        ['Net Adjustment', `$${money(adjustments.net_adjustment)}`],
                    ]} />
                </section>

                <DataTable
                    title="Top Selling Products"
                    headers={['#', 'Product', 'Quantity', 'Revenue']}
                    rows={topProducts.map((p, i) => [i + 1, p.product_name, p.total_quantity, `$${money(p.total_revenue)}`])}
                    emptyText="No data in selected period."
                />

                <DataTable
                    title="Daily Sales"
                    headers={['Date', 'Orders', 'Revenue', 'Cost', 'Profit']}
                    rows={dailySales.map((d) => [d.date, d.orders, `$${money(d.revenue)}`, `$${money(d.cost)}`, `$${money(d.profit)}`])}
                    footer={['Total', totals.orders, `$${money(totals.revenue)}`, `$${money(totals.cost)}`, `$${money(totals.profit)}`]}
                    emptyText="No data in selected period."
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
