<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

import PostCard from './PostCard.vue';
defineProps<{
    posts: Modules.Blog.Data.PostData[];
}>();
</script>

<template>
    <section class="mx-auto w-full max-w-6xl px-6 py-24">
        <div class="mb-12 flex items-end justify-between">
            <div>
                <h2 class="text-4xl font-bold tracking-tight text-gray-900 dark:text-white">
                    {{ $t('From the blog') }}
                </h2>
                <p class="mt-2 text-lg text-gray-500 dark:text-gray-400">
                    {{ $t('Tips, insights, and updates from our team.') }}
                </p>
            </div>
            <Link
                :href="route('blog.index')"
                class="text-primary shrink-0 text-sm font-medium underline-offset-4 hover:underline"
            >
                {{ $t('View all') }} →
            </Link>
        </div>

        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
            <PostCard v-for="post in posts" :key="post.id" :post="post" />
        </div>
    </section>
</template>
