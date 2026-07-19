<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    qrCodeSvg: String,
    secret: String,
    recoveryCodes: Array,
});

const form = useForm({
    one_time_password: '',
});

const copied = ref(false);

const submit = () => {
    form.post(route('2fa.confirm-setup'), {
        onFinish: () => form.reset('one_time_password'),
    });
};

const copyRecoveryCodes = () => {
    const text = props.recoveryCodes.join('\n');
    navigator.clipboard.writeText(text).then(() => {
        copied.value = true;
        setTimeout(() => copied.value = false, 3000);
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Setup Two-Factor Authentication" />

        <div class="mb-6">
            <h2 class="text-2xl font-bold text-white mb-2">Amankan Akun Anda</h2>
            <p class="text-sm text-slate-400">Scan QR Code di bawah ini menggunakan aplikasi Google Authenticator atau aplikasi TOTP lainnya.</p>
        </div>

        <div class="flex justify-center mb-6">
            <div class="bg-white p-4 rounded-xl inline-block" v-html="qrCodeSvg"></div>
        </div>

        <div class="text-center mb-6 text-sm text-slate-400">
            Atau masukkan kode rahasia secara manual:<br>
            <code class="text-emerald-400 font-mono text-base tracking-widest bg-emerald-950/30 px-2 py-1 rounded mt-2 inline-block">{{ secret }}</code>
        </div>

        <div class="bg-[#121614] border border-emerald-950/40 rounded-xl p-5 mb-8">
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-white font-semibold">Recovery Codes (Simpan dengan Aman!)</h3>
                <button @click="copyRecoveryCodes" class="text-xs text-emerald-500 hover:text-emerald-400 font-medium transition-colors">
                    {{ copied ? 'Berhasil disalin!' : 'Salin Semua' }}
                </button>
            </div>
            <p class="text-xs text-slate-500 mb-4">Gunakan salah satu kode ini jika Anda kehilangan akses ke aplikasi Authenticator Anda. Masing-masing kode hanya bisa dipakai satu kali.</p>
            <div class="grid grid-cols-2 gap-3 text-sm font-mono text-slate-300">
                <div v-for="code in recoveryCodes" :key="code" class="bg-black/20 p-2 rounded border border-white/5 text-center tracking-wider">
                    {{ code }}
                </div>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <InputLabel for="one_time_password" value="Masukkan Kode 6 Digit" />

                <TextInput
                    id="one_time_password"
                    type="text"
                    inputmode="numeric"
                    class="mt-1.5 block w-full text-center tracking-widest text-lg font-mono"
                    v-model="form.one_time_password"
                    required
                    autofocus
                    placeholder="123456"
                    maxlength="6"
                />

                <InputError class="mt-2 text-center" :message="form.errors.one_time_password" />
            </div>

            <div class="pt-2">
                <PrimaryButton
                    class="w-full py-3 text-sm"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Verifikasi dan Selesaikan Registrasi
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
