<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { cn } from '@/lib/utils';
import type { User } from '@/types';
import { router, usePage } from '@inertiajs/vue3';
import { onClickOutside, onKeyStroke } from '@vueuse/core';
import { Drama, HistoryIcon, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

// TODO: move this to the Auth module.

interface Impersonation {
    user: User;
    route: string;
    label: string;
    recent: User[];
}

const page = usePage();

const impersonation = computed<Impersonation | null>(
    () => (page.props?.impersonation as Impersonation) || null,
);

// User initials helper
const getUserInitials = (name: string) => {
    return name
        .split(' ')
        .map((word) => word.charAt(0).toUpperCase())
        .slice(0, 2)
        .join('');
};

// Role badge helper
const getRoleBadgeClasses = (role: string) => {
    const baseClasses =
        'shrink-0 rounded-full px-1 py-0.5 text-[9px] text-white uppercase';
    const colorClasses = role === 'admin' ? 'bg-red-800' : 'bg-cyan-700';

    return cn(baseClasses, colorClasses);
};

// State management
const isExpanded = ref(false);
const alertRef = ref<HTMLElement | null>(null);

// Actions
const toggleExpanded = () => {
    isExpanded.value = !isExpanded.value;
};
const collapse = () => {
    isExpanded.value = false;
};
const reimpersonate = (userId: number) => {
    router.post(
        route('auth.impersonate.reimpersonate', { userId }),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                collapse();
            },
        },
    );
};

// Interaction handlers
onClickOutside(alertRef, () => {
    if (isExpanded.value) collapse();
});

onKeyStroke('Escape', () => {
    if (isExpanded.value) collapse();
});

// Positioning classes
const containerClasses = computed(() => cn('fixed bottom-3 right-3 z-50'));
</script>

<template>
    <div
        v-if="impersonation"
        ref="alertRef"
        :class="containerClasses"
        data-testid="impersonation-alert"
    >
        <!-- Collapsed state: Avatar only with orange border -->
        <button
            v-if="!isExpanded"
            @click="toggleExpanded"
            :title="`Impersonating ${impersonation.user.name}`"
            class="animate-in fade-in zoom-in-95 relative cursor-pointer rounded-full shadow-lg ring-2 ring-orange-500 transition-all duration-300 hover:shadow-xl hover:ring-orange-600"
            :aria-label="$t('Show impersonation details')"
            :aria-expanded="false"
        >
            <Avatar class="bg-gray-00 size-10">
                <AvatarImage
                    :src="impersonation.user.avatar"
                    :alt="impersonation.user.name"
                />
                <AvatarFallback class="bg-yellow-600 text-sm text-white">
                    {{ getUserInitials(impersonation.user.name) }}
                </AvatarFallback>
            </Avatar>
            <!-- Impersonation icon badge -->
            <div
                class="absolute -top-3 -left-3 flex size-7 items-center justify-center rounded-full bg-orange-500 shadow-lg"
            >
                <Drama class="size-5 text-white" />
            </div>
        </button>

        <!-- Expanded state: Full card with user info and action button -->
        <div
            v-else
            class="bg-foreground animate-in fade-in slide-in-from-right-5 relative flex w-80 flex-col gap-3 rounded-4xl p-3 shadow-2xl duration-300"
            role="region"
            :aria-label="$t('Impersonation alert')"
        >
            <!-- Close button -->
            <button
                @click="collapse"
                class="text-background/60 hover:bg-background/10 hover:text-background absolute top-3 right-3 rounded-full p-1 transition-colors"
                :aria-label="$t('Close')"
            >
                <X class="size-5" />
            </button>
            <div class="flex items-center gap-3">
                <Avatar class="size-11 border-2 border-amber-500">
                    <AvatarImage
                        :src="impersonation.user.avatar"
                        :alt="impersonation.user.name"
                    />
                    <AvatarFallback class="bg-yellow-600 text-sm text-white">
                        {{ getUserInitials(impersonation.user.name) }}
                    </AvatarFallback>
                </Avatar>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <p
                            class="text-background truncate text-sm font-semibold"
                        >
                            {{ impersonation.user.name }}
                        </p>
                        <span
                            v-if="impersonation.user.role"
                            :class="
                                getRoleBadgeClasses(impersonation.user.role)
                            "
                        >
                            {{ impersonation.user.role }}
                        </span>
                    </div>
                    <p class="text-background/80 truncate text-xs">
                        {{ impersonation.user.email }}
                    </p>
                </div>
            </div>
            <a
                :href="impersonation.route"
                class="bg-background text-foreground hover:bg-background/90 w-full rounded-full px-3 py-2 text-center text-sm font-medium transition-colors"
            >
                {{ impersonation.label }}
            </a>

            <!-- Recent History Section -->
            <div
                v-if="impersonation.recent && impersonation.recent.length > 0"
                class="border-background/10 mt-1 border-t pt-2"
            >
                <p
                    class="text-background/50 mb-3 text-center text-sm font-medium tracking-wide"
                >
                    <HistoryIcon class="inline-block size-4" />
                    {{ $t('Recent impersonated users') }}
                </p>

                <div class="space-y-0">
                    <button
                        v-for="user in impersonation.recent"
                        :key="user.id"
                        @click="reimpersonate(user.id)"
                        class="hover:bg-background/20 flex w-full items-center rounded-full text-left transition-colors"
                    >
                        <Avatar
                            class="border-background/20 m-1 size-10 border-2"
                        >
                            <AvatarImage :src="user.avatar" :alt="user.name" />
                            <AvatarFallback
                                class="bg-yellow-600/80 text-xs text-white"
                            >
                                {{ getUserInitials(user.name) }}
                            </AvatarFallback>
                        </Avatar>
                        <div class="min-w-0 flex-1 p-2 pl-1">
                            <div class="flex items-center gap-1.5">
                                <p
                                    class="text-background truncate text-xs font-medium"
                                >
                                    {{ user.name }}
                                </p>
                                <span
                                    v-if="user.role"
                                    :class="getRoleBadgeClasses(user.role)"
                                >
                                    {{ user.role }}
                                </span>
                            </div>
                            <p class="text-background/70 truncate text-xs">
                                {{ user.email }}
                            </p>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
