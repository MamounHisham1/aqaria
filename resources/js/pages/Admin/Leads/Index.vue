<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Phone, Mail, MapPin, MessageSquare } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { index as leadsIndex, show as leadsShow } from '@/routes/admin/leads';

type Lead = {
    id: number;
    name: string;
    phone: string;
    email: string | null;
    status: string;
    message: string | null;
    created_at: string;
    listing?: {
        id: number;
        title: string;
        city: string;
        district: string;
        formatted_price: string;
    } | null;
};

type Props = {
    leads: { data: Lead[]; total: number; current_page: number; last_page: number; links: { url: string | null; label: string; active: boolean }[] };
    statuses: string[];
    statusCounts: Record<string, number>;
    currentStatus: string | null;
};

const props = defineProps<Props>();
const { t } = useI18n();

const statusClass = (status: string) => {
    return {
        new: 'bg-blue-100 text-blue-800',
        contacted: 'bg-amber-100 text-amber-800',
        visited: 'bg-purple-100 text-purple-800',
        closed: 'bg-green-100 text-green-800',
        lost: 'bg-red-100 text-red-800',
    }[status] ?? 'bg-gray-100 text-gray-800';
};

const formatDate = (dateString: string) =>
    new Date(dateString).toLocaleDateString(undefined, { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
</script>

<template>
    <Head :title="t('leads')" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-8 max-w-7xl mx-auto w-full">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-[#1F1F1F]">{{ t('leads') }}</h1>
            <p class="mt-1 text-muted-foreground">{{ t('leads_desc') }}</p>
        </div>

        <!-- Status pipeline -->
        <div class="flex flex-wrap gap-2">
            <Link
                :href="leadsIndex()"
                class="rounded-lg px-4 py-2 text-sm font-medium transition-colors"
                :class="!currentStatus ? 'bg-[#1F1F1F] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
            >
                {{ t('all') }}
                <span class="ml-1 opacity-70">{{ Object.values(statusCounts).reduce((a, b) => a + b, 0) }}</span>
            </Link>
            <Link
                v-for="status in statuses"
                :key="status"
                :href="leadsIndex({ status })"
                class="rounded-lg px-4 py-2 text-sm font-medium transition-colors"
                :class="currentStatus === status ? 'bg-[#1F1F1F] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
            >
                {{ t('lead_status_' + status) }}
                <span class="ml-1 opacity-70">{{ statusCounts[status] ?? 0 }}</span>
            </Link>
        </div>

        <!-- Leads list -->
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">{{ t('lead') }}</th>
                        <th class="px-4 py-3">{{ t('listing') }}</th>
                        <th class="px-4 py-3">{{ t('status') }}</th>
                        <th class="px-4 py-3">{{ t('received') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr
                        v-for="lead in leads.data"
                        :key="lead.id"
                        class="cursor-pointer hover:bg-gray-50"
                    >
                        <td class="px-4 py-3">
                            <Link :href="leadsShow({ lead: lead.id }).url" class="block">
                                <p class="font-semibold text-[#1F1F1F]">{{ lead.name }}</p>
                                <p class="mt-0.5 flex items-center gap-1 text-xs text-gray-500">
                                    <Phone class="size-3" />{{ lead.phone }}
                                </p>
                                <p v-if="lead.email" class="mt-0.5 flex items-center gap-1 text-xs text-gray-500">
                                    <Mail class="size-3" />{{ lead.email }}
                                </p>
                            </Link>
                        </td>
                        <td class="px-4 py-3">
                            <p v-if="lead.listing" class="text-[#1F1F1F]">{{ lead.listing.title }}</p>
                            <p v-if="lead.listing" class="mt-0.5 flex items-center gap-1 text-xs text-gray-500">
                                <MapPin class="size-3" />{{ lead.listing.district }}, {{ lead.listing.city }}
                            </p>
                            <p v-if="lead.listing" class="mt-0.5 text-xs font-medium text-[#FFC107]">
                                {{ lead.listing.formatted_price }}
                            </p>
                            <span v-else class="text-xs text-gray-400">—</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-block rounded-full px-2.5 py-1 text-xs font-medium" :class="statusClass(lead.status)">
                                {{ t('lead_status_' + lead.status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">
                            {{ formatDate(lead.created_at) }}
                        </td>
                    </tr>
                    <tr v-if="leads.data.length === 0">
                        <td colspan="4" class="px-4 py-12 text-center text-gray-400">
                            <MessageSquare class="mx-auto mb-2 size-8 opacity-40" />
                            {{ t('no_leads_yet') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="leads.last_page > 1" class="flex justify-center gap-1">
            <Link
                v-for="link in leads.links"
                :key="link.label"
                :href="link.url ?? '#'"
                v-html="link.label"
                class="rounded-md border border-gray-200 px-3 py-1.5 text-sm"
                :class="[
                    link.active ? 'bg-[#FFC107] text-[#1F1F1F]' : 'hover:bg-gray-100',
                    !link.url ? 'pointer-events-none opacity-40' : '',
                ]"
            />
        </div>
    </div>
</template>
