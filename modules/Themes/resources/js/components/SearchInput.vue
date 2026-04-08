<script setup lang="ts">
import IconSearch from '~icons/heroicons/magnifying-glass';
import IconX from '~icons/lucide/x';

defineProps<{ placeholder?: string; testId?: string }>();
const model = defineModel<string>({ default: '' });
</script>

<template>
    <div
        class="border-border focus-within:border-ring focus-within:ring-primary flex items-center gap-2 rounded-full border px-3 focus-within:ring-2"
    >
        <input
            v-model="model"
            type="text"
            :placeholder="placeholder"
            :data-testid="testId"
            class="text-foreground placeholder:text-muted-foreground flex-1 border-none bg-transparent text-sm ring-0 outline-none"
        />

        <IconSearch
            v-if="!model"
            class="text-muted-foreground size-4 shrink-0"
            aria-hidden="true"
        />
        <IconX
            v-else
            class="text-muted-foreground size-4 shrink-0 cursor-pointer"
            @click="model = ''"
        />
    </div>
</template>
