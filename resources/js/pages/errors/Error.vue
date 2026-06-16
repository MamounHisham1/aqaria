<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{ status: number }>();

const title = computed(() => {
    return {
        403: '403 — Forbidden',
        404: '404 — Not Found',
        500: '500 — Server Error',
    }[props.status] ?? `${props.status} — Error`;
});

const message = computed(() => {
    return {
        403: 'You do not have permission to access this page.',
        404: 'The page you are looking for could not be found.',
        500: 'Something went wrong on our end. Please try again later.',
    }[props.status] ?? 'An unexpected error occurred.';
});
</script>

<template>
    <Head :title="title" />

    <div class="flex min-h-screen items-center justify-center bg-gray-50 px-4 dark:bg-gray-900">
        <div class="text-center">
            <h1 class="text-6xl font-bold text-gray-300 dark:text-gray-700">{{ status }}</h1>
            <p class="mt-4 text-xl text-gray-600 dark:text-gray-400">{{ message }}</p>
            <Link
                href="/"
                class="mt-6 inline-block rounded-lg bg-blue-600 px-6 py-3 text-white transition hover:bg-blue-700"
            >
                Go Home
            </Link>
        </div>
    </div>
</template>
