<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

const { t } = useI18n();
</script>

<template>
    <div
        class="flex aspect-square size-9 items-center justify-center rounded-lg bg-[#FFC107]"
    >
        <AppLogoIcon class="size-5 fill-current text-[#1F1F1F]" />
    </div>
    <div class="ml-2 grid flex-1 text-left rtl:ml-0 rtl:mr-2 rtl:text-right">
        <span class="text-lg leading-tight font-bold text-[#1F1F1F]"
            >Aqaria</span
        >
        <span class="text-xs leading-tight font-medium text-gray-500"
            >{{ t('tagline') }}</span
        >
    </div>
</template>
