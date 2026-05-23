<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <!-- Aqaria Logo: Yellow Pin with House -->
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 40 48"
        :class="className"
        v-bind="$attrs"
    >
        <!-- Pin shape -->
        <path
            fill="currentColor"
            d="M20 0C12.82 0 7 5.82 7 13c0 7.18 13 23 13 23s13-15.82 13-23C33 5.82 27.18 0 20 0z"
        />
        <!-- House icon inside pin -->
        <path
            fill="#1F1F1F"
            d="M13 22v-7.5l7-5 7 5V22h-4.5v-4.5h-5V22H13z"
        />
    </svg>
</template>
