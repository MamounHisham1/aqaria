<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, Eye, ToggleLeft, ToggleRight } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import adminListings from '@/routes/admin/listings';

const { t } = useI18n();

type Listing = {
    id: number;
    title: string;
    price: string;
    formatted_price: string;
    city: string;
    property_type: string;
    listing_type: string;
    is_active: boolean;
    is_featured: boolean;
    views_count: number;
    clicks_count: number;
    primary_image: string | null;
};

type Props = {
    listings: {
        data: Listing[];
        current_page: number;
        last_page: number;
        total: number;
    };
};

defineProps<Props>();

function deleteListing(id: number) {
    if (confirm(t('confirm_delete'))) {
        router.delete(adminListings.destroy(id).url);
    }
}

function toggleActive(listing: Listing) {
    router.patch(adminListings.toggle(listing.id).url);
}
</script>

<template>
    <Head :title="t('listings')" />

    <div class="p-4 sm:p-6">
        <div class="mb-4 flex flex-col gap-3 sm:mb-6 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-bold text-[#1F1F1F] sm:text-2xl">{{ t('listings') }}</h1>
                <p class="mt-1 text-sm text-gray-500 sm:text-base">
                    {{ t('total_listings_count', { count: listings.total }) }}
                </p>
            </div>
            <Link
                :href="adminListings.create().url"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#FFC107] px-5 py-3 text-base font-semibold text-[#1F1F1F] hover:bg-yellow-500 sm:w-auto"
            >
                <Plus class="size-5" />
                {{ t('add_listing') }}
            </Link>
        </div>

        <!-- Mobile Card View -->
        <div class="space-y-3 sm:hidden">
            <div
                v-for="listing in listings.data"
                :key="listing.id"
                class="rounded-2xl border border-gray-200 bg-white p-4"
            >
                <div class="flex items-start gap-3">
                    <div class="size-14 shrink-0 overflow-hidden rounded-lg bg-gray-100">
                        <img
                            v-if="listing.primary_image"
                            :src="listing.primary_image"
                            :alt="listing.title"
                            class="size-full object-cover"
                        />
                        <div
                            v-else
                            class="flex size-full items-center justify-center bg-gray-200 text-xs text-gray-400"
                        >
                            {{ t('na') }}
                        </div>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate font-medium text-[#1F1F1F]">{{ listing.title }}</p>
                        <p class="text-sm capitalize text-gray-500">{{ listing.property_type }}</p>
                    </div>
                </div>

                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <span
                        class="rounded-lg px-2.5 py-1 text-xs font-medium"
                        :class="
                            listing.listing_type === 'sale'
                                ? 'bg-yellow-100 text-yellow-800'
                                : 'bg-blue-100 text-blue-800'
                        "
                    >
                        {{ listing.listing_type === 'sale' ? t('sale') : t('rent') }}
                    </span>
                    <span
                        class="rounded-lg px-2.5 py-1 text-xs font-medium"
                        :class="
                            listing.is_active
                                ? 'bg-green-100 text-green-700'
                                : 'bg-gray-100 text-gray-500'
                        "
                    >
                        {{ listing.is_active ? t('active') : t('inactive') }}
                    </span>
                    <span
                        v-if="listing.is_featured"
                        class="rounded-lg bg-[#FFC107]/20 px-2.5 py-1 text-xs font-medium text-[#1F1F1F]"
                    >
                        {{ t('featured') }}
                    </span>
                </div>

                <div class="mt-3 flex items-center justify-between">
                    <div>
                        <p class="font-medium text-[#FFC107]">{{ listing.formatted_price }}</p>
                        <p class="text-sm text-gray-500">{{ listing.city }}</p>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-gray-500">
                        <span class="flex items-center gap-1"><Eye class="size-3.5" /> {{ listing.views_count }}</span>
                        <span>{{ listing.clicks_count }} {{ t('clicks_col') }}</span>
                    </div>
                </div>

                <div class="mt-3 flex items-center gap-2 border-t border-gray-100 pt-3">
                    <button
                        @click="toggleActive(listing)"
                        class="flex size-10 items-center justify-center rounded-lg hover:bg-gray-100"
                        :title="listing.is_active ? t('deactivate') : t('activate')"
                    >
                        <ToggleRight v-if="listing.is_active" class="size-5 text-green-600" />
                        <ToggleLeft v-else class="size-5 text-gray-400" />
                    </button>
                    <Link
                        :href="adminListings.edit(listing.id).url"
                        class="flex size-10 items-center justify-center rounded-lg hover:bg-gray-100"
                    >
                        <Pencil class="size-5 text-gray-500" />
                    </Link>
                    <button
                        @click="deleteListing(listing.id)"
                        class="flex size-10 items-center justify-center rounded-lg hover:bg-red-50"
                    >
                        <Trash2 class="size-5 text-red-500" />
                    </button>
                </div>
            </div>

            <div v-if="listings.data.length === 0" class="py-20 text-center">
                <p class="text-lg text-gray-500">{{ t('no_listings_found') }}</p>
            </div>
        </div>

        <!-- Desktop Table View -->
        <div class="hidden overflow-x-auto rounded-2xl border border-gray-200 bg-white sm:block">
            <table class="w-full">
                <thead class="border-b border-gray-200 bg-gray-50">
                    <tr>
                        <th class="px-4 py-4 text-start text-sm font-semibold text-gray-500">{{ t('listing_col') }}</th>
                        <th class="px-4 py-4 text-start text-sm font-semibold text-gray-500">{{ t('type_col') }}</th>
                        <th class="px-4 py-4 text-start text-sm font-semibold text-gray-500">{{ t('price_col') }}</th>
                        <th class="px-4 py-4 text-start text-sm font-semibold text-gray-500">{{ t('city_col') }}</th>
                        <th class="px-4 py-4 text-start text-sm font-semibold text-gray-500">{{ t('status_col') }}</th>
                        <th class="px-4 py-4 text-center text-sm font-semibold text-gray-500">{{ t('views_col') }}</th>
                        <th class="px-4 py-4 text-center text-sm font-semibold text-gray-500">{{ t('clicks_col') }}</th>
                        <th class="px-4 py-4 text-end text-sm font-semibold text-gray-500">{{ t('actions_col') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="listing in listings.data" :key="listing.id" class="hover:bg-gray-50">
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-3">
                                <div class="size-12 shrink-0 overflow-hidden rounded-lg bg-gray-100">
                                    <img
                                        v-if="listing.primary_image"
                                        :src="listing.primary_image"
                                        :alt="listing.title"
                                        class="size-full object-cover"
                                    />
                                    <div
                                        v-else
                                        class="flex size-full items-center justify-center bg-gray-200 text-xs text-gray-400"
                                    >
                                        {{ t('na') }}
                                    </div>
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate font-medium text-[#1F1F1F]">
                                        {{ listing.title }}
                                    </p>
                                    <p class="text-sm capitalize text-gray-500">
                                        {{ listing.property_type }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-4 py-4">
                            <span
                                class="rounded-lg px-2.5 py-1 text-xs font-medium"
                                :class="
                                    listing.listing_type === 'sale'
                                        ? 'bg-yellow-100 text-yellow-800'
                                        : 'bg-blue-100 text-blue-800'
                                "
                            >
                                {{ listing.listing_type === 'sale' ? t('sale') : t('rent') }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-4 font-medium text-[#FFC107]">
                            {{ listing.formatted_price }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-4 text-[#1F1F1F]">
                            {{ listing.city }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-4">
                            <span
                                class="rounded-lg px-2.5 py-1 text-xs font-medium"
                                :class="
                                    listing.is_active
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-gray-100 text-gray-500'
                                "
                            >
                                {{ listing.is_active ? t('active') : t('inactive') }}
                            </span>
                            <span
                                v-if="listing.is_featured"
                                class="ms-1 rounded-lg bg-[#FFC107]/20 px-2.5 py-1 text-xs font-medium text-[#1F1F1F]"
                            >
                                {{ t('featured') }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-4 text-center text-sm font-medium text-gray-500">
                            {{ listing.views_count }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-4 text-center text-sm font-medium text-gray-500">
                            {{ listing.clicks_count }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-4 text-end">
                            <div class="flex items-center justify-end gap-2">
                                <button
                                    @click="toggleActive(listing)"
                                    class="flex size-10 items-center justify-center rounded-lg hover:bg-gray-100"
                                    :title="listing.is_active ? t('deactivate') : t('activate')"
                                >
                                    <ToggleRight v-if="listing.is_active" class="size-5 text-green-600" />
                                    <ToggleLeft v-else class="size-5 text-gray-400" />
                                </button>
                                <Link
                                    :href="adminListings.edit(listing.id).url"
                                    class="flex size-10 items-center justify-center rounded-lg hover:bg-gray-100"
                                >
                                    <Pencil class="size-5 text-gray-500" />
                                </Link>
                                <button
                                    @click="deleteListing(listing.id)"
                                    class="flex size-10 items-center justify-center rounded-lg hover:bg-red-50"
                                >
                                    <Trash2 class="size-5 text-red-500" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Empty State -->
            <div v-if="listings.data.length === 0" class="py-20 text-center">
                <p class="text-lg text-gray-500">{{ t('no_listings_found') }}</p>
            </div>
        </div>
    </div>
</template>
