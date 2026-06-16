<script setup lang="ts">
import { Languages } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useLocale } from '@/composables/useLocale';

const { locale, setLocale } = useLocale();

const languages = [
    { code: 'ar', name: 'العربية' },
    { code: 'en', name: 'English' },
];
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger :as-child="true">
            <Button variant="ghost" size="icon" class="h-9 w-9">
                <Languages class="h-5 w-5" />
                <span class="sr-only">{{ $t('switch_language') }}</span>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
            <DropdownMenuItem
                v-for="lang in languages"
                :key="lang.code"
                @click="setLocale(lang.code)"
                :class="{ 'bg-accent': locale === lang.code }"
            >
                {{ lang.name }}
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
