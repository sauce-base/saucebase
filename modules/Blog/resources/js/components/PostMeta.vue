<script setup lang="ts">
defineProps<{
    author: Modules.Blog.Data.AuthorData | null;
    publishedAt: string | null;
}>();

function formatDate(dateStr: string): string {
    return new Date(dateStr).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}
</script>

<template>
    <div class="flex items-center gap-2">
        <img
            v-if="author?.avatar_url"
            :src="author.avatar_url"
            :alt="author.name"
            class="h-6 w-6 rounded-full object-cover"
        />
        <div
            v-else-if="author"
            class="flex h-6 w-6 items-center justify-center rounded-full bg-primary/20 text-xs font-bold text-primary"
        >
            {{ author.name.charAt(0).toUpperCase() }}
        </div>
        <div class="flex items-center gap-3 text-sm">
            <span v-if="author" class="font-semibold text-foreground">{{ author.name }}</span>
            <time v-if="publishedAt" class="text-muted-foreground">{{ formatDate(publishedAt) }}</time>
        </div>
    </div>
</template>
