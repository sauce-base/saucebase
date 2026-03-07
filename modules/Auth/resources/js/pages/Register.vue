<script setup lang="ts">
import { Button } from '@/components/ui/button';
import InputField from '@/components/ui/input/InputField.vue';
import { Form, Link } from '@inertiajs/vue3';
import SocialiteProviders from '../components/SocialiteProviders.vue';
import AuthCardLayout from '../layouts/AuthCardLayout.vue';
</script>

<template>
    <AuthCardLayout
        :title="$t('Create your account')"
        :description="$t('Sign up for Saucebase to start building your SaaS')"
    >
        <SocialiteProviders />

        <Form
            :action="route('register')"
            method="post"
            class="space-y-3"
            data-testid="register-form"
            disable-while-processing
            :reset-on-error="['password']"
        >
            <!-- Name -->
            <InputField
                name="name"
                type="text"
                :label="$t('Name')"
                :placeholder="$t('Enter your full name')"
                autocomplete="name"
            />

            <!-- Email -->
            <InputField
                name="email"
                type="email"
                :label="$t('Email')"
                :placeholder="$t('Enter your email')"
                autocomplete="email"
            />

            <!-- Password -->
            <InputField
                name="password"
                type="password"
                :label="$t('Password')"
                :placeholder="$t('Enter your password')"
                autocomplete="new-password"
                required
            />

            <Button
                type="submit"
                class="mt-3 w-full"
                data-testid="register-button"
            >
                {{ $t('Register') }}
            </Button>

            <p
                class="mt-4 text-center text-sm text-gray-600 dark:text-gray-400"
            >
                {{ $t('Already registered?') }}
                <Link
                    :href="route('login')"
                    class="font-medium text-indigo-600 hover:text-indigo-500 hover:underline dark:text-indigo-400 dark:hover:text-indigo-300"
                    data-testid="login-link"
                >
                    {{ $t('Log in') }}
                </Link>
            </p>
        </Form>
    </AuthCardLayout>
</template>
