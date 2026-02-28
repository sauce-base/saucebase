<?php

namespace Modules\Billing\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\On;
use Modules\Billing\Enums\CheckoutSessionStatus;
use Modules\Billing\Enums\Currency;
use Modules\Billing\Enums\PaymentStatus;
use Modules\Billing\Enums\SubscriptionStatus;
use Modules\Billing\Models\CheckoutSession;
use Modules\Billing\Models\Payment;
use Modules\Billing\Models\Subscription;

class BillingSaasStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = null;

    public string $startDate = '';

    public string $endDate = '';

    public function mount(): void
    {
        $this->startDate = now()->subDays(30)->startOfDay()->toDateTimeString();
        $this->endDate = now()->toDateTimeString();
    }

    #[On('billing-filter-updated')]
    public function billingFilterUpdated(string $start, string $end): void
    {
        $this->startDate = $start;
        $this->endDate = $end;
        $this->cachedStats = null;
    }

    protected function getStats(): array
    {
        return [
            $this->mrrStat(),
            $this->activeSubscriptionsStat(),
            $this->totalRevenueStat(),
            $this->conversionRateStat(),
        ];
    }

    private function mrrStat(): Stat
    {
        $mrr = (int) Subscription::query()
            ->where('status', SubscriptionStatus::Active)
            ->with('price')
            ->get()
            ->sum(function (Subscription $subscription): float {
                $price = $subscription->price;

                if (! $price || ! $price->interval) {
                    return 0;
                }

                $divisor = match ($price->interval) {
                    'month' => $price->interval_count ?? 1,
                    'year' => ($price->interval_count ?? 1) * 12,
                    default => 1,
                };

                return $price->amount / $divisor;
            });

        return Stat::make(__('MRR'), Currency::default()->formatAmount($mrr))
            ->description(__('Recurring monthly revenue'))
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->color('success');
    }

    private function activeSubscriptionsStat(): Stat
    {
        $count = Subscription::where('status', SubscriptionStatus::Active)->count();

        return Stat::make(__('Active Subscriptions'), number_format($count))
            ->description(__('Active right now'))
            ->descriptionIcon('heroicon-m-user-group')
            ->color('info');
    }

    private function totalRevenueStat(): Stat
    {
        $total = (int) Payment::where('status', PaymentStatus::Succeeded)
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->sum('amount');

        return Stat::make(__('Total Revenue'), Currency::default()->formatAmount($total))
            ->description(__('Succeeded payments in period'))
            ->descriptionIcon('heroicon-m-banknotes')
            ->color('warning');
    }

    private function conversionRateStat(): Stat
    {
        $result = CheckoutSession::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereIn('status', [
                CheckoutSessionStatus::Completed->value,
                CheckoutSessionStatus::Abandoned->value,
                CheckoutSessionStatus::Expired->value,
            ])
            ->selectRaw('SUM(status = ?) as completed, COUNT(*) as total', [CheckoutSessionStatus::Completed->value])
            ->first();

        $total = $result ? (int) $result->getAttribute('total') : 0;
        $completed = $result ? (int) $result->getAttribute('completed') : 0;
        $rate = $total > 0 ? round($completed / $total * 100, 1) : 0;

        return Stat::make(__('Checkout Conversion Rate'), $rate.'%')
            ->description(__('Completed checkouts in period'))
            ->descriptionIcon('heroicon-m-shopping-cart')
            ->color('info');
    }
}
