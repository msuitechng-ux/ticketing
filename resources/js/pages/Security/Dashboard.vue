<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import Button from 'primevue/button'
import Card from 'primevue/card'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'

const props = defineProps<{
    stats: {
        today_scans: number
        today_successful: number
        today_fraud: number
        today_duplicate: number
    }
    recentScans: any[]
    activeCeremonies: any[]
}>()

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
        <Head title="Security Dashboard" />

        <!-- Header -->
        <div class="bg-white shadow dark:bg-gray-800">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                            Security Dashboard
                        </h1>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Entry verification and monitoring
                        </p>
                    </div>
                    <Link :href="`/security/scanner`">
                        <Button label="Open Scanner" icon="pi pi-qrcode" size="large" />
                    </Link>
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Today's Statistics -->
            <div class="mb-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <Card class="shadow-sm">
                    <template #content>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                    Total Scans Today
                                </p>
                                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                                    {{ stats.today_scans }}
                                </p>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900">
                                <i class="pi pi-qrcode text-2xl text-blue-600 dark:text-blue-400"></i>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="shadow-sm">
                    <template #content>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                    Successful
                                </p>
                                <p class="mt-2 text-3xl font-bold text-green-600 dark:text-green-400">
                                    {{ stats.today_successful }}
                                </p>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900">
                                <i class="pi pi-check-circle text-2xl text-green-600 dark:text-green-400"></i>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="shadow-sm">
                    <template #content>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                    Duplicates
                                </p>
                                <p class="mt-2 text-3xl font-bold text-orange-600 dark:text-orange-400">
                                    {{ stats.today_duplicate }}
                                </p>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-orange-100 dark:bg-orange-900">
                                <i class="pi pi-exclamation-triangle text-2xl text-orange-600 dark:text-orange-400"></i>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="shadow-sm">
                    <template #content>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                    Fraud Attempts
                                </p>
                                <p class="mt-2 text-3xl font-bold text-red-600 dark:text-red-400">
                                    {{ stats.today_fraud }}
                                </p>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900">
                                <i class="pi pi-ban text-2xl text-red-600 dark:text-red-400"></i>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Active Ceremonies -->
            <Card class="mb-8 shadow-sm">
                <template #title>Active Ceremonies</template>
                <template #content>
                    <DataTable :value="activeCeremonies" stripedRows>
                        <template #empty>
                            <div class="py-8 text-center text-gray-500">
                                No active ceremonies
                            </div>
                        </template>
                        <Column field="name" header="Ceremony Name" />
                        <Column field="ceremony_date" header="Date">
                            <template #body="{ data }">
                                {{ new Date(data.ceremony_date).toLocaleString() }}
                            </template>
                        </Column>
                        <Column field="venue" header="Venue" />
                        <Column field="tickets_count" header="Total Tickets" />
                    </DataTable>
                </template>
            </Card>

            <!-- Recent Scans -->
            <Card class="shadow-sm">
                <template #title>
                    <div class="flex items-center justify-between">
                        <span>Recent Scans</span>
                        <Link :href="`/security/entry-logs`">
                            <Button label="View All Logs" size="small" text />
                        </Link>
                    </div>
                </template>
                <template #content>
                    <DataTable :value="recentScans" stripedRows>
                        <template #empty>
                            <div class="py-8 text-center text-gray-500">
                                No recent scans
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
                                {{ new Date(data.scanned_at).toLocaleTimeString() }}
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>
        </div>
    </div>
</template>
