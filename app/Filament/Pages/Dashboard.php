<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\RevenueChart;
use App\Filament\Widgets\LatestOrders;
use App\Filament\Widgets\LowStockAlert;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Pages\Dashboard as BaseDashboard;


class Dashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Ana Sayfa';
    protected static ?string $title = 'Yönetim Paneli';
    protected static ?int $navigationSort = 0;

    public function getWidgets(): array
    {
        return [
            StatsOverview::class,
            RevenueChart::class,
            LatestOrders::class,
            LowStockAlert::class,
        ];
    }

    public function getColumns(): int|array
    {
        return [
            'sm'  => 1,
            'md'  => 2,
            'xl'  => 4,
        ];
    }
}
