<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Field, FieldLabel } from '@/components/ui/field';
import InputField from '@/components/ui/input/InputField.vue';
import { Form, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import SocialiteProviders from '../components/SocialiteProviders.vue';
import AuthCardLayout from '../layouts/AuthCardLayout.vue';

const emailRef = ref('chef@saucebase.dev');
const passwordRef = ref('secretsauce');

// compute forgot password url so the link updates as user types
const forgotUrl = computed(() =>
    route('password.request', { email: emailRef.value }),
);

</script>

<template>
    <AuthCardLayout
        :title="$t('Welcome back')"
        :description="$t('Login to your Saucebase account to continue')"
    >
        <SocialiteProviders />

        <Form
            :action="route('login')"
            method="post"
            class="space-y-3"
            data-testid="login-form"
            disable-while-processing
            :reset-on-error="['password']"
        >
            <!-- Email -->
            <InputField
                name="email"
                type="email"
                :label="$t('Email')"
                :placeholder="$t('Enter your email')"
                autocomplete="email"
                required
                v-model="emailRef"
            />

            <!-- Password -->
            <InputField
                name="password"
                type="password"
                :label="$t('Password')"
                :placeholder="$t('Enter your password')"
                autocomplete="current-password"
                required
                v-model="passwordRef"
            />

            <div class="flex items-center justify-between">
                <!-- Remember-me -->
                <div>
                    <Field>
                        <Field orientation="horizontal">
                            <Checkbox
                                id="remember"
                                name="remember"
                                data-testid="remember-me"
                            />
                            <FieldLabel for="remember" class="font-normal">
                                {{ $t('Remember-me') }}
                            </FieldLabel>
                        </Field>
                    </Field>
                </div>

                <!-- Forgot password link -->
                <Link
                    v-if="route().has('password.request')"
                    :href="forgotUrl"
                    class="ml-auto inline-block text-sm font-medium text-primary hover:underline underline-offset-4"
                    data-testid="forgot-password-link"
                    :data-invalid="false"
                >
                    {{ $t('Forgot your password?') }}
                </Link>
            </div>

            <Button
                type="submit"
                class="mt-3 w-full"
                data-testid="login-button"
            >
                {{ $t('Log in') }}
            </Button>

            <p class="mt-2 text-center text-sm">
                <Link
                    v-if="$page.props.auth.magic_link_enabled"
                    :href="route('magic-link.create')"
                    class="font-medium text-primary/70 hover:underline underline-offset-4"
                    data-testid="magic-link-login-link"
                >
                    {{ $t('Login with magic link') }}
                </Link>
            </p>

            <p
                class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400"
            >
                {{ $t("Don't have an account?") }}
                <Link
                    :href="route('register')"
                    class="font-medium text-primary hover:underline underline-offset-4"
                    data-testid="sign-up-link"
                >
                    {{ $t('Sign up') }}
                </Link>
            </p>
        </Form>
    </AuthCardLayout>
</template>
