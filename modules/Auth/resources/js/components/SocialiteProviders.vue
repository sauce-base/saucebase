<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import IconGithub from '~icons/simple-icons/github';
import IconGoogle from '~icons/simple-icons/google';

type Provider = { name: string; icon: any };

const providers: Provider[] = [
    { name: 'google', icon: IconGoogle },
    { name: 'github', icon: IconGithub },
];

const lastUsed = computed(() => usePage().props.auth.last_social_provider);
</script>

<template>
    <div
        v-if="route().has('auth.socialite.redirect') && providers.length"
        class="mb-2 space-y-3"
    >
        <div v-for="{ name, icon } in providers" :key="name" class="relative">
            <Button as-child variant="outline" class="w-full">
                <a :href="route('auth.socialite.redirect', { provider: name })">
                    <component :is="icon" class="h-5 w-5" />
                    <span>
                        {{ $t('Connect with :Provider', { provider: name }) }}
                    </span>
                </a>
            </Button>
            <span
                v-if="lastUsed === name"
                :data-testid="`last-used-badge-${name}`"
                class="absolute -top-2 -right-2 rounded-full bg-muted/80 border px-2 py-0.5 text-xs text-muted-foreground drop-shadow-lg"
            >
                {{ $t('Last used') }}
            </span>
        </div>
        <div
            class="after:border-border relative text-center text-sm after:absolute after:inset-0 after:top-1/2 after:z-0 after:flex after:items-center after:border-t"
        >
            <span class="bg-card text-muted-foreground relative z-10 px-2">
                {{ $t('Or continue with email') }}
            </span>
        </div>
    </div>
</template>
