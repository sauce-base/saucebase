<script setup lang="ts">
import Badge from '@/components/ui/badge/Badge.vue';
import Button from '@/components/ui/button/Button.vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import { router, useForm } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue-sonner';

import type { RoadmapItem } from '../types';

import IconMap from '~icons/heroicons/map';
import IconPlus from '~icons/heroicons/plus';
import IconAltArrowDownBold from '~icons/solar/alt-arrow-down-bold';
import IconAltArrowUpBold from '~icons/solar/alt-arrow-up-bold';

const props = defineProps<{
    items: RoadmapItem[];
    sort: string;
    types: Array<{ value: string; label: string; color: string }>;
}>();

const title = 'Roadmap';

// ── Static constants ─────────────────────────────────────────────────────────
const SORT_OPTIONS = [
    { value: 'top', label: 'Top' },
    { value: 'new', label: 'New' },
    { value: 'old', label: 'Old' },
];

const STATUS_CONFIG = [
    { value: 'in_progress', label: 'In Progress' },
    { value: 'approved', label: 'Planned' },
    { value: 'completed', label: 'Completed' },
] as const;

// ── Sorting ──────────────────────────────────────────────────────────────────
function changeSort(value: string) {
    router.get(
        route('roadmap.index'),
        { sort: value },
        {
            preserveState: true,
            replace: true,
        },
    );
}

// ── Optimistic local state ────────────────────────────────────────────────────
const localItems = ref(props.items.map((i) => ({ ...i })));

watch(
    () => props.items,
    (newItems) => {
        localItems.value = newItems.map((i) => ({ ...i }));
    },
    { deep: true },
);

// ── Status grouping ───────────────────────────────────────────────────────────
const groups = computed(() =>
    STATUS_CONFIG.map(({ value, label }) => ({
        status: value,
        label,
        items: localItems.value
            .filter((i) => i.status === value)
            .sort((a, b) =>
                props.sort === 'top' ? b.net_score - a.net_score : 0,
            ),
    })).filter((g) => g.items.length > 0),
);

// ── Vote class helpers ────────────────────────────────────────────────────────
function buttonClass(userVote: 'up' | 'down' | null, type: 'up' | 'down') {
    if (userVote !== type)
        return 'text-muted-foreground hover:bg-muted hover:text-foreground';
    return type === 'up'
        ? 'bg-secondary text-white'
        : 'bg-destructive text-destructive-foreground';
}

function scoreClass(vote: 'up' | 'down' | null) {
    if (vote === 'up') return 'bg-secondary text-white';
    if (vote === 'down') return 'bg-destructive text-destructive-foreground';
    return 'text-foreground';
}

// ── Voting ────────────────────────────────────────────────────────────────────
function showVoteToast(currentVote: 'up' | 'down' | null, type: 'up' | 'down') {
    if (currentVote === type) return toast.info(trans('Vote removed'));
    if (currentVote === null)
        return type === 'up'
            ? toast.success(trans('Upvoted!'))
            : toast.info(trans('Downvoted'));
    return type === 'up'
        ? toast.success(trans('Changed to upvote'))
        : toast.info(trans('Changed to downvote'));
}

function vote(item: RoadmapItem, type: 'up' | 'down') {
    const local = localItems.value.find((i) => i.id === item.id);
    if (!local) return;

    const currentVote = local.user_vote;
    const originalScore = local.net_score;
    const delta = type === 'up' ? 1 : -1;

    // Optimistic update
    if (currentVote === type) {
        local.user_vote = null;
        local.net_score -= delta;
    } else {
        if (currentVote !== null)
            local.net_score -= currentVote === 'up' ? 1 : -1;
        local.net_score += delta;
        local.user_vote = type;
    }

    router.post(
        route('roadmap.vote', item.id),
        { type },
        {
            preserveScroll: true,
            only: ['items'],
            onSuccess: () => showVoteToast(currentVote, type),
            onError: () => {
                local.net_score = originalScore;
                local.user_vote = currentVote;
            },
        },
    );
}

// ── Suggestion dialog ─────────────────────────────────────────────────────────
const dialogOpen = ref(false);

const form = useForm({
    title: '',
    description: '',
    type: props.types[0]?.value ?? 'feature',
});

// ── Badge variant ─────────────────────────────────────────────────────────────
const colorToVariant: Record<
    string,
    'default' | 'destructive' | 'secondary' | 'outline'
> = {
    primary: 'default',
    secondary: 'secondary',
    danger: 'destructive',
    warning: 'secondary',
    success: 'default',
    info: 'secondary',
    gray: 'outline',
};

