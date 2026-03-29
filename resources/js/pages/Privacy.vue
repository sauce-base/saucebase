<script setup lang="ts">
import Footer from '@/components/Footer.vue';
import Header from '@/components/Header.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head>
        <title>{{ $t('Privacy Policy') }}</title>
        <meta
            name="description"
            :content="
                $t(
                    'Learn how Saucebase collects, uses, and protects your personal information.',
                )
            "
        />
    </Head>
    <div class="py-10">
        <Header />

        <main class="mx-auto w-full max-w-3xl flex-1 px-6 py-16">
            <h1 class="mb-2 text-4xl font-bold text-gray-900 dark:text-white">
                {{ $t('Privacy Policy') }}
            </h1>
            <p class="mb-10 text-sm text-gray-500 dark:text-gray-400">
                {{ $t('Last updated: January 1, 2026') }}
            </p>

            <div class="space-y-10 text-gray-700 dark:text-gray-300">
                <section>
                    <h2
                        class="mb-3 text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        {{ $t('1. Information We Collect') }}
                    </h2>
                    <p>
                        {{
                            $t(
                                'We collect information you provide directly to us, such as when you create an account, make a purchase, or contact us for support. This may include your name, email address, payment information, and any other information you choose to provide.',
                            )
                        }}
                    </p>
                </section>

                <section>
                    <h2
                        class="mb-3 text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        {{ $t('2. How We Use Your Information') }}
                    </h2>
                    <p>
                        {{
                            $t(
                                'We use the information we collect to provide, maintain, and improve our services, process transactions, send you technical notices and support messages, and respond to your comments and questions.',
                            )
                        }}
                    </p>
                </section>

                <section>
                    <h2
                        class="mb-3 text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        {{ $t('3. Cookies') }}
                    </h2>
                    <p>
                        {{
                            $t(
                                'We use cookies and similar tracking technologies to track activity on our service and hold certain information. You can instruct your browser to refuse all cookies or to indicate when a cookie is being sent.',
                            )
                        }}
                    </p>
                </section>

                <section>
                    <h2
                        class="mb-3 text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        {{ $t('4. Third-Party Services') }}
                    </h2>
                    <p>
                        {{
                            $t(
                                'We may employ third-party companies and individuals to facilitate our service, provide the service on our behalf, perform service-related tasks, or assist us in analyzing how our service is used. These third parties have access to your personal data only to perform these tasks.',
                            )
                        }}
                    </p>
                </section>

                <section>
                    <h2
                        class="mb-3 text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        {{ $t('5. Data Retention') }}
                    </h2>
                    <p>
                        {{
                            $t(
                                'We will retain your personal data only for as long as is necessary for the purposes set out in this Privacy Policy. We will retain and use your data to the extent necessary to comply with our legal obligations, resolve disputes, and enforce our policies.',
                            )
                        }}
                    </p>
                </section>

                <section>
                    <h2
                        class="mb-3 text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        {{ $t('6. Security') }}
                    </h2>
                    <p>
                        {{
                            $t(
                                'The security of your data is important to us but remember that no method of transmission over the Internet or method of electronic storage is 100% secure. While we strive to use commercially acceptable means to protect your personal data, we cannot guarantee its absolute security.',
                            )
                        }}
                    </p>
                </section>

                <section>
                    <h2
                        class="mb-3 text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        {{ $t('7. Contact Us') }}
                    </h2>
                    <p>
                        {{
                            $t(
                                'If you have any questions about this Privacy Policy, please contact us at',
                            )
                        }}
                        <a
                            href="mailto:hello@example.com"
                            class="text-primary/70 font-medium underline-offset-4 hover:underline"
                            >hello@example.com</a
                        >.
                    </p>
                </section>
            </div>
        </main>

        <Footer />
    </div>
</template>
