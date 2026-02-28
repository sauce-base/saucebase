<script setup lang="ts">
import { computed, ref, watch } from 'vue';

import type { Product } from '@modules/Billing/resources/js/types';
import {
    getIntervalLabel,
    matchesInterval,
    normalizeInterval,
} from '../utils/intervals';

import ProductCard from './ProductCard.vue';

const props = defineProps<{
    products: Product[];
}>();

const availableIntervals = computed(() => {
    if (!props.products) return [];

    const intervals = new Set<string>();
    for (const product of props.products) {
        for (const price of product.prices) {
            intervals.add(normalizeInterval(price.interval));
        }
    }

    // Sort order: one_time, day, week, month, year
    const order = ['one_time', 'day', 'week', 'month', 'year'];
    return Array.from(intervals).sort(
        (a, b) => order.indexOf(a) - order.indexOf(b),
    );
});

const billingInterval = ref<string>('month');

// Set default to first available interval when products change
watch(
    availableIntervals,
    (intervals) => {
        if (
            intervals.length > 0 &&
            !intervals.includes(billingInterval.value)
        ) {
            billingInterval.value = intervals[0];
        }
    },
    { immediate: true },
);

const filteredProducts = computed(() => {
    if (!props.products) return [];

    return props.products
        .map((product) => ({
            ...product,
            prices: product.prices.filter((price) =>
                matchesInterval(price.interval, billingInterval.value),
            ),
        }))
        .filter((product) => product.prices.length > 0);
});

function getToggleLabel(interval: string): string {
    if (interval === 'one_time') return 'One-time';
    return getIntervalLabel(interval);
}
</script>

<template>
    <section id="pricing" class="relative w-full overflow-hidden px-6 py-32">
        <div class="mx-auto max-w-4xl text-center">
            <h2
                class="mt-2 text-4xl font-semibold tracking-tight text-gray-900 sm:text-5xl dark:text-white"
            >
                {{ $t('Build fast. Ship faster.') }}
            </h2>
            <p
                class="mx-auto mt-6 max-w-2xl text-lg text-gray-600 dark:text-gray-400"
            >
                {{
                    $t(
                        'Skip months of setup and boilerplate. Start building real features today and ship your product in days.',
                    )
                }}
            </p>
        </div>

        <!-- Billing Toggle -->
        <div
            v-if="availableIntervals.length > 1"
            class="mt-16 flex justify-center"
        >
            <div
                class="relative flex items-center rounded-full bg-gray-100 p-1 shadow-lg dark:bg-white/5"
            >
                <button
                    v-for="interval in availableIntervals"
                    :key="interval"
                    @click="billingInterval = interval"
                    :class="[
                        'relative z-10 rounded-full px-6 py-2 text-sm font-medium transition-all duration-200',
                        billingInterval === interval
                            ? 'bg-primary text-white'
                            : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white',
                    ]"
                >
                    {{ $t(getToggleLabel(interval)) }}
                </button>
            </div>
        </div>

        <!-- Pricing Cards -->
        <div
            class="mx-auto mt-16 grid max-w-6xl grid-cols-1 gap-8"
            :class="{
                'lg:grid-cols-2': filteredProducts.length === 2,
                'lg:grid-cols-3': filteredProducts.length === 3,
                'lg:grid-cols-4': filteredProducts.length >= 4,
            }"
        >
            <ProductCard
                v-for="product in filteredProducts"
                :key="product.id"
                :product="product"
                :price="product.prices[0]"
            />
        </div>
        <div class="absolute top-30 -z-10 h-2/3 w-full opacity-50">
            <div
                class="from-primary/20 to-secondary/20 dark:from-primary/30 dark:to-secondary/10 absolute inset-0 -top-50 -z-10 -skew-10 rounded-4xl bg-radial"
            />
            <div
                class="from-primary/20 to-secondary/20 dark:from-primary/30 dark:to-secondary/10 absolute inset-0 -top-30 -z-20 skew-30 rounded-4xl bg-radial"
            />
        </div>
    </section>
</template>
