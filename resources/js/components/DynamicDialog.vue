<script setup lang="ts">
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { buttonVariants } from '@/components/ui/button';
import { useDialog } from '@/composables/useDialog';
import { computed } from 'vue';

const { isOpen, options, resolve } = useDialog();

const isCentered = computed(
    () => !options.value.align || options.value.align === 'center',
);

const iconContainerClass = computed(() =>
    options.value.variant === 'destructive'
        ? 'bg-destructive/10 text-destructive'
        : 'bg-primary/10 text-primary',
);

const contentClass = computed(() =>
    isCentered.value
        ? 'flex flex-col items-center text-center'
        : 'flex flex-row items-center gap-4',
);

const iconClass = computed(() => [
    'flex size-14 items-center justify-center rounded-2xl',
    isCentered.value ? 'mb-4' : 'shrink-0',
    iconContainerClass.value,
]);

const headerClass = computed(() =>
    isCentered.value && options.value.icon ? 'sm:text-center' : '',
);
</script>

<template>
    <AlertDialog :open="isOpen">
        <AlertDialogContent class="overflow-hidden p-0 sm:max-w-sm">
            <div data-testid="confirm-dialog">
                <div
                    :class="[
                        'bg-background p-6',
                        options.icon ? contentClass : '',
                    ]"
                >
                    <div v-if="options.icon" :class="iconClass">
                        <component :is="options.icon" class="size-7" />
                    </div>
                    <AlertDialogHeader :class="headerClass">
                        <AlertDialogTitle>{{ options.title }}</AlertDialogTitle>
                        <AlertDialogDescription v-if="options.description">
                            {{ options.description }}
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                </div>
                <div class="bg-muted/50 border-t p-4">
                    <div class="grid grid-cols-2 gap-4">
                        <AlertDialogCancel
                            class="dark:hover:bg-accent dark:hover:text-accent-foreground mt-0 w-full"
                            data-testid="confirm-dialog-cancel"
                            @click="resolve(false)"
                        >
                            {{ options.cancelLabel ?? $t('Cancel') }}
                        </AlertDialogCancel>
                        <AlertDialogAction
                            :class="
                                buttonVariants({
                                    variant: options.variant ?? 'default',
                                })
                            "
                            class="w-full"
                            data-testid="confirm-dialog-confirm"
                            @click="resolve(true)"
                        >
                            {{ options.confirmLabel ?? $t('Confirm') }}
                        </AlertDialogAction>
                    </div>
                </div>
            </div>
        </AlertDialogContent>
    </AlertDialog>
</template>
