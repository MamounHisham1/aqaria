<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Bed, Bath, Maximize, MapPin } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

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
    views_count?: number;
};

type Props = {
    listing: Listing;
};

defineProps<Props>();

const propertyTypeKey = (type: string) => {
    const map: Record<string, string> = {
        apartment: 'apartment',
        villa: 'villa',
        townhouse: 'townhouse',
        commercial: 'commercial_type',
    };

    return map[type.toLowerCase()] || type;
};
</script>

<template>
    <Link
        :href="`/listings/${listing.id}`"
        class="group block overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition-all hover:shadow-lg hover:-translate-y-0.5 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#FFC107]"
    >
        <!-- Image -->
        <div class="relative aspect-[4/3] overflow-hidden bg-gray-100">
            <img
                v-if="listing.primary_image"
                :src="listing.primary_image"
                :alt="listing.title"
                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                loading="lazy"
            />
            <div
                v-else
                class="flex h-full w-full items-center justify-center bg-gray-200"
            >
                <span class="text-4xl text-gray-400">Aqaria</span>
            </div>

            <!-- Badge: Sale/Rent -->
            <span
                class="absolute left-3 top-3 rounded-lg px-3 py-1.5 text-sm font-semibold rtl:left-auto rtl:right-3"
                :class="
                    listing.listing_type === 'sale'
                        ? 'bg-[#FFC107] text-[#1F1F1F]'
                        : 'bg-[#1F1F1F] text-white'
                "
            >
                {{ listing.listing_type === 'sale' ? t('for_sale') : t('for_rent') }}
            </span>

            <!-- Property type badge -->
            <span
                class="absolute right-3 top-3 rounded-lg bg-white/90 px-3 py-1.5 text-sm font-medium text-[#1F1F1F] backdrop-blur-sm rtl:left-3 rtl:right-auto"
            >
                {{ t(propertyTypeKey(listing.property_type)) }}
            </span>
        </div>

        <!-- Content -->
        <div class="p-4">
            <!-- Price -->
            <p class="mb-1 text-xl font-bold text-[#FFC107]">
                {{ listing.formatted_price }}
            </p>

            <!-- Title -->
            <h3 class="mb-2 text-base font-semibold text-[#1F1F1F] line-clamp-2 group-hover:text-[#FFC107] text-left rtl:text-right">
                {{ listing.title }}
            </h3>

            <!-- Location -->
            <p class="mb-3 flex items-center gap-1 text-sm text-gray-500 text-left rtl:text-right">
                <MapPin class="size-4" />
                {{ listing.district }}, {{ listing.city }}
            </p>

            <!-- Features -->
            <div
                v-if="listing.property_type !== 'commercial'"
                class="flex items-center gap-4 border-t border-gray-100 pt-3 text-sm text-gray-600"
            >
                <span class="flex items-center gap-1.5">
                    <Bed class="size-4" />
                    {{ listing.bedrooms }} {{ t('beds') }}
                </span>
                <span class="flex items-center gap-1.5">
                    <Bath class="size-4" />
                    {{ listing.bathrooms }} {{ t('baths') }}
                </span>
                <span class="flex items-center gap-1.5">
                    <Maximize class="size-4" />
                    {{ listing.area_sqm }} {{ t('area_m2') }}
                </span>
            </div>
            <div
                v-else
                class="flex items-center gap-4 border-t border-gray-100 pt-3 text-sm text-gray-600"
            >
                <span class="flex items-center gap-1.5">
                    <Maximize class="size-4" />
                    {{ listing.area_sqm }} {{ t('area_m2') }}
                </span>
            </div>
        </div>
    </Link>
</template>
