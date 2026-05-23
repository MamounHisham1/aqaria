<script setup lang="ts">
import { ref } from 'vue';
import { Search, MapPin } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

type Props = {
    cities?: string[];
    initialQuery?: string;
    initialCity?: string;
    compact?: boolean;
};

const props = withDefaults(defineProps<Props>(), {
    cities: () => [],
    initialQuery: '',
    initialCity: '',
    compact: false,
});

const query = ref(props.initialQuery);
const city = ref(props.initialCity);

function submitSearch() {
    const params: Record<string, string> = {};
    if (query.value.trim()) params.q = query.value.trim();
    if (city.value) params.city = city.value;

    const queryString = new URLSearchParams(params).toString();
    window.location.href = `/listings${queryString ? '?' + queryString : ''}`;
}
</script>

<template>
    <div
        :class="[
            'w-full',
            compact ? 'max-w-lg' : 'max-w-2xl',
        ]"
    >
        <form
            @submit.prevent="submitSearch"
            :class="[
                'flex flex-col gap-3 sm:flex-row',
                compact ? '' : 'rounded-2xl bg-white p-3 shadow-lg',
            ]"
        >
            <!-- Keyword input -->
            <div class="relative flex-1">
                <Search
                    class="absolute left-4 top-1/2 size-5 -translate-y-1/2 text-gray-400 rtl:left-auto rtl:right-4"
                />
                <input
                    v-model="query"
                    type="text"
                    :placeholder="compact ? t('search_properties') : t('search_by_keyword')"
                    class="w-full rounded-xl border border-gray-200 py-3.5 pl-11 pr-4 text-base text-[#1F1F1F] placeholder-gray-400 focus:border-[#FFC107] focus:outline-none focus:ring-2 focus:ring-[#FFC107]/30 rtl:pl-4 rtl:pr-11"
                />
            </div>

            <!-- City select -->
            <div v-if="cities.length > 0" class="relative sm:w-48">
                <MapPin
                    class="absolute left-4 top-1/2 size-5 -translate-y-1/2 text-gray-400 rtl:left-auto rtl:right-4"
                />
                <select
                    v-model="city"
                    class="w-full appearance-none rounded-xl border border-gray-200 py-3.5 pl-11 pr-10 text-base text-[#1F1F1F] focus:border-[#FFC107] focus:outline-none focus:ring-2 focus:ring-[#FFC107]/30 rtl:pl-10 rtl:pr-11"
                >
                    <option value="">{{ t('all_cities') }}</option>
                    <option
                        v-for="c in cities"
                        :key="c"
                        :value="c"
                    >
                        {{ c }}
                    </option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-400 rtl:left-0 rtl:right-auto">
                    <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                    </svg>
                </div>
            </div>

            <!-- Search button -->
            <button
                type="submit"
                class="flex min-h-[44px] items-center justify-center gap-2 rounded-xl bg-[#FFC107] px-6 py-3.5 text-base font-semibold text-[#1F1F1F] transition-colors hover:bg-yellow-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#FFC107] sm:w-auto"
            >
                <Search class="size-5" />
                <span class="hidden sm:inline">{{ t('search') }}</span>
            </button>
        </form>
    </div>
</template>
