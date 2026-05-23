<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Home, Search, Building2 } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import AppLogo from '@/components/AppLogo.vue';
import LocaleSwitcher from '@/components/LocaleSwitcher.vue';
import { useLocale } from '@/composables/useLocale';
import { home } from '@/routes';
import { index as listings } from '@/routes/listings/index';

const { t } = useI18n();
useLocale();
</script>

<template>
    <div class="flex min-h-screen flex-col bg-white">
        <!-- Navbar -->
        <header class="sticky top-0 z-50 border-b border-gray-200 bg-white">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4">
                <!-- Logo -->
                <Link :href="home()" class="flex items-center gap-x-2">
                    <AppLogo />
                </Link>

                <!-- Desktop Nav -->
                <nav class="hidden items-center gap-6 md:flex">
                    <Link
                        :href="home()"
                        class="flex items-center gap-1.5 text-base font-medium text-[#1F1F1F] transition-colors hover:text-[#FFC107]"
                        :class="{ 'text-[#FFC107]': $page.url === '/' }"
                    >
                        <Home class="size-5" />
                        {{ t('home') }}
                    </Link>
                    <Link
                        :href="listings()"
                        class="flex items-center gap-1.5 text-base font-medium text-[#1F1F1F] transition-colors hover:text-[#FFC107]"
                        :class="{ 'text-[#FFC107]': $page.url.startsWith('/listings') }"
                    >
                        <Building2 class="size-5" />
                        {{ t('browse_listings') }}
                    </Link>
                    <LocaleSwitcher />
                </nav>

                <!-- Mobile Nav -->
                <nav class="flex items-center gap-4 md:hidden">
                    <LocaleSwitcher />
                    <Link
                        :href="home()"
                        class="flex h-11 w-11 items-center justify-center rounded-lg text-[#1F1F1F] hover:bg-gray-100"
                        :aria-label="t('home')"
                    >
                        <Home class="size-6" />
                    </Link>
                    <Link
                        :href="listings()"
                        class="flex h-11 w-11 items-center justify-center rounded-lg bg-[#FFC107] text-[#1F1F1F] hover:bg-yellow-500"
                        :aria-label="t('browse_listings')"
                    >
                        <Search class="size-6" />
                    </Link>
                </nav>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="border-t border-gray-200 bg-[#1F1F1F] text-white">
            <div class="mx-auto max-w-7xl px-4 py-12">
                <div class="grid gap-8 md:grid-cols-3">
                    <!-- Brand -->
                    <div>
                        <div class="mb-3 flex items-center gap-x-2">
                            <div class="flex aspect-square size-9 items-center justify-center rounded-lg bg-[#FFC107]">
                                <span class="text-lg font-bold text-[#1F1F1F]">A</span>
                            </div>
                            <span class="text-xl font-bold">Aqaria</span>
                        </div>
                        <p class="text-base leading-relaxed text-gray-300">
                            {{ t('brand_tagline') }}
                        </p>
                    </div>

                    <!-- Quick Links -->
                    <div>
                        <h3 class="mb-4 text-lg font-semibold">{{ t('quick_links') }}</h3>
                        <ul class="space-y-2">
                            <li>
                                <Link :href="home()" class="text-base text-gray-300 hover:text-[#FFC107]">
                                    {{ t('home') }}
                                </Link>
                            </li>
                            <li>
                                <Link :href="listings()" class="text-base text-gray-300 hover:text-[#FFC107]">
                                    {{ t('browse_listings') }}
                                </Link>
                            </li>
                            <li>
                                <Link :href="listings({ listing_type: 'sale' })" class="text-base text-gray-300 hover:text-[#FFC107]">
                                    {{ t('for_sale') }}
                                </Link>
                            </li>
                            <li>
                                <Link :href="listings({ listing_type: 'rent' })" class="text-base text-gray-300 hover:text-[#FFC107]">
                                    {{ t('for_rent') }}
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <!-- Contact -->
                    <div>
                        <h3 class="mb-4 text-lg font-semibold">{{ t('contact') }}</h3>
                        <ul class="space-y-2 text-base text-gray-300">
                            <li>{{ t('email') }}: info@aqaria.com</li>
                            <li>{{ t('phone') }}: +20 100 000 0000</li>
                            <li>{{ t('available_7_days') }}</li>
                        </ul>
                    </div>
                </div>

                <div class="mt-8 border-t border-gray-700 pt-8 text-center text-base text-gray-400">
                    &copy; {{ new Date().getFullYear() }} Aqaria. {{ t('all_rights_reserved') }}
                </div>
            </div>
        </footer>
    </div>
</template>
