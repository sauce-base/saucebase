<script setup lang="ts">
import Footer from '@/components/Footer.vue';
import Header from '@/components/Header.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head>
        <title>{{ $t('Terms of Service') }}</title>
        <meta
            name="description"
            :content="
                $t(
                    'Read our Terms of Service to understand the rules and guidelines for using Saucebase.',
                )
            "
        />
    </Head>
    <div class="py-10">
        <Header />

        <main class="mx-auto w-full max-w-3xl flex-1 px-6 py-16">
            <h1 class="mb-2 text-4xl font-bold text-gray-900 dark:text-white">
                {{ $t('Terms of Service') }}
            </h1>
            <p class="mb-10 text-sm text-gray-500 dark:text-gray-400">
                {{ $t('Last updated: January 1, 2026') }}
            </p>

            <div class="space-y-10 text-gray-700 dark:text-gray-300">
                <section>
                    <h2
                        class="mb-3 text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        {{ $t('1. Acceptance of Terms') }}
                    </h2>
                    <p>
                        {{
                            $t(
                                'By accessing or using Saucebase, you agree to be bound by these Terms of Service. If you do not agree to these terms, please do not use our service.',
                            )
                        }}
                    </p>
                </section>

                <section>
                    <h2
                        class="mb-3 text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        {{ $t('2. Use of Service') }}
                    </h2>
                    <p>
                        {{
                            $t(
                                'You agree to use Saucebase only for lawful purposes and in accordance with these Terms. You must not use the service in any way that violates applicable local, national, or international laws or regulations.',
                            )
                        }}
                    </p>
                </section>

                <section>
                    <h2
                        class="mb-3 text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        {{ $t('3. Accounts') }}
                    </h2>
                    <p>
                        {{
                            $t(
                                'When you create an account with us, you must provide accurate and complete information. You are responsible for safeguarding your account credentials and for all activity that occurs under your account.',
                            )
                        }}
                    </p>
                </section>

                <section>
                    <h2
                        class="mb-3 text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        {{ $t('4. Intellectual Property') }}
                    </h2>
                    <p>
                        {{
                            $t(
                                'The service and its original content, features, and functionality are and will remain the exclusive property of Saucebase and its licensors. Our trademarks and trade dress may not be used without our prior written consent.',
                            )
                        }}
                    </p>
                </section>

                <section>
                    <h2
                        class="mb-3 text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        {{ $t('5. Payments and Refunds') }}
                    </h2>
                    <p>
                        {{
                            $t(
                                'Certain features of Saucebase may require payment. You agree to pay all applicable fees as described on our pricing page. All payments are non-refundable unless otherwise specified or required by law.',
                            )
                        }}
                    </p>
                </section>

                <section>
                    <h2
                        class="mb-3 text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        {{ $t('6. Disclaimers') }}
                    </h2>
                    <p>
                        {{
                            $t(
                                'Saucebase is provided on an "as is" and "as available" basis without any warranties of any kind, either express or implied. We do not warrant that the service will be uninterrupted, error-free, or completely secure.',
                            )
                        }}
                    </p>
                </section>

                <section>
                    <h2
                        class="mb-3 text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        {{ $t('7. Limitation of Liability') }}
                    </h2>
                    <p>
                        {{
                            $t(
                                'To the fullest extent permitted by law, Saucebase shall not be liable for any indirect, incidental, special, consequential, or punitive damages arising from your use of the service.',
                            )
                        }}
                    </p>
                </section>

                <section>
                    <h2
                        class="mb-3 text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        {{ $t('8. Changes to Terms') }}
                    </h2>
                    <p>
                        {{
                            $t(
                                'We reserve the right to modify these terms at any time. We will notify users of significant changes by updating the date at the top of this page. Your continued use of the service after changes constitutes acceptance of the new terms.',
                            )
                        }}
                    </p>
                </section>

                <section>
                    <h2
                        class="mb-3 text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        {{ $t('9. Contact Us') }}
                    </h2>
                    <p>
                        {{
                            $t(
                                'If you have any questions about these Terms of Service, please contact us at',
                            )
                        }}
                        <a
                            href="mailto:hello@example.com"
                            class="text-indigo-600 hover:underline dark:text-indigo-400"
                            >hello@example.com</a
                        >.
                    </p>
                </section>
            </div>
        </main>

        <Footer />
    </div>
</template>
