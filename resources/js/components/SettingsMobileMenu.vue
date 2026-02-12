<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { NavGroup } from '@/components/ui/navigation';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { useSidebar } from '@/components/ui/sidebar';
import { PageProps } from '@/types';
import type { Navigation } from '@/types/navigation';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import IconMenu from '~icons/lucide/menu';

const page = usePage<PageProps<{ navigation: Navigation }>>();
const { isMobile } = useSidebar();

// Show settings navigation
const items = computed(() => page.props.navigation?.settings || []);
</script>

<template>
    <!-- Only show on mobile -->
    <Sheet v-if="isMobile">
        <SheetTrigger as-child>
            <Button
                variant="outline"
                size="icon"
                class="mr-3 align-middle lg:hidden"
                aria-label="Open settings menu"
            >
                <IconMenu class="size-5" />
            </Button>
        </SheetTrigger>
        <SheetContent side="left" class="w-64">
            <SheetHeader>
                <SheetTitle>{{ $t('Settings') }}</SheetTitle>
            </SheetHeader>
            <div class="mt-2">
                <NavGroup :items="items" />
            </div>
        </SheetContent>
    </Sheet>
</template>
