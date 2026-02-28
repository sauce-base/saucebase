<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Form, Link } from '@inertiajs/vue3';
import AuthCardLayout from '../layouts/AuthCardLayout.vue';
</script>

<template>
    <AuthCardLayout
        :title="$t('Email Verification')"
        :description="
            $t(
                'Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.',
            )
        "
    >
        <Form
            :action="route('verification.send')"
            method="post"
            class="min-w-sm space-y-3"
            data-testid="verify-email-form"
            disable-while-processing
        >
            <Button type="submit" class="w-full">
                {{ $t('Resend Verification Email') }}
            </Button>

            <p
                class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400"
            >
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="cursor-pointer font-medium text-indigo-600 hover:text-indigo-500 hover:underline dark:text-indigo-400 dark:hover:text-indigo-300"
                    data-testid="logout-link"
                >
                    {{ $t('Log Out') }}
                </Link>
            </p>
        </Form>
    </AuthCardLayout>
</template>
