<?php

namespace Modules\Billing\Filament\Widgets;

use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;
use Modules\Billing\Enums\PaymentStatus;
use Modules\Billing\Models\Payment;

class RevenueChartWidget extends ChartWidget
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
        return __('Revenue Over Time');
    }

    public function getDescription(): string
    {
        return __('Monthly revenue from succeeded payments');
    }

    protected function getType(): string
    {
        return 'line';
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

        $rows = Payment::where('status', PaymentStatus::Succeeded)
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        foreach ($rows as $row) {
            $month = (string) $row->getAttribute('month');
            if (array_key_exists($month, $buckets)) {
                $buckets[$month] = (int) $row->getAttribute('total');
            }
        }

        $labels = array_map(
            fn (string $month) => Carbon::parse($month.'-01')->format('M Y'),
            array_keys($buckets)
        );

        return [
            'datasets' => [
                [
                    'label' => __('Revenue'),
                    'data' => array_values($buckets),
                    'borderColor' => 'rgb(99, 102, 241)',
                    'backgroundColor' => 'rgba(99, 102, 241, 0.1)',
                    'tension' => 0.3,
                    'fill' => false,
                ],
            ],
            'labels' => $labels,
        ];
    }

    /**
     * @return array<string, int>
     */
    private function buildMonthlyBuckets(): array
    {
        $buckets = [];
        $cursor = Carbon::parse($this->startDate)->startOfMonth();
        $end = Carbon::parse($this->endDate)->endOfMonth();

        while ($cursor <= $end) {
            $buckets[$cursor->format('Y-m')] = 0;
            $cursor->addMonth();
        }

        return $buckets;
    }
}
