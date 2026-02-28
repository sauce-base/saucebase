<script setup lang="ts">
import SettingsLayout from '@/layouts/SettingsLayout.vue';

const title = 'Settings';
</script>

<template>
    <SettingsLayout :title="title">
        <template #header>
            <h1 class="text-2xl font-bold">
                {{ $t('General Settings') }}
            </h1>
        </template>

        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div class="bg-muted/50 aspect-video rounded-xl" />
            <div class="bg-muted/50 aspect-video rounded-xl" />
            <div class="bg-muted/50 aspect-video rounded-xl" />
        </div>
        <div class="bg-muted/50 min-h-[100vh] flex-1 rounded-xl md:min-h-min" />
    </SettingsLayout>
</template>
