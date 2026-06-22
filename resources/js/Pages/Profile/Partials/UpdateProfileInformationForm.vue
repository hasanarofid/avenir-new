<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
    photo: null,
});

const photoPreview = ref(null);

const updatePhotoPreview = (e) => {
    const file = e.target.files[0];
    if (!file) return;

    form.photo = file;
    const reader = new FileReader();
    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Profile Information
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Update your account's profile information and email address.
            </p>
        </header>

        <form
            @submit.prevent="form.post(route('profile.update', { _method: 'patch' }))"
            class="mt-6 space-y-6"
        >
            <!-- Profile Photo -->
            <div class="col-span-1 md:col-span-2 flex flex-col items-start gap-4">
                <InputLabel for="photo" value="Profile Photo" />
                
                <div class="flex items-center gap-4">
                    <!-- Current Profile Photo -->
                    <div v-show="!photoPreview" class="mt-2 relative h-20 w-20 rounded-full overflow-hidden border-2 border-emerald-500/30">
                        <img :src="user.profile_photo_url" :alt="user.name" class="rounded-full h-full w-full object-cover">
                    </div>

                    <!-- New Profile Photo Preview -->
                    <div v-show="photoPreview" class="mt-2 relative h-20 w-20 rounded-full overflow-hidden border-2 border-emerald-500/30">
                        <span class="block rounded-full w-full h-full bg-cover bg-no-repeat bg-center"
                              :style="'background-image: url(\'' + photoPreview + '\');'">
                        </span>
                    </div>

                    <div>
                        <input
                            ref="photoInput"
                            type="file"
                            class="hidden"
                            accept="image/*"
                            @change="updatePhotoPreview"
                        >
                        
                        <button
                            type="button"
                            class="inline-flex items-center px-4 py-2 bg-emerald-600/10 border border-emerald-500 text-emerald-400 rounded-md font-semibold text-xs tracking-widest shadow-sm hover:bg-emerald-600/20 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                            @click="$refs.photoInput.click()"
                        >
                            Select A New Photo
                        </button>
                        
                        <InputError class="mt-2" :message="form.errors.photo" />
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <InputLabel for="name" value="Name" />

                    <TextInput
                        id="name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.name"
                        required
                        autofocus
                        autocomplete="name"
                    />

                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div>
                    <InputLabel for="email" value="Email" />

                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full opacity-60 cursor-not-allowed"
                        v-model="form.email"
                        required
                        autocomplete="username"
                        disabled
                    />

                    <InputError class="mt-2" :message="form.errors.email" />
                </div>
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-gray-800 dark:text-gray-200">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:text-gray-400 dark:hover:text-gray-100 dark:focus:ring-offset-gray-800"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-600 dark:text-green-400"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600 dark:text-gray-400"
                    >
                        Saved.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
