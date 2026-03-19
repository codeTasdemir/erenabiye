<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RevenueChart extends ChartWidget
{
    protected ?string $heading = 'Aylık Gelir Grafiği';
    protected static ?int $sort = 2;
    protected ?string $maxHeight = '300px';
    public ?string $filter = 'thisYear';


    protected function getFilters(): ?array
    {
        return [
            'thisYear'  => 'Bu Yıl',
            'lastYear'  => 'Geçen Yıl',
            'last6'     => 'Son 6 Ay',
        ];
    }

    protected function getData(): array
    {
        $filter = $this->filter;

        if ($filter === 'last6') {
            $months = collect();
            for ($i = 5; $i >= 0; $i--) {
                $months->push(Carbon::now()->subMonths($i));
            }
        } elseif ($filter === 'lastYear') {
            $months = collect();
            for ($i = 11; $i >= 0; $i--) {
                $months->push(Carbon::now()->subYear()->addMonths(11 - $i + 1)->startOfMonth());
            }
        } else {
            $months = collect();
            for ($i = 0; $i < 12; $i++) {
                $months->push(Carbon::now()->startOfYear()->addMonths($i));
            }
        }

        $labels  = [];
        $revenue = [];
        $orders  = [];

        foreach ($months as $month) {
            $labels[] = $month->locale('tr')->isoFormat('MMM YYYY');

            $revenue[] = Order::where('payment_status', 'paid')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('total');

            $orders[] = Order::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Gelir (₺)',
                    'data'            => $revenue,
                    'borderColor'     => '#6366f1',
                    'backgroundColor' => 'rgba(99, 102, 241, 0.1)',
                    'fill'            => true,
                    'tension'         => 0.4,
                    'yAxisID'         => 'y',
                ],
                [
                    'label'           => 'Sipariş Sayısı',
                    'data'            => $orders,
                    'borderColor'     => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'fill'            => false,
                    'tension'         => 0.4,
                    'yAxisID'         => 'y1',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'type'     => 'linear',
                    'display'  => true,
                    'position' => 'left',
                    'ticks'    => [
                        'callback' => "function(value) { return '₺' + value.toLocaleString('tr-TR'); }",
                    ],
                ],
                'y1' => [
                    'type'     => 'linear',
                    'display'  => true,
                    'position' => 'right',
                    'grid'     => ['drawOnChartArea' => false],
                ],
            ],
        ];
    }
}
