<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected function getStats(): array
    {
        $today         = Carbon::today();
        $thisMonth     = Carbon::now()->startOfMonth();
        $lastMonth     = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd  = Carbon::now()->subMonth()->endOfMonth();

        $thisMonthRevenue = Order::where('payment_status', 'paid')
            ->where('currency', 'TRY')
            ->whereDate('created_at', '>=', $thisMonth)
            ->sum('total');

        $lastMonthRevenue = Order::where('payment_status', 'paid')
            ->where('currency', 'TRY')
            ->whereBetween('created_at', [$lastMonth, $lastMonthEnd])
            ->sum('total');

        $revenueTrend = $lastMonthRevenue > 0
            ? round((($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : 0;

        $todayOrders = Order::whereDate('created_at', $today)->count();

        $thisMonthOrders = Order::whereDate('created_at', '>=', $thisMonth)->count();

        $pendingOrders = Order::where('status', 'pending')->count();

        $totalCustomers = User::count();

        $newCustomers = User::whereDate('created_at', '>=', $thisMonth)->count();

        $outOfStock = Product::where('track_stock', true)
            ->where('stock', '<=', 0)
            ->count();

        $revenueData = [];
        for ($i = 6; $i >= 0; $i--) {
            $revenueData[] = Order::where('payment_status', 'paid')
                ->whereDate('created_at', Carbon::today()->subDays($i))
                ->sum('total');
        }

        $orderData = [];
        for ($i = 6; $i >= 0; $i--) {
            $orderData[] = Order::whereDate('created_at', Carbon::today()->subDays($i))
                ->count();
        }

        return [
            Stat::make('Bu Ay Gelir', '₺' . number_format($thisMonthRevenue, 2, ',', '.'))
                ->description(
                    $revenueTrend >= 0
                        ? "Geçen aya göre %{$revenueTrend} artış"
                        : "Geçen aya göre %" . abs($revenueTrend) . " azalış"
                )
                ->descriptionIcon(
                    $revenueTrend >= 0
                        ? 'heroicon-m-arrow-trending-up'
                        : 'heroicon-m-arrow-trending-down'
                )
                ->color($revenueTrend >= 0 ? 'success' : 'danger')
                ->chart($revenueData),

            Stat::make('Siparişler', "Bugün: {$todayOrders} / Bu ay: {$thisMonthOrders}")
                ->description("Bekleyen: {$pendingOrders} sipariş")
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingOrders > 0 ? 'warning' : 'success')
                ->chart($orderData),

            Stat::make('Müşteriler', number_format($totalCustomers))
                ->description("Bu ay {$newCustomers} yeni müşteri")
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('info'),

            Stat::make('Stok Durumu', "{$outOfStock} ürün tükendi")
                ->description('Stok takibi açık ürünler')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($outOfStock > 0 ? 'danger' : 'success'),
        ];
    }
}
