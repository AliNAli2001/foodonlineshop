<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\StatisticsService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StatisticsController extends Controller
{
    protected StatisticsService $statisticsService;

    public function __construct(StatisticsService $statisticsService)
    {
        $this->statisticsService = $statisticsService;
    }

    /**
     * Display statistics overview
     */
    public function index(Request $request)
    {
        // Default to current month
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $statistics = $this->statisticsService->getStatistics($startDate, $endDate);
        $topProducts = $this->statisticsService->getTopSellingProducts(
            Carbon::parse($startDate),
            Carbon::parse($endDate),
            10
        );
        $dailySales = $this->statisticsService->getDailySalesChart(
            Carbon::parse($startDate),
            Carbon::parse($endDate)
        );

        return view('admin.statistics.index', compact('statistics', 'topProducts', 'dailySales', 'startDate', 'endDate'));
    }

    /**
     * Display sales statistics
     */
    public function sales(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $salesStats = $this->statisticsService->getSalesStatistics(
            Carbon::parse($startDate),
            Carbon::parse($endDate)
        );
        $topProducts = $this->statisticsService->getTopSellingProducts(
            Carbon::parse($startDate),
            Carbon::parse($endDate),
            20
        );
        $dailySales = $this->statisticsService->getDailySalesChart(
            Carbon::parse($startDate),
            Carbon::parse($endDate)
        );

        return view('admin.statistics.sales', compact('salesStats', 'topProducts', 'dailySales', 'startDate', 'endDate'));
    }

    /**
     * Display earnings and losses statistics
     */
    public function earnings(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $earningsStats = $this->statisticsService->getEarningsStatistics(
            Carbon::parse($startDate),
            Carbon::parse($endDate)
        );
        $adjustmentsStats = $this->statisticsService->getAdjustmentsStatistics(
            Carbon::parse($startDate),
            Carbon::parse($endDate)
        );
        $salesStats = $this->statisticsService->getSalesStatistics(
            Carbon::parse($startDate),
            Carbon::parse($endDate)
        );

        return view('admin.statistics.earnings', compact('earningsStats', 'adjustmentsStats', 'salesStats', 'startDate', 'endDate'));
    }
}