function typeBadgeVariant(
    item: RoadmapItem,
): 'default' | 'destructive' | 'secondary' | 'outline' {
    const found = props.types.find((t) => t.value === item.type);
    return colorToVariant[found?.color ?? 'primary'] ?? 'default';
}

function openDialog() {
    form.reset();
    dialogOpen.value = true;
}

function submitSuggestion() {
    form.post(route('roadmap.store'), {
        onSuccess: () => {
            dialogOpen.value = false;
            form.reset();
        },
    });
}

// ── Formatting ────────────────────────────────────────────────────────────────
function formatDate(dateStr: string): string {
    return new Date(dateStr).toLocaleDateString(undefined, {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}
</script>

<template>
    <AppLayout :title="title" :breadcrumbs="[{ title }]">
        <div class="flex flex-1 flex-col gap-6 p-6 pt-2">
            <div class="w-full max-w-3xl space-y-6">
                <!-- Header -->
                <div
                    class="mt-6 mb-10 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex items-center gap-3">
                        <div class="bg-primary/10 text-primary rounded-xl p-4">
                            <IconMap class="size-10" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold tracking-tight">
                                {{ $t('Product Roadmap') }}
                            </h1>
                            <p class="text-muted-foreground text-sm">
                                {{
                                    $t(
                                        'Vote on features and suggest new ideas.',
                                    )
                                }}
                            </p>
                        </div>
                    </div>

                    <Button
                        data-testid="suggest-btn"
                        class="w-full sm:w-auto"
                        @click="openDialog"
                    >
                        <IconPlus class="size-5 text-white" />
                        {{ $t('Submit feedback') }}
                    </Button>
                </div>

                <!-- Sort controls -->
                <div
                    v-if="localItems.length > 0"
                    class="flex items-center gap-1.5"
                >
                    <span class="text-muted-foreground mr-1 text-xs">{{
                        $t('Sort:')
                    }}</span>
                    <button
                        v-for="opt in SORT_OPTIONS"
                        :key="opt.value"
                        type="button"
                        :class="[
                            'rounded-md border px-3 py-1 text-xs font-medium transition-colors',
                            sort === opt.value
                                ? 'bg-primary text-primary-foreground border-primary'
                                : 'hover:bg-accent border-border text-muted-foreground',
                        ]"
                        @click="changeSort(opt.value)"
                    >
                        {{ $t(opt.label) }}
                    </button>
                </div>

                <!-- Empty state -->
                <div
                    v-if="localItems.length === 0"
                    class="bg-muted/30 flex flex-col items-center justify-center gap-6 rounded-lg py-20 text-center"
                >
                    <IconMap class="text-muted-foreground size-14" />
                    <p class="text-muted-foreground">
                        {{
                            $t(
                                'No roadmap items yet. Be the first to suggest a feature!',
                            )
                        }}
                    </p>
                    <button
                        type="button"
                        data-testid="suggest-btn-empty"
                        class="border-primary text-primary hover:bg-primary hover:text-primary-foreground rounded-md border px-4 py-1.5 text-sm transition-colors"
                        @click="openDialog"
                    >
                        {{ $t('Submit feedback') }}
                    </button>
                </div>

                <!-- Grouped item list -->
                <div
                    v-for="group in groups"
                    :key="group.status"
                    class="space-y-2"
                >
                    <!-- Group header -->
                    <p
                        class="text-muted-foreground px-1 text-xs font-semibold tracking-wider uppercase"
                    >
                        {{ $t(group.label) }}
                        <span class="ml-1 font-normal"
                            >({{ group.items.length }})</span
                        >
                    </p>

                    <!-- Items -->
                    <TransitionGroup
                        tag="div"
                        class="flex flex-col gap-2"
                        move-class="transition-transform duration-500 ease-out"
                    >
                        <div
                            v-for="item in group.items"
                            :key="item.id"
                            class="bg-muted/50 hover:bg-muted/80 dark:bg-muted/20 dark:hover:bg-muted/40 relative flex items-stretch gap-0 overflow-hidden rounded border transition-colors"
                        >
                            <!-- Vote box -->
                            <div
                                :data-testid="`vote-box-${item.id}`"
                                :data-user-vote="item.user_vote ?? 'none'"
                                class="flex w-12 shrink-0 flex-col border-r"
                            >
                                <button
                                    :data-testid="`upvote-btn-${item.id}`"
                                    :class="[
                                        'flex cursor-pointer items-center justify-center py-3 transition-colors',
                                        buttonClass(item.user_vote, 'up'),
                                    ]"
                                    @click="vote(item, 'up')"
                                >
                                    <IconAltArrowUpBold class="size-5" />
                                </button>
                                <span
                                    :data-testid="`vote-score-${item.id}`"
                                    :class="[
                                        'flex items-center justify-center py-2 text-sm font-semibold tabular-nums',
                                        scoreClass(item.user_vote),
                                    ]"
                                >
                                    {{ item.net_score }}
                                </span>
                                <button
                                    :data-testid="`downvote-btn-${item.id}`"
                                    :class="[
                                        'flex cursor-pointer items-center justify-center py-3 transition-colors',
                                        buttonClass(item.user_vote, 'down'),
                                    ]"
                                    @click="vote(item, 'down')"
                                >
                                    <IconAltArrowDownBold class="size-5" />
                                </button>
                            </div>

                            <!-- Content -->
                            <div
                                class="flex flex-1 flex-col justify-center gap-1 px-4 py-3"
                            >
                                <p class="leading-snug font-semibold">
                                    {{ item.title }}
                                </p>
                                <Badge
                                    :variant="typeBadgeVariant(item)"
                                    class="absolute top-0 right-0 rounded-none rounded-bl text-xs text-white"
                                >
                                    {{ item.type_label }}
                                </Badge>
                                <p
                                    v-if="item.description"
                                    class="line-clamp-1 text-sm"
                                >
                                    {{ item.description }}
                                </p>
                                <p class="text-muted-foreground mt-2 text-xs">
                                    {{ $t('Created on') }}
                                    {{ formatDate(item.created_at) }}
                                </p>
                            </div>
                        </div>
                    </TransitionGroup>
                </div>
            </div>
        </div>

        <!-- Suggestion dialog -->
        <Dialog v-model:open="dialogOpen">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>{{ $t('Submit feedback') }}</DialogTitle>
                    <DialogDescription>
                        {{
                            $t(
                                'We review all submissions before publishing them to the roadmap.',
                            )
                        }}
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitSuggestion" class="space-y-4">
                    <!-- Type toggle -->
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium">{{
                            $t('Type')
                        }}</label>
                        <div class="flex gap-2">
                            <button
                                v-for="t in types"
                                :key="t.value"
                                type="button"
                                :class="[
                                    'rounded-md border px-4 py-1.5 text-sm font-medium transition-colors',
                                    form.type === t.value
                                        ? 'bg-primary text-primary-foreground border-primary'
                                        : 'hover:bg-accent border-border',
                                ]"
                                @click="form.type = t.value"
                            >
                                {{ t.label }}
                            </button>
                        </div>
                    </div>

                    <!-- Title -->
                    <div class="space-y-1.5">
                        <label for="suggest-title" class="text-sm font-medium">
                            {{ $t('Title') }}
                            <span class="text-destructive">*</span>
                        </label>
                        <input
                            id="suggest-title"
                            data-testid="suggest-title"
                            v-model="form.title"
                            type="text"
                            :placeholder="
                                $t('e.g. Dark mode, login bug, faster search…')
                            "
                            maxlength="255"
                            class="border-input bg-background placeholder:text-muted-foreground focus-visible:ring-ring flex h-9 w-full rounded-md border px-3 py-1 text-sm shadow-sm transition-colors focus-visible:ring-1 focus-visible:outline-none"
                        />
                        <p
                            v-if="form.errors.title"
                            class="text-destructive text-xs"
                        >
                            {{ form.errors.title }}
                        </p>
                    </div>

                    <!-- Description -->
                    <div class="space-y-1.5">
                        <label
                            for="suggest-description"
                            class="text-sm font-medium"
                        >
                            {{ $t('Description') }}
                        </label>
                        <textarea
                            id="suggest-description"
                            data-testid="suggest-description"
                            v-model="form.description"
                            :placeholder="
                                $t(
                                    'What\'s the problem or idea? Any details help.',
                                )
                            "
                            rows="3"
                            maxlength="2000"
                            class="border-input bg-background placeholder:text-muted-foreground focus-visible:ring-ring flex w-full resize-none rounded-md border px-3 py-2 text-sm shadow-sm transition-colors focus-visible:ring-1 focus-visible:outline-none"
                        />
                        <p
                            v-if="form.errors.description"
                            class="text-destructive text-xs"
                        >
                            {{ form.errors.description }}
                        </p>
                    </div>

                    <DialogFooter>
                        <Button
                            data-testid="suggest-cancel-btn"
                            type="button"
                            variant="outline"
                            @click="dialogOpen = false"
                        >
                            {{ $t('Cancel') }}
                        </Button>
                        <Button
                            data-testid="suggest-submit-btn"
                            type="submit"
                            :disabled="form.processing"
                        >
                            {{
                                form.processing
                                    ? $t('Submitting…')
                                    : $t('Submit')
                            }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
