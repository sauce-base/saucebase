<script setup lang="ts">
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

import AlertMessage from '@/components/AlertMessage.vue';
import AppLogo from '@/components/AppLogo.vue';
import Footer from '@/components/Footer.vue';
import PageTransition from '@/components/PageTransition.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
    cardClass?: string | object;
}>();
</script>

<template>
    <div class="flex h-screen flex-col items-center gap-6">
        <div class="mt-6">
            <Head :title="title" />
            <Link :href="route('index')" class="mt-6 font-medium">
                <AppLogo size="md" :showText="true" />
            </Link>
        </div>

        <div class="flex w-full grow flex-col items-center">
            <div
                class="w-full px-4 min-[450px]:w-auto min-[450px]:min-w-md min-[450px]:px-0"
            >
                <Card :class="cardClass">
                    <CardHeader class="px-8 text-center">
                        <CardTitle class="text-2xl">
                            {{ title }}
                        </CardTitle>
                        <CardDescription>
                            {{ description }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="px-8">
                        <PageTransition>
                            <AlertMessage
                                :message="
                                    $page.props.status || $page.props.error
                                "
                                :variant="
                                    $page.props.status ? 'success' : 'error'
                                "
                                class="mt-4"
                                data-testid="alert"
                            />
                            <slot />
                        </PageTransition>
                    </CardContent>
                </Card>
            </div>
            <slot name="outside" />
        </div>
        <Footer class="mt-16 w-full pt-35" />
    </div>
</template>
