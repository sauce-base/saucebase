<script setup lang="ts">
import Alert from '@/components/ui/alert/Alert.vue';
import AlertDescription from '@/components/ui/alert/AlertDescription.vue';
import AlertTitle from '@/components/ui/alert/AlertTitle.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import Button from '@/components/ui/button/Button.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardDescription from '@/components/ui/card/CardDescription.vue';
import CardFooter from '@/components/ui/card/CardFooter.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import Separator from '@/components/ui/separator/Separator.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

import IconArrowRight from '~icons/heroicons/arrow-right';
import IconArrowTrendingDown from '~icons/heroicons/arrow-trending-down';
import IconArrowTrendingUp from '~icons/heroicons/arrow-trending-up';
import IconChart from '~icons/heroicons/chart-bar';
import IconCog from '~icons/heroicons/cog-6-tooth';
import IconCreditCard from '~icons/heroicons/credit-card';
import IconInfo from '~icons/heroicons/information-circle';
import IconRocket from '~icons/heroicons/rocket-launch';
import IconSparkles from '~icons/heroicons/sparkles';
import IconUsers from '~icons/heroicons/user-group';

const title = 'Dashboard';

// Sample stats data
const stats = ref([
    {
        label: 'Total Revenue',
        value: '$45,231.89',
        icon: IconCreditCard,
        change: '+20.1%',
        changeLabel: 'from last month',
        positive: true,
    },
    {
        label: 'Subscriptions',
        value: '+2,350',
        icon: IconUsers,
        change: '+180.1%',
        changeLabel: 'from last month',
        positive: true,
    },
    {
        label: 'Sales',
        value: '+12,234',
        icon: IconChart,
        change: '+19%',
        changeLabel: 'from last month',
        positive: true,
    },
    {
        label: 'Active Now',
        value: '+573',
        icon: IconSparkles,
        change: '+201',
        changeLabel: 'since last hour',
        positive: true,
    },
]);

// Sample feature data
const features = ref([
    {
        title: 'Feature One',
        description:
            'Powerful feature that helps you accomplish tasks efficiently',
        icon: IconSparkles,
        status: 'active',
    },
    {
        title: 'Feature Two',
        description: 'Another amazing capability to enhance your workflow',
        icon: IconRocket,
        status: 'beta',
    },
    {
        title: 'Feature Three',
        description: 'Coming soon to make your experience even better',
        icon: IconChart,
        status: 'planned',
    },
]);

// Sample recent activity
const recentActivity = ref([
    { id: 1, action: 'Item created', time: '2 minutes ago', user: 'John Doe' },
    {
        id: 2,
        action: 'Settings updated',
        time: '1 hour ago',
        user: 'Jane Smith',
    },
    {
        id: 3,
        action: 'New user registered',
        time: '3 hours ago',
        user: 'Renan Roble',
    },
    {
        id: 4,
        action: 'Item updated',
        time: '30 seconds ago',
        user: 'Alice Johnson',
    },
]);
</script>

