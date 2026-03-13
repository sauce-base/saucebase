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

import Separator from '@/components/ui/separator/Separator.vue';
import SettingsLayout from '@/layouts/SettingsLayout.vue';
import type { User } from '@/types';
import { Link, router } from '@inertiajs/vue3';
import { Loader2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import IconGithub from '~icons/simple-icons/github';
import IconGoogle from '~icons/simple-icons/google';

const title = 'Profile';

const props = defineProps<{
    user: User & {
        social_accounts?: Array<{
            provider: string;
            last_login_at: string;
            provider_avatar_url?: string;
        }>;
    };
    available_providers?: Array<{
        name: string;
        label: string;
    }>;
}>();

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

const formatDate = (date: string | null) => {
    if (!date) return 'Never';
    return new Date(date).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

// Social Accounts State
const isDisconnecting = ref<string | null>(null);
const isDisconnectDialogOpen = ref(false);
const providerToDisconnect = ref<string | null>(null);

// Social Accounts Helpers
const isProviderConnected = (providerName: string): boolean => {
    return (
        props.user?.social_accounts?.some(
            (account) => account.provider === providerName,
        ) ?? false
    );
};

const getConnectedAccount = (providerName: string) => {
    return props.user?.social_accounts?.find(
        (account) => account.provider === providerName,
    );
};

const formatLastLogin = (date: string): string => {
    return new Date(date).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const initiateDisconnect = (provider: string) => {
    providerToDisconnect.value = provider;
    isDisconnectDialogOpen.value = true;
};

const confirmDisconnect = () => {
    if (!providerToDisconnect.value) return;

    const provider = providerToDisconnect.value;
    isDisconnecting.value = provider;
    isDisconnectDialogOpen.value = false;

    router.delete(route('auth.socialite.disconnect', provider), {
        onFinish: () => {
            isDisconnecting.value = null;
            providerToDisconnect.value = null;
        },
        onError: () => {
            isDisconnecting.value = null;
        },
    });
};

const providerIcons: Record<string, any> = {
    google: IconGoogle,
    github: IconGithub,
};

const getProviderIcon = (providerName: string) => {
    return providerIcons[providerName.toLowerCase()];
};

const hasSocialiteProviders = computed(() => {
    return (
        route().has('auth.socialite.redirect') &&
        props.available_providers &&
        props.available_providers.length > 0
    );
});
</script>
<template>
    <SettingsLayout :title="title">
        <div class="grid gap-6">
            <!-- Profile Overview Card -->
            <Card class="max-w-3xl">
                <CardHeader>
                    <CardTitle>{{ $t('Profile Information') }}</CardTitle>
                    <CardDescription>
                        {{
                            $t('Your personal information and account details')
                        }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="flex flex-col gap-6 sm:flex-row sm:items-start">
                        <!-- Avatar -->
                        <div class="flex justify-center sm:justify-start">
                            <Avatar class="ring-border h-24 w-24 ring-2">
                                <AvatarImage
                                    :src="props.user.avatar ?? ''"
                                    :alt="props.user.name"
                                />
                                <AvatarFallback class="text-2xl">
                                    {{ getInitials(props.user.name) }}
                                </AvatarFallback>
                            </Avatar>
                        </div>

                        <!-- User Details -->
                        <div class="flex-1 space-y-4">
                            <div class="grid gap-4">
                                <div>
                                    <div
                                        class="text-muted-foreground text-sm font-medium"
                                    >
                                        {{ $t('Name') }}
                                    </div>
                                    <div class="mt-1 text-lg font-semibold">
                                        {{ props.user.name }}
                                    </div>
                                </div>
                                <div>
                                    <div
                                        class="text-muted-foreground text-sm font-medium"
                                    >
                                        {{ $t('Email') }}
                                    </div>
                                    <div class="mt-1 text-lg font-semibold">
                                        {{ props.user.email }}
                                    </div>
                                </div>
                            </div>

                            <Separator />

                            <div>
                                <div
                                    class="text-muted-foreground text-sm font-medium"
                                >
                                    {{ $t('Last Login') }}
                                </div>
                                <div class="mt-1">
                                    {{ formatDate(props.user.last_login_at) }}
                                </div>
                            </div>

                            <div class="flex gap-2 pt-2">
                                <Link :href="route('settings.profile.edit')">
                                    <Button variant="outline">
                                        {{ $t('Edit Profile') }}
                                    </Button>
                                </Link>
                                <Link
                                    :href="
                                        route('settings.profile.password.edit')
                                    "
                                >
                                    <Button variant="outline">
                                        {{ $t('Change Password') }}
                                    </Button>
                                </Link>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Connected Social Accounts -->
            <Card v-if="hasSocialiteProviders" class="max-w-3xl">
                <CardHeader>
                    <CardTitle>{{ $t('Connected Accounts') }}</CardTitle>
                    <CardDescription>
                        {{ $t('Manage your connected social login providers') }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div
                            v-for="provider in available_providers"
                            :key="provider.name"
                            class="flex items-center justify-between rounded-lg border p-4"
                        >
                            <div class="flex items-center gap-4">
                                <!-- Provider Icon -->
                                <component
                                    v-if="getProviderIcon(provider.name)"
                                    :is="getProviderIcon(provider.name)"
                                    class="size-6"
                                    :class="{
                                        'opacity-50': !isProviderConnected(
                                            provider.name,
                                        ),
                                    }"
                                />

                                <!-- Provider Name -->
                                <div>
                                    <p class="font-medium">
                                        {{ provider.label }}
                                    </p>
                                    <p
                                        v-if="
                                            isProviderConnected(provider.name)
                                        "
                                        class="text-muted-foreground text-sm"
                                    >
                                        {{ $t('Last login') }}:
                                        {{
                                            formatLastLogin(
                                                getConnectedAccount(
                                                    provider.name,
                                                )?.last_login_at ?? '',
                                            )
                                        }}
                                    </p>
                                    <p
                                        v-else
                                        class="text-muted-foreground text-sm"
                                    >
                                        {{ $t('Not connected') }}
                                    </p>
                                </div>
                            </div>

                            <!-- Status Badge & Action Button -->
                            <div class="flex items-center gap-3">
                                <!-- Disconnect Button (for connected providers) -->
                                <Button
                                    v-if="isProviderConnected(provider.name)"
                                    variant="destructive"
                                    size="sm"
                                    @click="initiateDisconnect(provider.name)"
                                    :disabled="
                                        isDisconnecting === provider.name
                                    "
                                >
                                    <Loader2
                                        v-if="isDisconnecting === provider.name"
                                        class="mr-2 size-4 animate-spin"
                                    />

                                    {{
                                        isDisconnecting === provider.name
                                            ? $t('Disconnecting...')
                                            : $t('Disconnect')
                                    }}
                                </Button>

                                <!-- Connect Button (for not connected providers) -->
                                <Button
                                    v-else
                                    as="a"
                                    :href="
                                        route(
                                            'auth.socialite.redirect',
                                            provider.name,
                                        )
                                    "
                                    variant="default"
                                    size="sm"
                                >
                                    {{ $t('Connect') }}
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Disconnect Social Account Confirmation Dialog -->
        <Dialog v-model:open="isDisconnectDialogOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {{ $t('Disconnect Social Account') }}
                    </DialogTitle>
                    <DialogDescription>
                        {{
                            $t(
                                'Are you sure you want to disconnect this social account? You can reconnect it anytime.',
                            )
                        }}
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button
                        variant="outline"
                        @click="isDisconnectDialogOpen = false"
                    >
                        {{ $t('Cancel') }}
                    </Button>
                    <Button variant="destructive" @click="confirmDisconnect">
                        {{ $t('Disconnect') }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </SettingsLayout>
</template>
