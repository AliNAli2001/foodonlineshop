<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemBatch;
use App\Models\Adjustment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatisticsService
{
    /**
     * Get comprehensive statistics for a date range
     */
    public function getStatistics(?string $startDate = null, ?string $endDate = null): array
    {
        $startDate = $startDate ? Carbon::parse($startDate) : Carbon::now()->startOfMonth();
        $endDate = $endDate ? Carbon::parse($endDate) : Carbon::now()->endOfMonth();

        return [
            'sales' => $this->getSalesStatistics($startDate, $endDate),
            'earnings' => $this->getEarningsStatistics($startDate, $endDate),
            'adjustments' => $this->getAdjustmentsStatistics($startDate, $endDate),
            'summary' => $this->getSummaryStatistics($startDate, $endDate),
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
        ];
    }

    /**
     * Get sales statistics (done orders only)
     */
    public function getSalesStatistics(Carbon $startDate, Carbon $endDate): array
    {
        $doneOrders = Order::where('status', 'done')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->with(['items.batches'])
            ->get();

        $totalOrders = $doneOrders->count();
        $totalRevenue = $doneOrders->sum('total_amount');
        $totalCost = $doneOrders->sum('cost_price');
        $totalProfit = $totalRevenue - $totalCost;

        return [
            'total_orders' => $totalOrders,
            'total_revenue' => round($totalRevenue, 2),
            'total_cost' => round($totalCost, 2),
            'total_profit' => round($totalProfit, 2),
            'average_order_value' => $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0,
            'profit_margin' => $totalRevenue > 0 ? round(($totalProfit / $totalRevenue) * 100, 2) : 0,
        ];
    }

    /**
     * Get earnings and losses from adjustments
     */
    public function getAdjustmentsStatistics(Carbon $startDate, Carbon $endDate): array
    {
        $adjustments = Adjustment::whereBetween('date', [$startDate, $endDate])->get();

        $totalGains = $adjustments->where('adjustment_type', 'gain')->sum('quantity');
        $totalLosses = $adjustments->where('adjustment_type', 'loss')->sum('quantity');

        return [
            'total_gains' => $totalGains,
            'total_losses' => $totalLosses,
            'net_adjustment' => $totalGains - $totalLosses,
            'gains_count' => $adjustments->where('adjustment_type', 'gain')->count(),
            'losses_count' => $adjustments->where('adjustment_type', 'loss')->count(),
        ];
    }

    /**
     * Get comprehensive earnings statistics
     */
    public function getEarningsStatistics(Carbon $startDate, Carbon $endDate): array
    {
        $salesStats = $this->getSalesStatistics($startDate, $endDate);
        $adjustmentsStats = $this->getAdjustmentsStatistics($startDate, $endDate);

        // Calculate net earnings (profit from sales + gains - losses)
        $netEarnings = $salesStats['total_profit'] + $adjustmentsStats['total_gains'] - $adjustmentsStats['total_losses'];

        return [
            'profit_from_sales' => $salesStats['total_profit'],
            'gains_from_adjustments' => $adjustmentsStats['total_gains'],
            'losses_from_adjustments' => $adjustmentsStats['total_losses'],
            'net_earnings' => round($netEarnings, 2),
        ];
    }

    /**
     * Get summary statistics
     */
    public function getSummaryStatistics(Carbon $startDate, Carbon $endDate): array
    {
        $sales = $this->getSalesStatistics($startDate, $endDate);
        $earnings = $this->getEarningsStatistics($startDate, $endDate);
        $adjustments = $this->getAdjustmentsStatistics($startDate, $endDate);

        return [
            'total_revenue' => $sales['total_revenue'],
            'total_cost' => $sales['total_cost'],
            'gross_profit' => $sales['total_profit'],
            'adjustments_impact' => $adjustments['net_adjustment'],
            'net_profit' => $earnings['net_earnings'],
            'profit_margin' => $sales['profit_margin'],
        ];
    }

    /**
     * Get top selling products for a period
     */
    public function getTopSellingProducts(Carbon $startDate, Carbon $endDate, int $limit = 10): array
    {
        return OrderItem::select('product_id', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(subtotal) as total_revenue'))
            ->whereHas('order', function ($query) use ($startDate, $endDate) {
                $query->where('status', 'done')
                    ->whereBetween('updated_at', [$startDate, $endDate]);
            })
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name_ar ?? 'N/A',
                    'total_quantity' => $item->total_quantity,
                    'total_revenue' => round($item->total_revenue, 2),
                ];
            })
            ->toArray();
    }

    /**
     * Get daily sales chart data
     */
    public function getDailySalesChart(Carbon $startDate, Carbon $endDate): array
    {
        $dailySales = Order::where('status', 'done')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(updated_at) as date'), DB::raw('SUM(total_amount) as revenue'), DB::raw('SUM(cost_price) as cost'), DB::raw('COUNT(*) as orders'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $dailySales->map(function ($day) {
            return [
                'date' => (new Carbon($day->date))->format('Y-m-d'),
                'revenue' => round($day->revenue, 2),
                'cost' => round($day->cost, 2),
                'profit' => round($day->revenue - $day->cost, 2),
                'orders' => $day->orders,
            ];
        })->toArray();
    }
}