<template>
    <AppLayout :title="title" :breadcrumbs="[{ title: title }]">
        <div class="flex flex-1 flex-col gap-6 p-6 pt-2">
            <!-- Header Section -->
            <div
                class="flex flex-col sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">
                        {{ title }}
                    </h1>
                    <p class="text-muted-foreground mt-1">
                        {{ $t('Manage and monitor your module activities') }}
                    </p>
                </div>
                <div class="flex gap-4">
                    <Link :href="route('settings.index')">
                        <Button variant="outline">
                            <IconCog class="h-4 w-4" />
                            {{ $t('Settings') }}
                        </Button>
                    </Link>
                    <Button>
                        <IconSparkles class="h-4 w-4" />
                        {{ $t('New') }}
                    </Button>
                </div>
            </div>

            <!-- Info Alert -->
            <Alert class="border-accent-foreground text-foreground">
                <IconInfo />
                <AlertTitle class="text-lg">{{
                    $t('Welcome to Saucebase!')
                }}</AlertTitle>
                <AlertDescription>
                    {{
                        $t(
                            'This is a sample dashboard with fake data. Customize it to fit your needs.',
                        )
                    }}
                </AlertDescription>
            </Alert>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
                <Card v-for="stat in stats" :key="stat.label">
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-base font-medium">
                            {{ $t(stat.label) }}
                        </CardTitle>
                        <component
                            :is="stat.icon"
                            class="text-muted-foreground size-6"
                        />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold">{{ stat.value }}</div>
                        <p
                            class="text-muted-foreground flex items-center gap-1 text-xs"
                        >
                            <component
                                :is="
                                    stat.positive
                                        ? IconArrowTrendingUp
                                        : IconArrowTrendingDown
                                "
                                :class="[
                                    'size-5',
                                    stat.positive
                                        ? 'text-green-600 dark:text-green-500'
                                        : 'text-red-600 dark:text-red-500',
                                ]"
                            />
                            <span
                                :class="
                                    stat.positive
                                        ? 'text-green-600 dark:text-green-500'
                                        : 'text-red-600 dark:text-red-500'
                                "
                            >
                                {{ stat.change }}
                            </span>
                            {{ $t(stat.changeLabel) }}
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Call to Action Section -->
            <Card
                class="bg-primary text-primary-foreground border-primary relative overflow-hidden"
            >
                <!-- Background Rocket Icon -->
                <IconRocket
                    class="pointer-events-none absolute -right-8 -bottom-12 size-72 text-white opacity-10"
                />

                <CardHeader class="relative z-10">
                    <CardTitle class="text-xl">{{
                        $t('Get Started')
                    }}</CardTitle>
                    <CardDescription class="text-primary-foreground/80">
                        {{
                            $t(
                                'Ready to build something amazing? Start by customizing this template.',
                            )
                        }}
                    </CardDescription>
                </CardHeader>
                <CardFooter class="relative z-10 flex gap-2">
                    <a
                        href="https://sauce-base.github.io/docs/"
                        target="_blank"
                        rel="noopener"
                    >
                        <Button variant="secondary">
                            <IconRocket class="size-4" />
                            {{ $t('View Documentation') }}
                        </Button>
                    </a>
                </CardFooter>
            </Card>

            <!-- Main Content Grid -->
            <div class="grid gap-6 lg:grid-cols-7">
                <!-- Features Section -->
                <div class="lg:col-span-4">
                    <Card class="h-full">
                        <CardHeader>
                            <CardTitle>{{ $t('Features') }}</CardTitle>
                            <CardDescription>
                                {{
                                    $t(
                                        'Explore the capabilities of this module',
                                    )
                                }}
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div
                                v-for="feature in features"
                                :key="feature.title"
                                class="hover:bg-accent flex items-start gap-4 rounded-lg border p-4 transition-colors"
                            >
                                <div
                                    class="bg-primary/10 text-primary rounded-full p-4"
                                >
                                    <component
                                        :is="feature.icon"
                                        class="size-8"
                                    />
                                </div>
                                <div class="flex-1 space-y-1">
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-semibold">
                                            {{ $t(feature.title) }}
                                        </h3>
                                        <Badge
                                            :variant="
                                                feature.status === 'active'
                                                    ? 'default'
                                                    : 'secondary'
                                            "
                                        >
                                            {{ $t(feature.status) }}
                                        </Badge>
                                    </div>
                                    <p class="text-muted-foreground text-sm">
                                        {{ $t(feature.description) }}
                                    </p>
                                </div>
                                <Button variant="ghost" size="icon">
                                    <IconArrowRight class="h-4 w-4" />
                                    <span class="sr-only">
                                        {{ $t('View') }} {{ $t(feature.title) }}
                                    </span>
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Recent Activity Sidebar -->
                <div class="lg:col-span-3">
                    <Card class="h-full">
                        <CardHeader>
                            <CardTitle>{{ $t('Recent Activity') }}</CardTitle>
                            <CardDescription>
                                {{ $t('Latest updates and changes') }}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div
                                    v-for="(activity, index) in recentActivity"
                                    :key="activity.id"
                                >
                                    <div class="flex items-start gap-4">
                                        <div
                                            class="bg-primary/10 flex size-10 items-center justify-center rounded-full"
                                        >
                                            <div
                                                class="bg-primary size-2 rounded-full"
                                            ></div>
                                        </div>
                                        <div class="flex-1 space-y-1">
                                            <p class="text-sm font-medium">
                                                {{ $t(activity.action) }}
                                            </p>
                                            <p
                                                class="text-muted-foreground text-xs"
                                            >
                                                {{ activity.user }} •
                                                {{ activity.time }}
                                            </p>
                                        </div>
                                    </div>
                                    <Separator
                                        v-if="index < recentActivity.length - 1"
                                        class="my-4"
                                    />
                                </div>
                            </div>
                        </CardContent>
                        <CardFooter>
                            <Button variant="outline" class="w-full">
                                {{ $t('View All Activity') }}
                                <IconArrowRight class="ml-2 h-4 w-4" />
                            </Button>
                        </CardFooter>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
