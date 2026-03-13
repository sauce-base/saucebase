<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import IconXMark from '~icons/heroicons/x-mark';
import { Announcement } from '../types';

const page = usePage();

const announcement = computed(
    () => (page.props?.announcement as Announcement) ?? null,
);
const isAuthenticated = computed(() => !!page.props?.auth?.user);
const isDismissed = ref(false);

const isVisible = computed(() => {
    if (isDismissed.value) {
        return false;
    }

    if (!announcement.value) {
        return false;
    }

    if (!isAuthenticated.value && !announcement.value.show_on_frontend) {
        return false;
    }

    if (isAuthenticated.value && !announcement.value.show_on_dashboard) {
        return false;
    }

    return true;
});

function dismiss() {
    if (!announcement.value) {
        return;
    }

    isDismissed.value = true;

    router.post(
        route('announcements.dismiss', { announcement: announcement.value.id }),
        {},
        { preserveScroll: true },
    );
}
</script>

<template>
    <Transition
        leave-active-class="transition-all duration-300 ease-in"
        leave-to-class="opacity-0 -translate-y-full"
    >
        <div
            v-if="isVisible && announcement"
            data-announcement-banner
            class="sticky inset-x-0 top-0 z-50 flex min-h-16 items-center bg-indigo-600 dark:bg-indigo-700"
        >
            <div class="mx-auto max-w-7xl px-6">
                <!-- eslint-disable-next-line vue/no-v-html -->
                <p
                    class="announcement-text line-clamp-2 text-center text-sm/6 text-white [&_a]:underline [&_a]:underline-offset-2 [&_strong]:font-semibold"
                    :class="announcement.is_dismissable ? 'pr-8' : ''"
                    v-html="announcement.text"
                />
            </div>

            <button
                v-if="announcement.is_dismissable"
                type="button"
                class="absolute top-1/2 right-4 -translate-y-1/2 p-1.5 text-white opacity-80 transition hover:opacity-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-white"
                :aria-label="'Dismiss announcement'"
                @click="dismiss"
            >
                <IconXMark class="size-5" aria-hidden="true" />
            </button>
        </div>
    </Transition>
</template>
