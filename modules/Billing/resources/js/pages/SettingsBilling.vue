<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import Separator from '@/components/ui/separator/Separator.vue';
import { useDialog } from '@/composables/useDialog';
import { trans } from 'laravel-vue-i18n';
import SettingsLayout from '@/layouts/SettingsLayout.vue';
import { router } from '@inertiajs/vue3';
import { CreditCard, Loader2 } from 'lucide-vue-next';
import { ref } from 'vue';
import type { Invoice, PaymentMethod, Subscription } from '../types';

defineProps<{
    subscription: Subscription | null;
    paymentMethod: PaymentMethod | null;
    invoices: Invoice[];
    billingPortalUrl: string;
}>();

const title = 'Billing';
const isCancelling = ref(false);
const isResuming = ref(false);
const { confirm } = useDialog();

function formatDate(date: string | null): string {
    if (!date) return '';
    return new Date(date).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}

function formatCurrency(amount: number, currency: string | null): string {
    const cur = currency?.toUpperCase() ?? 'USD';
    return new Intl.NumberFormat(undefined, {
        style: 'currency',
        currency: cur,
    }).format(amount / 100);
}

function formatInterval(interval: string | null): string {
    if (!interval) return '';
    return interval === 'year' ? 'Yearly' : 'Monthly';
}

function ucfirst(value: string | null | undefined): string {
    if (!value) return '';
    return value.charAt(0).toUpperCase() + value.slice(1);
}

function pad(value: number | null | undefined): string {
    return String(value ?? 0).padStart(2, '0');
}

function statusVariant(
    status: string,
): 'default' | 'secondary' | 'destructive' | 'outline' {
    switch (status) {
        case 'paid':
            return 'default';
        case 'posted':
        case 'unpaid':
            return 'secondary';
        default:
            return 'outline';
    }
}

async function handleCancelSubscription() {
    if (
        await confirm({
            title: trans('Cancel subscription'),
            description: trans(
                'Are you sure you want to cancel your subscription? You will continue to have access until the end of your current billing period.',
            ),
            confirmLabel: trans('Cancel subscription'),
            cancelLabel: trans('Keep plan'),
            variant: 'destructive',
            icon: CreditCard,
        })
    ) {
        isCancelling.value = true;
        router.post(
            route('billing.subscription.cancel'),
            {},
            {
                onFinish: () => {
                    isCancelling.value = false;
                },
            },
        );
    }
}

function resumeSubscription() {
    isResuming.value = true;
    router.post(
        route('billing.subscription.resume'),
        {},
        {
            onFinish: () => {
                isResuming.value = false;
            },
        },
    );
}
</script>

