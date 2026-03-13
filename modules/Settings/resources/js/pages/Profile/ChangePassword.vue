<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import InputField from '@/components/ui/input/InputField.vue';
import SettingsLayout from '@/layouts/SettingsLayout.vue';
import { Form, Link } from '@inertiajs/vue3';
import PageHeader from '../../components/PageHeader.vue';
</script>

<template>
    <SettingsLayout :title="$t('Change Password')">
        <template #header>
            <PageHeader
                :title="$t('Change Password')"
                :back-url="route('settings.profile')"
            />
        </template>

        <Card class="max-w-3xl">
            <CardHeader>
                <CardTitle>{{ $t('Update Password') }}</CardTitle>
                <CardDescription>
                    {{
                        $t(
                            'Ensure your account is using a long, random password to stay secure.',
                        )
                    }}
                </CardDescription>
            </CardHeader>
            <CardContent>
                <Form
                    :action="route('settings.profile.password.update')"
                    method="put"
                    class="space-y-4"
                    disable-while-processing
                    :reset-on-success="true"
                >
                    <InputField
                        name="current_password"
                        type="password"
                        :label="$t('Current Password')"
                        :placeholder="$t('Enter your current password')"
                        autocomplete="current-password"
                        required
                    />

                    <InputField
                        name="password"
                        type="password"
                        :label="$t('New Password')"
                        :placeholder="$t('Enter your new password')"
                        autocomplete="new-password"
                        required
                    />

                    <InputField
                        name="password_confirmation"
                        type="password"
                        :label="$t('Confirm Password')"
                        :placeholder="$t('Confirm your new password')"
                        autocomplete="new-password"
                        required
                    />

                    <div class="flex justify-end gap-2 pt-2">
                        <Link :href="route('settings.profile')">
                            <Button type="button" variant="outline">
                                {{ $t('Cancel') }}
                            </Button>
                        </Link>
                        <Button type="submit">
                            {{ $t('Update Password') }}
                        </Button>
                    </div>
                </Form>
            </CardContent>
        </Card>
    </SettingsLayout>
</template>
