<script setup lang="ts">
import SiteLayout from '@/layouts/SiteLayout.vue';
import { Link } from '@inertiajs/vue3';

import PostCard from '../components/PostCard.vue';
import type { PaginatedPosts } from '../types';

defineProps<{
    posts: PaginatedPosts;
}>();
</script>

<template>
    <SiteLayout
        :title="$t('Blog')"
        :description="$t('Tips, insights, and updates from our team.')"
    >
        <div class="w-full py-16">
            <main class="mx-auto w-full max-w-5xl flex-1 px-6 py-16">
                <!-- Header -->
                <div class="mb-18">
                    <h1
                        class="mb-3 text-5xl font-bold tracking-tight text-gray-900 dark:text-white"
                    >
                        {{ $t('Blog') }}
                    </h1>
                    <p class="text-2xl text-gray-500 dark:text-gray-400">
                        {{ $t('Tips, insights, and updates from our team.') }}
                    </p>
                </div>

                <!-- Empty state -->
                <div
                    v-if="posts.data.length === 0"
                    class="flex flex-col items-center justify-center py-20 text-center"
                >
                    <p class="text-gray-500 dark:text-gray-400">
                        {{ $t('No posts yet. Check back soon!') }}
                    </p>
                </div>

                <!-- Post grid -->
                <div
                    v-else
                    class="grid grid-cols-1 gap-8 sm:grid-cols-1 lg:grid-cols-2"
                >
                    <PostCard v-for="post in posts.data" :key="post.id" :post="post" />
                </div>

                <!-- Pagination -->
                <div
                    v-if="posts.last_page > 1"
                    class="mt-14 flex justify-center gap-2"
                >
                    <Link
                        v-if="posts.prev_page_url"
                        :href="posts.prev_page_url"
                        class="mt-8 cursor-pointer rounded-xl px-4 py-3 font-semibold shadow-lg transition-all duration-200 focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-2 text-gray-900 ring-1 ring-gray-200 ring-inset hover:ring-gray-300 dark:bg-white/10 dark:text-white dark:ring-white/10 dark:hover:bg-white/20"
                    >
                        {{ $t('← Previous') }}
                    </Link>
                    <Link
                        v-if="posts.next_page_url"
                        :href="posts.next_page_url"
                        class="mt-8 cursor-pointer rounded-xl px-4 py-3 font-semibold shadow-lg transition-all duration-200 focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-2 text-gray-900 ring-1 ring-gray-200 ring-inset hover:ring-gray-300 dark:bg-white/10 dark:text-white dark:ring-white/10 dark:hover:bg-white/20"
                    >
                        {{ $t('Next →') }}
                    </Link>
                </div>
            </main>
        </div>
    </SiteLayout>
</template>
