<script setup lang="ts">
import { useSettings } from '@/composables/useSettings';
import { useSettingsModal } from '@/composables/useSettingsModal';
import { resolveIcon } from '@/lib/navigation';
import { cn, resolveModularPageComponent } from '@/lib/utils';
import type { SettingsSection } from '@js/settings';
import { router } from '@inertiajs/vue3';
import { Modal, useModal } from '@inertiaui/modal-vue';
import { X } from '@lucide/vue';
import { computed, onUnmounted, shallowRef, watch, type Component } from 'vue';

const props = defineProps<{
    activeSection: string | null;
    sectionProps: Record<string, Record<string, unknown>>;
}>();

const settings = useSettings();
const { section: fragmentSection, open, restore } = useSettingsModal();
const modal = useModal();

const sections = computed<SettingsSection[]>(
    () => settings.value.sections ?? [],
);

/** The fragment wins; the server prop is the fallback for the first render. */
const current = computed(
    () =>
        sections.value.find(
            (item) =>
                item.slug === (fragmentSection.value ?? props.activeSection),
        ) ?? sections.value[0],
);

const panel = shallowRef<Component | null>(null);

watch(
    current,
    async (item) => {
        if (!item?.component) {
            panel.value = null;
            return;
        }

        const resolved = await resolveModularPageComponent(item.component);
        panel.value = (resolved as { default?: Component }).default ?? resolved;
    },
    { immediate: true },
);

/**
 * Sections other than the one first requested arrive as optional props, so the
 * first time one is shown its data is fetched with a partial reload. Reload
 * targets the same URL, which is all the modal supports, so the panel swap
 * happens without leaving or restacking the modal.
 */
watch(
    current,
    (item) => {
        if (!item?.slug || props.sectionProps?.[item.slug] !== undefined) {
            return;
        }

        modal?.reload({ only: [`sectionProps.${item.slug}`] });
    },
    { immediate: true },
);

/**
 * Refresh the active section after a panel saves something.
 *
 * Panels post to their own routes, which update the *page* props behind the
 * modal. The panel itself renders from the *modal's* props, so without this a
 * save would leave the panel showing stale data — an uploaded avatar, for
 * instance, would never reveal its remove button. Reload uses the modal's own
 * XHR client rather than the router, so this cannot feed back into itself.
 */
onUnmounted(
    router.on('success', () => {
        const slug = current.value?.slug;

        if (!slug) {
            return;
        }

        modal?.reload({
            only: [`sectionProps.${slug}`],
            onSuccess: () => restore(slug),
        });
        restore(slug);
    }),
);

const panelProps = computed(
    () => props.sectionProps?.[current.value?.slug ?? ''],
);

/**
 * A section's props arrive with the modal only when it was the one requested;
 * the rest are fetched on first view. Panels are written against their own data,
 * so one must not be handed an empty object while that request is in flight.
 */
const isPanelReady = computed(
    () => panel.value !== null && panelProps.value !== undefined,
);
</script>

<template>
    <Modal max-width="4xl" :padding-classes="false" :close-button="false">
        <!--
            `tabindex="0"` on the panel itself, rather than a focus call that has
            to beat the modal's focus trap: the trap focuses the first focusable
            element in the dialog, so being that element is what keeps a focus
            ring off whichever control happens to come first.
        -->
        <div
            tabindex="0"
            class="flex h-[80vh] outline-none"
            data-testid="settings-modal"
        >
            <!-- Section navigation -->
            <nav
                class="bg-muted/40 w-56 shrink-0 overflow-y-auto border-r p-3"
                data-testid="settings-sidebar"
            >
                <p
                    class="text-muted-foreground px-3 pt-2 pb-3 text-xs font-medium tracking-wide uppercase"
                >
                    {{ $t('Settings') }}
                </p>
                <ul class="space-y-1">
                    <li v-for="item in sections" :key="item.slug">
                        <button
                            type="button"
                            :data-testid="`settings-section-${item.slug}`"
                            :aria-current="
                                current?.slug === item.slug ? 'page' : undefined
                            "
                            :class="
                                cn(
                                    'flex w-full cursor-pointer items-center gap-2 rounded-md px-3 py-2 text-sm transition-colors',
                                    current?.slug === item.slug
                                        ? 'bg-accent text-accent-foreground font-medium'
                                        : 'text-muted-foreground hover:bg-accent/50 hover:text-foreground',
                                )
                            "
                            @click="open(item.slug)"
                        >
                            <component
                                :is="resolveIcon(item.icon)"
                                v-if="item.icon && resolveIcon(item.icon)"
                                class="size-4 shrink-0"
                            />
                            {{ $t(item.title) }}
                        </button>
                    </li>
                </ul>
            </nav>

            <!-- Active section -->
            <div class="flex min-w-0 flex-1 flex-col">
                <!-- Fixed header: the close control must not scroll away with
                     the section, and only the section below it scrolls. -->
                <div class="flex shrink-0 justify-end p-3">
                    <button
                        type="button"
                        class="text-muted-foreground hover:bg-accent hover:text-foreground focus-visible:ring-ring cursor-pointer rounded-md p-1.5 transition-colors focus-visible:ring-2 focus-visible:outline-none"
                        data-testid="settings-close"
                        :aria-label="$t('Close')"
                        @click="modal?.close()"
                    >
                        <X class="size-4" />
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto px-6 pb-6">
                    <component
                        :is="panel"
                        v-if="isPanelReady"
                        v-bind="panelProps"
                        :key="current?.slug"
                    />

                    <!-- Section data still loading -->
                    <div
                        v-else-if="current"
                        class="space-y-4"
                        aria-busy="true"
                        data-testid="settings-panel-loading"
                    >
                        <div class="bg-muted h-6 w-40 animate-pulse rounded" />
                        <div class="bg-muted h-4 w-64 animate-pulse rounded" />
                        <div
                            class="bg-muted h-32 w-full animate-pulse rounded-lg"
                        />
                    </div>

                    <div
                        v-else
                        class="text-muted-foreground flex h-full items-center justify-center text-sm"
                    >
                        {{ $t('Select a section') }}
                    </div>
                </div>
            </div>
        </div>
    </Modal>
</template>
