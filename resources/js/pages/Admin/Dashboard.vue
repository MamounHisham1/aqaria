<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Building2, Eye, MousePointerClick, CheckCircle2,
    ArrowRight,
} from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import StatCard from '@/components/StatCard.vue';
import { analytics } from '@/routes/admin/index';
import adminListings from '@/routes/admin/listings';

const { t } = useI18n();

type Listing = {
    id: number;
    title: string;
    city: string;
    property_type: string;
    listing_type: string;
    formatted_price: string;
    is_active: boolean;
    views_count: number;
    created_at: string;
};

type Props = {
    stats: {
        totalListings: number;
        activeListings: number;
        totalViews: number;
        totalClicks: number;
    };
    recentListings: Listing[];
    mostViewed: Listing[];
    viewsLast7Days: { date: string; count: number }[];
};

defineProps<Props>();
</script>

<template>
    <Head :title="t('admin_dashboard')" />

    <div class="p-4 sm:p-6">
        <h1 class="mb-4 text-xl font-bold text-[#1F1F1F] sm:mb-6 sm:text-2xl">{{ t('admin_dashboard') }}</h1>

        <!-- Stats Grid -->
        <div class="mb-6 grid grid-cols-2 gap-3 sm:mb-8 sm:gap-4 lg:grid-cols-4">
            <StatCard
                :title="t('total_listings')"
                :value="stats.totalListings"
                :icon="Building2"
                color="white"
            />
            <StatCard
                :title="t('active_listings')"
                :value="stats.activeListings"
                :icon="CheckCircle2"
                color="yellow"
            />
            <StatCard
                :title="t('total_views')"
                :value="stats.totalViews.toLocaleString()"
                :icon="Eye"
                color="white"
            />
            <StatCard
                :title="t('total_clicks')"
                :value="stats.totalClicks.toLocaleString()"
                :icon="MousePointerClick"
                :description="t('total_clicks_desc')"
                color="charcoal"
            />
        </div>

        <div class="grid gap-6 sm:gap-8 lg:grid-cols-2">
            <!-- Recent Listings -->
            <div class="rounded-2xl border border-gray-200 bg-white">
                <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 sm:px-6 sm:py-4">
                    <h2 class="text-base font-bold text-[#1F1F1F] sm:text-lg">{{ t('recent_listings') }}</h2>
                    <Link
                        :href="adminListings.index().url"
                        class="flex items-center gap-1 text-sm font-medium text-[#FFC107] hover:text-yellow-600"
                    >
                        {{ t('view_all') }}
                        <ArrowRight class="size-4 rtl:rotate-180" />
                    </Link>
                </div>
                <div class="divide-y divide-gray-100">
                    <div
                        v-for="listing in recentListings"
                        :key="listing.id"
                        class="flex items-center justify-between px-4 py-3 sm:px-6 sm:py-4"
                    >
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-medium text-[#1F1F1F]">{{ listing.title }}</p>
                            <p class="text-sm text-gray-500">
                                {{ listing.city }} · {{ listing.formatted_price }}
                            </p>
                        </div>
                        <span
                            class="ms-3 shrink-0 rounded-lg px-2.5 py-1 text-xs font-medium"
                            :class="
                                listing.is_active
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-gray-100 text-gray-500'
                            "
                        >
                            {{ listing.is_active ? t('active') : t('inactive') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Most Viewed -->
            <div class="rounded-2xl border border-gray-200 bg-white">
                <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 sm:px-6 sm:py-4">
                    <h2 class="text-base font-bold text-[#1F1F1F] sm:text-lg">{{ t('most_viewed') }}</h2>
                    <Link
                        :href="analytics().url"
                        class="flex items-center gap-1 text-sm font-medium text-[#FFC107] hover:text-yellow-600"
                    >
                        {{ t('analytics') }}
                        <ArrowRight class="size-4 rtl:rotate-180" />
                    </Link>
                </div>
                <div class="divide-y divide-gray-100">
                    <div
                        v-for="listing in mostViewed"
                        :key="listing.id"
                        class="flex items-center justify-between px-4 py-3 sm:px-6 sm:py-4"
                    >
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-medium text-[#1F1F1F]">{{ listing.title }}</p>
                            <p class="text-sm text-gray-500">{{ listing.city }}</p>
                        </div>
                        <div class="ms-3 flex shrink-0 items-center gap-1.5 text-sm font-medium text-gray-500">
                            <Eye class="size-4" />
                            {{ listing.views_count }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Views Chart -->
        <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-4 sm:mt-8 sm:p-6">
            <h2 class="mb-4 text-base font-bold text-[#1F1F1F] sm:text-lg">{{ t('views_last_7_days') }}</h2>
            <div v-if="viewsLast7Days.length > 0" class="flex h-40 items-end gap-2">
                <div
                    v-for="day in viewsLast7Days"
                    :key="day.date"
                    class="flex flex-1 flex-col items-center gap-2"
                >
                    <div
                        class="w-full rounded-t-lg bg-[#FFC107] transition-all"
                        :style="{
                            height: `${Math.max(4, (day.count / Math.max(...viewsLast7Days.map(d => d.count))) * 100)}%`,
                        }"
                    />
                    <span class="text-xs text-gray-500">{{ day.date.slice(5) }}</span>
                </div>
            </div>
            <p v-else class="py-8 text-center text-gray-500">
                {{ t('no_view_data') }}
            </p>
        </div>
    </div>
</template>
