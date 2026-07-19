<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    code: '',
});

const useRecovery = ref(false);

const submit = () => {
    form.post(route('2fa.confirm-challenge'), {
        onFinish: () => form.reset('code'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Verifikasi 2-Langkah" />

        <div class="mb-8">
            <Link :href="route('login')" class="inline-flex items-center text-xs font-semibold text-slate-400 hover:text-emerald-400 transition-colors mb-6 group">
                <svg class="w-4 h-4 mr-1.5 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali
            </Link>

            <h2 class="text-2xl font-bold text-white mb-2">Verifikasi Identitas</h2>
            <p v-if="!useRecovery" class="text-sm text-slate-400">Masukkan 6 digit kode dari aplikasi Google Authenticator Anda.</p>
            <p v-else class="text-sm text-slate-400">Masukkan salah satu Recovery Code yang Anda simpan saat registrasi.</p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <InputLabel :for="'code'" :value="useRecovery ? 'Recovery Code' : 'Kode Autentikator (6 Digit)'" />

                <TextInput
                    id="code"
                    type="text"
                    :inputmode="useRecovery ? 'text' : 'numeric'"
                    class="mt-1.5 block w-full tracking-widest font-mono text-center"
                    :class="useRecovery ? 'text-sm' : 'text-xl'"
                    v-model="form.code"
                    required
                    autofocus
                    :placeholder="useRecovery ? 'xxxxxxxx-xxxxxxxx' : '123456'"
                    :maxlength="useRecovery ? '25' : '6'"
                />

                <InputError class="mt-2 text-center" :message="form.errors.code" />
            </div>

            <div class="pt-2">
                <PrimaryButton
                    class="w-full py-3 text-sm"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Verifikasi
                </PrimaryButton>
            </div>
            
            <div class="text-center mt-6">
                <button type="button" @click="useRecovery = !useRecovery; form.code = ''" class="text-sm text-emerald-500 hover:text-emerald-400 font-medium transition-colors">
                    {{ useRecovery ? 'Gunakan Google Authenticator' : 'Kehilangan HP? Gunakan Recovery Code' }}
                </button>
            </div>
        </form>
    </GuestLayout>
</template>
