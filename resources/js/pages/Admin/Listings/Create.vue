<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { ImagePlus, X } from 'lucide-vue-next';
import adminListings from '@/routes/admin/listings';

const { t } = useI18n();

const form = useForm({
    title: '',
    description: '',
    price: '',
    area_sqm: '',
    bedrooms: '0',
    bathrooms: '0',
    property_type: 'apartment',
    listing_type: 'sale',
    city: '',
    district: '',
    address: '',
    contact_phone: '',
    contact_whatsapp: '',
    is_featured: false,
    is_active: true,
    images: [] as (File | string)[],
    amenities: [] as string[],
});

const handleImageUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files) {
        form.images = [...form.images, ...Array.from(target.files)];
    }
    target.value = ''; // reset input
};

const removeImage = (index: number) => {
    form.images.splice(index, 1);
};

const getImageUrl = (image: File | string) => {
    if (image instanceof File) {
        return URL.createObjectURL(image);
    }
    return image;
};

function submit() {
    form.post(adminListings.store().url);
}
</script>

<template>
    <Head :title="t('create_listing')" />

    <div class="p-4 sm:p-6">
        <div class="mx-auto max-w-3xl">
            <h1 class="mb-4 text-xl font-bold text-[#1F1F1F] sm:mb-6 sm:text-2xl">{{ t('create_listing') }}</h1>

            <form @submit.prevent="submit" class="space-y-4 sm:space-y-6">
                <!-- Basic Information -->
                <div class="rounded-2xl border border-gray-200 bg-white p-4 sm:p-6">
                    <h2 class="mb-4 text-base font-bold text-[#1F1F1F] sm:text-lg">{{ t('basic_information') }}</h2>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-sm font-medium text-[#1F1F1F]">{{ t('title_label') }} *</label>
                            <input
                                v-model="form.title"
                                type="text"
                                required
                                class="w-full rounded-xl border border-gray-200 px-4 py-3 text-base text-[#1F1F1F] focus:border-[#FFC107] focus:outline-none focus:ring-2 focus:ring-[#FFC107]/30"
                            />
                            <p v-if="form.errors.title" class="mt-1 text-sm text-red-500">{{ form.errors.title }}</p>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-sm font-medium text-[#1F1F1F]">{{ t('description_label') }} *</label>
                            <textarea
                                v-model="form.description"
                                rows="4"
                                required
                                class="w-full rounded-xl border border-gray-200 px-4 py-3 text-base text-[#1F1F1F] focus:border-[#FFC107] focus:outline-none focus:ring-2 focus:ring-[#FFC107]/30"
                            ></textarea>
                            <p v-if="form.errors.description" class="mt-1 text-sm text-red-500">{{ form.errors.description }}</p>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-[#1F1F1F]">{{ t('price_egp_label') }} *</label>
                            <input
                                v-model="form.price"
                                type="number"
                                required
                                class="w-full rounded-xl border border-gray-200 px-4 py-3 text-base text-[#1F1F1F] focus:border-[#FFC107] focus:outline-none focus:ring-2 focus:ring-[#FFC107]/30"
                            />
                            <p v-if="form.errors.price" class="mt-1 text-sm text-red-500">{{ form.errors.price }}</p>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-[#1F1F1F]">{{ t('area_sqm_label') }} *</label>
                            <input
                                v-model="form.area_sqm"
                                type="number"
                                required
                                class="w-full rounded-xl border border-gray-200 px-4 py-3 text-base text-[#1F1F1F] focus:border-[#FFC107] focus:outline-none focus:ring-2 focus:ring-[#FFC107]/30"
                            />
                            <p v-if="form.errors.area_sqm" class="mt-1 text-sm text-red-500">{{ form.errors.area_sqm }}</p>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-[#1F1F1F]">{{ t('bedrooms_label') }}</label>
                            <input
                                v-model="form.bedrooms"
                                type="number"
                                min="0"
                                class="w-full rounded-xl border border-gray-200 px-4 py-3 text-base text-[#1F1F1F] focus:border-[#FFC107] focus:outline-none focus:ring-2 focus:ring-[#FFC107]/30"
                            />
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-[#1F1F1F]">{{ t('bathrooms_label') }}</label>
                            <input
                                v-model="form.bathrooms"
                                type="number"
                                min="0"
                                class="w-full rounded-xl border border-gray-200 px-4 py-3 text-base text-[#1F1F1F] focus:border-[#FFC107] focus:outline-none focus:ring-2 focus:ring-[#FFC107]/30"
                            />
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-[#1F1F1F]">{{ t('property_type_label') }} *</label>
                            <select
                                v-model="form.property_type"
                                class="w-full rounded-xl border border-gray-200 px-4 py-3 text-base text-[#1F1F1F] focus:border-[#FFC107] focus:outline-none focus:ring-2 focus:ring-[#FFC107]/30"
                            >
                                <option value="apartment">{{ t('apartment_option') }}</option>
                                <option value="villa">{{ t('villa_option') }}</option>
                                <option value="townhouse">{{ t('townhouse_option') }}</option>
                                <option value="commercial">{{ t('commercial_option') }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-[#1F1F1F]">{{ t('listing_type_label') }} *</label>
                            <select
                                v-model="form.listing_type"
                                class="w-full rounded-xl border border-gray-200 px-4 py-3 text-base text-[#1F1F1F] focus:border-[#FFC107] focus:outline-none focus:ring-2 focus:ring-[#FFC107]/30"
                            >
                                <option value="sale">{{ t('for_sale_option') }}</option>
                                <option value="rent">{{ t('for_rent_option') }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Location -->
                <div class="rounded-2xl border border-gray-200 bg-white p-4 sm:p-6">
                    <h2 class="mb-4 text-base font-bold text-[#1F1F1F] sm:text-lg">{{ t('location') }}</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-[#1F1F1F]">{{ t('city_label') }} *</label>
                            <input
                                v-model="form.city"
                                type="text"
                                required
                                class="w-full rounded-xl border border-gray-200 px-4 py-3 text-base text-[#1F1F1F] focus:border-[#FFC107] focus:outline-none focus:ring-2 focus:ring-[#FFC107]/30"
                            />
                            <p v-if="form.errors.city" class="mt-1 text-sm text-red-500">{{ form.errors.city }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-[#1F1F1F]">{{ t('district_label') }} *</label>
                            <input
                                v-model="form.district"
                                type="text"
                                required
                                class="w-full rounded-xl border border-gray-200 px-4 py-3 text-base text-[#1F1F1F] focus:border-[#FFC107] focus:outline-none focus:ring-2 focus:ring-[#FFC107]/30"
                            />
                            <p v-if="form.errors.district" class="mt-1 text-sm text-red-500">{{ form.errors.district }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-sm font-medium text-[#1F1F1F]">{{ t('address_label') }} *</label>
                            <input
                                v-model="form.address"
                                type="text"
                                required
                                class="w-full rounded-xl border border-gray-200 px-4 py-3 text-base text-[#1F1F1F] focus:border-[#FFC107] focus:outline-none focus:ring-2 focus:ring-[#FFC107]/30"
                            />
                            <p v-if="form.errors.address" class="mt-1 text-sm text-red-500">{{ form.errors.address }}</p>
                        </div>
                    </div>
                </div>

                <!-- Contact -->
                <div class="rounded-2xl border border-gray-200 bg-white p-4 sm:p-6">
                    <h2 class="mb-4 text-base font-bold text-[#1F1F1F] sm:text-lg">{{ t('contact_info') }}</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-[#1F1F1F]">{{ t('phone_label') }} *</label>
                            <input
                                v-model="form.contact_phone"
                                type="text"
                                required
                                class="w-full rounded-xl border border-gray-200 px-4 py-3 text-base text-[#1F1F1F] focus:border-[#FFC107] focus:outline-none focus:ring-2 focus:ring-[#FFC107]/30"
                            />
                            <p v-if="form.errors.contact_phone" class="mt-1 text-sm text-red-500">{{ form.errors.contact_phone }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-[#1F1F1F]">{{ t('whatsapp_label') }}</label>
                            <input
                                v-model="form.contact_whatsapp"
                                type="text"
                                class="w-full rounded-xl border border-gray-200 px-4 py-3 text-base text-[#1F1F1F] focus:border-[#FFC107] focus:outline-none focus:ring-2 focus:ring-[#FFC107]/30"
                            />
                        </div>
                    </div>
                </div>

                <!-- Images -->
                <div class="rounded-2xl border border-gray-200 bg-white p-4 sm:p-6">
                    <h2 class="mb-4 text-base font-bold text-[#1F1F1F] sm:text-lg">{{ t('images') }}</h2>
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
                        <div v-for="(image, index) in form.images" :key="index" class="relative aspect-[4/3] rounded-xl border border-gray-200 bg-gray-50 overflow-hidden group">
                            <img :src="getImageUrl(image)" class="w-full h-full object-cover" />
                            <button
                                type="button"
                                @click="removeImage(index)"
                                class="absolute top-2 end-2 bg-white/90 text-red-500 rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity hover:bg-white"
                            >
                                <X class="size-4" />
                            </button>
                        </div>
                        <label class="relative aspect-[4/3] flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 cursor-pointer hover:border-[#FFC107] hover:bg-yellow-50/50 transition-colors">
                            <input
                                type="file"
                                multiple
                                accept="image/*"
                                class="hidden"
                                @change="handleImageUpload"
                            />
                            <ImagePlus class="size-8 text-gray-400 mb-2" />
                            <span class="text-sm font-medium text-gray-500">{{ t('add_images') }}</span>
                        </label>
                    </div>
                </div>

                <!-- Settings -->
                <div class="rounded-2xl border border-gray-200 bg-white p-4 sm:p-6">
                    <h2 class="mb-4 text-base font-bold text-[#1F1F1F] sm:text-lg">{{ t('settings_section') }}</h2>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">
                        <label class="flex cursor-pointer items-center gap-2">
                            <input v-model="form.is_featured" type="checkbox" class="size-5 accent-[#FFC107]" />
                            <span class="text-base text-[#1F1F1F]">{{ t('featured_listing') }}</span>
                        </label>
                        <label class="flex cursor-pointer items-center gap-2">
                            <input v-model="form.is_active" type="checkbox" class="size-5 accent-[#FFC107]" />
                            <span class="text-base text-[#1F1F1F]">{{ t('active_status') }}</span>
                        </label>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full rounded-xl bg-[#FFC107] px-6 py-3.5 text-base font-semibold text-[#1F1F1F] hover:bg-yellow-500 disabled:opacity-50 sm:w-auto"
                    >
                        {{ t('create_listing_btn') }}
                    </button>
                    <Link
                        :href="adminListings.index().url"
                        class="w-full rounded-xl border border-gray-200 px-6 py-3.5 text-center text-base font-medium text-[#1F1F1F] hover:bg-gray-50 sm:w-auto"
                    >
                        {{ t('cancel') }}
                    </Link>
                </div>
            </form>
        </div>
    </div>
</template>
