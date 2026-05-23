<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { LayoutGrid, Building2, BarChart3 } from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard, analytics } from '@/routes/admin/index';
import adminListings from '@/routes/admin/listings';
import type { NavItem } from '@/types';

import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const mainNavItems: NavItem[] = [
    {
        title: t('dashboard'),
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: t('listings'),
        href: adminListings.index(),
        icon: Building2,
    },
    {
        title: t('analytics'),
        href: analytics(),
        icon: BarChart3,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
