<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Aktivasi Akun Email" />

        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 mb-4 shadow-lg shadow-emerald-500/5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-white tracking-tight">Verifikasi Email Anda</h2>
            <p class="mt-2 text-sm text-slate-400">
                Terima kasih telah mendaftar di Research Avenir. Harap lakukan aktivasi akun sebelum melanjutkan.
            </p>
        </div>

        <div class="mb-5 p-4 rounded-xl bg-slate-900/60 border border-slate-800 text-sm text-slate-300 leading-relaxed">
            Kami telah mengirimkan link aktivasi ke email Anda. Silakan periksa folder inbox atau spam email Anda dan klik link tersebut untuk mengaktifkan akun.
        </div>

        <div
            v-if="verificationLinkSent"
            class="mb-5 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-sm font-medium text-emerald-400 flex items-center gap-2"
        >
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span>Link aktivasi baru telah berhasil dikirimkan ke alamat email Anda.</span>
        </div>

        <form @submit.prevent="submit" class="mt-6">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <PrimaryButton
                    class="w-full sm:w-auto justify-center !py-3 !px-6 !bg-gradient-to-r !from-emerald-500 !to-teal-600 hover:!from-emerald-600 hover:!to-teal-700 !text-white !font-semibold !rounded-xl !shadow-lg !shadow-emerald-500/20"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Kirim Ulang Email Aktivasi
                </PrimaryButton>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="text-sm font-medium text-slate-400 hover:text-white transition-colors"
                >
                    Keluar / Logout
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
