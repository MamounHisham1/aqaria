<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import Heading from '@/components/Heading.vue';
import { edit } from '@/routes/appearance';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'settings_appearance_breadcrumbs',
                href: edit(),
            },
        ],
    },
});
</script>

<template>
    <Head :title="t('settings_appearance_title')" />

    <h1 class="sr-only">{{ t('settings_appearance_title') }}</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            :title="t('settings_appearance_title')"
            :description="t('settings_appearance_desc')"
        />
        <AppearanceTabs />
    </div>
</template>
