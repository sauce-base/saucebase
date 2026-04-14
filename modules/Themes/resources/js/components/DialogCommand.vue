<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { ButtonGroup } from '@/components/ui/button-group';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { trans } from 'laravel-vue-i18n';
import { computed } from 'vue';
import { toast } from 'vue-sonner';

import IconCopy from '~icons/lucide/copy';

const open = defineModel({ default: false });

const props = defineProps<{
    themeId: string;
}>();

const command = computed(
    () => `php artisan saucebase:theme:apply ${props.themeId}`,
);

async function handleCopy() {
    navigator.clipboard
        .writeText(command.value)
        .then(() => toast.success(trans('Copied to clipboard')))
        .catch(() => toast.error(trans('Failed to copy to clipboard')));
}
</script>

<template>
    <Dialog :open="open" @update:open="(v) => (open = v)">
        <DialogContent class="sm:max-w-lg">
            <DialogHeader>
                <DialogTitle>{{ $t('Use this theme') }}</DialogTitle>
                <DialogDescription>
                    {{
                        $t(
                            'Run this command to apply the theme to your project.',
                        )
                    }}
                </DialogDescription>
            </DialogHeader>
            <ButtonGroup class="w-full">
                <Input
                    :model-value="command"
                    readonly
                    class="flex-1 font-mono text-sm"
                />
                <Button variant="outline" size="icon" @click="handleCopy">
                    <IconCopy class="size-4" />
                </Button>
            </ButtonGroup>
        </DialogContent>
    </Dialog>
</template>
