<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ status: Number });

const title = computed(() => {
    return (
        {
            503: '503: Service Unavailable',
            500: '500: Server Error',
            404: '404: Page Not Found',
            403: '403: Forbidden',
        }[props.status as number] ?? 'Error'
    );
});

const description = computed(() => {
    return (
        {
            503: 'Sorry, we are doing some maintenance. Please check back soon.',
            500: 'Whoops, something went wrong on our servers.',
            404: 'Sorry, the page you are looking for could not be found.',
            403: 'Sorry, you are forbidden from accessing this page.',
        }[props.status as number] ?? 'An error occurred.'
    );
});
</script>

<template>
    <Head :title="`${props.status} - ${title}`" />
    <div
        class="flex min-h-screen items-center justify-center bg-gray-50 dark:bg-gray-900"
    >
        <div class="w-full max-w-md space-y-8 text-center">
            <div>
                <h1 class="text-6xl font-bold text-gray-900 dark:text-gray-100">
                    {{ props.status }}
                </h1>
                <h2
                    class="mt-4 text-2xl font-semibold text-gray-700 dark:text-gray-300"
                >
                    {{ title }}
                </h2>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    {{ description }}
                </p>
            </div>

            <div class="space-y-4">
                <Button as-child>
                    <Link :href="route('dashboard')"> Go to Dashboard </Link>
                </Button>

                <div>
                    <Link
                        :href="route('index')"
                        class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                    >
                        ← Back to Home
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
