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

/**
 * A centred card on an otherwise empty page.
 *
 * The short, self-contained pages several modules need — signing in, registering, a
 * one-question form — are one flow to the person moving through them, so they share a
 * layout rather than each module inventing its own. It lives in core for
 * the same reason `Card` and `Button` do: it is a presentational primitive, and a module
 * importing another module's layout is the coupling this avoids.
 */
withDefaults(
    defineProps<{
        title?: string;
        description?: string;
        cardClass?: string | object;
        /**
         * Width of the card. Overridden by pages whose content reads better narrow —
         * the default suits the sign-in and registration forms.
         */
        widthClass?: string;
    }>(),
    {
        widthClass: 'min-[450px]:min-w-md',
    },
);
</script>

<template>
    <div class="flex min-h-dvh flex-col items-center gap-6">
        <div class="mt-6">
            <Head :title="title" />
            <Link :href="route('index')" class="mt-6 font-medium">
                <AppLogo size="md" :showText="true" />
            </Link>
        </div>

        <div class="flex w-full grow flex-col items-center">
            <div
                class="w-full px-4 min-[450px]:w-auto min-[450px]:px-0"
                :class="widthClass"
            >
                <Card :class="cardClass">
                    <CardHeader class="px-8 text-center">
                        <!-- Optional, and centred above the heading. A page with a single
                             message to deliver gets a mark for it; the sign-in and
                             registration forms pass nothing and are unchanged. -->
                        <div
                            v-if="$slots.icon"
                            class="mb-2 flex justify-center"
                            data-testid="card-icon"
                        >
                            <slot name="icon" />
                        </div>

                        <!-- `title` stays a string for `<Head>`; the slot is for headings
                             that need markup of their own rather than plain text. -->
                        <CardTitle class="text-2xl">
                            <slot name="heading">{{ title }}</slot>
                        </CardTitle>
                        <!-- Same split as the heading: `description` stays a plain
                             string, and the slot is for descriptions that need markup
                             inside the sentence. -->
                        <CardDescription>
                            <slot name="subheading">{{ description }}</slot>
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
        <Footer class="mt-16 w-full pt-8" />
    </div>
</template>
