<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

type Schema = Record<string, unknown>;

type Props = {
    title: string;
    description?: string;
    image?: string | null;
    url?: string;
    schema?: Schema;
    type?: string;
};

const props = withDefaults(defineProps<Props>(), {
    type: 'website',
    description: '',
    image: null,
    url: '',
    schema: () => ({}),
});
</script>

<template>
    <Head>
        <title>{{ title }}</title>
        <meta v-if="description" name="description" :content="description" />

        <!-- Open Graph -->
        <meta property="og:type" :content="type" />
        <meta property="og:title" :content="title" />
        <meta v-if="description" property="og:description" :content="description" />
        <meta v-if="url" property="og:url" :content="url" />
        <meta v-if="image" property="og:image" :content="image" />

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" :content="title" />
        <meta v-if="description" name="twitter:description" :content="description" />
        <meta v-if="image" name="twitter:image" :content="image" />

        <!-- Canonical -->
        <link v-if="url" rel="canonical" :href="url" />

        <!-- Schema.org JSON-LD -->
        <script v-if="schema && Object.keys(schema).length > 0" type="application/ld+json" v-html="JSON.stringify(schema)" />
    </Head>
</template>
