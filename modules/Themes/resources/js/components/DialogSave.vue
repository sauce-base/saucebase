<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { router, useHttp } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';

const open = defineModel({ default: false });

const props = defineProps<{
    toJson: (name: string) => { name: string; title: string; description: string; cssVars: { theme?: Record<string, string>; light: Record<string, string>; dark: Record<string, string> } };
    onThemeSaved?: (id: string) => void;
}>();

const name = ref('');
const loading = ref(false);
const isLoading = computed(() => loading.value);

const handleCancel = () => {
    open.value = false;
    name.value = '';
};

const handleSave = async () => {
    const payload = props.toJson(name.value);
    const http = useHttp(payload).withAllErrors();

    http.post(route('themes.store'), {
        onBefore() {
            loading.value = true;
        },
        onSuccess() {
            router.reload({
                only: ['themes'],
                onSuccess: () => {
                    props.onThemeSaved?.(payload.name);
                    open.value = false;
                    name.value = '';
                    toast.success(trans('Theme saved successfully'), { testId: 'theme-saved-toast' });
                    loading.value = false;
                },
            });
        },
        onError: (errors) => {
            if (errors?.name) toast.error(trans(errors.name));
            else toast.error(trans('Failed to save theme'));
            return true;
        },
        onFinish: () => {
            loading.value = false;
        },
    });
};
</script>

<template>
    <Dialog :open="open" @update:open="(v) => (open = v)">
        <DialogContent
            class="sm:max-w-sm"
            @escape-key-down="(e) => e.preventDefault()"
            @pointer-down-outside="(e) => e.preventDefault()"
        >
            <DialogHeader>
                <DialogTitle>{{ $t('Save theme') }}</DialogTitle>
                <DialogDescription>
                    {{
                        $t(
                            'Save this theme to reuse it later without reconfiguring colors, fonts, and radius.',
                        )
                    }}
                </DialogDescription>
            </DialogHeader>
            <Input
                v-model="name"
                data-testid="save-theme-name"
                :placeholder="$t('Theme name')"
                :disabled="isLoading"
                @keydown.enter="handleSave"
            />
            <DialogFooter>
                <Button variant="destructive" data-testid="save-theme-cancel" @click="handleCancel">
                    {{ $t('Cancel') }}
                </Button>
                <Button data-testid="save-theme-submit" :disabled="!name.trim() || isLoading" @click="handleSave">
                    {{ isLoading ? $t('Saving...') : $t('Save') }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
