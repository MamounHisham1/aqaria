<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { logout } from '@/routes';
import { send } from '@/routes/verification';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineOptions({
    layout: {
        title: 'auth_verify_title',
        description: 'auth_verify_desc',
    },
});

defineProps<{
    status?: string;
}>();
</script>

<template>
    <Head :title="t('email_verification')" />

    <div
        v-if="status === 'verification-link-sent'"
        class="mb-4 text-center text-sm font-medium text-green-600"
    >
        {{ t('verification_link_sent') }}
    </div>

    <Form
        v-bind="send.form()"
        class="space-y-6 text-center"
        v-slot="{ processing }"
    >
        <Button :disabled="processing" variant="secondary">
            <Spinner v-if="processing" />
            {{ t('resend_verification_email') }}
        </Button>

        <TextLink :href="logout()" as="button" class="mx-auto block text-sm">
            {{ t('log_out') }}
        </TextLink>
    </Form>
</template>
