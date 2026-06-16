<script setup lang="ts">
import { ChevronDown, ChevronUp } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

type Filters = {
    q?: string;
    city?: string;
    listing_type?: string;
    property_type?: string;
    min_price?: string;
    max_price?: string;
    min_area?: string;
    max_area?: string;
    bedrooms?: string;
    bathrooms?: string;
    sort?: string;
};

type Props = {
    filters: Filters;
    cities?: string[];
    propertyTypes?: string[];
    listingTypes?: string[];
};

const props = withDefaults(defineProps<Props>(), {
    cities: () => [],
    propertyTypes: () => [],
    listingTypes: () => [],
});

const emit = defineEmits<{
    (e: 'update:filters', filters: Record<string, string>): void;
}>();

const localFilters = ref<Record<string, string>>({ ...props.filters });
const openSections = ref<Record<string, boolean>>({
    type: true,
    property: true,
    price: false,
    area: false,
    rooms: false,
    sort: true,
});

watch(
    () => props.filters,
    (newFilters) => {
        localFilters.value = { ...newFilters };
    },
    { deep: true },
);

function toggleSection(section: string) {
    openSections.value[section] = !openSections.value[section];
}

function applyFilter(key: string, value: string) {
    if (value) {
        localFilters.value[key] = value;
    } else {
        delete localFilters.value[key];
    }

    emit('update:filters', { ...localFilters.value });
}

function clearAll() {
    localFilters.value = { sort: props.filters.sort || 'newest' };
    emit('update:filters', { ...localFilters.value });
}
</script>

