<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import Button from 'primevue/button'
import Card from 'primevue/card'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'
import Avatar from 'primevue/avatar'
import Menu from 'primevue/menu'
import TicketsByCeremonyChart from '@/components/charts/TicketsByCeremonyChart.vue'
import RequestStatusChart from '@/components/charts/RequestStatusChart.vue'
import FacultyDistributionChart from '@/components/charts/FacultyDistributionChart.vue'
import DegreeLevelChart from '@/components/charts/DegreeLevelChart.vue'
import { ref, computed } from 'vue'

const props = defineProps<{
    auth: {
        user: {
            name: string
            email: string
        }
    }
    stats: {
        total_ceremonies: number
        active_ceremonies: number
        total_graduates: number
        total_tickets: number
        tickets_used: number
        ticket_utilization_rate: number
        pending_requests: number
    }
    upcomingCeremonies: any[]
    recentTicketRequests: any[]
    recentEntries: any[]
    ceremonyStats: any[]
    analytics: {
        requestStatusBreakdown: Record<string, number>
        facultyDistribution: Record<string, number>
        degreeLevelDistribution: Record<string, number>
        ticketsByCeremony: Array<{
            name: string
            allocated: number
            used: number
            available: number
        }>
    }
}>()

const userMenu = ref()
const userMenuItems = ref([
    {
        label: 'Profile',
        icon: 'pi pi-user',
        command: () => router.visit('/settings/profile'),
    },
    {
        separator: true,
    },
    {
        label: 'Logout',
        icon: 'pi pi-sign-out',
        command: () => router.post('/logout'),
    },
])

const toggleUserMenu = (event: Event) => {
    userMenu.value.toggle(event)
}

const userInitials = computed(() => {
    const names = props.auth.user.name.split(' ')
    return names.length >= 2
        ? names[0][0] + names[names.length - 1][0]
        : names[0][0]
})

const getStatusSeverity = (status: string) => {
    const severityMap: Record<string, string> = {
        Pending: 'warn',
        Approved: 'success',
        Denied: 'danger',
        Waitlisted: 'info',
        'Partially Approved': 'warn',
    }
    return severityMap[status] || 'info'
}

