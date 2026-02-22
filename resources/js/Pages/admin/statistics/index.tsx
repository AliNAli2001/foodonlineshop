import React from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { useI18n } from '../../../i18n';
import { Bar, BarChart, CartesianGrid, ResponsiveContainer, XAxis } from 'recharts';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '../../../components/ui/card';
import {
    ChartContainer,
    ChartTooltip,
    ChartTooltipContent,
    type ChartConfig,
} from '../../../components/ui/chart';

type TranslationFn = (key: string, fallback?: string) => string;
type PrimitiveCell = string | number;
type Row = PrimitiveCell[];

type DailySale = {
    date: string;
    orders: number;
    revenue: number;
    cost: number;
    profit: number;
};

type TopProduct = {
    product_name: string;
    total_quantity: number;
    total_revenue: number;
};

type StatisticsData = {
    summary?: {
        total_revenue?: number;
        total_cost?: number;
        gross_profit?: number;
        net_profit?: number;
    };
    sales?: {
        total_orders?: number;
        average_order_value?: number;
        profit_margin?: number;
    };
    adjustments?: {
        total_gains?: number;
        total_losses?: number;
        net_adjustment?: number;
    };
};

type PageProps = {
    statistics?: StatisticsData;
    topProducts?: TopProduct[];
    dailySales?: DailySale[];
    startDate?: string;
    endDate?: string;
};

function money(value: unknown): string {
    return Number(value ?? 0).toFixed(2);
}

function DateFilter({ action, startDate, endDate, t }: { action: string; startDate?: string; endDate?: string; t: TranslationFn }) {
    const submit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        const form = new FormData(e.currentTarget);
        router.get(action, Object.fromEntries(form.entries()), { preserveState: true });
    };

    return (
        <form onSubmit={submit} className="grid gap-3 rounded-2xl border border-white/10 bg-white/[0.04] p-4 md:grid-cols-3">
            <input type="date" name="start_date" defaultValue={startDate} className="rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white" />
            <input type="date" name="end_date" defaultValue={endDate} className="rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white" />
            <button className="rounded-xl bg-cyan-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-cyan-300">{t('admin.pages.statistics.common.filter')}</button>
        </form>
    );
}

function StatCard({ label, value }: { label: string; value: string | number }) {
    return (
        <div className="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
            <p className="text-sm text-slate-400">{label}</p>
            <p className="mt-1 text-2xl font-bold text-white">{value}</p>
        </div>
    );
}

export default function StatisticsIndex() {
    const { t } = useI18n();
    const { statistics = {}, topProducts = [], dailySales = [], startDate, endDate } = usePage<PageProps>().props;
    const summary = statistics.summary || {};
    const sales = statistics.sales || {};
    const adjustments = statistics.adjustments || {};

    const totals = {
        orders: dailySales.reduce((sum: number, d: DailySale) => sum + Number(d.orders || 0), 0),
        revenue: dailySales.reduce((sum: number, d: DailySale) => sum + Number(d.revenue || 0), 0),
        cost: dailySales.reduce((sum: number, d: DailySale) => sum + Number(d.cost || 0), 0),
        profit: dailySales.reduce((sum: number, d: DailySale) => sum + Number(d.profit || 0), 0),
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
                        [t('admin.pages.statistics.index.salesStats.completedOrders'), sales.total_orders ?? 0],
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
                    rows={topProducts.map((p: TopProduct, i: number): Row => [i + 1, p.product_name, p.total_quantity, `$${money(p.total_revenue)}`])}
                    emptyText={t('admin.pages.statistics.common.noData')}
                />

                <DataTable
                    title={t('admin.pages.statistics.index.dailySales.title')}
                    headers={[t('admin.pages.statistics.common.date'), t('admin.pages.statistics.common.orders'), t('admin.pages.statistics.common.revenue'), t('admin.pages.statistics.common.cost'), t('admin.pages.statistics.common.profit')]}
                    rows={dailySales.map((d: DailySale): Row => [d.date, d.orders, `$${money(d.revenue)}`, `$${money(d.cost)}`, `$${money(d.profit)}`])}
                    footer={[t('admin.pages.statistics.common.total'), totals.orders, `$${money(totals.revenue)}`, `$${money(totals.cost)}`, `$${money(totals.profit)}`]}
                    emptyText={t('admin.pages.statistics.common.noData')}
                />

                <ChartBarInteractive
                    title={t('admin.pages.statistics.index.charts.revenueTrend', 'Revenue Trend')}
                    subtitle={t('admin.pages.statistics.index.dailySales.title')}
                    data={dailySales}
                    labels={{
                        amount: t('admin.pages.statistics.common.amount', 'Amount'),
                        revenue: t('admin.pages.statistics.common.revenue', 'Revenue'),
                        profit: t('admin.pages.statistics.common.profit', 'Profit'),
                    }}
                />
            </div>
        </AdminLayout>
    );
}

function InfoTable({ title, rows }: { title: string; rows: [PrimitiveCell, PrimitiveCell][] }) {
    return (
        <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
            <h2 className="mb-3 text-lg font-semibold text-white">{title}</h2>
            <div className="space-y-2">
                {rows.map(([k, v]: [PrimitiveCell, PrimitiveCell]) => (
                    <div key={k} className="flex items-center justify-between text-sm">
                        <span className="text-slate-400">{k}</span>
                        <span className="text-slate-100">{v}</span>
                    </div>
                ))}
            </div>
        </section>
    );
}

