<?php

namespace Modules\Billing\Console;

use Illuminate\Console\Command;
use Modules\Billing\Enums\CheckoutSessionStatus;
use Modules\Billing\Models\CheckoutSession;

class ExpireCheckoutSessionsCommand extends Command
{
    protected $signature = 'billing:expire-checkout-sessions';

    protected $description = 'Mark expired pending checkout sessions as expired';

    public function handle(): int
    {
        $expired = CheckoutSession::where('status', CheckoutSessionStatus::Pending)
            ->where('expires_at', '<', now())
            ->update(['status' => CheckoutSessionStatus::Expired]);

        $abandonedBefore = now()->subMinutes(config('billing.checkout.abandon_after_minutes', 60));

        $abandoned = CheckoutSession::where('status', CheckoutSessionStatus::Pending)
            ->where('created_at', '<', $abandonedBefore)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->update(['status' => CheckoutSessionStatus::Abandoned]);

        $this->info("Marked {$expired} session(s) as expired, {$abandoned} as abandoned.");

        return self::SUCCESS;
    }
}
