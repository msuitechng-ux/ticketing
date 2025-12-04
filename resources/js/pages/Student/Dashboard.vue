<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import Button from 'primevue/button'
import Card from 'primevue/card'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'
import ProgressBar from 'primevue/progressbar'

const props = defineProps<{
    graduate: any
    ceremony: any
    tickets: any[]
    ticketRequests: any[]
    stats: {
        total_tickets: number
        tickets_used: number
        tickets_available: number
        attendance_rate: number
        request_status: string
        can_request_more: boolean
    } | null
}>()

const getStatusSeverity = (status: string) => {
    const severityMap: Record<string, string> = {
        Active: 'success',
        Used: 'info',
        Cancelled: 'danger',
        Redistributed: 'warn',
    }
    return severityMap[status] || 'info'
}

const getRequestStatusSeverity = (status: string) => {
    const severityMap: Record<string, string> = {
        Pending: 'warn',
        Approved: 'success',
        Denied: 'danger',
        Waitlisted: 'info',
        'Partially Approved': 'warn',
    }
    return severityMap[status] || 'info'
}
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <Head title="Student Dashboard" />

        <!-- Header -->
        <div class="bg-white shadow dark:bg-gray-800">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    My Dashboard
                </h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    View your tickets and ceremony information
                </p>
            </div>
        </div>

        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- No Graduate Profile -->
            <div v-if="!graduate" class="text-center">
                <Card class="shadow-sm">
                    <template #content>
                        <div class="py-12">
                            <i class="pi pi-user-plus mb-4 text-6xl text-gray-400"></i>
                            <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">
                                No Graduate Profile Found
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400">
                                You haven't been registered for any ceremonies yet.
                                Please contact the registry office.
                            </p>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Graduate Profile Exists -->
            <div v-else class="space-y-6">
                <!-- Ceremony Information -->
                <Card class="shadow-sm">
                    <template #title>
                        <div class="flex items-center gap-3">
                            <i class="pi pi-calendar text-2xl text-blue-600"></i>
                            <span>Your Graduation Ceremony</span>
                        </div>
                    </template>
                    <template #content>
                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <h3 class="mb-4 font-semibold text-gray-900 dark:text-white">
                                    {{ ceremony.name }}
                                </h3>
                                <dl class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <dt class="text-gray-600 dark:text-gray-400">Date:</dt>
                                        <dd class="font-medium text-gray-900 dark:text-white">
                                            {{ new Date(ceremony.ceremony_date).toLocaleString() }}
                                        </dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-gray-600 dark:text-gray-400">Venue:</dt>
                                        <dd class="font-medium text-gray-900 dark:text-white">
                                            {{ ceremony.venue }}
                                        </dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-gray-600 dark:text-gray-400">Faculty:</dt>
                                        <dd class="font-medium text-gray-900 dark:text-white">
                                            {{ graduate.faculty }}
                                        </dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-gray-600 dark:text-gray-400">Department:</dt>
                                        <dd class="font-medium text-gray-900 dark:text-white">
                                            {{ graduate.department }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                            <div class="rounded-lg bg-gradient-to-br from-blue-50 to-purple-50 p-6 dark:from-blue-900/20 dark:to-purple-900/20">
                                <h4 class="mb-4 font-semibold text-gray-900 dark:text-white">
                                    Ticket Statistics
                                </h4>
                                <div class="space-y-4">
                                    <div>
                                        <div class="mb-2 flex justify-between text-sm">
                                            <span class="text-gray-600 dark:text-gray-400">Total Tickets</span>
                                            <span class="font-bold text-gray-900 dark:text-white">
                                                {{ stats.total_tickets }}
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="mb-2 flex justify-between text-sm">
                                            <span class="text-gray-600 dark:text-gray-400">Tickets Used</span>
                                            <span class="font-bold text-gray-900 dark:text-white">
                                                {{ stats.tickets_used }}
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="mb-2 flex justify-between text-sm">
                                            <span class="text-gray-600 dark:text-gray-400">Available</span>
                                            <span class="font-bold text-green-600 dark:text-green-400">
                                                {{ stats.tickets_available }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Quick Actions -->
                <div class="grid gap-6 md:grid-cols-3">
                    <Card class="shadow-sm">
                        <template #content>
                            <div class="text-center">
                                <i class="pi pi-ticket mb-3 text-4xl text-blue-600"></i>
                                <h3 class="mb-2 font-semibold text-gray-900 dark:text-white">
                                    View Tickets
                                </h3>
                                <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                                    View and download your ticket QR codes
                                </p>
                                <Link :href="`/student/tickets`">
                                    <Button label="View Tickets" class="w-full" />
                                </Link>
                            </div>
                        </template>
                    </Card>

                    <Card class="shadow-sm">
                        <template #content>
                            <div class="text-center">
                                <i class="pi pi-inbox mb-3 text-4xl text-purple-600"></i>
                                <h3 class="mb-2 font-semibold text-gray-900 dark:text-white">
                                    Request Extra Tickets
                                </h3>
                                <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                                    Submit a request for additional tickets
                                </p>
                                <Link :href="`/student/ticket-requests`">
                                    <Button
                                        label="Request Tickets"
                                        class="w-full"
                                        :disabled="!stats.can_request_more"
                                    />
                                </Link>
                            </div>
                        </template>
                    </Card>

                    <Card class="shadow-sm">
                        <template #content>
                            <div class="text-center">
                                <i class="pi pi-download mb-3 text-4xl text-green-600"></i>
                                <h3 class="mb-2 font-semibold text-gray-900 dark:text-white">
                                    Download All
                                </h3>
                                <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                                    Download all QR codes as ZIP
                                </p>
                                <a :href="`/student/tickets-download-all`" download>
                                    <Button label="Download ZIP" class="w-full" severity="success" />
                                </a>
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- My Tickets -->
                <Card class="shadow-sm">
                    <template #title>My Tickets</template>
                    <template #content>
                        <DataTable :value="tickets" stripedRows>
                            <template #empty>
                                <div class="py-8 text-center text-gray-500">
                                    No tickets allocated yet
                                </div>
                            </template>
                            <Column field="ticket_code" header="Ticket Code" />
                            <Column field="ticket_type" header="Type" />
                            <Column field="guest_name" header="Guest Name">
                                <template #body="{ data }">
                                    {{ data.guest_name || '-' }}
                                </template>
                            </Column>
                            <Column field="status" header="Status">
                                <template #body="{ data }">
                                    <Tag :value="data.status" :severity="getStatusSeverity(data.status)" />
                                </template>
                            </Column>
                            <Column header="Actions">
                                <template #body="{ data }">
                                    <div class="flex gap-2">
                                        <Link :href="`/student/tickets/${data.id}`">
                                            <Button label="View" size="small" text />
                                        </Link>
                                        <a v-if="data.status === 'Active'" :href="`/student/tickets/${data.id}/download-qr`" download>
                                            <Button label="Download QR" size="small" severity="success" text />
                                        </a>
                                    </div>
                                </template>
                            </Column>
                        </DataTable>
                    </template>
                </Card>

                <!-- Ticket Requests -->
                <Card class="shadow-sm">
                    <template #title>
                        <div class="flex items-center justify-between">
                            <span>My Ticket Requests</span>
                            <Link :href="`/student/ticket-requests`">
                                <Button label="View All" size="small" text />
                            </Link>
                        </div>
                    </template>
                    <template #content>
                        <DataTable :value="ticketRequests" stripedRows>
                            <template #empty>
                                <div class="py-8 text-center text-gray-500">
                                    No ticket requests yet
                                </div>
                            </template>
                            <Column field="requested_quantity" header="Requested" />
                            <Column field="approved_quantity" header="Approved" />
                            <Column field="status" header="Status">
                                <template #body="{ data }">
                                    <Tag :value="data.status" :severity="getRequestStatusSeverity(data.status)" />
                                </template>
                            </Column>
                            <Column field="created_at" header="Submitted">
                                <template #body="{ data }">
                                    {{ new Date(data.created_at).toLocaleDateString() }}
                                </template>
                            </Column>
                            <Column field="reviewed_at" header="Reviewed">
                                <template #body="{ data }">
                                    {{ data.reviewed_at ? new Date(data.reviewed_at).toLocaleDateString() : '-' }}
                                </template>
                            </Column>
                        </DataTable>
                    </template>
                </Card>
            </div>
        </div>
    </div>
</template>
