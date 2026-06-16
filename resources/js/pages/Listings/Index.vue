<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { SlidersHorizontal, X } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import FilterPanel from '@/components/FilterPanel.vue';
import ListingCard from '@/components/ListingCard.vue';
import SearchBar from '@/components/SearchBar.vue';

type Listing = {
    id: number;
    title: string;
    price: string;
    formatted_price: string;
    primary_image: string | null;
    city: string;
    district: string;
    property_type: string;
    listing_type: string;
    bedrooms: number;
    bathrooms: number;
    area_sqm: number;
};

type Props = {
    listings: {
        data: Listing[];
        current_page: number;
        last_page: number;
        total: number;
        from: number | null;
        to: number | null;
    };
    filters: Record<string, string>;
    filterOptions: {
        cities: string[];
        propertyTypes: string[];
        listingTypes: string[];
    };
};

const props = defineProps<Props>();

const mobileFiltersOpen = ref(false);

function updateFilters(newFilters: Record<string, string>) {
    router.get(
        '/listings',
        { ...newFilters },
        {
            preserveState: true,
            replace: true,
            preserveScroll: true,
        },
    );
}

function goToPage(page: number) {
    router.get(
        '/listings',
        { ...props.filters, page: page.toString() },
        {
            preserveState: true,
            replace: true,
            preserveScroll: false,
        },
    );
}
</script>

<template>
    <Head :title="$t('browse_listings')" />

    <div class="border-b border-gray-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-6">
            <div class="mb-4 flex items-center justify-between">
                <div class="rtl:text-right">
                    <h1 class="text-2xl font-bold text-[#1F1F1F] md:text-3xl">
                        {{ $t('browse_listings') }}
                    </h1>
                    <p class="mt-1 text-base text-gray-500">
                        {{ $t('properties_found', { count: listings.total }) }}
                    </p>
                </div>

                <!-- Mobile filter toggle -->
                <button
                    @click="mobileFiltersOpen = !mobileFiltersOpen"
                    class="flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-3 text-base font-medium text-[#1F1F1F] hover:bg-gray-50 md:hidden"
                >
                    <SlidersHorizontal class="size-5" />
                    {{ $t('filters') }}
                </button>
            </div>

            <SearchBar
                :cities="filterOptions.cities"
                :initial-query="filters.q"
                :initial-city="filters.city"
                compact
            />
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="flex gap-8">
            <!-- Desktop Filters Sidebar -->
            <aside class="hidden w-72 shrink-0 md:block">
                <FilterPanel
                    :filters="filters"
                    :cities="filterOptions.cities"
                    :property-types="filterOptions.propertyTypes"
                    :listing-types="filterOptions.listingTypes"
                    @update:filters="updateFilters"
                />
            </aside>

            <!-- Mobile Filters Drawer -->
            <div
                v-if="mobileFiltersOpen"
                class="fixed inset-0 z-50 md:hidden"
            >
                <div class="absolute inset-0 bg-black/50" @click="mobileFiltersOpen = false" />
                <div class="absolute inset-y-0 right-0 w-full max-w-sm bg-white shadow-xl rtl:left-0 rtl:right-auto">
                    <div class="flex items-center justify-between border-b border-gray-200 px-4 py-4">
                        <h2 class="text-lg font-bold text-[#1F1F1F]">{{ $t('filters') }}</h2>
                        <button
                            @click="mobileFiltersOpen = false"
                            class="flex size-10 items-center justify-center rounded-lg hover:bg-gray-100"
                        >
                            <X class="size-6" />
                        </button>
                    </div>
                    <div class="overflow-y-auto p-4">
                        <FilterPanel
                            :filters="filters"
                            :cities="filterOptions.cities"
                            :property-types="filterOptions.propertyTypes"
                            :listing-types="filterOptions.listingTypes"
                            @update:filters="(f: Record<string, string>) => { updateFilters(f); mobileFiltersOpen = false; }"
                        />
                    </div>
                </div>
            </div>

            <!-- Listings Grid -->
            <div class="flex-1">
                <!-- Results count and sort info -->
                <div
                    v-if="listings.data.length > 0"
                    class="mb-6 flex items-center justify-between text-sm text-gray-500 rtl:text-right"
                >
                    <span>
                        {{ $t('showing_results', { from: listings.from, to: listings.to, total: listings.total }) }}
                    </span>
                </div>

                <!-- Listing Cards -->
                <div
                    v-if="listings.data.length > 0"
                    class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3"
                >
                    <ListingCard
                        v-for="listing in listings.data"
                        :key="listing.id"
                        :listing="listing"
                    />
                </div>

                <!-- Empty State -->
                <div
                    v-else
                    class="flex flex-col items-center justify-center py-20 text-center"
                >
                    <div class="mb-4 flex size-20 items-center justify-center rounded-2xl bg-gray-100">
                        <SlidersHorizontal class="size-10 text-gray-400" />
                    </div>
                    <h3 class="mb-2 text-xl font-bold text-[#1F1F1F]">
                        {{ $t('no_properties_found') }}
                    </h3>
                    <p class="mb-6 text-base text-gray-500">
                        {{ $t('adjust_filters') }}
                    </p>
                    <button
                        @click="updateFilters({ sort: 'newest' })"
                        class="rounded-xl bg-[#FFC107] px-6 py-3 text-base font-semibold text-[#1F1F1F] hover:bg-yellow-500"
                    >
                        {{ $t('clear_all_filters') }}
                    </button>
                </div>

                <!-- Pagination -->
                <div
                    v-if="listings.last_page > 1"
                    class="mt-8 flex items-center justify-center gap-2"
                >
                    <button
                        v-for="page in listings.last_page"
                        :key="page"
                        @click="goToPage(page)"
                        class="flex h-11 min-w-[44px] items-center justify-center rounded-lg border text-base font-medium"
                        :class="
                            page === listings.current_page
                                ? 'border-[#FFC107] bg-[#FFC107] text-[#1F1F1F]'
                                : 'border-gray-200 text-[#1F1F1F] hover:bg-gray-50'
                        "
                    >
                        {{ page }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
