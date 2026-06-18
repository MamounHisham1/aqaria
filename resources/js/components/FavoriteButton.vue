<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Heart } from 'lucide-vue-next';
import { ref } from 'vue';
import { toggle as favoriteToggle } from '@/routes/listings/favorite';

type Props = {
    listingId: number;
    initialFavorited?: boolean;
};

const props = defineProps<Props>();

const isFavorited = ref(props.initialFavorited ?? false);

function toggle() {
    router.post(
        favoriteToggle({ listing: props.listingId }).url,
        {},
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: (page) => {
                // The controller returns JSON with is_favorited, but via Inertia
                // we read it from the flash/response props when available.
                const json = (page.props as Record<string, unknown>);
                // Optimistic flip; server already persisted the toggle.
                isFavorited.value = !isFavorited.value;
                if (json && typeof json.is_favorited === 'boolean') {
                    isFavorited.value = json.is_favorited;
                }
            },
        },
    );
}
</script>

<template>
    <button
        type="button"
        @click.prevent="toggle"
        class="flex size-10 items-center justify-center rounded-full bg-white/90 shadow transition-colors hover:bg-white"
        :title="isFavorited ? 'Remove from favorites' : 'Save to favorites'"
    >
        <Heart
            class="size-5 transition-colors"
            :class="isFavorited ? 'fill-red-500 text-red-500' : 'text-gray-400'"
        />
    </button>
</template>
