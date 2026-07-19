<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div class="mb-8">
            <h2 class="text-2xl font-bold text-white mb-2">Welcome back</h2>
            <p class="text-sm text-slate-400">Sign in to your account to continue.</p>
        </div>

        <div v-if="status" class="mb-4 text-sm font-medium text-emerald-500 bg-emerald-500/10 border border-emerald-500/20 rounded-lg p-3">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <InputLabel for="email" value="Email Address" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1.5 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="name@company.com"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <div class="flex items-center justify-between mt-1.5">
                    <InputLabel for="password" value="Password" />
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-xs font-semibold text-emerald-500 hover:text-emerald-400 transition-colors"
                    >
                        Forgot password?
                    </Link>
                </div>

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1.5 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="block">
                <label class="flex items-center group cursor-pointer w-fit">
                    <Checkbox name="remember" v-model:checked="form.remember" class="border-slate-600 bg-[#090b0a] text-emerald-500 focus:ring-emerald-500/30 rounded" />
                    <span class="ms-2 text-sm font-medium text-slate-400 group-hover:text-slate-300 transition-colors"
                        >Remember me</span
                    >
                </label>
            </div>

            <div class="pt-2">
                <PrimaryButton
                    class="w-full py-3 text-sm"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Sign In
                </PrimaryButton>
            </div>

            <div class="relative flex items-center py-2">
                <div class="flex-grow border-t border-emerald-950/40"></div>
                <span class="flex-shrink-0 mx-4 text-xs font-semibold text-slate-500 uppercase tracking-widest">Or continue with</span>
                <div class="flex-grow border-t border-emerald-950/40"></div>
            </div>

            <div>
                <a
                    href="/auth/google"
                    class="flex items-center justify-center w-full px-4 py-2.5 text-sm font-semibold text-slate-200 bg-[#121614] border border-emerald-950/60 rounded-xl hover:bg-[#1a201d] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-[#090b0a] focus:ring-emerald-500/50 transition-all duration-200 shadow-sm group"
                >
                    <svg class="w-5 h-5 mr-3 transition-transform group-hover:scale-110" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    Google
                </a>
            </div>
            
            <p class="text-center text-sm text-slate-400 mt-6">
                Don't have an account? 
                <Link :href="route('register')" class="font-semibold text-emerald-500 hover:text-emerald-400 transition-colors">
                    Sign up now
                </Link>
            </p>
        </form>
    </GuestLayout>
</template>
