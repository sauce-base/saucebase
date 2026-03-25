<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Head, Link } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { computed } from 'vue';

const props = defineProps({ status: Number });

const title = computed(() => {
    return (
        {
            503: trans('503: Service Unavailable'),
            500: trans('500: Server Error'),
            404: trans('404: Page Not Found'),
            403: trans('403: Forbidden'),
        }[props.status as number] ?? trans('Error')
    );
});

const description = computed(() => {
    return (
        {
            503: trans(
                'Sorry, we are doing some maintenance. Please check back soon.',
            ),
            500: trans('Whoops, something went wrong on our servers.'),
            404: trans(
                'Sorry, the page you are looking for could not be found.',
            ),
            403: trans('Sorry, you are forbidden from accessing this page.'),
        }[props.status as number] ?? trans('An error occurred.')
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
                    <Link :href="route('index')"> {{ $t('Go to Home') }} </Link>
                </Button>
            </div>
        </div>
    </div>
</template>
