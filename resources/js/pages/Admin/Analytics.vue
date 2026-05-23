<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Eye, Phone, MessageCircle, MousePointer } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import StatCard from '@/components/StatCard.vue';
import { analytics } from '@/routes/admin/index';

const { t } = useI18n();

type Props = {
    viewsOverTime: { date: string; count: number }[];
    clicksByType: { click_type: string; count: number }[];
    topListings: {
        id: number;
        title: string;
        city: string;
        views_count: number;
        clicks_count: number;
        formatted_price: string;
    }[];
    totalViews: number;
    totalClicks: number;
    selectedDays: number;
};

defineProps<Props>();

function changePeriod(event: Event) {
    const target = event.target as HTMLSelectElement;
    router.get(analytics().url, { days: target.value }, { preserveState: true });
}
</script>

<template>
    <Head :title="t('analytics')" />

    <div class="p-4 sm:p-6">
        <div class="mb-4 flex flex-col gap-3 sm:mb-6 sm:flex-row sm:items-center sm:justify-between">
            <h1 class="text-xl font-bold text-[#1F1F1F] sm:text-2xl">{{ t('analytics') }}</h1>
            <select
                @change="changePeriod"
                :value="selectedDays"
                class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-base text-[#1F1F1F] focus:border-[#FFC107] focus:outline-none focus:ring-2 focus:ring-[#FFC107]/30 sm:w-auto"
            >
                <option :value="7">{{ t('last_7_days') }}</option>
                <option :value="30">{{ t('last_30_days') }}</option>
                <option :value="90">{{ t('last_90_days') }}</option>
            </select>
        </div>

        <!-- Stats -->
        <div class="mb-6 grid grid-cols-2 gap-3 sm:mb-8 sm:gap-4">
            <StatCard :title="t('total_views')" :value="totalViews.toLocaleString()" :icon="Eye" color="yellow" />
            <StatCard :title="t('total_clicks')" :value="totalClicks.toLocaleString()" :icon="MousePointer" color="charcoal" />
        </div>

        <div class="grid gap-6 sm:gap-8 lg:grid-cols-2">
            <!-- Views Over Time -->
            <div class="rounded-2xl border border-gray-200 bg-white p-4 sm:p-6">
                <h2 class="mb-4 text-base font-bold text-[#1F1F1F] sm:text-lg">{{ t('views_over_time') }}</h2>
                <div v-if="viewsOverTime.length > 0" class="flex h-48 items-end gap-1 overflow-x-auto">
                    <div v-for="day in viewsOverTime" :key="day.date" class="flex min-w-6 flex-1 flex-col items-center gap-2">
                        <div class="w-full rounded-t bg-[#FFC107] transition-all" :style="{ height: `${Math.max(4, (day.count / Math.max(...viewsOverTime.map(d => d.count || 1))) * 100)}%` }" />
                        <span class="text-xs text-gray-400">{{ day.date.slice(5) }}</span>
                    </div>
                </div>
                <p v-else class="py-12 text-center text-gray-500">{{ t('no_data_period') }}</p>
            </div>

            <!-- Clicks by Type -->
            <div class="rounded-2xl border border-gray-200 bg-white p-4 sm:p-6">
                <h2 class="mb-4 text-base font-bold text-[#1F1F1F] sm:text-lg">{{ t('clicks_by_type') }}</h2>
                <div v-if="clicksByType.length > 0" class="space-y-4">
                    <div v-for="item in clicksByType" :key="item.click_type" class="flex items-center gap-3">
                        <div class="flex size-10 items-center justify-center rounded-lg bg-gray-100">
                            <Phone v-if="item.click_type === 'phone'" class="size-5 text-[#FFC107]" />
                            <MessageCircle v-else-if="item.click_type === 'whatsapp'" class="size-5 text-green-500" />
                            <Eye v-else class="size-5 text-blue-500" />
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium capitalize text-[#1F1F1F]">{{ item.click_type }}</p>
                            <div class="mt-1 h-2 rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-[#FFC107]" :style="{ width: `${(item.count / Math.max(...clicksByType.map(c => c.count))) * 100}%` }" />
                            </div>
                        </div>
                        <span class="text-sm font-semibold text-[#1F1F1F]">{{ item.count }}</span>
                    </div>
                </div>
                <p v-else class="py-12 text-center text-gray-500">{{ t('no_click_data_period') }}</p>
            </div>
        </div>

        <!-- Top Listings - Mobile card view, desktop table -->
        <div class="mt-6 rounded-2xl border border-gray-200 bg-white sm:mt-8">
            <div class="border-b border-gray-100 px-4 py-3 sm:px-6 sm:py-4">
                <h2 class="text-base font-bold text-[#1F1F1F] sm:text-lg">{{ t('top_performing_listings') }}</h2>
            </div>

            <!-- Mobile Card View -->
            <div class="divide-y divide-gray-100 sm:hidden">
                <div v-for="listing in topListings" :key="listing.id" class="p-4 space-y-2">
                    <p class="font-medium text-[#1F1F1F]">{{ listing.title }}</p>
                    <p class="text-sm text-gray-500">{{ listing.city }}</p>
                    <div class="flex items-center justify-between">
                        <span class="font-medium text-[#FFC107]">{{ listing.formatted_price }}</span>
                        <div class="flex items-center gap-3 text-sm text-gray-500">
                            <span class="flex items-center gap-1">
                                <Eye class="size-3.5" /> {{ listing.views_count }}
                            </span>
                            <span class="flex items-center gap-1">
                                <MousePointer class="size-3.5" /> {{ listing.clicks_count }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Desktop Table View -->
            <table class="hidden w-full sm:table">
                <thead class="border-b border-gray-100 bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-start text-sm font-semibold text-gray-500">{{ t('listing_col') }}</th>
                        <th class="px-6 py-3 text-start text-sm font-semibold text-gray-500">{{ t('price_col') }}</th>
                        <th class="px-6 py-3 text-center text-sm font-semibold text-gray-500">{{ t('views_col') }}</th>
                        <th class="px-6 py-3 text-center text-sm font-semibold text-gray-500">{{ t('clicks_col') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="listing in topListings" :key="listing.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <p class="font-medium text-[#1F1F1F]">{{ listing.title }}</p>
                            <p class="text-sm text-gray-500">{{ listing.city }}</p>
                        </td>
                        <td class="px-6 py-4 font-medium text-[#FFC107]">{{ listing.formatted_price }}</td>
                        <td class="px-6 py-4 text-center text-sm font-medium">{{ listing.views_count }}</td>
                        <td class="px-6 py-4 text-center text-sm font-medium">{{ listing.clicks_count }}</td>
                    </tr>
                </tbody>
            </table>
            <div v-if="topListings.length === 0" class="py-12 text-center text-gray-500">{{ t('no_data_available') }}</div>
        </div>
    </div>
</template>
