<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import {
    Bed, Bath, Maximize, MapPin, Phone, MessageCircle,
    ChevronLeft, ChevronRight, Check,
} from 'lucide-vue-next';
import ListingCard from '@/components/ListingCard.vue';
import { index as listings, click } from '@/routes/listings/index';

type Listing = {
    id: number;
    title: string;
    description: string;
    price: string;
    formatted_price: string;
    primary_image: string | null;
    images: string[];
    city: string;
    district: string;
    address: string;
    property_type: string;
    listing_type: string;
    bedrooms: number;
    bathrooms: number;
    area_sqm: number;
    contact_phone: string;
    contact_whatsapp: string | null;
    amenities: string[];
    views_count: number;
    clicks_count: number;
};

type Props = {
    listing: Listing;
    relatedListings: Listing[];
};

const props = defineProps<Props>();

const currentImageIndex = ref(0);

function nextImage() {
    if (props.listing.images && props.listing.images.length > 1) {
        currentImageIndex.value =
            (currentImageIndex.value + 1) % props.listing.images.length;
    }
}

function prevImage() {
    if (props.listing.images && props.listing.images.length > 1) {
        currentImageIndex.value =
            (currentImageIndex.value - 1 + props.listing.images.length) %
            props.listing.images.length;
    }
}

