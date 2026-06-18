<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Phone, Mail, MapPin, MessageCircle, ChevronLeft, Save } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { index as leadsIndex, update as leadsUpdate } from '@/routes/admin/leads';
import { show as listingsShow } from '@/routes/listings';

type Lead = {
    id: number;
    name: string;
    phone: string;
    email: string | null;
    message: string | null;
    status: string;
    admin_notes: string | null;
    ip_address: string | null;
    created_at: string;
    contacted_at: string | null;
    closed_at: string | null;
    listing: {
        id: number;
        title: string;
        city: string;
        district: string;
        address: string;
        contact_phone: string;
        contact_whatsapp: string | null;
        formatted_price: string;
    } | null;
};

type Props = {
    lead: Lead;
    statuses: string[];
};

const props = defineProps<Props>();
const { t } = useI18n();

const form = useForm({
    status: props.lead.status,
    admin_notes: props.lead.admin_notes ?? '',
});

function submit() {
    form.patch(leadsUpdate({ lead: props.lead.id }).url, { preserveScroll: true });
}

const statusClass = (status: string) => {
    return {
        new: 'bg-blue-100 text-blue-800',
        contacted: 'bg-amber-100 text-amber-800',
        visited: 'bg-purple-100 text-purple-800',
        closed: 'bg-green-100 text-green-800',
        lost: 'bg-red-100 text-red-800',
    }[status] ?? 'bg-gray-100 text-gray-800';
};

const formatDate = (dateString: string | null) =>
    dateString ? new Date(dateString).toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' }) : '—';

const whatsappUrl = (phone: string) => `https://wa.me/${phone.replace(/[^0-9]/g, '')}`;
</script>

<template>
    <Head :title="t('lead') + ' #' + lead.id" />

    <div class="mx-auto max-w-5xl px-4 py-6">
        <Link
            :href="leadsIndex()"
            class="mb-4 inline-flex items-center gap-2 text-base font-medium text-[#1F1F1F] hover:text-[#FFC107]"
        >
            <ChevronLeft class="size-5 rtl:rotate-180" />
            {{ t('back_to_leads') }}
        </Link>

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Lead details -->
            <div class="lg:col-span-2 space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-[#1F1F1F]">{{ lead.name }}</h1>
                            <p class="mt-1 text-sm text-gray-500">#{{ lead.id }} · {{ formatDate(lead.created_at) }}</p>
                        </div>
                        <span class="inline-block rounded-full px-3 py-1 text-sm font-medium" :class="statusClass(lead.status)">
                            {{ t('lead_status_' + lead.status) }}
                        </span>
                    </div>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        <a
                            :href="`tel:${lead.phone}`"
                            class="flex items-center gap-2 rounded-xl bg-[#FFC107]/10 px-4 py-3 text-[#1F1F1F] hover:bg-[#FFC107]/20"
                        >
                            <Phone class="size-5 text-[#FFC107]" />
                            <span class="font-medium">{{ lead.phone }}</span>
                        </a>
                        <a
                            v-if="lead.phone"
                            :href="whatsappUrl(lead.phone)"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex items-center gap-2 rounded-xl bg-green-500/10 px-4 py-3 text-green-700 hover:bg-green-500/20"
                        >
                            <MessageCircle class="size-5" />
                            <span class="font-medium">{{ t('whatsapp_label') }}</span>
                        </a>
                        <a
                            v-if="lead.email"
                            :href="`mailto:${lead.email}`"
                            class="flex items-center gap-2 rounded-xl bg-gray-100 px-4 py-3 text-[#1F1F1F] hover:bg-gray-200"
                        >
                            <Mail class="size-5 text-gray-500" />
                            <span class="font-medium">{{ lead.email }}</span>
                        </a>
                    </div>

                    <div v-if="lead.message" class="mt-6">
                        <h2 class="mb-2 text-sm font-semibold uppercase text-gray-500">{{ t('message') }}</h2>
                        <p class="rounded-xl bg-gray-50 p-4 text-base text-gray-700">{{ lead.message }}</p>
                    </div>
                </div>

                <!-- Manage -->
                <div class="rounded-2xl border border-gray-200 bg-white p-6">
                    <h2 class="mb-4 text-lg font-bold text-[#1F1F1F]">{{ t('manage_lead') }}</h2>
                    <form class="space-y-4" @submit.prevent="submit">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700">{{ t('status') }}</label>
                            <select
                                v-model="form.status"
                                class="w-full rounded-xl border border-gray-200 px-4 py-3 text-base text-[#1F1F1F] focus:border-[#FFC107] focus:outline-none focus:ring-2 focus:ring-[#FFC107]/30"
                            >
                                <option v-for="status in statuses" :key="status" :value="status">
                                    {{ t('lead_status_' + status) }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700">{{ t('admin_notes') }}</label>
                            <textarea
                                v-model="form.admin_notes"
                                rows="4"
                                :placeholder="t('admin_notes_placeholder')"
                                class="w-full rounded-xl border border-gray-200 px-4 py-3 text-base text-[#1F1F1F] focus:border-[#FFC107] focus:outline-none focus:ring-2 focus:ring-[#FFC107]/30"
                            />
                        </div>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center gap-2 rounded-xl bg-[#1F1F1F] px-6 py-3 text-base font-semibold text-white transition-colors hover:bg-[#1F1F1F]/90 disabled:opacity-60"
                        >
                            <Save class="size-5" />
                            {{ t('save') }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <div v-if="lead.listing" class="rounded-2xl border border-gray-200 bg-white p-6">
                    <h2 class="mb-3 text-sm font-semibold uppercase text-gray-500">{{ t('listing') }}</h2>
                    <p class="text-lg font-bold text-[#1F1F1F]">{{ lead.listing.title }}</p>
                    <p class="mt-1 flex items-center gap-1 text-sm text-gray-500">
                        <MapPin class="size-4" />{{ lead.listing.address }}
                    </p>
                    <p class="mt-2 text-xl font-bold text-[#FFC107]">{{ lead.listing.formatted_price }}</p>
                    <Link
                        :href="listingsShow({ listing: lead.listing.id }).url"
                        class="mt-4 inline-flex rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-[#1F1F1F] hover:bg-gray-50"
                    >
                        {{ t('view_listing') }}
                    </Link>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-6">
                    <h2 class="mb-3 text-sm font-semibold uppercase text-gray-500">{{ t('timeline') }}</h2>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-500">{{ t('received') }}</dt>
                            <dd class="font-medium text-[#1F1F1F]">{{ formatDate(lead.created_at) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">{{ t('contacted') }}</dt>
                            <dd class="font-medium text-[#1F1F1F]">{{ formatDate(lead.contacted_at) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">{{ t('closed') }}</dt>
                            <dd class="font-medium text-[#1F1F1F]">{{ formatDate(lead.closed_at) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</template>
