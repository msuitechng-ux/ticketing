<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import Avatar from 'primevue/avatar'
import Button from 'primevue/button'
import Card from 'primevue/card'
import Tag from 'primevue/tag'
import Menu from 'primevue/menu'

const props = defineProps<{
    auth: {
        user: {
            name: string
            email: string
        }
    }
    ceremony: {
        id: number
        name: string
        description: string | null
        ceremony_date: string
        venue: string
        venue_address: string | null
        total_capacity: number
        base_tickets_per_graduate: number
        ticket_request_deadline: string | null
        redistribution_deadline: string | null
        is_active: boolean
        graduates_count: number
        tickets_count: number
        ticket_requests_count: number
    }
    stats: {
        total_tickets_allocated: number
        total_tickets_used: number
        utilization_rate: number
    }
}>()

const formatDate = (value: string | null) => (value ? new Date(value).toLocaleString() : '—')

const userMenu = ref()
const userMenuItems = ref([
    { label: 'Profile', icon: 'pi pi-user', command: () => router.visit('/settings/profile') },
    { separator: true },
    { label: 'Logout', icon: 'pi pi-sign-out', command: () => router.post('/logout') },
])

const userInitials = computed(() => {
    const names = props.auth.user.name.split(' ')
    return names.length >= 2 ? names[0][0] + names[names.length - 1][0] : names[0][0]
})
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
        <Head :title="`Ceremony: ${props.ceremony.name}`" />

        <!-- Header -->
        <header class="sticky top-0 z-50 border-b border-gray-200 bg-white/80 backdrop-blur-md shadow-sm dark:border-gray-700 dark:bg-gray-800/80">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <Link href="/admin/ceremonies" class="flex items-center gap-3 transition-opacity hover:opacity-80">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 via-purple-600 to-pink-600 shadow-lg ring-2 ring-white/20">
                            <i class="pi pi-ticket text-xl text-white"></i>
                        </div>
                        <div class="hidden sm:block">
                            <h1 class="text-lg font-bold text-gray-900 dark:text-white">Ceremony Details</h1>
                            <p class="text-xs text-gray-600 dark:text-gray-400">{{ props.ceremony.name }}</p>
                        </div>
                    </Link>
                    <div class="flex items-center gap-3">
                        <div class="hidden text-right md:block">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ auth.user.name }}</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Administrator</p>
                        </div>
                        <Avatar
                            :label="userInitials"
                            class="cursor-pointer bg-gradient-to-br from-blue-600 to-purple-600 text-white shadow-md ring-2 ring-white/20 transition-all hover:shadow-lg hover:ring-4"
                            size="large"
                            shape="circle"
                            @click="userMenu?.toggle($event)"
                        />
                        <Menu ref="userMenu" :model="userMenuItems" popup class="w-56" />
                    </div>
                </div>
            </div>
        </header>

        <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8 space-y-8">
            <nav class="text-sm text-gray-500 dark:text-gray-400">
                <Link href="/admin/dashboard" class="hover:text-gray-700 dark:hover:text-gray-200">Dashboard</Link>
                <span class="mx-2">/</span>
                <Link href="/admin/ceremonies" class="hover:text-gray-700 dark:hover:text-gray-200">Ceremonies</Link>
                <span class="mx-2">/</span>
                <span class="text-gray-700 dark:text-gray-200">{{ props.ceremony.name }}</span>
            </nav>

            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white">{{ props.ceremony.name }}</h2>
                    <p class="mt-1 text-gray-600 dark:text-gray-400">{{ formatDate(props.ceremony.ceremony_date) }}</p>
                </div>
                <div class="flex gap-2">
                    <Link :href="`/admin/ceremonies/${props.ceremony.id}/edit`">
                        <Button label="Edit" icon="pi pi-pencil" class="shadow-md" />
                    </Link>
                    <Link href="/admin/ceremonies">
                        <Button label="Back" severity="secondary" outlined />
                    </Link>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <Card class="shadow-lg">
                    <template #title>
                        <div class="flex items-center gap-2">
                            <i class="pi pi-info-circle text-blue-600"></i>
                            <span>Overview</span>
                        </div>
                    </template>
                    <template #content>
                        <dl class="space-y-3 text-sm text-gray-700 dark:text-gray-200">
                            <div class="flex justify-between">
                                <dt class="font-medium">Status</dt>
                                <dd>
                                    <Tag :value="props.ceremony.is_active ? 'Active' : 'Inactive'" :severity="props.ceremony.is_active ? 'success' : 'danger'" />
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="font-medium">Venue</dt>
                                <dd>{{ props.ceremony.venue }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="font-medium">Venue Address</dt>
                                <dd>{{ props.ceremony.venue_address || '—' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="font-medium">Total Capacity</dt>
                                <dd>{{ props.ceremony.total_capacity }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="font-medium">Base Tickets per Graduate</dt>
                                <dd>{{ props.ceremony.base_tickets_per_graduate }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="font-medium">Ticket Request Deadline</dt>
                                <dd>{{ formatDate(props.ceremony.ticket_request_deadline) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="font-medium">Redistribution Deadline</dt>
                                <dd>{{ formatDate(props.ceremony.redistribution_deadline) }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium mb-1">Description</dt>
                                <dd class="text-gray-600 dark:text-gray-300">
                                    {{ props.ceremony.description || 'No description provided.' }}
                                </dd>
                            </div>
                        </dl>
                    </template>
                </Card>

                <Card class="shadow-lg">
                    <template #title>
                        <div class="flex items-center gap-2">
                            <i class="pi pi-chart-bar text-blue-600"></i>
                            <span>Stats</span>
                        </div>
                    </template>
                    <template #content>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="rounded-lg bg-white/60 p-4 shadow-sm ring-1 ring-gray-200 dark:bg-gray-800/60 dark:ring-gray-700">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Graduates</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ props.ceremony.graduates_count }}</p>
                            </div>
                            <div class="rounded-lg bg-white/60 p-4 shadow-sm ring-1 ring-gray-200 dark:bg-gray-800/60 dark:ring-gray-700">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Tickets</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ props.ceremony.tickets_count }}</p>
                            </div>
                            <div class="rounded-lg bg-white/60 p-4 shadow-sm ring-1 ring-gray-200 dark:bg-gray-800/60 dark:ring-gray-700">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Ticket Requests</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ props.ceremony.ticket_requests_count }}</p>
                            </div>
                            <div class="rounded-lg bg-white/60 p-4 shadow-sm ring-1 ring-gray-200 dark:bg-gray-800/60 dark:ring-gray-700">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Tickets Allocated</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total_tickets_allocated }}</p>
                            </div>
                            <div class="rounded-lg bg-white/60 p-4 shadow-sm ring-1 ring-gray-200 dark:bg-gray-800/60 dark:ring-gray-700">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Tickets Used</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total_tickets_used }}</p>
                            </div>
                            <div class="rounded-lg bg-white/60 p-4 shadow-sm ring-1 ring-gray-200 dark:bg-gray-800/60 dark:ring-gray-700">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Utilization Rate</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.utilization_rate.toFixed(1) }}%</p>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>
        </div>
    </div>
</template>