function trackClick(type: string) {
    router.post(
        click({ listing: props.listing.id }).url,
        { click_type: type },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
}

const { t } = useI18n();

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
    <Head :title="listing.title" />

    <div class="bg-gray-50">
        <div class="mx-auto max-w-7xl px-4 pt-6">
            <Link
                :href="listings()"
                class="inline-flex items-center gap-2 text-base font-medium text-[#1F1F1F] hover:text-[#FFC107]"
            >
                <ChevronLeft class="size-5 rtl:rotate-180" />
                {{ t('back_to_listings') }}
            </Link>
        </div>

        <div class="mx-auto max-w-7xl px-4 py-6">
            <div class="grid gap-8 lg:grid-cols-3">
                <!-- Main Content -->
                <div class="lg:col-span-2">
                    <!-- Image Gallery -->
                    <div class="relative overflow-hidden rounded-2xl bg-gray-200">
                        <img
                            v-if="listing.images && listing.images.length > 0"
                            :src="listing.images[currentImageIndex]"
                            :alt="listing.title"
                            class="aspect-[16/10] w-full object-cover"
                        />
                        <div
                            v-else
                            class="flex aspect-[16/10] items-center justify-center bg-gray-200"
                        >
                            <span class="text-6xl text-gray-400">Aqaria</span>
                        </div>

                        <!-- Image Navigation -->
                        <template v-if="listing.images && listing.images.length > 1">
                            <button
                                @click="prevImage"
                                class="absolute left-3 top-1/2 flex size-12 -translate-y-1/2 items-center justify-center rounded-xl bg-white/80 backdrop-blur-sm hover:bg-white"
                            >
                                <ChevronLeft class="size-6" />
                            </button>
                            <button
                                @click="nextImage"
                                class="absolute right-3 top-1/2 flex size-12 -translate-y-1/2 items-center justify-center rounded-xl bg-white/80 backdrop-blur-sm hover:bg-white"
                            >
                                <ChevronRight class="size-6" />
                            </button>

                            <!-- Dots -->
                            <div class="absolute bottom-3 left-1/2 flex -translate-x-1/2 gap-2">
                                <button
                                    v-for="(_, i) in listing.images"
                                    :key="i"
                                    @click="currentImageIndex = i"
                                    class="size-3 rounded-full transition-colors"
                                    :class="
                                        i === currentImageIndex
                                            ? 'bg-[#FFC107]'
                                            : 'bg-white/60'
                                    "
                                />
                            </div>
                        </template>

                        <span
                            class="absolute left-4 top-4 rounded-lg px-3 py-1.5 text-sm font-semibold rtl:left-auto rtl:right-4"
                            :class="
                                listing.listing_type === 'sale'
                                    ? 'bg-[#FFC107] text-[#1F1F1F]'
                                    : 'bg-[#1F1F1F] text-white'
                            "
                        >
                            {{ listing.listing_type === 'sale' ? t('for_sale') : t('for_rent') }}
                        </span>
                    </div>

                    <!-- Key Details -->
                    <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-6">
                        <div class="mb-4">
                            <p class="mb-1 text-3xl font-bold text-[#FFC107]">
                                {{ listing.formatted_price }}
                            </p>
                            <h1 class="text-2xl font-bold text-[#1F1F1F]">
                                {{ listing.title }}
                            </h1>
                            <p class="mt-2 flex items-center gap-1 text-base text-gray-500">
                                <MapPin class="size-5" />
                                {{ listing.address }}
                            </p>
                        </div>

                        <div
                            v-if="listing.property_type !== 'commercial'"
                            class="flex flex-wrap gap-6 border-t border-gray-100 pt-4"
                        >
                            <div class="flex items-center gap-2">
                                <Bed class="size-5 text-gray-400" />
                                <span class="text-base font-medium text-[#1F1F1F]">
                                    {{ t('bedrooms_count', { count: listing.bedrooms }) }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <Bath class="size-5 text-gray-400" />
                                <span class="text-base font-medium text-[#1F1F1F]">
                                    {{ t('bathrooms_count', { count: listing.bathrooms }) }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <Maximize class="size-5 text-gray-400" />
                                <span class="text-base font-medium text-[#1F1F1F]">
                                    {{ listing.area_sqm }} {{ t('area_m2') }}
                                </span>
                            </div>
                        </div>
                        <div v-else class="flex flex-wrap gap-6 border-t border-gray-100 pt-4">
                            <div class="flex items-center gap-2">
                                <Maximize class="size-5 text-gray-400" />
                                <span class="text-base font-medium text-[#1F1F1F]">
                                    {{ listing.area_sqm }} {{ t('area_m2') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-6">
                        <h2 class="mb-3 text-xl font-bold text-[#1F1F1F]">{{ t('description_label') }}</h2>
                        <p class="text-base leading-relaxed text-gray-600">
                            {{ listing.description }}
                        </p>
                    </div>

                    <div
                        v-if="listing.amenities && listing.amenities.length > 0"
                        class="mt-6 rounded-2xl border border-gray-200 bg-white p-6"
                    >
                        <h2 class="mb-4 text-xl font-bold text-[#1F1F1F]">{{ t('amenities') }}</h2>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                            <div
                                v-for="amenity in listing.amenities"
                                :key="amenity"
                                class="flex items-center gap-2"
                            >
                                <Check class="size-5 text-[#FFC107]" />
                                <span class="text-base text-[#1F1F1F]">{{ amenity }}</span>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="relatedListings.length > 0"
                        class="mt-8"
                    >
                        <h2 class="mb-4 text-xl font-bold text-[#1F1F1F]">
                            {{ t('similar_properties_in', { city: listing.city }) }}
                        </h2>
                        <div class="grid gap-6 sm:grid-cols-2">
                            <ListingCard
                                v-for="related in relatedListings"
                                :key="related.id"
                                :listing="related"
                            />
                        </div>
                    </div>
                </div>

                <!-- Sidebar - Contact Card -->
                <div class="lg:col-span-1">
                    <div class="sticky top-24 space-y-6">
                        <div class="rounded-2xl border border-gray-200 bg-white p-6">
                            <h3 class="mb-4 text-lg font-bold text-[#1F1F1F]">
                                {{ t('interested_in_property') }}
                            </h3>
                            <div class="space-y-3">
                                <a
                                    :href="`tel:${listing.contact_phone}`"
                                    @click="trackClick('phone')"
                                    class="flex items-center justify-center gap-2 rounded-xl bg-[#FFC107] px-4 py-4 text-base font-semibold text-[#1F1F1F] transition-colors hover:bg-yellow-500"
                                >
                                    <Phone class="size-5" />
                                    {{ listing.contact_phone }}
                                </a>
                                <a
                                    v-if="listing.contact_whatsapp"
                                    :href="`https://wa.me/${listing.contact_whatsapp}`"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    @click="trackClick('whatsapp')"
                                    class="flex items-center justify-center gap-2 rounded-xl bg-green-500 px-4 py-4 text-base font-semibold text-white transition-colors hover:bg-green-600"
                                >
                                    <MessageCircle class="size-5" />
                                    {{ t('whatsapp_label') }}
                                </a>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-gray-200 bg-white p-6">
                            <h3 class="mb-4 text-lg font-bold text-[#1F1F1F]">
                                {{ t('property_details') }}
                            </h3>
                            <dl class="space-y-3">
                                <div class="flex justify-between">
                                    <dt class="text-base text-gray-500">{{ t('type_col') }}</dt>
                                    <dd class="text-base font-medium capitalize text-[#1F1F1F]">
                                        {{ t(propertyTypeKey(listing.property_type)) }}
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-base text-gray-500">{{ t('status_col') }}</dt>
                                    <dd class="text-base font-medium text-[#1F1F1F]">
                                        {{ listing.listing_type === 'sale' ? t('for_sale') : t('for_rent') }}
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-base text-gray-500">{{ t('city_label') }}</dt>
                                    <dd class="text-base font-medium text-[#1F1F1F]">
                                        {{ listing.city }}
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-base text-gray-500">{{ t('district_label') }}</dt>
                                    <dd class="text-base font-medium text-[#1F1F1F]">
                                        {{ listing.district }}
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-base text-gray-500">{{ t('views_col') }}</dt>
                                    <dd class="text-base font-medium text-[#1F1F1F]">
                                        {{ listing.views_count }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
