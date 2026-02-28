<script setup lang="ts">
import { Button } from '@/components/ui/button';
import InputField from '@/components/ui/input/InputField.vue';
import { Form } from '@inertiajs/vue3';
import AuthCardLayout from '../layouts/AuthCardLayout.vue';

defineProps<{
    email: string;
    token: string;
}>();
</script>

<template>
    <AuthCardLayout
        :title="$t('Reset Password')"
        :description="$t('Enter your new password below')"
    >
        <Form
            :action="route('password.store')"
            method="post"
            class="min-w-sm space-y-3"
            data-testid="reset-password-form"
            disable-while-processing
            :reset-on-error="['password', 'password_confirmation']"
        >
            <input
                type="hidden"
                name="token"
                :value="token"
                data-testid="token"
            />

            <!-- Email -->
            <InputField
                name="email"
                type="email"
                :label="$t('Email')"
                :model-value="email"
                required
                readonly
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

            <!-- Password Confirmation -->
            <InputField
                name="password_confirmation"
                type="password"
                :label="$t('Confirm Password')"
                :placeholder="$t('Confirm your new password')"
                autocomplete="new-password"
                required
            />

            <Button type="submit" class="mt-3 w-full">
                {{ $t('Reset Password') }}
            </Button>
        </Form>
    </AuthCardLayout>
</template>
