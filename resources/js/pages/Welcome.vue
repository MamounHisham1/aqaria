<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Search, Building2, Home, MapPin } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import ListingCard from '@/components/ListingCard.vue';
import SearchBar from '@/components/SearchBar.vue';
import { index as listings } from '@/routes/listings/index';

const { t } = useI18n();

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
    featuredListings: Listing[];
    totalListings: number;
    cities: string[];
};

defineProps<Props>();
</script>

<template>
    <Head :title="t('hero_title')" />

    <!-- Hero Section -->
    <section class="relative bg-[#1F1F1F] px-4 py-16 md:py-24">
        <div class="mx-auto max-w-7xl text-center">
            <h1 class="mb-4 text-3xl font-bold text-white md:text-5xl">
                {{ t('hero_title') }}
            </h1>
            <p class="mx-auto mb-8 max-w-2xl text-lg text-gray-300 md:text-xl">
                {{ t('hero_subtitle') }}
            </p>

            <!-- Search -->
            <div class="mx-auto flex justify-center">
                <SearchBar :cities="cities" />
            </div>

            <!-- Quick Links -->
            <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                <Link
                    :href="listings({ listing_type: 'sale' })"
                    class="rounded-xl bg-white/10 px-5 py-3 text-base font-medium text-white backdrop-blur-sm transition-colors hover:bg-[#FFC107] hover:text-[#1F1F1F]"
                >
                    {{ t('for_sale') }}
                </Link>
                <Link
                    :href="listings({ listing_type: 'rent' })"
                    class="rounded-xl bg-white/10 px-5 py-3 text-base font-medium text-white backdrop-blur-sm transition-colors hover:bg-[#FFC107] hover:text-[#1F1F1F]"
                >
                    {{ t('for_rent') }}
                </Link>
                <Link
                    :href="listings()"
                    class="rounded-xl bg-[#FFC107] px-5 py-3 text-base font-semibold text-[#1F1F1F] transition-colors hover:bg-yellow-500"
                >
                    {{ t('browse_all_listings') }}
                </Link>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="border-b border-gray-200 bg-white px-4 py-12">
        <div class="mx-auto max-w-7xl">
            <div class="grid grid-cols-2 gap-8 md:grid-cols-4">
                <div class="text-center">
                    <p class="text-3xl font-bold text-[#FFC107]">
                        {{ totalListings.toLocaleString() }}
                    </p>
                    <p class="mt-1 text-base text-gray-500">{{ t('properties_listed') }}</p>
                </div>
                <div class="text-center">
                    <p class="text-3xl font-bold text-[#FFC107]">
                        {{ cities.length }}
                    </p>
                    <p class="mt-1 text-base text-gray-500">{{ t('cities_covered') }}</p>
                </div>
                <div class="text-center">
                    <p class="text-3xl font-bold text-[#FFC107]">2</p>
                    <p class="mt-1 text-base text-gray-500">{{ t('listing_types') }}</p>
                </div>
                <div class="text-center">
                    <p class="text-3xl font-bold text-[#FFC107]">4</p>
                    <p class="mt-1 text-base text-gray-500">{{ t('property_types') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Listings -->
    <section class="bg-gray-50 px-4 py-16">
        <div class="mx-auto max-w-7xl">
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-[#1F1F1F] md:text-3xl">
                        {{ t('featured_properties') }}
                    </h2>
                    <p class="mt-2 text-base text-gray-500">
                        {{ t('hand_picked_properties') }}
                    </p>
                </div>
                <Link
                    :href="listings()"
                    class="hidden rounded-xl bg-[#FFC107] px-5 py-3 text-base font-semibold text-[#1F1F1F] transition-colors hover:bg-yellow-500 md:inline-flex"
                >
                    {{ t('view_all') }}
                </Link>
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <ListingCard
                    v-for="listing in featuredListings"
                    :key="listing.id"
                    :listing="listing"
                />
            </div>

            <div class="mt-8 text-center md:hidden">
                <Link
                    :href="listings()"
                    class="inline-flex rounded-xl bg-[#FFC107] px-6 py-3.5 text-base font-semibold text-[#1F1F1F]"
                >
                    {{ t('view_all_listings') }}
                </Link>
            </div>
        </div>
    </section>

    <!-- Property Type Categories -->
    <section class="bg-white px-4 py-16">
        <div class="mx-auto max-w-7xl">
            <h2 class="mb-8 text-center text-2xl font-bold text-[#1F1F1F] md:text-3xl">
                {{ t('browse_by_property_type') }}
            </h2>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <Link
                    :href="listings({ property_type: 'apartment' })"
                    class="group rounded-2xl border border-gray-200 p-6 text-center transition-all hover:border-[#FFC107] hover:shadow-lg"
                >
                    <div class="mx-auto mb-4 flex size-16 items-center justify-center rounded-2xl bg-[#FFC107]/10">
                        <Building2 class="size-8 text-[#FFC107]" />
                    </div>
                    <h3 class="text-lg font-bold text-[#1F1F1F] group-hover:text-[#FFC107]">
                        {{ t('apartments') }}
                    </h3>
                    <p class="mt-1 text-base text-gray-500">{{ t('apartments_desc') }}</p>
                </Link>

                <Link
                    :href="listings({ property_type: 'villa' })"
                    class="group rounded-2xl border border-gray-200 p-6 text-center transition-all hover:border-[#FFC107] hover:shadow-lg"
                >
                    <div class="mx-auto mb-4 flex size-16 items-center justify-center rounded-2xl bg-[#FFC107]/10">
                        <Home class="size-8 text-[#FFC107]" />
                    </div>
                    <h3 class="text-lg font-bold text-[#1F1F1F] group-hover:text-[#FFC107]">
                        {{ t('villas') }}
                    </h3>
                    <p class="mt-1 text-base text-gray-500">{{ t('villas_desc') }}</p>
                </Link>

                <Link
                    :href="listings({ property_type: 'townhouse' })"
                    class="group rounded-2xl border border-gray-200 p-6 text-center transition-all hover:border-[#FFC107] hover:shadow-lg"
                >
                    <div class="mx-auto mb-4 flex size-16 items-center justify-center rounded-2xl bg-[#FFC107]/10">
                        <Building2 class="size-8 text-[#FFC107]" />
                    </div>
                    <h3 class="text-lg font-bold text-[#1F1F1F] group-hover:text-[#FFC107]">
                        {{ t('townhouses') }}
                    </h3>
                    <p class="mt-1 text-base text-gray-500">{{ t('townhouses_desc') }}</p>
                </Link>

                <Link
                    :href="listings({ property_type: 'commercial' })"
                    class="group rounded-2xl border border-gray-200 p-6 text-center transition-all hover:border-[#FFC107] hover:shadow-lg"
                >
                    <div class="mx-auto mb-4 flex size-16 items-center justify-center rounded-2xl bg-[#FFC107]/10">
                        <Building2 class="size-8 text-[#FFC107]" />
                    </div>
                    <h3 class="text-lg font-bold text-[#1F1F1F] group-hover:text-[#FFC107]">
                        {{ t('commercial') }}
                    </h3>
                    <p class="mt-1 text-base text-gray-500">{{ t('commercial_desc') }}</p>
                </Link>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="bg-[#1F1F1F] px-4 py-16">
        <div class="mx-auto max-w-3xl text-center">
            <h2 class="mb-4 text-2xl font-bold text-white md:text-3xl">
                {{ t('ready_to_find_home') }}
            </h2>
            <p class="mb-8 text-lg text-gray-300">
                {{ t('cta_desc') }}
            </p>
            <Link
                :href="listings()"
                class="inline-flex rounded-xl bg-[#FFC107] px-8 py-4 text-lg font-bold text-[#1F1F1F] transition-colors hover:bg-yellow-500"
            >
                {{ t('start_searching') }}
            </Link>
        </div>
    </section>
</template>
