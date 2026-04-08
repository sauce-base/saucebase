<script setup lang="ts">
import {
    Tooltip,
    TooltipContent,
    TooltipTrigger,
} from '@/components/ui/tooltip';

import IconLock from '~icons/lucide/lock';
import IconLockOpen from '~icons/lucide/lock-open';
import IconMinus from '~icons/lucide/minus';

const props = defineProps<{
    label?: string;
    tooltip?: string;
    tooltipActive?: string;
    tooltipIndeterminate?: string;
}>();

const model = defineModel<boolean | null>({ default: false });

function toggle() {
    model.value = model.value === true ? false : true;
}
</script>

<template>
    <Tooltip>
        <TooltipTrigger as-child>
            <span
                class="flex items-center p-1.5 rounded-full text-xs font-medium transition-colors cursor-pointer shadow-sm"
                :class="
                    model === true
                        ? 'bg-primary text-primary-foreground'
                        : model === null
                          ? 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400'
                          : 'text-muted-foreground hover:bg-accent hover:text-foreground'
                "
                @click.stop="toggle"
                v-bind="$attrs"
            >
                <IconLock v-if="model === true" class="size-3" />
                <IconMinus v-else-if="model === null" class="size-3" />
                <IconLockOpen v-else class="size-3" />
            </span>
        </TooltipTrigger>
        <TooltipContent>
            {{
                model === true
                    ? (props.tooltipActive ?? $t('Active'))
                    : model === null
                      ? (props.tooltipIndeterminate ?? $t('Partially linked'))
                      : (props.tooltip ?? $t('Link'))
            }}
        </TooltipContent>
    </Tooltip>
</template>
