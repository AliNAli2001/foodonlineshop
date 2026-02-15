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

export default function StatisticsSales() {
    const { salesStats = {}, topProducts = [], dailySales = [], startDate, endDate } = usePage().props;

    return (
        <AdminLayout title="Sales Statistics">
            <div className="mx-auto max-w-7xl space-y-6">
                <section className="flex items-center justify-between rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                    <div>
                        <h1 className="text-2xl font-bold text-white">Sales Statistics</h1>
                        <p className="text-sm text-slate-300">Sales, revenue, and profit distribution.</p>
                    </div>
                    <Link href="/admin/statistics" className="rounded-xl border border-white/15 bg-white/5 px-3 py-2 text-sm text-slate-200 hover:bg-white/10">Back</Link>
                </section>

                <DateFilter action="/admin/statistics/sales" startDate={startDate} endDate={endDate} />

                <section className="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    <Stat title="Completed Orders" value={salesStats.total_orders ?? 0} />
                    <Stat title="Total Revenue" value={`$${money(salesStats.total_revenue)}`} />
                    <Stat title="Average Order Value" value={`$${money(salesStats.average_order_value)}`} />
                    <Stat title="Total Cost" value={`$${money(salesStats.total_cost)}`} />
                    <Stat title="Total Profit" value={`$${money(salesStats.total_profit)}`} />
                    <Stat title="Profit Margin" value={`${money(salesStats.profit_margin)}%`} />
                </section>

                <Table title="Top Selling Products" headers={['#', 'Product', 'Quantity', 'Revenue']} rows={topProducts.map((p, i) => [i + 1, p.product_name, p.total_quantity, `$${money(p.total_revenue)}`])} emptyText="No data for selected period." />
                <Table title="Daily Sales" headers={['Date', 'Orders', 'Revenue', 'Cost', 'Profit']} rows={dailySales.map((d) => [d.date, d.orders, `$${money(d.revenue)}`, `$${money(d.cost)}`, `$${money(d.profit)}`])} emptyText="No data for selected period." />
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
