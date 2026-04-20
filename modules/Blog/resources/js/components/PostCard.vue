<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

import PostMeta from './PostMeta.vue';
defineProps<{
    post: Modules.Blog.Data.PostData;
}>();
</script>

<template>
    <article
        :data-testid="`post-card-${post.id}`"
        class="group hover:bg-card flex flex-col overflow-hidden rounded-2xl p-2 transition-all duration-200 hover:-translate-y-1 hover:shadow-xl"
    >
        <Link :href="post.url" class="flex flex-1 flex-col">
            <!-- Cover image -->
            <div class="aspect-video overflow-hidden rounded-xl">
                <img
                    v-if="post.cover_url"
                    :src="post.cover_url"
                    :alt="post.title"
                    class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                />
                <div
                    v-else
                    class="relative flex h-full w-full items-center justify-center bg-[linear-gradient(45deg,var(--primary),var(--secondary))] p-6"
                >
                    <span
                        class="text-center text-lg leading-snug font-bold text-white/90 drop-shadow"
                    >
                        {{ post.title }}
                    </span>
                </div>
            </div>

            <div class="flex flex-1 flex-col gap-3 pt-4 px-1 pb-2">
                <!-- Category badge -->
                <div v-if="post.category">
                    <span
                        class="bg-secondary/80 text-secondary-foreground/80 inline-block rounded-full px-2.5 py-1 text-xs font-semibold"
                    >
                        {{ post.category.name }}
                    </span>
                </div>

                <!-- Title -->
                <h2
                    :data-testid="`post-title-${post.id}`"
                    class="text-foreground text-lg font-bold transition-colors group-hover:underline"
                >
                    {{ post.title }}
                </h2>

                <!-- Excerpt -->
                <p
                    v-if="post.excerpt"
                    class="text-muted-foreground line-clamp-3 text-sm"
                >
                    {{ post.excerpt }}
                </p>

                <!-- Author + publish date -->
                <div class="mt-auto pt-2">
                    <PostMeta
                        :author="post.author"
                        :published-at="post.published_at"
                    />
                </div>
            </div>
        </Link>
    </article>
</template>
