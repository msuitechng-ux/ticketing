<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';
import { Head, Link } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';

defineProps<{
    canRegister: boolean;
}>();

const { isDark } = useAppearance();
</script>

<template>
    <div
        class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900"
    >
        <Head title="Welcome - Graduation Ticketing System" />

        <!-- Navigation -->
        <nav
            class="border-b border-gray-200 bg-white/80 backdrop-blur-sm dark:border-gray-700 dark:bg-gray-800/80"
        >
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-blue-600 to-purple-600"
                        >
                            <i class="pi pi-ticket text-xl text-white"></i>
                        </div>
                        <div>
                            <h1
                                class="text-lg font-bold text-gray-900 dark:text-white"
                            >
                                Graduation Ticketing
                            </h1>
                            <p class="text-xs text-gray-600 dark:text-gray-400">
                                Northumbria University London
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <Link href="/login">
                            <Button
                                label="Sign In"
                                severity="secondary"
                                outlined
                            />
                        </Link>
                        <Link v-if="canRegister" href="/register">
                            <Button label="Register" severity="primary" />
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="text-center">
                <div
                    class="mb-6 inline-flex items-center gap-2 rounded-full bg-blue-100 px-4 py-2 text-sm font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-200"
                >
                    <i class="pi pi-sparkles"></i>
                    <span>Secure Digital Ticketing Platform</span>
                </div>
                <h2
                    class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl md:text-6xl dark:text-white"
                >
                    Your Graduation
                    <span
                        class="bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent"
                    >
                        Made Simple
                    </span>
                </h2>
                <p
                    class="mx-auto mt-6 max-w-2xl text-lg text-gray-600 dark:text-gray-300"
                >
                    Welcome to the Northumbria University London Campus
                    Graduation Guest Ticketing System. Manage your ceremony
                    tickets, request extras, and access your QR codes all in one
                    place.
                </p>
                <div class="mt-10 flex items-center justify-center gap-4">
                    <Link href="/login">
                        <Button
                            label="Get Started"
                            icon="pi pi-arrow-right"
                            iconPos="right"
                            size="large"
                            class="px-8"
                        />
                    </Link>
                    <Button
                        label="Learn More"
                        severity="secondary"
                        outlined
                        size="large"
                        @click="
                            () =>
                                document
                                    .getElementById('features')
                                    ?.scrollIntoView({ behavior: 'smooth' })
                        "
                    />
                </div>
            </div>

            <!-- Stats -->
            <div class="mt-20 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <Card class="text-center">
                    <template #content>
                        <div class="flex flex-col items-center gap-2">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900"
                            >
                                <i
                                    class="pi pi-shield-check text-2xl text-blue-600 dark:text-blue-400"
                                ></i>
                            </div>
                            <h3
                                class="text-3xl font-bold text-gray-900 dark:text-white"
                            >
                                100%
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Secure QR Codes
                            </p>
                        </div>
                    </template>
                </Card>
                <Card class="text-center">
                    <template #content>
                        <div class="flex flex-col items-center gap-2">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-full bg-purple-100 dark:bg-purple-900"
                            >
                                <i
                                    class="pi pi-clock text-2xl text-purple-600 dark:text-purple-400"
                                ></i>
                            </div>
                            <h3
                                class="text-3xl font-bold text-gray-900 dark:text-white"
                            >
                                &lt;2s
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Entry Verification
                            </p>
                        </div>
                    </template>
                </Card>
                <Card class="text-center">
                    <template #content>
                        <div class="flex flex-col items-center gap-2">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900"
                            >
                                <i
                                    class="pi pi-users text-2xl text-green-600 dark:text-green-400"
                                ></i>
                            </div>
                            <h3
                                class="text-3xl font-bold text-gray-900 dark:text-white"
                            >
                                Fair
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Ticket Allocation
                            </p>
                        </div>
                    </template>
                </Card>
                <Card class="text-center">
                    <template #content>
                        <div class="flex flex-col items-center gap-2">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-full bg-orange-100 dark:bg-orange-900"
                            >
                                <i
                                    class="pi pi-chart-line text-2xl text-orange-600 dark:text-orange-400"
                                ></i>
                            </div>
                            <h3
                                class="text-3xl font-bold text-gray-900 dark:text-white"
                            >
                                Real-time
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Analytics Dashboard
                            </p>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Features Section -->
            <div id="features" class="mt-20">
                <div class="text-center">
                    <h3
                        class="text-3xl font-bold text-gray-900 dark:text-white"
                    >
                        Everything You Need
                    </h3>
                    <p class="mt-4 text-gray-600 dark:text-gray-300">
                        A comprehensive platform designed for graduates, guests,
                        and university staff
                    </p>
                </div>

                <div class="mt-12 grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    <!-- Feature 1 -->
                    <Card>
                        <template #header>
                            <div class="flex items-center gap-3 p-6 pb-0">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900"
                                >
                                    <i
                                        class="pi pi-ticket text-2xl text-blue-600 dark:text-blue-400"
                                    ></i>
                                </div>
                                <h4
                                    class="text-xl font-semibold text-gray-900 dark:text-white"
                                >
                                    Base Ticket Allocation
                                </h4>
                            </div>
                        </template>
                        <template #content>
                            <p class="text-gray-600 dark:text-gray-400">
                                Every graduate automatically receives their base
                                ticket allocation. View and manage your tickets
                                from your personalized dashboard.
                            </p>
                        </template>
                    </Card>

                    <!-- Feature 2 -->
                    <Card>
                        <template #header>
                            <div class="flex items-center gap-3 p-6 pb-0">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900"
                                >
                                    <i
                                        class="pi pi-plus-circle text-2xl text-purple-600 dark:text-purple-400"
                                    ></i>
                                </div>
                                <h4
                                    class="text-xl font-semibold text-gray-900 dark:text-white"
                                >
                                    Extra Ticket Requests
                                </h4>
                            </div>
                        </template>
                        <template #content>
                            <p class="text-gray-600 dark:text-gray-400">
                                Need more tickets? Submit extra ticket requests
                                with transparent approval workflow and waitlist
                                management for fair distribution.
                            </p>
                        </template>
                    </Card>

                    <!-- Feature 3 -->
                    <Card>
                        <template #header>
                            <div class="flex items-center gap-3 p-6 pb-0">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900"
                                >
                                    <i
                                        class="pi pi-qrcode text-2xl text-green-600 dark:text-green-400"
                                    ></i>
                                </div>
                                <h4
                                    class="text-xl font-semibold text-gray-900 dark:text-white"
                                >
                                    QR Code Access
                                </h4>
                            </div>
                        </template>
                        <template #content>
                            <p class="text-gray-600 dark:text-gray-400">
                                Download your secure QR codes instantly. Each
                                code is uniquely encrypted with fraud prevention
                                technology for maximum security.
                            </p>
                        </template>
                    </Card>

                    <!-- Feature 4 -->
                    <Card>
                        <template #header>
                            <div class="flex items-center gap-3 p-6 pb-0">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-lg bg-orange-100 dark:bg-orange-900"
                                >
                                    <i
                                        class="pi pi-shield-check text-2xl text-orange-600 dark:text-orange-400"
                                    ></i>
                                </div>
                                <h4
                                    class="text-xl font-semibold text-gray-900 dark:text-white"
                                >
                                    Entry Verification
                                </h4>
                            </div>
                        </template>
                        <template #content>
                            <p class="text-gray-600 dark:text-gray-400">
                                Fast and secure entry with real-time QR
                                scanning. Duplicate detection and fraud
                                prevention ensure only valid tickets are
                                accepted.
                            </p>
                        </template>
                    </Card>

                    <!-- Feature 5 -->
                    <Card>
                        <template #header>
                            <div class="flex items-center gap-3 p-6 pb-0">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900"
                                >
                                    <i
                                        class="pi pi-sync text-2xl text-red-600 dark:text-red-400"
                                    ></i>
                                </div>
                                <h4
                                    class="text-xl font-semibold text-gray-900 dark:text-white"
                                >
                                    Automatic Redistribution
                                </h4>
                            </div>
                        </template>
                        <template #content>
                            <p class="text-gray-600 dark:text-gray-400">
                                Unused tickets are automatically redistributed
                                to waitlisted students, ensuring maximum
                                attendance and fair opportunity.
                            </p>
                        </template>
                    </Card>

                    <!-- Feature 6 -->
                    <Card>
                        <template #header>
                            <div class="flex items-center gap-3 p-6 pb-0">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900"
                                >
                                    <i
                                        class="pi pi-chart-bar text-2xl text-indigo-600 dark:text-indigo-400"
                                    ></i>
                                </div>
                                <h4
                                    class="text-xl font-semibold text-gray-900 dark:text-white"
                                >
                                    Real-time Analytics
                                </h4>
                            </div>
                        </template>
                        <template #content>
                            <p class="text-gray-600 dark:text-gray-400">
                                Administrators get comprehensive analytics and
                                reporting for informed decision-making and event
                                planning.
                            </p>
                        </template>
                    </Card>
                </div>
            </div>

            <!-- CTA Section -->
            <div class="mt-20">
                <Card class="bg-gradient-to-r from-blue-600 to-purple-600">
                    <template #content>
                        <div class="p-6 text-center">
                            <h3 class="text-3xl font-bold text-white">
                                Ready to Get Started?
                            </h3>
                            <p class="mt-4 text-lg text-blue-100">
                                Sign in to access your tickets and manage your
                                graduation ceremony attendance
                            </p>
                            <div
                                class="mt-8 flex items-center justify-center gap-4"
                            >
                                <Link href="/login">
                                    <Button
                                        label="Sign In Now"
                                        severity="secondary"
                                        size="large"
                                        class="bg-white text-blue-600"
                                    />
                                </Link>
                                <Link v-if="canRegister" href="/register">
                                    <Button
                                        label="Create Account"
                                        severity="secondary"
                                        outlined
                                        size="large"
                                        class="border-white text-white hover:bg-white/10"
                                    />
                                </Link>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>
        </div>

        <!-- Footer -->
        <footer
            class="mt-20 border-t border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800"
        >
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="grid gap-8 md:grid-cols-3">
                    <div>
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-blue-600 to-purple-600"
                            >
                                <i class="pi pi-ticket text-xl text-white"></i>
                            </div>
                            <div>
                                <h4
                                    class="font-bold text-gray-900 dark:text-white"
                                >
                                    Graduation Ticketing
                                </h4>
                                <p
                                    class="text-sm text-gray-600 dark:text-gray-400"
                                >
                                    Northumbria University London
                                </p>
                            </div>
                        </div>
                        <p
                            class="mt-4 text-sm text-gray-600 dark:text-gray-400"
                        >
                            Secure, fair, and transparent ticketing for your
                            special day
                        </p>
                    </div>
                    <div>
                        <h5
                            class="mb-4 font-semibold text-gray-900 dark:text-white"
                        >
                            Module Information
                        </h5>
                        <ul
                            class="space-y-2 text-sm text-gray-600 dark:text-gray-400"
                        >
                            <li>Module Code: LD7206</li>
                            <li>Ceremony Date: July 7th, 2025</li>
                            <li>Document Version: 1.0</li>
                        </ul>
                    </div>
                    <div>
                        <h5
                            class="mb-4 font-semibold text-gray-900 dark:text-white"
                        >
                            Support
                        </h5>
                        <ul
                            class="space-y-2 text-sm text-gray-600 dark:text-gray-400"
                        >
                            <li>
                                <a href="#" class="hover:text-blue-600"
                                    >Help Center</a
                                >
                            </li>
                            <li>
                                <a href="#" class="hover:text-blue-600"
                                    >Contact Us</a
                                >
                            </li>
                            <li>
                                <a href="#" class="hover:text-blue-600">FAQs</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div
                    class="mt-8 border-t border-gray-200 pt-8 text-center text-sm text-gray-600 dark:border-gray-700 dark:text-gray-400"
                >
                    <p>
                        &copy; 2025 Northumbria University London Campus. All
                        rights reserved.
                    </p>
                    <p class="mt-2">
                        Developed by: Khaoula Sbaiti, Rayhan MD, Alishba
                        Maqsood, Syed Azeem, Syed Zaki
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>