<template>
    <SettingsLayout :title="title">
        <template #header>
            <h1 class="text-2xl font-bold">
                {{ $t('Billing') }}
            </h1>
        </template>

        <!-- Has subscription -->
        <Card
            v-if="subscription"
            data-testid="subscription-section"
            class="max-w-3xl"
        >
            <CardHeader>
                <CardTitle>{{ $t('Billing & Subscription') }}</CardTitle>
                <CardDescription>
                    {{
                        $t(
                            'Manage your subscription, payment method, and invoices',
                        )
                    }}
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-0">
                <!-- Section 1: Current Plan -->
                <div class="py-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3
                                class="text-sm font-medium text-gray-500 dark:text-gray-400"
                            >
                                {{ $t('Current Plan') }}
                            </h3>
                            <p
                                data-testid="plan-name"
                                class="mt-1 text-lg font-semibold text-gray-900 dark:text-white"
                            >
                                {{
                                    subscription.plan_name ?? $t('Unknown Plan')
                                }}
                            </p>
                            <p
                                class="mt-0.5 text-sm text-gray-600 dark:text-gray-400"
                            >
                                {{ formatInterval(subscription.interval) }}
                                <template v-if="subscription.cancelled_at">
                                    &middot;
                                    <span
                                        class="text-red-600 dark:text-red-400"
                                    >
                                        {{ $t('Cancels on') }}
                                        {{ formatDate(subscription.ends_at) }}
                                    </span>
                                </template>
                                <template
                                    v-else-if="
                                        subscription.current_period_ends_at
                                    "
                                >
                                    &middot;
                                    {{ $t('Renews on') }}
                                    {{
                                        formatDate(
                                            subscription.current_period_ends_at,
                                        )
                                    }}
                                </template>
                            </p>
                        </div>
                        <a :href="billingPortalUrl">
                            <Button variant="outline" size="sm">
                                {{ $t('Adjust plan') }}
                            </Button>
                        </a>
                    </div>
                </div>

                <Separator />

                <!-- Section 2: Payment Method -->
                <div class="py-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3
                                class="text-sm font-medium text-gray-500 dark:text-gray-400"
                            >
                                {{ $t('Payment Method') }}
                            </h3>
                            <p
                                v-if="paymentMethod"
                                class="mt-1 text-sm text-gray-900 dark:text-white"
                            >
                                <template
                                    v-if="
                                        paymentMethod.category === 'card' &&
                                        paymentMethod.details
                                    "
                                >
                                    {{ ucfirst(paymentMethod.details?.brand) }}
                                    &bull;&bull;&bull;&bull;{{
                                        paymentMethod.details?.last4
                                    }}
                                    <span
                                        v-if="paymentMethod.details?.expMonth"
                                        class="text-gray-500 dark:text-gray-400"
                                    >
                                        &middot;
                                        {{ $t('Expires') }}
                                        {{
                                            pad(paymentMethod.details.expMonth)
                                        }}/{{ paymentMethod.details.expYear }}
                                    </span>
                                </template>
                                <template
                                    v-else-if="
                                        paymentMethod.category === 'wallet' &&
                                        paymentMethod.details
                                    "
                                >
                                    {{ ucfirst(paymentMethod.type) }}
                                    <span v-if="paymentMethod.details?.email">
                                        {{ paymentMethod.details.email }}
                                    </span>
                                </template>
                                <template
                                    v-else-if="
                                        paymentMethod.category === 'bank' &&
                                        paymentMethod.details
                                    "
                                >
                                    {{
                                        paymentMethod.details?.bankName ??
                                        $t('Bank account')
                                    }}
                                    <template
                                        v-if="paymentMethod.details?.last4"
                                    >
                                        &bull;&bull;&bull;&bull;{{
                                            paymentMethod.details.last4
                                        }}
                                    </template>
                                </template>
                                <template v-else>
                                    {{ $t('Payment method') }}
                                </template>
                            </p>
                            <p
                                v-else
                                class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                            >
                                {{ $t('No payment method on file') }}
                            </p>
                        </div>
                        <a :href="billingPortalUrl">
                            <Button variant="outline" size="sm">
                                {{ $t('Update') }}
                            </Button>
                        </a>
                    </div>
                </div>

                <Separator />

                <!-- Section 3: Invoices -->
                <div class="py-4">
                    <h3
                        class="text-sm font-medium text-gray-500 dark:text-gray-400"
                    >
                        {{ $t('Invoices') }}
                    </h3>

                    <div v-if="invoices.length > 0" class="mt-3">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr
                                        class="border-b border-gray-200 dark:border-gray-800"
                                    >
                                        <th
                                            class="pb-2 text-left font-medium text-gray-500 dark:text-gray-400"
                                        >
                                            {{ $t('Date') }}
                                        </th>
                                        <th
                                            class="pb-2 text-left font-medium text-gray-500 dark:text-gray-400"
                                        >
                                            {{ $t('Amount') }}
                                        </th>
                                        <th
                                            class="pb-2 text-left font-medium text-gray-500 dark:text-gray-400"
                                        >
                                            {{ $t('Status') }}
                                        </th>
                                        <th
                                            class="pb-2 text-right font-medium text-gray-500 dark:text-gray-400"
                                        >
                                            {{ $t('Invoice') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="invoice in invoices"
                                        :key="invoice.id"
                                        class="border-b border-gray-100 last:border-0 dark:border-gray-800/50"
                                    >
                                        <td
                                            class="py-3 text-gray-900 dark:text-white"
                                        >
                                            {{ formatDate(invoice.paid_at) }}
                                        </td>
                                        <td
                                            class="py-3 text-gray-900 dark:text-white"
                                        >
                                            {{
                                                formatCurrency(
                                                    invoice.total,
                                                    invoice.currency,
                                                )
                                            }}
                                        </td>
                                        <td class="py-3">
                                            <Badge
                                                :variant="
                                                    statusVariant(
                                                        invoice.status,
                                                    )
                                                "
                                            >
                                                {{ invoice.status }}
                                            </Badge>
                                        </td>
                                        <td class="py-3 text-right">
                                            <a
                                                v-if="
                                                    invoice.hosted_invoice_url
                                                "
                                                :href="
                                                    invoice.hosted_invoice_url
                                                "
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300"
                                            >
                                                {{ $t('View') }}
                                            </a>
                                            <span
                                                v-else
                                                class="text-gray-400 dark:text-gray-600"
                                                >&mdash;</span
                                            >
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <p
                        v-else
                        class="mt-2 text-sm text-gray-500 dark:text-gray-400"
                    >
                        {{ $t('No invoices yet') }}
                    </p>
                </div>

                <!-- Section 4: Resume (when pending cancellation) -->
                <template v-if="subscription.cancelled_at">
                    <Separator />

                    <div class="py-4">
                        <h3
                            class="text-sm font-medium text-gray-500 dark:text-gray-400"
                        >
                            {{ $t('Resume subscription') }}
                        </h3>
                        <p
                            class="mt-1 text-sm text-gray-600 dark:text-gray-400"
                        >
                            {{
                                $t(
                                    'Changed your mind? Resume your subscription to keep your access.',
                                )
                            }}
                        </p>
                        <Button
                            data-testid="resume-button"
                            size="sm"
                            class="mt-3"
                            :disabled="isResuming"
                            @click="resumeSubscription"
                        >
                            <Loader2
                                v-if="isResuming"
                                class="mr-2 size-4 animate-spin"
                            />
                            {{ $t('Resume plan') }}
                        </Button>
                    </div>
                </template>

                <!-- Section 4: Cancellation (when active) -->
                <template v-else>
                    <Separator />

                    <div class="py-4">
                        <h3
                            class="text-sm font-medium text-gray-500 dark:text-gray-400"
                        >
                            {{ $t('Cancellation') }}
                        </h3>
                        <p
                            class="mt-1 text-sm text-gray-600 dark:text-gray-400"
                        >
                            {{
                                $t(
                                    'Your subscription will remain active until the end of the current billing period.',
                                )
                            }}
                        </p>
                        <Button
                            data-testid="cancel-button"
                            variant="destructive"
                            size="sm"
                            class="mt-3"
                            :disabled="isCancelling"
                            @click="handleCancelSubscription"
                        >
                            <Loader2
                                v-if="isCancelling"
                                class="mr-2 size-4 animate-spin"
                            />
                            {{ $t('Cancel subscription') }}
                        </Button>
                    </div>
                </template>
            </CardContent>
        </Card>

        <!-- No subscription -->
        <div
            v-else
            data-testid="no-subscription"
            class="flex max-w-3xl flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 p-12 text-center dark:border-gray-700"
        >
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ $t('No active subscription') }}
            </h3>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ $t('Choose a plan to get started with all the features.') }}
            </p>
            <a href="/#pricing" class="mt-4">
                <Button>
                    {{ $t('View Plans') }}
                </Button>
            </a>
        </div>

    </SettingsLayout>
</template>
