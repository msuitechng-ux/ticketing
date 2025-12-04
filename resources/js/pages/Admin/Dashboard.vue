<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import Button from 'primevue/button'
import Card from 'primevue/card'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'
import { computed } from 'vue'

const props = defineProps<{
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
}>()

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
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <Head title="Admin Dashboard" />

        <!-- Header -->
        <div class="bg-white shadow dark:bg-gray-800">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                            Admin Dashboard
                        </h1>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            System overview and statistics
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <Link :href="`/admin/ceremonies/create`">
                            <Button label="New Ceremony" icon="pi pi-plus" />
                        </Link>
                        <Link :href="`/admin/graduates-import`">
                            <Button label="Import Graduates" icon="pi pi-upload" severity="secondary" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Statistics Grid -->
            <div class="mb-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <Card class="shadow-sm">
                    <template #content>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                    Total Ceremonies
                                </p>
                                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                                    {{ stats.total_ceremonies }}
                                </p>
                                <p class="mt-1 text-xs text-gray-500">
                                    {{ stats.active_ceremonies }} active
                                </p>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900">
                                <i class="pi pi-calendar text-2xl text-blue-600 dark:text-blue-400"></i>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="shadow-sm">
                    <template #content>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                    Total Graduates
                                </p>
                                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                                    {{ stats.total_graduates }}
                                </p>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900">
                                <i class="pi pi-users text-2xl text-green-600 dark:text-green-400"></i>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="shadow-sm">
                    <template #content>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                    Tickets Issued
                                </p>
                                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                                    {{ stats.total_tickets }}
                                </p>
                                <p class="mt-1 text-xs text-gray-500">
                                    {{ stats.tickets_used }} used ({{ stats.ticket_utilization_rate }}%)
                                </p>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900">
                                <i class="pi pi-ticket text-2xl text-purple-600 dark:text-purple-400"></i>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="shadow-sm">
                    <template #content>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                    Pending Requests
                                </p>
                                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                                    {{ stats.pending_requests }}
                                </p>
                                <Link v-if="stats.pending_requests > 0" :href="`/admin/ticket-requests?status=Pending`" class="mt-1 text-xs text-blue-600 hover:underline dark:text-blue-400">
                                    Review now →
                                </Link>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-orange-100 dark:bg-orange-900">
                                <i class="pi pi-inbox text-2xl text-orange-600 dark:text-orange-400"></i>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Upcoming Ceremonies -->
            <Card class="mb-8 shadow-sm">
                <template #title>
                    <div class="flex items-center justify-between">
                        <span>Upcoming Ceremonies</span>
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
            <Card class="mb-8 shadow-sm">
                <template #title>
                    <div class="flex items-center justify-between">
                        <span>Recent Ticket Requests</span>
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
                        <Column field="graduate.student_name" header="Student" />
                        <Column field="graduate.ceremony.name" header="Ceremony" />
                        <Column field="requested_quantity" header="Requested" />
                        <Column field="status" header="Status">
                            <template #body="{ data }">
                                <Tag :value="data.status" :severity="getStatusSeverity(data.status)" />
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
            <Card class="shadow-sm">
                <template #title>
                    <div class="flex items-center justify-between">
                        <span>Recent Entries</span>
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
