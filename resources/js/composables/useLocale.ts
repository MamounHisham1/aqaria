import { watch, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';

export function useLocale() {
    const { locale } = useI18n();

    const setLocale = (newLocale: string) => {
        locale.value = newLocale;
        localStorage.setItem('locale', newLocale);
        updateDocumentAttributes(newLocale);
    };

    const updateDocumentAttributes = (lang: string) => {
        document.documentElement.lang = lang;
        document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr';
    };

    onMounted(() => {
        const savedLocale = localStorage.getItem('locale') || 'ar';
        locale.value = savedLocale;
        updateDocumentAttributes(savedLocale);
    });

    return {
        locale,
        setLocale,
    };
}