function DataTable({ title, headers, rows, footer, emptyText }: { title: string; headers: string[]; rows: Row[]; footer?: Row; emptyText: string }) {
    return (
        <section className="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.04]">
            <h2 className="border-b border-white/10 px-4 py-3 text-lg font-semibold text-white">{title}</h2>
            <div className="overflow-x-auto">
                <table className="min-w-full">
                    <thead className="bg-white/[0.03]">
                        <tr>
                            {headers.map((h: string) => <th key={h} className="px-4 py-3 text-left text-xs uppercase tracking-[0.12em] text-slate-400">{h}</th>)}
                        </tr>
                    </thead>
                    <tbody>
                        {rows.length === 0 ? (
                            <tr><td colSpan={headers.length} className="px-4 py-8 text-center text-sm text-slate-400">{emptyText}</td></tr>
                        ) : rows.map((r: Row, idx: number) => (
                            <tr key={idx} className="border-t border-white/10">
                                {r.map((cell: PrimitiveCell, i: number) => <td key={i} className="px-4 py-3 text-sm text-slate-200">{cell}</td>)}
                            </tr>
                        ))}
                    </tbody>
                    {footer ? (
                        <tfoot className="border-t border-white/10 bg-white/[0.03]">
                            <tr>{footer.map((cell: PrimitiveCell, i: number) => <td key={i} className="px-4 py-3 text-sm font-semibold text-slate-100">{cell}</td>)}</tr>
                        </tfoot>
                    ) : null}
                </table>
            </div>
        </section>
    );
}

function ChartBarInteractive({
    title,
    subtitle,
    data,
    labels,
}: {
    title: string;
    subtitle: string;
    data: DailySale[];
    labels: {
        amount: string;
        revenue: string;
        profit: string;
    };
}) {
    const chartData = data.map((row) => ({
        date: row.date,
        revenue: Number(row.revenue || 0),
        profit: Number(row.profit || 0),
    }));
    const [activeChart, setActiveChart] = React.useState<'revenue' | 'profit'>('revenue');

    const total = React.useMemo(
        () => ({
            revenue: chartData.reduce((acc, curr) => acc + curr.revenue, 0),
            profit: chartData.reduce((acc, curr) => acc + curr.profit, 0),
        }),
        [chartData],
    );

    const chartConfig = {
        views: {
            label: labels.amount,
        },
        revenue: {
            label: labels.revenue,
            color: 'var(--chart-2, #22d3ee)',
        },
        profit: {
            label: labels.profit,
            color: 'var(--chart-1, #38bdf8)',
        },
    } satisfies ChartConfig;

    return (
        <Card className="py-0">
            <CardHeader className="flex flex-col items-stretch border-b border-slate-200 dark:border-white/10 !p-0 sm:flex-row">
                <div className="flex flex-1 flex-col justify-center gap-1 px-6 pb-3 pt-4 sm:!py-0">
                    <CardTitle>{title}</CardTitle>
                    <CardDescription>{subtitle}</CardDescription>
                </div>
                <div className="flex">
                    {(['revenue', 'profit'] as const).map((key) => (
                       <button
  key={key}
  data-active={activeChart === key}
  className="
    relative z-30 flex flex-1 flex-col justify-center gap-1
    px-6 py-4 text-left
    border-t border-slate-200
    even:border-l even:border-slate-200
    sm:border-l sm:border-t-0 sm:px-8 sm:py-6

    /* Dark mode borders */
    dark:border-white/10
    dark:even:border-white/10

    /* Dark mode backgrounds */
    dark:bg-slate-900
    dark:bg-slate-800
  "
  onClick={() => setActiveChart(key)}
>
                            <span className="text-xs dark:text-slate-400">{chartConfig[key].label}</span>
                            <span className="text-lg font-bold leading-none dark:text-slate-100 sm:text-3xl">
                                {money(total[key])}
                            </span>
                        </button>
                    ))}
                </div>
            </CardHeader>
            <CardContent className="px-2 sm:p-6">
                {chartData.length === 0 ? (
                    <p className="text-sm dark:text-slate-400">No chart data.</p>
                ) : (
                    <ChartContainer config={chartConfig} className="aspect-auto h-[250px] w-full text-slate-400 dark:text-slate-500">
                        <ResponsiveContainer width="100%" height="100%">
                            <BarChart
                                accessibilityLayer
                                data={chartData}
                                margin={{
                                    left: 12,
                                    right: 12,
                                }}
                            >
                                <CartesianGrid vertical={false} stroke="currentColor" strokeOpacity={0.3} />
                                <XAxis
                                    dataKey="date"
                                    tickLine={false}
                                    axisLine={false}
                                    tickMargin={8}
                                    minTickGap={32}
                                    tick={{ fill: 'currentColor', fontSize: 12 }}
                                    tickFormatter={(value: string) => {
                                        const date = new Date(value);
                                        return date.toLocaleDateString('en-US', {
                                            month: 'short',
                                            day: 'numeric',
                                        });
                                    }}
                                />
                                <ChartTooltip
                                    content={
                                        <ChartTooltipContent
                                            className="w-[150px]"
                                            nameKey="views"
                                            dataKey="date"
                                            labelFormatter={(data: any) => String(data?.date ?? '')}
                                        />
                                    }
                                />
                                <Bar dataKey={activeChart} fill={`var(--color-${activeChart})`} radius={4} />
                            </BarChart>
                        </ResponsiveContainer>
                    </ChartContainer>
                )}
            </CardContent>
        </Card>
    );
}
