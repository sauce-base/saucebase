<script setup lang="ts">
import SiteLayout from '@/layouts/SiteLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

import PostCard from '../components/PostCard.vue';
import PostMeta from '../components/PostMeta.vue';
const props = defineProps<{
    post: Modules.Blog.Data.PostData;
    related: Modules.Blog.Data.PostData[];
}>();

const jsonLd = computed(() => ({
    '@context': 'https://schema.org',
    '@type': 'Article',
    headline: props.post.title,
    description: props.post.excerpt ?? undefined,
    image: props.post.cover_url || undefined,
    datePublished: props.post.published_at ?? undefined,
    author: props.post.author
        ? { '@type': 'Person', name: props.post.author.name }
        : undefined,
    url: props.post.url,
}));
</script>

<template>
    <SiteLayout
        :title="post.title"
        :description="post.excerpt ?? undefined"
        :image="post.cover_url || undefined"
        :canonical="post.url"
        type="article"
    >
        <Head>
            <meta v-if="post.published_at" property="article:published_time" :content="post.published_at" />
            <meta v-if="post.author" property="article:author" :content="post.author.name" />
            <!-- eslint-disable-next-line vue/no-v-text-v-html-on-component -->
            <component :is="'script'" type="application/ld+json" v-html="JSON.stringify(jsonLd)" />
        </Head>
        <main class="mx-auto w-full max-w-3xl flex-1 px-6 py-28">
            <!-- Back link -->
            <Link
                :href="route('blog.index')"
                class="mb-8 inline-flex items-center gap-1 text-sm text-gray-500 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
            >
                ← {{ $t('Back to Blog') }}
            </Link>

            <!-- Title -->
            <h1
                data-testid="post-title"
                class="mb-4 text-4xl leading-tight font-bold text-gray-900 sm:text-5xl dark:text-white"
            >
                {{ post.title }}
            </h1>

            <!-- Meta: author + date -->
            <div class="mb-10">
                <PostMeta :author="post.author" :published-at="post.published_at" />
            </div>

            <!-- Cover image -->
            <div
                v-if="post.cover_url"
                class="mb-12 overflow-hidden rounded-2xl bg-gray-100 dark:bg-gray-800"
            >
                <img
                    :src="post.cover_url"
                    :alt="post.title"
                    class="h-full w-full object-cover"
                />
            </div>

            <!-- Content -->
            <div
                data-testid="post-content"
                class="prose prose-gray dark:prose-invert max-w-none leading-loose"
                v-html="post.content"
            />
        </main>

        <!-- You might also like -->
        <section v-if="related.length > 0">
            <div class="mx-auto w-full max-w-6xl px-6 py-8 mb-16">
                <h2
                    class="mb-10 text-2xl font-bold tracking-tight text-gray-900 dark:text-white"
                >
                    {{ $t('You might also like') }}
                </h2>

                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    <PostCard v-for="item in related" :key="item.id" :post="item" />
                </div>
            </div>
        </section>
    </SiteLayout>
</template>
