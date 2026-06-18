<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Building2, Home } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import ListingCard from '@/components/ListingCard.vue';
import SearchBar from '@/components/SearchBar.vue';
import SeoMeta from '@/components/SeoMeta.vue';
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
    seo?: { title: string; description?: string; url?: string };
};

defineProps<Props>();
</script>

<template>
    <SeoMeta
        :title="seo?.title ?? t('hero_title')"
        :description="seo?.description ?? t('hero_subtitle')"
        :url="seo?.url"
    />

    <!-- Hero Section -->
    <section class="relative min-h-[75vh] md:min-h-[85vh] flex items-center justify-center bg-[#1F1F1F] px-4 py-16 md:py-24 overflow-hidden">
        <!-- Background Image with elegant overlay -->
        <div class="absolute inset-0 z-0 select-none pointer-events-none">
            <img
                src="/images/hero-bg.png"
                alt="Aqaria Luxury Real Estate"
                class="h-full w-full object-cover object-center scale-105 animate-zoom"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-[#1F1F1F] via-[#1F1F1F]/80 to-[#1F1F1F]/45"></div>
            <!-- Bottom subtle glow matching the golden hour sunset -->
            <div class="absolute -bottom-1/4 left-1/2 h-[300px] w-[500px] -translate-x-1/2 rounded-full bg-[#FFC107]/10 blur-[120px]"></div>
        </div>

        <div class="relative z-10 mx-auto max-w-7xl text-center w-full">
            <h1 class="mb-4 text-4xl font-extrabold tracking-tight text-white md:text-6xl drop-shadow-[0_4px_12px_rgba(0,0,0,0.65)]">
                {{ t('hero_title') }}
            </h1>
            <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-200 md:text-xl font-medium drop-shadow-[0_2px_8px_rgba(0,0,0,0.7)]">
                {{ t('hero_subtitle') }}
            </p>

            <!-- Search -->
            <div class="mx-auto flex justify-center transform hover:scale-[1.01] transition-transform duration-300">
                <SearchBar :cities="cities" class="shadow-2xl border border-white/10" />
            </div>

            <!-- Quick Links -->
            <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                <Link
                    :href="listings({ listing_type: 'sale' })"
                    class="rounded-xl bg-white/10 border border-white/15 px-6 py-3.5 text-base font-semibold text-white backdrop-blur-md transition-all duration-300 hover:bg-[#FFC107] hover:border-[#FFC107] hover:text-[#1F1F1F] hover:shadow-lg hover:shadow-[#FFC107]/15"
                >
                    {{ t('for_sale') }}
                </Link>
                <Link
                    :href="listings({ listing_type: 'rent' })"
                    class="rounded-xl bg-white/10 border border-white/15 px-6 py-3.5 text-base font-semibold text-white backdrop-blur-md transition-all duration-300 hover:bg-[#FFC107] hover:border-[#FFC107] hover:text-[#1F1F1F] hover:shadow-lg hover:shadow-[#FFC107]/15"
                >
                    {{ t('for_rent') }}
                </Link>
                <Link
                    :href="listings()"
                    class="rounded-xl bg-[#FFC107] px-6 py-3.5 text-base font-bold text-[#1F1F1F] shadow-lg shadow-[#FFC107]/20 transition-all duration-300 hover:bg-yellow-500 hover:shadow-[#FFC107]/40 hover:-translate-y-0.5"
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
