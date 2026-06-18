<script setup lang="ts">
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ShieldCheck, ShieldAlert, Calendar, Settings, User, Building2, Palette, Heart, Search, Trash2, Bell } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import ListingCard from '@/components/ListingCard.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { dashboard } from '@/routes/index';
import { index as listingsIndex } from '@/routes/listings';
import type { Auth } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

const { t } = useI18n();
const page = usePage();
const user = computed(() => page.props.auth.user);

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

type SavedSearch = {
    id: number;
    name: string;
    filters: Record<string, string | number | null>;
    notify: boolean;
    created_at: string;
};

type Props = {
    favorites?: Listing[];
    savedSearches?: SavedSearch[];
    favoritesCount?: number;
};

const props = defineProps<Props>();

const resendVerification = () => {
    router.post('/email/verification-notification');
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const filtersToQuery = (filters: Record<string, string | number | null>) => {
    const q: Record<string, string | number> = {};

    for (const [k, v] of Object.entries(filters)) {
        if (v !== null && v !== '') {
q[k] = v;
}
    }

    return q;
};

const destroySavedSearch = (id: number) => {
    if (confirm(t('confirm') + '?')) {
        router.delete(`/saved-searches/${id}`);
    }
};
</script>

<template>
    <Head :title="t('customer_dashboard')" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-8 max-w-7xl mx-auto w-full">
        <!-- Welcome Section -->
        <div class="flex flex-col gap-2">
            <h1 class="text-3xl font-bold tracking-tight text-[#1F1F1F] dark:text-white">
                {{ t('welcome_back') }}, {{ user?.name }}!
            </h1>
            <p class="text-muted-foreground">
                {{ t('account_overview') }}
            </p>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <!-- Account Status Card -->
            <Card class="border-sidebar-border shadow-sm">
                <CardHeader>
                    <CardTitle>{{ t('account_overview') }}</CardTitle>
                    <CardDescription>{{ t('manage_account_status') }}</CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-muted-foreground">{{ t('email_status') }}</span>
                        <div class="flex items-center gap-2">
                            <Badge v-if="user?.email_verified_at" variant="default" class="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 hover:bg-green-100">
                                <ShieldCheck class="w-3 h-3 mr-1" />
                                {{ t('email_verified') }}
                            </Badge>
                            <div v-else class="flex items-center gap-2">
                                <Badge variant="destructive" class="bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 hover:bg-red-100">
                                    <ShieldAlert class="w-3 h-3 mr-1" />
                                    {{ t('email_not_verified') }}
                                </Badge>
                                <Button variant="outline" size="sm" @click="resendVerification">
                                    {{ t('resend_verification') }}
                                </Button>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-muted-foreground">{{ t('member_since') }}</span>
                        <div class="flex items-center text-sm">
                            <Calendar class="w-4 h-4 mr-2 text-muted-foreground" />
                            {{ user?.created_at ? formatDate(user.created_at) : '' }}
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Quick Actions Card -->
            <Card class="border-sidebar-border shadow-sm">
                <CardHeader>
                    <CardTitle>{{ t('quick_actions') }}</CardTitle>
                    <CardDescription>{{ t('access_settings') }}</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-2 gap-4">
                        <Button as-child variant="outline" class="h-20 flex flex-col items-center justify-center gap-2">
                            <Link href="/settings/profile">
                                <User class="w-5 h-5 text-[#FFC107]" />
                                <span>{{ t('edit_profile') }}</span>
                            </Link>
                        </Button>

                        <Button as-child variant="outline" class="h-20 flex flex-col items-center justify-center gap-2">
                            <Link href="/settings/security">
                                <Settings class="w-5 h-5 text-[#1F1F1F] dark:text-white" />
                                <span>{{ t('security_settings') }}</span>
                            </Link>
                        </Button>

                        <Button as-child variant="outline" class="h-20 flex flex-col items-center justify-center gap-2">
                            <Link href="/settings/appearance">
                                <Palette class="w-5 h-5 text-[#1F1F1F] dark:text-white" />
                                <span>{{ t('appearance') }}</span>
                            </Link>
                        </Button>

                        <Button as-child variant="outline" class="h-20 flex flex-col items-center justify-center gap-2">
                            <Link :href="listingsIndex()">
                                <Building2 class="w-5 h-5 text-[#FFC107]" />
                                <span>{{ t('browse_properties') }}</span>
                            </Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Favorites -->
        <Card class="border-sidebar-border shadow-sm">
            <CardHeader class="flex flex-row items-center justify-between">
                <div>
                    <CardTitle class="flex items-center gap-2">
                        <Heart class="size-5 text-red-500" />
                        {{ t('favorites') }}
                    </CardTitle>
                    <CardDescription>{{ favoritesCount ?? 0 }} {{ t('saved_properties').toLowerCase() }}</CardDescription>
                </div>
                <Button as-child variant="ghost" size="sm">
                    <Link :href="listingsIndex()">{{ t('browse_properties') }}</Link>
                </Button>
            </CardHeader>
            <CardContent>
                <div v-if="favorites && favorites.length > 0" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <ListingCard
                        v-for="listing in favorites"
                        :key="listing.id"
                        :listing="listing"
                    />
                </div>
                <div v-else class="flex flex-col items-center justify-center py-12 text-center">
                    <Heart class="mb-3 size-10 text-gray-300" />
                    <p class="text-muted-foreground">{{ t('no_favorites_yet') }}</p>
                </div>
            </CardContent>
        </Card>

        <!-- Saved Searches -->
        <Card class="border-sidebar-border shadow-sm">
            <CardHeader>
                <CardTitle class="flex items-center gap-2">
                    <Search class="size-5 text-[#FFC107]" />
                    {{ t('saved_searches') }}
                </CardTitle>
                <CardDescription>{{ t('alert_email_desc') }}</CardDescription>
            </CardHeader>
            <CardContent>
                <div v-if="savedSearches && savedSearches.length > 0" class="space-y-3">
                    <div
                        v-for="search in savedSearches"
                        :key="search.id"
                        class="flex items-center justify-between rounded-xl border border-gray-200 p-4"
                    >
                        <div class="min-w-0 flex-1">
                            <Link
                                :href="listingsIndex(filtersToQuery(search.filters))"
                                class="block font-semibold text-[#1F1F1F] hover:text-[#FFC107]"
                            >
                                {{ search.name }}
                            </Link>
                            <p class="mt-0.5 truncate text-xs text-gray-500">
                                {{ formatDate(search.created_at) }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <Badge v-if="search.notify" variant="secondary" class="bg-amber-100 text-amber-800">
                                <Bell class="mr-1 size-3" />
                                {{ t('confirm') }}
                            </Badge>
                            <button
                                class="rounded-lg p-2 text-gray-400 transition-colors hover:bg-red-50 hover:text-red-500"
                                :title="t('remove_favorite')"
                                @click="destroySavedSearch(search.id)"
                            >
                                <Trash2 class="size-4" />
                            </button>
                        </div>
                    </div>
                </div>
                <div v-else class="flex flex-col items-center justify-center py-12 text-center">
                    <Search class="mb-3 size-10 text-gray-300" />
                    <p class="text-muted-foreground">{{ t('no_saved_searches_yet') }}</p>
                    <Button as-child variant="outline" size="sm" class="mt-4">
                        <Link :href="listingsIndex()">{{ t('save_search') }}</Link>
                    </Button>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
