<?php

namespace Modules\Billing\Filament\Widgets;

use Carbon\Carbon;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;
use Modules\Billing\Enums\CheckoutSessionStatus;
use Modules\Billing\Models\CheckoutSession;

class ConversionChartWidget extends ChartWidget
{
    protected static bool $isDiscovered = false;

    protected ?string $pollingInterval = null;

    protected ?string $maxHeight = '300px';

    public string $startDate = '';

    public string $endDate = '';

    public function mount(): void
    {
        $this->startDate = now()->subDays(30)->startOfDay()->toDateTimeString();
        $this->endDate = now()->toDateTimeString();

        parent::mount();
    }

    public function getHeading(): string
    {
        return __('Checkout Conversion Rate Over Time');
    }

    public function getDescription(): string
    {
        return __('Monthly checkout session conversion percentage');
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array|RawJs|null
    {
        return RawJs::make(<<<'JS'
            {
                scales: {
                    y: {
                        ticks: {
                            callback: (value) => value + '%',
                        },
                        min: 0,
                        max: 100,
                    },
                },
            }
        JS);
    }

    #[On('billing-filter-updated')]
    public function billingFilterUpdated(string $start, string $end): void
    {
        $this->startDate = $start;
        $this->endDate = $end;
        $this->cachedData = null;
        $this->updateChartData();
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $buckets = $this->buildMonthlyBuckets();

        $rows = CheckoutSession::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereIn('status', [
                CheckoutSessionStatus::Completed->value,
                CheckoutSessionStatus::Abandoned->value,
                CheckoutSessionStatus::Expired->value,
            ])
            ->selectRaw(
                'DATE_FORMAT(created_at, "%Y-%m") as month, SUM(status = ?) as completed, COUNT(*) as total',
                [CheckoutSessionStatus::Completed->value]
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        foreach ($rows as $row) {
            $month = (string) $row->getAttribute('month');
            $total = (int) $row->getAttribute('total');
            $completed = (int) $row->getAttribute('completed');
            if (array_key_exists($month, $buckets) && $total > 0) {
                $buckets[$month] = round($completed / $total * 100, 1);
            }
        }

        $labels = array_map(
            fn (string $month) => Carbon::parse($month.'-01')->format('M Y'),
            array_keys($buckets)
        );

        return [
            'datasets' => [
                [
                    'label' => __('Conversion Rate'),
                    'data' => array_values($buckets),
                    'borderColor' => 'rgb(245, 158, 11)',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'tension' => 0.3,
                    'fill' => false,
                ],
            ],
            'labels' => $labels,
        ];
    }

    /**
     * @return array<string, float>
     */
    private function buildMonthlyBuckets(): array
    {
        $buckets = [];
        $cursor = Carbon::parse($this->startDate)->startOfMonth();
        $end = Carbon::parse($this->endDate)->endOfMonth();

        while ($cursor <= $end) {
            $buckets[$cursor->format('Y-m')] = 0.0;
            $cursor->addMonth();
        }

        return $buckets;
    }
}
