<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import Button from 'primevue/button'
import Card from 'primevue/card'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'
import Avatar from 'primevue/avatar'
import Menu from 'primevue/menu'
import { ref, computed } from 'vue'

const props = defineProps<{
    auth: {
        user: {
            name: string
            email: string
        }
    }
    ticketRequests: {
        data: any[]
        links: any
        meta: any
    }
    stats: {
        pending: number
        approved: number
        denied: number
        waitlisted: number
    }
}>()

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
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
        <Head title="Ticket Requests" />

        <!-- Header -->
        <header class="sticky top-0 z-50 border-b border-gray-200 bg-white/80 backdrop-blur-md shadow-sm dark:border-gray-700 dark:bg-gray-800/80">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <Link href="/admin/dashboard" class="flex items-center gap-3 transition-opacity hover:opacity-80">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 via-purple-600 to-pink-600 shadow-lg ring-2 ring-white/20">
                            <i class="pi pi-ticket text-xl text-white"></i>
                        </div>
                        <div class="hidden sm:block">
                            <h1 class="text-lg font-bold text-gray-900 dark:text-white">Ticket Requests</h1>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Manage Requests</p>
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

        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Stats -->
            <div class="mb-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <Card class="overflow-hidden shadow-lg">
                    <template #content>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending</p>
                                <p class="mt-2 text-3xl font-bold text-orange-600 dark:text-orange-400">{{ stats.pending }}</p>
                            </div>
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-orange-500 to-orange-600 shadow-lg">
                                <i class="pi pi-clock text-3xl text-white"></i>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="overflow-hidden shadow-lg">
                    <template #content>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Approved</p>
                                <p class="mt-2 text-3xl font-bold text-green-600 dark:text-green-400">{{ stats.approved }}</p>
                            </div>
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-green-500 to-green-600 shadow-lg">
                                <i class="pi pi-check-circle text-3xl text-white"></i>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="overflow-hidden shadow-lg">
                    <template #content>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Denied</p>
                                <p class="mt-2 text-3xl font-bold text-red-600 dark:text-red-400">{{ stats.denied }}</p>
                            </div>
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-red-500 to-red-600 shadow-lg">
                                <i class="pi pi-times-circle text-3xl text-white"></i>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="overflow-hidden shadow-lg">
                    <template #content>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Waitlisted</p>
                                <p class="mt-2 text-3xl font-bold text-blue-600 dark:text-blue-400">{{ stats.waitlisted }}</p>
                            </div>
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 shadow-lg">
                                <i class="pi pi-list text-3xl text-white"></i>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Requests Table -->
            <Card class="shadow-lg">
                <template #content>
                    <DataTable :value="ticketRequests.data" stripedRows paginator :rows="20">
                        <template #empty>
                            <div class="py-8 text-center text-gray-500">No ticket requests found</div>
                        </template>
                        <Column field="graduate.student_name" header="Student" sortable />
                        <Column field="graduate.ceremony.name" header="Ceremony" />
                        <Column field="requested_quantity" header="Requested" sortable />
                        <Column field="approved_quantity" header="Approved" sortable />
                        <Column field="status" header="Status">
                            <template #body="{ data }">
                                <Tag :value="data.status" :severity="getStatusSeverity(data.status)" />
                            </template>
                        </Column>
                        <Column field="created_at" header="Submitted">
                            <template #body="{ data }">
                                {{ new Date(data.created_at).toLocaleDateString() }}
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
        </div>
    </div>
</template>
