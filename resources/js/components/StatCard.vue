<script setup lang="ts">
import type { LucideIcon } from 'lucide-vue-next';

type Props = {
    title: string;
    value: string | number;
    icon?: LucideIcon;
    description?: string;
    color?: 'yellow' | 'charcoal' | 'white';
};

withDefaults(defineProps<Props>(), {
    icon: undefined,
    description: undefined,
    color: 'white',
});
</script>

<template>
    <div
        :class="[
            'rounded-xl border p-6 transition-shadow hover:shadow-md',
            color === 'white'
                ? 'border-gray-200 bg-white'
                : color === 'yellow'
                  ? 'border-[#FFC107] bg-[#FFC107]/10'
                  : 'border-[#1F1F1F] bg-[#1F1F1F] text-white',
        ]"
    >
        <div class="flex items-start justify-between">
            <div>
                <p
                    :class="[
                        'mb-1 text-sm font-medium',
                        color === 'charcoal' ? 'text-gray-400' : 'text-gray-500',
                    ]"
                >
                    {{ title }}
                </p>
                <p class="text-3xl font-bold">{{ value }}</p>
                <p
                    v-if="description"
                    :class="[
                        'mt-1 text-sm',
                        color === 'charcoal' ? 'text-gray-400' : 'text-gray-500',
                    ]"
                >
                    {{ description }}
                </p>
            </div>
            <div
                v-if="icon"
                :class="[
                    'flex size-12 items-center justify-center rounded-xl',
                    color === 'charcoal'
                        ? 'bg-gray-700'
                        : 'bg-[#FFC107]/20',
                ]"
            >
                <component
                    :is="icon"
                    :class="[
                        'size-6',
                        color === 'charcoal' ? 'text-[#FFC107]' : 'text-[#1F1F1F]',
                    ]"
                />
            </div>
        </div>
    </div>
</template>
