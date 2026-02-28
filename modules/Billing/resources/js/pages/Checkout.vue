<script setup lang="ts">
import { Button } from '@/components/ui/button';
import InputField from '@/components/ui/input/InputField.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import type { CheckoutSession } from '@modules/Billing/resources/js/types';
import { computed } from 'vue';
import CheckoutLayout from '../layouts/CheckoutLayout.vue';
import { getIntervalDisplay } from '../utils/intervals';

import IconArrowLeft from '~icons/heroicons/arrow-left';
import IconCheck from '~icons/heroicons/check';
import IconLock from '~icons/heroicons/lock-closed';

const props = defineProps<{
    session: CheckoutSession;
}>();

const page = usePage();
const user = computed(() => page.props.auth?.user);

const price = computed(() => props.session.price);
const product = computed(() => price.value.product);

const form = useForm({
    name: user.value?.name ?? '',
    email: user.value?.email ?? '',
    phone: '',
    address: {
        street: '',
        city: '',
        state: '',
        postal_code: '',
        country: '',
    },
});

function formatPrice(amount: number, currency?: string): string {
    return new Intl.NumberFormat(undefined, {
        style: 'currency',
        currency: currency?.toUpperCase() ?? 'EUR',
    }).format(amount / 100);
}

function handleCheckout() {
    form.post(route('billing.checkout.store', props.session.uuid));
}
</script>

<template>
    <CheckoutLayout :title="$t('Checkout')">
        <div class="grid gap-8 lg:grid-cols-2">
            <!-- Order Summary (mobile: first, desktop: second via order) -->
            <div data-testid="order-summary" class="order-first lg:order-last">
                <div
                    class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
                >
                    <h2
                        class="text-lg font-semibold text-gray-900 dark:text-white"
                    >
                        {{ $t('Order summary') }}
                    </h2>

                    <div
                        class="mt-4 border-b border-gray-200 pb-4 dark:border-gray-800"
                    >
                        <h3
                            data-testid="checkout-product-name"
                            class="font-medium text-gray-900 dark:text-white"
                        >
                            {{ product.name }}
                        </h3>
                        <p
                            v-if="product.description"
                            class="mt-1 text-sm text-gray-600 dark:text-gray-400"
                            v-html="product.description"
                        />
                    </div>

                    <!-- Features -->
                    <ul
                        v-if="product.features?.length"
                        class="mt-4 space-y-2 border-b border-gray-200 pb-4 dark:border-gray-800"
                    >
                        <li
                            v-for="(feature, index) in product.features"
                            :key="index"
                            class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400"
                        >
                            <IconCheck class="text-primary h-4 w-4 shrink-0" />
                            {{ feature }}
                        </li>
                    </ul>

                    <!-- Subtotal -->
                    <div class="mt-4 space-y-2">
                        <div
                            class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400"
                        >
                            <span>{{ $t('Subtotal') }}</span>
                            <span>{{
                                formatPrice(price.amount, price.currency)
                            }}</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-500">
                            {{
                                $t(
                                    'Tax will be calculated at payment if applicable',
                                )
                            }}
                        </p>
                    </div>

                    <!-- Total -->
                    <div
                        class="mt-4 flex items-center justify-between border-t border-gray-200 pt-4 dark:border-gray-800"
                    >
                        <span
                            class="text-base font-semibold text-gray-900 dark:text-white"
                        >
                            {{ $t('Total') }}
                        </span>
                        <div class="text-right">
                            <span
                                class="text-lg font-bold text-gray-900 dark:text-white"
                            >
                                {{ formatPrice(price.amount, price.currency) }}
                            </span>
                            <span
                                class="ml-1 text-sm text-gray-500 dark:text-gray-400"
                            >
                                {{ getIntervalDisplay(price.interval) }}
                            </span>
                        </div>
                    </div>

                    <p
                        v-if="!price.interval"
                        class="mt-3 text-xs text-gray-500 dark:text-gray-500"
                    >
                        {{
                            $t(
                                'This is a one-time payment. You will not be charged again.',
                            )
                        }}
                    </p>
                </div>
            </div>

            <!-- Billing Information Form -->
            <div class="order-last lg:order-first">
                <div
                    class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
                >
                    <h2
                        class="text-lg font-semibold text-gray-900 dark:text-white"
                    >
                        {{ $t('Billing information') }}
                    </h2>

                    <form
                        data-testid="checkout-form"
                        @submit.prevent="handleCheckout"
                        class="mt-4 space-y-4"
                    >
                        <InputField
                            name="name"
                            data-testid="checkout-name"
                            type="text"
                            :label="$t('Name')"
                            :placeholder="$t('Enter your name')"
                            autocomplete="name"
                            required
                            v-model="form.name"
                        />

                        <InputField
                            name="email"
                            data-testid="checkout-email"
                            type="email"
                            :label="$t('Email')"
                            :placeholder="$t('Enter your email')"
                            autocomplete="email"
                            required
                            v-model="form.email"
                        />

                        <InputField
                            name="phone"
                            type="tel"
                            :label="$t('Phone')"
                            :placeholder="$t('Enter your phone number')"
                            autocomplete="tel"
                            v-model="form.phone"
                        />

                        <InputField
                            name="address.street"
                            type="text"
                            :label="$t('Street address')"
                            :placeholder="$t('Enter your street address')"
                            autocomplete="street-address"
                            v-model="form.address.street"
                        />

                        <div class="grid grid-cols-2 gap-4">
                            <InputField
                                name="address.city"
                                type="text"
                                :label="$t('City')"
                                :placeholder="$t('City')"
                                autocomplete="address-level2"
                                v-model="form.address.city"
                            />

                            <InputField
                                name="address.state"
                                type="text"
                                :label="$t('State')"
                                :placeholder="$t('State')"
                                autocomplete="address-level1"
                                v-model="form.address.state"
                            />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <InputField
                                name="address.postal_code"
                                type="text"
                                :label="$t('Postal code')"
                                :placeholder="$t('Postal code')"
                                autocomplete="postal-code"
                                v-model="form.address.postal_code"
                            />

                            <InputField
                                name="address.country"
                                type="text"
                                :label="$t('Country')"
                                :placeholder="$t('e.g. US')"
                                autocomplete="country"
                                maxlength="2"
                                v-model="form.address.country"
                            />
                        </div>

                        <Button
                            type="submit"
                            data-testid="checkout-submit"
                            class="mt-6 w-full"
                            :disabled="form.processing"
                        >
                            <IconLock class="h-4 w-4" />
                            {{
                                form.processing
                                    ? $t('Redirecting...')
                                    : $t('Proceed to Payment')
                            }}
                        </Button>

                        <p
                            class="text-center text-xs text-gray-500 dark:text-gray-500"
                        >
                            {{
                                $t(
                                    'You will be redirected to a secure payment page to complete your purchase.',
                                )
                            }}
                        </p>
                    </form>
                </div>

                <div class="mt-4 text-center">
                    <a
                        href="/#pricing"
                        class="inline-flex items-center gap-1 text-sm text-gray-500 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                    >
                        <IconArrowLeft class="h-4 w-4" />
                        {{ $t('Back to plans') }}
                    </a>
                </div>
            </div>
        </div>
    </CheckoutLayout>
</template>
