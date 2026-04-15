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
        <slot />
        <!-- Billing Toggle -->
        <div
            v-if="availableIntervals.length > 1"
            class="mt-16 flex justify-center"
        >
            <div
                class="relative flex items-center rounded-xl bg-gray-100 p-1 shadow-lg dark:bg-white/5"
            >
                <button
                    v-for="interval in availableIntervals"
                    :key="interval"
                    @click="billingInterval = interval"
                    :class="[
                        'relative z-10 rounded-xl px-6 py-2 text-sm font-medium transition-all duration-200',
                        billingInterval === interval
                            ? 'bg-primary text-white'
                            : 'text-gray-600 hover:text-gray-900 dark:text-gray-200 dark:hover:text-white',
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

       <slot name="footer" />
    </section>
</template>