const getVerificationSeverity = (status: string) => {
    const severityMap: Record<string, string> = {
        Success: 'success',
        Duplicate: 'warn',
        Invalid: 'danger',
        'Fraud Attempt': 'danger',
    }
    return severityMap[status] || 'info'
}
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
        <Head title="Admin Dashboard" />

        <!-- Beautiful Header -->
        <header class="sticky top-0 z-50 border-b border-gray-200 bg-white/80 backdrop-blur-md shadow-sm dark:border-gray-700 dark:bg-gray-800/80">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <Link href="/dashboard" class="flex items-center gap-3 transition-opacity hover:opacity-80">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 via-purple-600 to-pink-600 shadow-lg ring-2 ring-white/20">
                            <i class="pi pi-ticket text-xl text-white"></i>
                        </div>
                        <div class="hidden sm:block">
                            <h1 class="text-lg font-bold text-gray-900 dark:text-white">
                                Graduation Ticketing
                            </h1>
                            <p class="text-xs text-gray-600 dark:text-gray-400">
                                Admin Dashboard
                            </p>
                        </div>
                    </Link>

                    <div class="flex items-center gap-3">
                        <div class="hidden text-right md:block">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ auth.user.name }}
                            </p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">
                                Administrator
                            </p>
                        </div>
                        <Avatar
                            :label="userInitials"
                            class="cursor-pointer bg-gradient-to-br from-blue-600 to-purple-600 text-white shadow-md ring-2 ring-white/20 transition-all hover:shadow-lg hover:ring-4"
                            size="large"
                            shape="circle"
                            @click="toggleUserMenu"
                        />
                        <Menu ref="userMenu" :model="userMenuItems" popup class="w-56" />
                    </div>
                </div>
            </div>
        </header>

        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Welcome Section -->
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white">
                    Welcome back, {{ auth.user.name.split(' ')[0] }}! 👋
                </h2>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    Here's what's happening with your graduation ticketing system today.
                </p>
            </div>

            <!-- Statistics Grid -->
            <div class="mb-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <Card class="overflow-hidden shadow-lg transition-all hover:shadow-xl">
                    <template #content>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                    Total Ceremonies
                                </p>
                                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                                    {{ stats.total_ceremonies }}
                                </p>
                                <p class="mt-1 text-xs text-green-600 dark:text-green-400">
                                    <i class="pi pi-check-circle"></i> {{ stats.active_ceremonies }} active
                                </p>
                            </div>
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 shadow-lg">
                                <i class="pi pi-calendar text-3xl text-white"></i>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="overflow-hidden shadow-lg transition-all hover:shadow-xl">
                    <template #content>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                    Total Graduates
                                </p>
                                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                                    {{ stats.total_graduates }}
                                </p>
                                <p class="mt-1 text-xs text-gray-500">
                                    Registered students
                                </p>
                            </div>
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-green-500 to-green-600 shadow-lg">
                                <i class="pi pi-users text-3xl text-white"></i>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="overflow-hidden shadow-lg transition-all hover:shadow-xl">
                    <template #content>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                    Tickets Issued
                                </p>
                                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                                    {{ stats.total_tickets }}
                                </p>
                                <p class="mt-1 text-xs text-purple-600 dark:text-purple-400">
                                    {{ stats.tickets_used }} used ({{ stats.ticket_utilization_rate }}%)
                                </p>
                            </div>
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-purple-500 to-purple-600 shadow-lg">
                                <i class="pi pi-ticket text-3xl text-white"></i>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="overflow-hidden shadow-lg transition-all hover:shadow-xl">
                    <template #content>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                    Pending Requests
                                </p>
                                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                                    {{ stats.pending_requests }}
                                </p>
                                <Link v-if="stats.pending_requests > 0" :href="`/admin/ticket-requests?status=Pending`" class="mt-1 text-xs text-orange-600 hover:underline dark:text-orange-400">
                                    Review now →
                                </Link>
                            </div>
                            <div class="flex h-14 w-14 items-center justify-between rounded-2xl bg-gradient-to-br from-orange-500 to-orange-600 shadow-lg">
                                <i class="pi pi-inbox text-3xl text-white"></i>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Quick Actions -->
            <div class="mb-8 flex flex-wrap gap-3">
                <Link :href="`/admin/ceremonies/create`">
                    <Button label="New Ceremony" icon="pi pi-plus" class="shadow-md" />
                </Link>
                <Link :href="`/admin/graduates-import`">
                    <Button label="Import Graduates" icon="pi pi-upload" severity="secondary" class="shadow-md" />
                </Link>
                <Link :href="`/admin/ceremonies`">
                    <Button label="Manage Ceremonies" icon="pi pi-calendar" severity="secondary" outlined />
                </Link>
                <Link :href="`/admin/ticket-requests`">
                    <Button label="View All Requests" icon="pi pi-inbox" severity="secondary" outlined />
                </Link>
            </div>

            <!-- Analytics Section -->
            <div class="mb-8">
                <h3 class="mb-6 text-2xl font-bold text-gray-900 dark:text-white">
                    <i class="pi pi-chart-bar mr-2 text-blue-600"></i>
                    Analytics & Insights
                </h3>

                <!-- Charts Grid -->
                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Tickets by Ceremony -->
                    <Card class="shadow-lg">
                        <template #title>
                            <div class="flex items-center gap-2">
                                <i class="pi pi-ticket text-blue-600"></i>
                                <span>Ticket Allocation by Ceremony</span>
                            </div>
                        </template>
                        <template #content>
                            <TicketsByCeremonyChart :data="analytics.ticketsByCeremony" />
                        </template>
                    </Card>

                    <!-- Request Status Breakdown -->
                    <Card class="shadow-lg">
                        <template #title>
                            <div class="flex items-center gap-2">
                                <i class="pi pi-chart-pie text-orange-600"></i>
                                <span>Request Status Distribution</span>
                            </div>
                        </template>
                        <template #content>
                            <RequestStatusChart :data="analytics.requestStatusBreakdown" />
                        </template>
                    </Card>

                    <!-- Faculty Distribution -->
                    <Card class="shadow-lg">
                        <template #title>
                            <div class="flex items-center gap-2">
                                <i class="pi pi-building text-green-600"></i>
                                <span>Graduates by Faculty</span>
                            </div>
                        </template>
                        <template #content>
                            <FacultyDistributionChart :data="analytics.facultyDistribution" />
                        </template>
                    </Card>

                    <!-- Degree Level Distribution -->
                    <Card class="shadow-lg">
                        <template #title>
                            <div class="flex items-center gap-2">
                                <i class="pi pi-book text-purple-600"></i>
                                <span>Graduates by Degree Level</span>
                            </div>
                        </template>
                        <template #content>
                            <DegreeLevelChart :data="analytics.degreeLevelDistribution" />
                        </template>
                    </Card>
                </div>
            </div>

            <!-- Upcoming Ceremonies -->
            <Card class="mb-8 shadow-lg">
                <template #title>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i class="pi pi-calendar text-blue-600"></i>
                            <span>Upcoming Ceremonies</span>
                        </div>
                        <Link :href="`/admin/ceremonies`">
                            <Button label="View All" size="small" text />
                        </Link>
                    </div>
                </template>
                <template #content>
                    <DataTable :value="upcomingCeremonies" stripedRows>
                        <template #empty>
                            <div class="py-8 text-center text-gray-500">
                                No upcoming ceremonies
                            </div>
                        </template>
                        <Column field="name" header="Ceremony Name" />
                        <Column field="ceremony_date" header="Date">
                            <template #body="{ data }">
                                {{ new Date(data.ceremony_date).toLocaleString() }}
                            </template>
                        </Column>
                        <Column field="venue" header="Venue" />
                        <Column field="graduates_count" header="Graduates" />
                        <Column field="tickets_count" header="Tickets" />
                        <Column header="Actions">
                            <template #body="{ data }">
                                <Link :href="`/admin/ceremonies/${data.id}`">
                                    <Button label="View" size="small" text />
                                </Link>
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>

            <!-- Recent Ticket Requests -->
            <Card class="mb-8 shadow-lg">
                <template #title>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i class="pi pi-inbox text-orange-600"></i>
                            <span>Recent Ticket Requests</span>
                        </div>
                        <Link :href="`/admin/ticket-requests`">
                            <Button label="View All" size="small" text />
                        </Link>
                    </div>
                </template>
                <template #content>
                    <DataTable :value="recentTicketRequests" stripedRows>
                        <template #empty>
                            <div class="py-8 text-center text-gray-500">
                                No recent ticket requests
                            </div>
                        </template>
                        <Column field="graduate.student_name" header="Student">
                            <template #body="{ data }">
                                {{ data.graduate?.student_name || data.graduate?.user?.name || '-' }}
                            </template>
                        </Column>
                        <Column field="graduate.ceremony.name" header="Ceremony">
                            <template #body="{ data }">
                                {{ data.graduate?.ceremony?.name || '-' }}
                            </template>
                        </Column>
                        <Column field="requested_quantity" header="Requested" />
                        <Column field="status" header="Status">
                            <template #body="{ data }">
                                <Tag :value="data.status" :severity="getStatusSeverity(data.status)" />
                            </template>
                        </Column>
                        <Column field="created_at" header="Submitted">
                            <template #body="{ data }">
                                {{ new Date(data.created_at).toLocaleString() }}
                            </template>
                        </Column>
                        <Column header="Actions">
                            <template #body="{ data }">
                                <Link :href="`/admin/ticket-requests/${data.id}`">
                                    <Button label="Review" size="small" text />
                                </Link>
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>

            <!-- Recent Entries -->
            <Card class="shadow-lg">
                <template #title>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i class="pi pi-check-circle text-green-600"></i>
                            <span>Recent Entries</span>
                        </div>
                        <Link :href="`/security/entry-logs`">
                            <Button label="View All" size="small" text />
                        </Link>
                    </div>
                </template>
                <template #content>
                    <DataTable :value="recentEntries" stripedRows>
                        <template #empty>
                            <div class="py-8 text-center text-gray-500">
                                No recent entries
                            </div>
                        </template>
                        <Column field="ticket.graduate.student_name" header="Graduate" />
                        <Column field="ceremony.name" header="Ceremony" />
                        <Column field="entry_point" header="Entry Point" />
                        <Column field="verification_status" header="Status">
                            <template #body="{ data }">
                                <Tag :value="data.verification_status" :severity="getVerificationSeverity(data.verification_status)" />
                            </template>
                        </Column>
                        <Column field="scanned_at" header="Time">
                            <template #body="{ data }">
                                {{ new Date(data.scanned_at).toLocaleString() }}
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>
        </div>
    </div>
</template>
