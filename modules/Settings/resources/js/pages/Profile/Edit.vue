<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';

import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

import InputField from '@/components/ui/input/InputField.vue';
import type { User } from '@/types';
import { Form, router, usePage } from '@inertiajs/vue3';
import { Camera, Loader2, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

import SettingsLayout from '@/layouts/SettingsLayout.vue';
import PageHeader from '../../components/PageHeader.vue';

const props = defineProps<{
    user: User & {
        has_uploaded_avatar?: boolean;
        has_password?: boolean;
    };
}>();

const page = usePage();

// Avatar Form State
const avatarFile = ref<File | null>(null);
const isUpdatingAvatar = ref(false);
const isRemovingAvatar = ref(false);
const isDeleteDialogOpen = ref(false);

// Reactive avatar preview - syncs with backend updates
const avatarPreview = computed(() => {
    // If a file is selected locally, show that preview
    if (avatarFile.value) {
        return URL.createObjectURL(avatarFile.value);
    }
    // Otherwise, show the avatar from backend (reactive)
    return props.user?.avatar ?? null;
});

// Check if user has an uploaded avatar (not just the default)
const hasUploadedAvatar = computed(() => {
    return props.user?.has_uploaded_avatar ?? false;
});

// Form submission handlers
const handleAvatarChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (file) {
        // Set the selected file for upload
        avatarFile.value = file;
        // Automatically upload the file
        submitAvatarForm();
    }

    // Reset file input to allow re-selecting same file
    target.value = '';
};

const submitAvatarForm = () => {
    if (!avatarFile.value) return;

    isUpdatingAvatar.value = true;
    const formData = new FormData();
    formData.append('avatar', avatarFile.value);

    router.post(route('settings.profile.update-avatar'), formData, {
        onFinish: () => {
            avatarFile.value = null;
            isUpdatingAvatar.value = false;
        },
    });
};

const removeAvatar = () => {
    isDeleteDialogOpen.value = true;
};

const confirmRemoveAvatar = () => {
    isRemovingAvatar.value = true;
    isDeleteDialogOpen.value = false;

    router.delete(route('settings.profile.delete-avatar'), {
        onFinish: () => {
            isRemovingAvatar.value = false;
        },
    });
};

const userInitials = computed(() => {
    const name = props.user?.name ?? '';
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
});
</script>

<template>
    <SettingsLayout :title="$t('Edit Profile')">
        <template #header>
            <PageHeader
                :title="$t('Edit Profile')"
                :back-url="route('settings.profile')"
            />
        </template>

        <Card class="max-w-3xl">
            <CardHeader>
                <CardTitle>{{ $t('Profile Information') }}</CardTitle>
                <CardDescription>
                    {{ $t('Update your name and email address') }}
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div class="flex flex-col gap-8 md:flex-row md:gap-10">
                    <!-- Avatar Section -->
                    <div
                        class="flex flex-col items-center gap-2 md:items-start"
                    >
                        <div class="group relative">
                            <Avatar
                                class="size-36 border-4 border-gray-300 shadow-lg dark:border-white"
                            >
                                <AvatarImage
                                    :src="avatarPreview ?? ''"
                                    :alt="user?.name"
                                />
                                <AvatarFallback class="text-3xl">
                                    {{ userInitials }}
                                </AvatarFallback>
                            </Avatar>

                            <!-- Loading Overlay -->
                            <div
                                v-if="isUpdatingAvatar || isRemovingAvatar"
                                class="absolute inset-0 flex items-center justify-center rounded-full bg-black/50"
                            >
                                <Loader2
                                    class="size-8 animate-spin text-white"
                                />
                            </div>

                            <!-- Hidden File Input -->
                            <input
                                type="file"
                                id="avatar-upload"
                                name="avatar"
                                accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                                class="hidden"
                                @change="handleAvatarChange"
                            />

                            <!-- Camera Icon Overlay (when no avatar uploaded) -->
                            <label
                                v-if="
                                    !hasUploadedAvatar &&
                                    !isUpdatingAvatar &&
                                    !isRemovingAvatar
                                "
                                for="avatar-upload"
                                class="bg-primary text-primary-foreground absolute right-0 bottom-0 flex h-10 w-10 cursor-pointer items-center justify-center rounded-full shadow-md transition-transform hover:scale-110"
                            >
                                <Camera class="h-5 w-5" />
                            </label>

                            <!-- Delete Icon Overlay (when avatar exists - replaces camera icon) -->
                            <button
                                v-if="
                                    hasUploadedAvatar &&
                                    !isUpdatingAvatar &&
                                    !isRemovingAvatar
                                "
                                type="button"
                                @click="removeAvatar"
                                class="absolute right-0 bottom-0 flex h-10 w-10 items-center justify-center rounded-full bg-red-500 text-white shadow-md transition-transform hover:scale-110"
                            >
                                <Trash2 class="h-5 w-5" />
                            </button>
                        </div>

                        <!-- Error Display -->
                        <div
                            v-if="page.props.errors.avatar"
                            class="text-destructive text-xs"
                        >
                            {{ page.props.errors.avatar }}
                        </div>
                    </div>

                    <!-- Form Section -->
                    <div class="flex-1">
                        <Form
                            :action="route('settings.profile.update-info')"
                            method="patch"
                            class="space-y-4"
                            disable-while-processing
                        >
                            <InputField
                                name="name"
                                type="text"
                                :label="$t('Name')"
                                :placeholder="$t('Enter your full name')"
                                autocomplete="name"
                                required
                                :model-value="user?.name"
                            />

                            <InputField
                                name="email"
                                type="email"
                                :label="$t('Email')"
                                :placeholder="$t('Enter your email address')"
                                autocomplete="email"
                                required
                                :model-value="user?.email"
                            />

                            <div class="flex justify-end gap-2 pt-2">
                                <Button
                                    type="submit"
                                    size="lg"
                                    class="rounded-full"
                                >
                                    {{ $t('Update Profile') }}
                                </Button>
                            </div>
                        </Form>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Delete Avatar Confirmation Dialog -->
        <Dialog v-model:open="isDeleteDialogOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{{ $t('Remove Avatar') }}</DialogTitle>
                    <DialogDescription>
                        {{
                            $t(
                                'Are you sure you want to remove your avatar? This action cannot be undone.',
                            )
                        }}
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button
                        variant="outline"
                        @click="isDeleteDialogOpen = false"
                    >
                        {{ $t('Cancel') }}
                    </Button>
                    <Button
                        variant="destructive"
                        @click="confirmRemoveAvatar"
                        :disabled="isRemovingAvatar"
                    >
                        {{
                            isRemovingAvatar ? $t('Removing...') : $t('Remove')
                        }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </SettingsLayout>
</template>
