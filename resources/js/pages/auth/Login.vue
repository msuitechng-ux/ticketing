<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Checkbox from 'primevue/checkbox';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';
import Password from 'primevue/password';

const props = defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <div class="flex min-h-screen">
        <Head title="Sign In" />

        <!-- Left Side - Branding -->
        <div
            class="hidden w-1/2 bg-gradient-to-br from-blue-600 via-purple-600 to-indigo-700 lg:flex lg:flex-col lg:justify-between lg:p-12"
        >
            <div>
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-lg bg-white/20 backdrop-blur"
                    >
                        <i class="pi pi-ticket text-2xl text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-white">
                            Graduation Ticketing
                        </h1>
                        <p class="text-sm text-blue-100">
                            Northumbria University London
                        </p>
                    </div>
                </div>
            </div>

            <div class="space-y-8">
                <div>
                    <h2 class="text-4xl font-bold text-white">
                        Welcome to Your
                        <br />
                        Graduation Journey
                    </h2>
                    <p class="mt-4 text-lg text-blue-100">
                        Access your tickets, manage requests, and prepare for
                        your special day
                    </p>
                </div>

                <div class="space-y-6">
                    <div class="flex items-start gap-4">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/20"
                        >
                            <i
                                class="pi pi-shield-check text-xl text-white"
                            ></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-white">
                                Secure & Encrypted
                            </h3>
                            <p class="text-sm text-blue-100">
                                Your tickets are protected with SHA-256
                                encryption
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/20"
                        >
                            <i class="pi pi-qrcode text-xl text-white"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-white">
                                Digital QR Codes
                            </h3>
                            <p class="text-sm text-blue-100">
                                Download and share your tickets instantly
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/20"
                        >
                            <i class="pi pi-users text-xl text-white"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-white">
                                Fair Distribution
                            </h3>
                            <p class="text-sm text-blue-100">
                                Transparent ticket allocation and request system
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-sm text-blue-100">
                <p>Module: LD7206 | Ceremony: July 7th, 2025</p>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div
            class="flex w-full flex-col justify-center bg-white px-6 py-12 lg:w-1/2 lg:px-20 dark:bg-gray-900"
        >
            <div class="mx-auto w-full max-w-md">
                <!-- Mobile Logo -->
                <div
                    class="mb-8 flex items-center justify-center gap-3 lg:hidden"
                >
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-lg bg-gradient-to-br from-blue-600 to-purple-600"
                    >
                        <i class="pi pi-ticket text-2xl text-white"></i>
                    </div>
                    <div>
                        <h1
                            class="text-xl font-bold text-gray-900 dark:text-white"
                        >
                            Graduation Ticketing
                        </h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Northumbria University
                        </p>
                    </div>
                </div>

                <!-- Header -->
                <div class="mb-8">
                    <h2
                        class="text-3xl font-bold text-gray-900 dark:text-white"
                    >
                        Sign in to your account
                    </h2>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        Access your graduation tickets and manage your
                        attendance
                    </p>
                </div>

                <!-- Status Message -->
                <Message
                    v-if="status"
                    severity="success"
                    :closable="false"
                    class="mb-6"
                >
                    {{ status }}
                </Message>

                <!-- Login Form -->
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Email -->
                    <div class="space-y-2">
                        <label
                            for="email"
                            class="block text-sm font-medium text-gray-900 dark:text-white"
                        >
                            Email Address
                        </label>
                        <InputText
                            id="email"
                            v-model="form.email"
                            type="email"
                            placeholder="your.email@northumbria.ac.uk"
                            class="w-full"
                            :class="{ 'p-invalid': form.errors.email }"
                            required
                            autofocus
                        />
                        <small v-if="form.errors.email" class="text-red-500">
                            {{ form.errors.email }}
                        </small>
                    </div>

                    <!-- Password -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label
                                for="password"
                                class="block text-sm font-medium text-gray-900 dark:text-white"
                            >
                                Password
                            </label>
                            <Link
                                v-if="canResetPassword"
                                href="/forgot-password"
                                class="text-sm text-blue-600 hover:text-blue-500 dark:text-blue-400"
                            >
                                Forgot password?
                            </Link>
                        </div>
                        <Password
                            id="password"
                            v-model="form.password"
                            placeholder="Enter your password"
                            class="w-full"
                            input-class="w-full"
                            :class="{ 'p-invalid': form.errors.password }"
                            :feedback="false"
                            toggle-mask
                            required
                        />
                        <small v-if="form.errors.password" class="text-red-500">
                            {{ form.errors.password }}
                        </small>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center gap-2">
                        <Checkbox
                            v-model="form.remember"
                            input-id="remember"
                            :binary="true"
                        />
                        <label
                            for="remember"
                            class="text-sm text-gray-700 dark:text-gray-300"
                        >
                            Remember me for 30 days
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <Button
                        type="submit"
                        label="Sign In"
                        icon="pi pi-sign-in"
                        class="w-full"
                        size="large"
                        :loading="form.processing"
                        :disabled="form.processing"
                    />

                    <!-- Register Link -->
                    <div v-if="canRegister" class="text-center">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Don't have an account?
                            <Link
                                href="/register"
                                class="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400"
                            >
                                Create an account
                            </Link>
                        </p>
                    </div>
                </form>

                <!-- Test Accounts Info -->
                <Card class="mt-8">
                    <template #content>
                        <div class="space-y-3">
                            <div
                                class="flex items-center gap-2 text-sm font-medium text-gray-900 dark:text-white"
                            >
                                <i class="pi pi-info-circle"></i>
                                <span>Test Accounts</span>
                            </div>
                            <div
                                class="space-y-2 text-xs text-gray-600 dark:text-gray-400"
                            >
                                <div
                                    class="rounded bg-gray-50 p-2 dark:bg-gray-800"
                                >
                                    <p class="font-medium">Admin</p>
                                    <p>admin@northumbria.ac.uk</p>
                                </div>
                                <div
                                    class="rounded bg-gray-50 p-2 dark:bg-gray-800"
                                >
                                    <p class="font-medium">Student</p>
                                    <p>student@northumbria.ac.uk</p>
                                </div>
                                <div
                                    class="rounded bg-gray-50 p-2 dark:bg-gray-800"
                                >
                                    <p class="font-medium">Security Staff</p>
                                    <p>security@northumbria.ac.uk</p>
                                </div>
                                <p class="text-center italic">
                                    Password: password
                                </p>
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Footer -->
                <div
                    class="mt-8 text-center text-xs text-gray-500 dark:text-gray-400"
                >
                    <p>&copy; 2025 Northumbria University London Campus</p>
                    <p class="mt-1">
                        Module LD7206 - Graduation Ticketing System
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
:deep(.p-password-input) {
    width: 100%;
}
</style>
