<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, Building2, BarChart3, Settings, Inbox } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
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
import { dashboard as adminDashboard, analytics } from '@/routes/admin/index';
import adminListings from '@/routes/admin/listings';
import adminLeads from '@/routes/admin/leads';
import { dashboard as customerDashboard } from '@/routes/index';
import { index as listingsIndex } from '@/routes/listings';
import type { NavItem, Auth } from '@/types';


const { t } = useI18n();
const page = usePage();
const user = computed(() => page.props.auth.user);

const mainNavItems = computed<NavItem[]>(() => {
    if (user.value?.is_admin) {
        return [
            {
                title: t('dashboard'),
                href: adminDashboard(),
                icon: LayoutGrid,
            },
            {
                title: t('listings'),
                href: adminListings.index(),
                icon: Building2,
            },
            {
                title: t('leads'),
                href: adminLeads.index(),
                icon: Inbox,
            },
            {
                title: t('analytics'),
                href: analytics(),
                icon: BarChart3,
            },
        ];
    }

    return [
        {
            title: t('dashboard'),
            href: customerDashboard(),
            icon: LayoutGrid,
        },
        {
            title: t('browse_properties'),
            href: listingsIndex(),
            icon: Building2,
        },
        {
            title: t('settings'),
            href: '/settings',
            icon: Settings,
        },
    ];
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="user?.is_admin ? adminDashboard() : customerDashboard()">
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
