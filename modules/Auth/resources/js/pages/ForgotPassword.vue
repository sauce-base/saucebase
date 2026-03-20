<script setup lang="ts">
import { Button } from '@/components/ui/button';
import InputField from '@/components/ui/input/InputField.vue';
import { Form, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AuthCardLayout from '../layouts/AuthCardLayout.vue';

const page = usePage();
const email = computed(() =>
    page.props.email ? String(page.props.email) : undefined,
);
</script>

<template>
    <AuthCardLayout
        :title="$t('Forgot Password')"
        :description="
            $t(
                'Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.',
            )
        "
    >
        <Form
            :action="route('password.email')"
            method="post"
            class="w-full space-y-3"
            data-testid="forgot-password-form"
            disable-while-processing
            :reset-on-success="['email']"
        >
            <InputField
                name="email"
                type="email"
                :label="$t('Email')"
                :placeholder="$t('Enter your email')"
                :model-value="email"
                required
                autocomplete="email"
            />

            <div class="flex flex-col-reverse gap-2 pt-1 sm:flex-row sm:items-center sm:justify-between">
                <Link
                    :href="route('login')"
                    class="mt-4 text-center text-sm text-gray-600 hover:text-gray-900 sm:mt-0 sm:text-left dark:text-gray-400 dark:hover:text-gray-100"
                    data-testid="back-to-login-link"
                >
                    {{ $t('Back to login') }}
                </Link>
                <Button type="submit" data-testid="reset-button">
                    {{ $t('Email Password Reset Link') }}
                </Button>
            </div>
        </Form>
    </AuthCardLayout>
</template>
