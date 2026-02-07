<script setup lang="ts">
const faqs = [
    {
        question: 'What is the copy-and-own philosophy?',
        answer: 'Unlike traditional packages that stay in vendor/, Saucebase modules copy directly into your repository. You own every line of code and can modify, refactor, or rebuild features to match your exact needs.',
    },
    {
        question: 'How do I install modules?',
        answer: 'Run composer require saucebase/module-name, then enable it with php artisan module:enable ModuleName and run migrations. The code is copied to your modules/ directory where you can customize it freely.',
    },
    {
        question: 'Do I need Docker to use Saucebase?',
        answer: "Docker is recommended for consistent development environments, but it's optional. You can use your local PHP, MySQL, and Node.js setup. The saucebase:install command supports both approaches.",
    },
    {
        question: 'What tech stack does Saucebase use?',
        answer: 'Saucebase uses the VILT stack: Vue 3, Inertia.js, Laravel 12, and Tailwind CSS 4. It also includes TypeScript, Filament admin panel, PHPStan, and Playwright for testing.',
    },
    {
        question: 'Can I customize the modules after installation?',
        answer: "Absolutely! That's the whole point. Since modules live in your repository, you can edit controllers, modify Vue components, add database fields, or completely rewrite features to fit your needs.",
    },
    {
        question: 'Is Saucebase free to use?',
        answer: 'Yes! Saucebase is open-source software licensed under the MIT license. You can use it freely in your projects, modify it, and even use it commercially without restrictions.',
    },
];
</script>

<template>
    <div id="faq" class="bg-primary/2 dark:bg-primary/5 isolate py-24 sm:py-32">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h2
                    class="text-4xl font-semibold tracking-tight text-gray-900 sm:text-5xl dark:text-white"
                >
                    {{ $t('Frequently asked questions') }}
                </h2>
                <p class="mt-6 text-lg/8 text-gray-600 dark:text-gray-300">
                    {{
                        $t(
                            "Have a different question and can't find the answer you're looking for? Reach out to our support team by",
                        )
                    }}
                    <a
                        href="https://github.com/sauce-base/saucebase/discussions"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300"
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
                    <h3
                        class="text-base/7 font-semibold text-gray-900 dark:text-white"
                    >
                        {{ $t(faq.question) }}
                    </h3>
                    <p
                        class="mt-2 text-base/7 text-gray-600 dark:text-gray-400"
                    >
                        {{ $t(faq.answer) }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