<template>
    <div class="space-y-1">
        <!-- Clear Filters -->
        <div class="mb-4 flex items-center justify-between rtl:flex-row-reverse">
            <h3 class="text-lg font-bold text-[#1F1F1F]">{{ $t('filters') }}</h3>
            <button
                @click="clearAll"
                class="text-sm font-medium text-[#FFC107] hover:text-yellow-600"
            >
                {{ $t('clear_all') }}
            </button>
        </div>

        <!-- Sort By -->
        <div class="rounded-xl border border-gray-200 bg-white">
            <button
                @click="toggleSection('sort')"
                class="flex w-full items-center justify-between p-4 text-left rtl:text-right"
            >
                <span class="font-semibold text-[#1F1F1F]">{{ $t('sort_by') }}</span>
                <ChevronUp v-if="openSections.sort" class="size-5 text-gray-400" />
                <ChevronDown v-else class="size-5 text-gray-400" />
            </button>
            <div v-show="openSections.sort" class="border-t border-gray-100 px-4 pb-4">
                <select
                    :value="localFilters.sort || 'newest'"
                    @change="applyFilter('sort', ($event.target as HTMLSelectElement).value)"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-base text-[#1F1F1F] focus:border-[#FFC107] focus:outline-none focus:ring-2 focus:ring-[#FFC107]/30"
                >
                    <option value="newest">{{ $t('newest_first') }}</option>
                    <option value="price_asc">{{ $t('price_low_high') }}</option>
                    <option value="price_desc">{{ $t('price_high_low') }}</option>
                    <option value="area_asc">{{ $t('area_small_large') }}</option>
                    <option value="area_desc">{{ $t('area_large_small') }}</option>
                </select>
            </div>
        </div>

        <!-- Listing Type -->
        <div class="rounded-xl border border-gray-200 bg-white">
            <button
                @click="toggleSection('type')"
                class="flex w-full items-center justify-between p-4 text-left rtl:text-right"
            >
                <span class="font-semibold text-[#1F1F1F]">{{ $t('listing_type') }}</span>
                <ChevronUp v-if="openSections.type" class="size-5 text-gray-400" />
                <ChevronDown v-else class="size-5 text-gray-400" />
            </button>
            <div v-show="openSections.type" class="border-t border-gray-100 px-4 pb-4">
                <div class="space-y-2">
                    <label
                        v-for="type in listingTypes"
                        :key="type"
                        class="flex cursor-pointer items-center gap-3 rounded-lg px-3 py-2.5 hover:bg-gray-50 rtl:flex-row-reverse rtl:justify-end"
                    >
                        <input
                            type="radio"
                            name="listing_type"
                            :value="type"
                            :checked="localFilters.listing_type === type"
                            @change="applyFilter('listing_type', type)"
                            class="size-5 accent-[#FFC107]"
                        />
                        <span class="text-base font-medium capitalize text-[#1F1F1F]">
                            {{ type === 'sale' ? $t('for_sale') : $t('for_rent') }}
                        </span>
                    </label>
                    <label class="flex cursor-pointer items-center gap-3 rounded-lg px-3 py-2.5 hover:bg-gray-50 rtl:flex-row-reverse rtl:justify-end">
                        <input
                            type="radio"
                            name="listing_type"
                            value=""
                            :checked="!localFilters.listing_type"
                            @change="applyFilter('listing_type', '')"
                            class="size-5 accent-[#FFC107]"
                        />
                        <span class="text-base font-medium text-[#1F1F1F]">{{ $t('any') }}</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Property Type -->
        <div class="rounded-xl border border-gray-200 bg-white">
            <button
                @click="toggleSection('property')"
                class="flex w-full items-center justify-between p-4 text-left rtl:text-right"
            >
                <span class="font-semibold text-[#1F1F1F]">{{ $t('property_type') }}</span>
                <ChevronUp v-if="openSections.property" class="size-5 text-gray-400" />
                <ChevronDown v-else class="size-5 text-gray-400" />
            </button>
            <div v-show="openSections.property" class="border-t border-gray-100 px-4 pb-4">
                <div class="space-y-2">
                    <label
                        v-for="type in propertyTypes"
                        :key="type"
                        class="flex cursor-pointer items-center gap-3 rounded-lg px-3 py-2.5 hover:bg-gray-50 rtl:flex-row-reverse rtl:justify-end"
                    >
                        <input
                            type="radio"
                            name="property_type"
                            :value="type"
                            :checked="localFilters.property_type === type"
                            @change="applyFilter('property_type', type)"
                            class="size-5 accent-[#FFC107]"
                        />
                        <span class="text-base font-medium capitalize text-[#1F1F1F]">
                            {{ $t(type.toLowerCase()) }}
                        </span>
                    </label>
                    <label class="flex cursor-pointer items-center gap-3 rounded-lg px-3 py-2.5 hover:bg-gray-50 rtl:flex-row-reverse rtl:justify-end">
                        <input
                            type="radio"
                            name="property_type"
                            value=""
                            :checked="!localFilters.property_type"
                            @change="applyFilter('property_type', '')"
                            class="size-5 accent-[#FFC107]"
                        />
                        <span class="text-base font-medium text-[#1F1F1F]">{{ $t('any') }}</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Price Range -->
        <div class="rounded-xl border border-gray-200 bg-white">
            <button
                @click="toggleSection('price')"
                class="flex w-full items-center justify-between p-4 text-left rtl:text-right"
            >
                <span class="font-semibold text-[#1F1F1F]">{{ $t('price_range_egp') }}</span>
                <ChevronUp v-if="openSections.price" class="size-5 text-gray-400" />
                <ChevronDown v-else class="size-5 text-gray-400" />
            </button>
            <div v-show="openSections.price" class="border-t border-gray-100 px-4 pb-4">
                <div class="flex gap-3 rtl:flex-row-reverse">
                    <div class="flex-1">
                        <label class="mb-1 block text-sm text-gray-500 rtl:text-right">{{ $t('min') }}</label>
                        <input
                            type="number"
                            :value="localFilters.min_price || ''"
                            @input="applyFilter('min_price', ($event.target as HTMLInputElement).value)"
                            placeholder="0"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-base text-[#1F1F1F] focus:border-[#FFC107] focus:outline-none focus:ring-2 focus:ring-[#FFC107]/30"
                        />
                    </div>
                    <div class="flex-1">
                        <label class="mb-1 block text-sm text-gray-500 rtl:text-right">{{ $t('max') }}</label>
                        <input
                            type="number"
                            :value="localFilters.max_price || ''"
                            @input="applyFilter('max_price', ($event.target as HTMLInputElement).value)"
                            :placeholder="$t('any')"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-base text-[#1F1F1F] focus:border-[#FFC107] focus:outline-none focus:ring-2 focus:ring-[#FFC107]/30"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Area Range -->
        <div class="rounded-xl border border-gray-200 bg-white">
            <button
                @click="toggleSection('area')"
                class="flex w-full items-center justify-between p-4 text-left rtl:text-right"
            >
                <span class="font-semibold text-[#1F1F1F]">{{ $t('area_m2_range') }}</span>
                <ChevronUp v-if="openSections.area" class="size-5 text-gray-400" />
                <ChevronDown v-else class="size-5 text-gray-400" />
            </button>
            <div v-show="openSections.area" class="border-t border-gray-100 px-4 pb-4">
                <div class="flex gap-3 rtl:flex-row-reverse">
                    <div class="flex-1">
                        <label class="mb-1 block text-sm text-gray-500 rtl:text-right">{{ $t('min') }}</label>
                        <input
                            type="number"
                            :value="localFilters.min_area || ''"
                            @input="applyFilter('min_area', ($event.target as HTMLInputElement).value)"
                            placeholder="0"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-base text-[#1F1F1F] focus:border-[#FFC107] focus:outline-none focus:ring-2 focus:ring-[#FFC107]/30"
                        />
                    </div>
                    <div class="flex-1">
                        <label class="mb-1 block text-sm text-gray-500 rtl:text-right">{{ $t('max') }}</label>
                        <input
                            type="number"
                            :value="localFilters.max_area || ''"
                            @input="applyFilter('max_area', ($event.target as HTMLInputElement).value)"
                            :placeholder="$t('any')"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-base text-[#1F1F1F] focus:border-[#FFC107] focus:outline-none focus:ring-2 focus:ring-[#FFC107]/30"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Bedrooms & Bathrooms -->
        <div class="rounded-xl border border-gray-200 bg-white">
            <button
                @click="toggleSection('rooms')"
                class="flex w-full items-center justify-between p-4 text-left rtl:text-right"
            >
                <span class="font-semibold text-[#1F1F1F]">{{ $t('rooms') }}</span>
                <ChevronUp v-if="openSections.rooms" class="size-5 text-gray-400" />
                <ChevronDown v-else class="size-5 text-gray-400" />
            </button>
            <div v-show="openSections.rooms" class="border-t border-gray-100 px-4 pb-4">
                <div class="space-y-3">
                    <div>
                        <label class="mb-1 block text-sm text-gray-500 rtl:text-right">{{ $t('min_bedrooms') }}</label>
                        <select
                            :value="localFilters.bedrooms || ''"
                            @change="applyFilter('bedrooms', ($event.target as HTMLSelectElement).value)"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-base text-[#1F1F1F] focus:border-[#FFC107] focus:outline-none focus:ring-2 focus:ring-[#FFC107]/30"
                        >
                            <option value="">{{ $t('any') }}</option>
                            <option v-for="n in 6" :key="n" :value="n">{{ n }}+</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm text-gray-500 rtl:text-right">{{ $t('min_bathrooms') }}</label>
                        <select
                            :value="localFilters.bathrooms || ''"
                            @change="applyFilter('bathrooms', ($event.target as HTMLSelectElement).value)"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-base text-[#1F1F1F] focus:border-[#FFC107] focus:outline-none focus:ring-2 focus:ring-[#FFC107]/30"
                        >
                            <option value="">{{ $t('any') }}</option>
                            <option v-for="n in 4" :key="n" :value="n">{{ n }}+</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
