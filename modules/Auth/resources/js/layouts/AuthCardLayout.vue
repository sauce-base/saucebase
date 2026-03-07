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

        <div class="grow">
            <Card :class="['mx-4', cardClass]">
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
                            :message="$page.props.status || $page.props.error"
                            :variant="$page.props.status ? 'success' : 'error'"
                            class="mt-4"
                            data-testid="alert"
                        />
                        <slot />
                    </PageTransition>
                </CardContent>
            </Card>
            <slot name="outside" />
        </div>
        <Footer class="mt-6 w-full" />
    </div>
</template>
