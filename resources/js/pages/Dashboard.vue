<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ShieldCheck, ShieldAlert, Calendar, Settings, User, Building2, Palette } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { dashboard } from '@/routes/index';
import { index as listingsIndex } from '@/routes/listings';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
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
    </div>
</template>
