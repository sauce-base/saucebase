<script setup lang="ts">
const faqs = [
    {
        question: 'What is the copy-and-own philosophy?',
        answer: 'Unlike traditional packages that stay in vendor/, Saucebase modules copy directly into your repository. You own every line of code and can modify, refactor, or rebuild any feature, with no forking, no pull requests, no waiting for maintainers.',
    },
    {
        question: 'How do I install modules?',
        answer: 'Run php artisan saucebase:install to launch the interactive installer. It prompts you to select modules, runs composer require, enables them, and runs migrations. The code lands in modules/. Open the files and customize freely.',
    },
    {
        question: 'Do I need Docker to use Saucebase?',
        answer: 'Docker is recommended for a consistent environment, but optional. You can use a local PHP, MySQL, and Node.js setup. The bin/setup-env script handles Docker automatically; skip it and configure .env manually if you prefer.',
    },
    {
        question: 'What tech stack does Saucebase use?',
        answer: 'Saucebase uses the VILT stack: Vue 3 (Composition API), Inertia.js 3, Laravel 13, and Tailwind CSS 4. It also includes TypeScript, Filament 5 admin panel, PHPStan level 5, and Playwright for E2E testing.',
    },
    {
        question: 'Can I customize the modules after installation?',
        answer: 'Completely. Since modules live in your repository, you can edit controllers, modify Vue components, add database columns, or rewrite features entirely. There are no APIs to override or packages to fork.',
    },
    {
        question: 'Is Saucebase free to use?',
        answer: 'Yes. Saucebase is open-source software licensed under MIT. Use it freely in personal or commercial projects, modify it, and distribute it without restrictions.',
    },
];
</script>

<template>
    <div id="faq" class="bg-primary/2 dark:bg-primary/5 isolate py-24 sm:py-32">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h2
                    class="text-foreground text-4xl font-semibold tracking-tight sm:text-5xl"
                >
                    {{ $t('Frequently asked questions') }}
                </h2>
                <p class="text-muted-foreground mt-6 text-lg/8">
                    {{
                        $t(
                            "Have a different question and can't find the answer you're looking for? Reach out to our support team by",
                        )
                    }}
                    <a
                        href="https://github.com/saucebase-dev/saucebase/discussions"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="text-primary hover:text-primary/80 font-semibold"
                    >
                        {{ $t('opening a discussion') }}
                    </a>
                    {{ $t("and we'll get back to you as soon as we can.") }}
                </p>
            </div>
            <div
                class="mx-auto mt-16 grid max-w-2xl grid-cols-1 gap-x-8 gap-y-10 lg:max-w-none lg:grid-cols-3"
            >
                <div v-for="(faq, index) in faqs" :key="index">
                    <h3 class="text-foreground text-base/7 font-semibold">
                        {{ $t(faq.question) }}
                    </h3>
                    <p class="text-muted-foreground mt-2 text-base/7">
                        {{ $t(faq.answer) }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
