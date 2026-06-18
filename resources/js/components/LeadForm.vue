<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Loader2, Send } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { store } from '@/routes/listings/leads';

type Props = {
    listingId: number;
};

const props = defineProps<Props>();
const { t } = useI18n();

const form = useForm({
    name: '',
    phone: '',
    email: '',
    message: '',
});

function submit() {
    form.post(store({ listing: props.listingId }).url, {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}
</script>

<template>
    <div class="rounded-2xl border border-gray-200 bg-white p-6">
        <h3 class="mb-1 text-lg font-bold text-[#1F1F1F]">
            {{ t('request_callback') }}
        </h3>
        <p class="mb-4 text-sm text-gray-500">
            {{ t('lead_form_desc') }}
        </p>

        <form class="space-y-3" @submit.prevent="submit">
            <div>
                <input
                    v-model="form.name"
                    type="text"
                    :placeholder="t('name')"
                    required
                    class="w-full rounded-xl border border-gray-200 px-4 py-3 text-base text-[#1F1F1F] focus:border-[#FFC107] focus:outline-none focus:ring-2 focus:ring-[#FFC107]/30"
                />
                <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">
                    {{ form.errors.name }}
                </p>
            </div>

            <div>
                <input
                    v-model="form.phone"
                    type="tel"
                    :placeholder="t('phone')"
                    required
                    class="w-full rounded-xl border border-gray-200 px-4 py-3 text-base text-[#1F1F1F] focus:border-[#FFC107] focus:outline-none focus:ring-2 focus:ring-[#FFC107]/30"
                />
                <p v-if="form.errors.phone" class="mt-1 text-sm text-red-500">
                    {{ form.errors.phone }}
                </p>
            </div>

            <div>
                <input
                    v-model="form.email"
                    type="email"
                    :placeholder="t('email')"
                    class="w-full rounded-xl border border-gray-200 px-4 py-3 text-base text-[#1F1F1F] focus:border-[#FFC107] focus:outline-none focus:ring-2 focus:ring-[#FFC107]/30"
                />
                <p v-if="form.errors.email" class="mt-1 text-sm text-red-500">
                    {{ form.errors.email }}
                </p>
            </div>

            <div>
                <textarea
                    v-model="form.message"
                    rows="3"
                    :placeholder="t('message_optional')"
                    class="w-full rounded-xl border border-gray-200 px-4 py-3 text-base text-[#1F1F1F] focus:border-[#FFC107] focus:outline-none focus:ring-2 focus:ring-[#FFC107]/30"
                />
                <p v-if="form.errors.message" class="mt-1 text-sm text-red-500">
                    {{ form.errors.message }}
                </p>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#FFC107] px-4 py-3 text-base font-semibold text-[#1F1F1F] transition-colors hover:bg-yellow-500 disabled:opacity-60"
            >
                <Loader2 v-if="form.processing" class="size-5 animate-spin" />
                <Send v-else class="size-5" />
                {{ t('send_inquiry') }}
            </button>
        </form>
    </div>
</template>
