<?php

namespace Modules\Billing\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\Url;

class BillingDashboard extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected static ?int $navigationSort = 0;

    protected string $view = 'billing::filament.pages.billing-dashboard';

    #[Url]
    public string $preset = '30d';

    #[Url]
    public ?string $customStart = null;

    #[Url]
    public ?string $customEnd = null;

    public string $startDate = '';

    public string $endDate = '';

    public static function getNavigationGroup(): ?string
    {
        return __('Billing');
    }

    public static function getNavigationLabel(): string
    {
        return __('Dashboard');
    }

    public function mount(): void
    {
        if ($this->preset === 'custom' && $this->customStart && $this->customEnd) {
            $start = \Carbon\Carbon::parse($this->customStart)->startOfDay()->toDateTimeString();
            $end = \Carbon\Carbon::parse($this->customEnd)->endOfDay()->toDateTimeString();
            $this->startDate = $start;
            $this->endDate = $end;
            $this->dispatch('billing-filter-updated', start: $start, end: $end);
        } else {
            $this->applyPreset($this->preset !== 'custom' ? $this->preset : '30d');
        }
    }

    public function updatedPreset(string $value): void
    {
        if ($value !== 'custom') {
            $this->applyPreset($value);
        } else {
            $this->customStart = null;
            $this->customEnd = null;
        }
    }

    public function updatedCustomStart(): void
    {
        if ($this->customStart && $this->customEnd) {
            $start = \Carbon\Carbon::parse($this->customStart)->startOfDay()->toDateTimeString();
            $end = \Carbon\Carbon::parse($this->customEnd)->endOfDay()->toDateTimeString();
            $this->startDate = $start;
            $this->endDate = $end;
            $this->dispatch('billing-filter-updated', start: $start, end: $end);
        }
    }

    public function updatedCustomEnd(): void
    {
        if ($this->customStart && $this->customEnd) {
            $start = \Carbon\Carbon::parse($this->customStart)->startOfDay()->toDateTimeString();
            $end = \Carbon\Carbon::parse($this->customEnd)->endOfDay()->toDateTimeString();
            $this->startDate = $start;
            $this->endDate = $end;
            $this->dispatch('billing-filter-updated', start: $start, end: $end);
        }
    }

    /**
     * @return array<string, string>
     */
    public function getPresetOptions(): array
    {
        return [
            'today' => __('Today'),
            '7d' => __('Last 7 days'),
            '30d' => __('Last 30 days'),
            '90d' => __('Last 3 months'),
            '12m' => __('Last 12 months'),
            'custom' => __('Custom range'),
        ];
    }

    private function applyPreset(string $preset): void
    {
        [$start, $end] = $this->computeDateRange($preset);
        $this->startDate = $start;
        $this->endDate = $end;
        $this->dispatch('billing-filter-updated', start: $start, end: $end);
    }

    /**
     * @return array{string, string}
     */
    private function computeDateRange(string $preset): array
    {
        $end = now();
        $start = match ($preset) {
            'today' => now()->startOfDay(),
            '7d' => now()->subDays(7)->startOfDay(),
            '30d' => now()->subDays(30)->startOfDay(),
            '90d' => now()->subDays(90)->startOfDay(),
            '12m' => now()->subMonths(12)->startOfDay(),
            default => now()->subDays(30)->startOfDay(),
        };

        return [$start->toDateTimeString(), $end->toDateTimeString()];
    }
}
