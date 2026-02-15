<script setup lang="ts">
import { modules } from '@/composables/useModules';
import type { MenuItem } from '@/types/navigation';
import { Link, usePage } from '@inertiajs/vue3';
import { ExternalLink } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import IconMenu from '~icons/heroicons/bars-3';
import IconX from '~icons/heroicons/x-mark';
import AppLogo from './AppLogo.vue';
import LanguageSelector from './LanguageSelector.vue';
import ThemeSelector from './ThemeSelector.vue';

const page = usePage();
const landingNav = computed<MenuItem[]>(
    () => (page.props.navigation as { landing?: MenuItem[] })?.landing || [],
);

const isScrolled = ref(false);
const mobileMenuOpen = ref(false);

const handleScroll = () => {
    isScrolled.value = window.scrollY > 10;
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <header
        class="fixed top-0 right-0 left-0 z-50 transition-all duration-300"
        :class="
            isScrolled
                ? 'border-b bg-white/5 shadow-2xl backdrop-blur-lg dark:border-b-gray-400/25'
                : 'bg-transparent'
        "
    >
        <nav class="mx-auto max-w-7xl px-6 py-3">
            <div class="flex items-center justify-between">
                <!-- Logo (Left) -->
                <Link
                    href="/"
                    class="flex shrink-0 items-center transition-opacity hover:opacity-80"
                >
                    <AppLogo size="md" :showText="true" />
                </Link>

                <!-- Landing navigation - Centered -->
                <div
                    class="absolute left-1/2 hidden -translate-x-1/2 items-center space-x-1 lg:flex"
                >
                    <a
                        v-for="item in landingNav"
                        :key="item.slug"
                        :href="item.url"
                        :target="item.newPage ? '_blank' : '_self'"
                        class="after:bg-primary relative px-4 py-2 text-sm font-semibold text-gray-700 transition-all duration-300 after:absolute after:bottom-0 after:left-1/2 after:h-0.5 after:w-0 after:-translate-x-1/2 after:rounded-full after:transition-all after:duration-300 hover:text-gray-900 hover:after:w-3/4 dark:text-gray-300 dark:hover:text-white"
                    >
                        {{ $t(item.title) }}
                        <ExternalLink
                            v-if="item.newPage"
                            class="-mt-1 ml-1 inline-block size-3.5"
                        />
                    </a>
                </div>

                <!-- Right side actions -->
                <div class="hidden items-center space-x-3 lg:flex">
                    <div class="flex items-center space-x-1">
                        <LanguageSelector mode="standalone" />
                        <ThemeSelector mode="standalone" />
                    </div>

                    <Link
                        v-if="modules().has('Auth') && !$page.props.auth?.user"
                        :href="route('login')"
                        class="rounded-full px-4 py-2 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white"
                    >
                        {{ $t('Sign In') }}
                    </Link>

                    <Link
                        v-if="modules().has('Auth') && !$page.props.auth?.user"
                        :href="route('register')"
                        class="bg-primary text-primary-foreground hover:bg-primary/90 focus:ring-primary inline-flex items-center justify-center rounded-full px-5 py-2.5 text-sm font-semibold transition-all duration-200 hover:scale-105 focus:ring-2 focus:ring-offset-2 focus:outline-none"
                    >
                        {{ $t('Get Started') }}
                    </Link>

                    <Link
                        v-if="
                            route().has('dashboard') && $page.props.auth?.user
                        "
                        :href="route('dashboard')"
                        class="bg-primary text-primary-foreground hover:bg-primary/90 focus:ring-primary inline-flex items-center justify-center rounded-full px-5 py-2.5 text-sm font-semibold transition-all duration-200 hover:scale-105 focus:ring-2 focus:ring-offset-2 focus:outline-none"
                    >
                        {{ $t('Dashboard') }}
                    </Link>
                    <Link
                        v-if="route().has('logout') && $page.props.auth?.user"
                        :href="route('logout')"
                        class="rounded-full px-4 py-2 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white"
                    >
                        {{ $t('Logout') }}
                    </Link>
                </div>

                <!-- Mobile Menu Button - Better positioning -->
                <div class="flex items-center space-x-3 lg:hidden">
                    <LanguageSelector mode="standalone" />
                    <ThemeSelector mode="standalone" />
                    <button
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        :aria-label="
                            mobileMenuOpen
                                ? $t('Close mobile menu')
                                : $t('Open mobile menu')
                        "
                        :aria-expanded="mobileMenuOpen"
                        class="rounded-full p-2 text-gray-700 transition-colors duration-200 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                    >
                        <IconMenu v-if="!mobileMenuOpen" class="h-6 w-6" />
                        <IconX v-else class="h-6 w-6" />
                    </button>
                </div>
            </div>

            <!-- Mobile Menu - Enhanced with animations -->
            <Transition
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="opacity-0 transform -translate-y-2"
                enter-to-class="opacity-100 transform translate-y-0"
                leave-active-class="transition-all duration-200 ease-in"
                leave-from-class="opacity-100 transform translate-y-0"
                leave-to-class="opacity-0 transform -translate-y-2"
            >
                <div
                    v-if="mobileMenuOpen"
                    class="mx-2 mt-4 rounded-lg border-t border-gray-200/40 bg-white/80 pb-6 backdrop-blur-sm lg:hidden dark:border-gray-800/40 dark:bg-gray-950/80"
                >
                    <div class="flex flex-col space-y-1 px-2 pt-4">
                        <!-- Landing navigation (anchor links) -->
                        <a
                            v-for="item in landingNav"
                            :key="item.slug"
                            :href="item.url"
                            :target="item.newPage ? '_blank' : '_self'"
                            class="after:bg-primary hover:text-primary dark:hover:text-primary relative px-4 py-3 text-base font-semibold text-gray-900 transition-all duration-300 after:absolute after:bottom-1 after:left-4 after:h-0.5 after:w-0 after:rounded-full after:transition-all after:duration-300 hover:after:w-1/2 dark:text-gray-100"
                            @click="mobileMenuOpen = false"
                        >
                            {{ $t(item.title) }}
                        </a>

                        <div class="flex flex-col space-y-3">
                            <Link
                                v-if="
                                    modules().has('auth') &&
                                    !$page.props.auth?.user
                                "
                                :href="route('login')"
                                class="hover:text-primary dark:hover:text-primary rounded-full px-4 py-3 text-base font-medium text-gray-900 transition-all duration-200 hover:bg-gray-50 dark:text-gray-100 dark:hover:bg-gray-800/50"
                                @click="mobileMenuOpen = false"
                            >
                                {{ $t('Sign In') }}
                            </Link>

                            <Link
                                v-if="
                                    modules().has('auth') &&
                                    !$page.props.auth?.user
                                "
                                :href="route('register')"
                                class="bg-primary text-primary-foreground hover:bg-primary/90 focus:ring-primary inline-flex items-center justify-center rounded-full px-4 py-3 text-base font-semibold transition-all duration-200 focus:ring-2 focus:ring-offset-2 focus:outline-none"
                                @click="mobileMenuOpen = false"
                            >
                                {{ $t('Get Started') }}
                            </Link>

                            <Link
                                v-if="
                                    modules().has('auth') &&
                                    $page.props.auth?.user
                                "
                                :href="route('dashboard')"
                                class="bg-primary text-primary-foreground hover:bg-primary/90 focus:ring-primary inline-flex items-center justify-center rounded-full px-4 py-3 text-base font-semibold transition-all duration-200 focus:ring-2 focus:ring-offset-2 focus:outline-none"
                                @click="mobileMenuOpen = false"
                            >
                                {{ $t('Dashboard') }}
                            </Link>
                        </div>
                    </div>
                </div>
            </Transition>
        </nav>
    </header>
</template>
