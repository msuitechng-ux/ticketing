<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import Button from 'primevue/button'
import Card from 'primevue/card'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import { ref } from 'vue'

const props = defineProps<{
    tickets: any[]
    graduate: any
}>()

const showGuestDialog = ref(false)
const selectedTicket = ref<any>(null)
const guestForm = ref({
    guest_name: '',
    guest_email: '',
})

const openGuestDialog = (ticket: any) => {
    selectedTicket.value = ticket
    guestForm.value = {
        guest_name: ticket.guest_name || '',
        guest_email: ticket.guest_email || '',
    }
    showGuestDialog.value = true
}

const updateGuest = () => {
    if (selectedTicket.value) {
        router.put(`/student/tickets/${selectedTicket.value.id}/guest`, guestForm.value, {
            onSuccess: () => {
                showGuestDialog.value = false
            },
        })
    }
}

const getStatusSeverity = (status: string) => {
    const severityMap: Record<string, string> = {
        Active: 'success',
        Used: 'info',
        Cancelled: 'danger',
        Redistributed: 'warn',
    }
    return severityMap[status] || 'info'
}
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <Head title="My Tickets" />

        <!-- Header -->
        <div class="bg-white shadow dark:bg-gray-800">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                            My Tickets
                        </h1>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            View and manage your graduation tickets
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <Link :href="`/student/dashboard`">
                            <Button label="Back to Dashboard" severity="secondary" outlined />
                        </Link>
                        <a v-if="tickets.length > 0" :href="`/student/tickets-download-all`" download>
                            <Button label="Download All QR" icon="pi pi-download" severity="success" />
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- No Graduate Profile -->
            <div v-if="!graduate" class="text-center">
                <Card class="shadow-sm">
                    <template #content>
                        <div class="py-12">
                            <i class="pi pi-ticket mb-4 text-6xl text-gray-400"></i>
                            <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">
                                No Tickets Found
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400">
                                You haven't been registered for any ceremonies yet.
                            </p>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Tickets Table -->
            <Card v-else class="shadow-sm">
                <template #title>All Tickets</template>
                <template #content>
                    <DataTable :value="tickets" stripedRows>
                        <template #empty>
                            <div class="py-8 text-center text-gray-500">
                                No tickets allocated yet
                            </div>
                        </template>
                        <Column field="ticket_code" header="Ticket Code">
                            <template #body="{ data }">
                                <code class="rounded bg-gray-100 px-2 py-1 text-sm font-mono dark:bg-gray-800">
                                    {{ data.ticket_code }}
                                </code>
                            </template>
                        </Column>
                        <Column field="ticket_type" header="Type">
                            <template #body="{ data }">
                                <Tag :value="data.ticket_type" :severity="data.ticket_type === 'Base' ? 'info' : 'warn'" />
                            </template>
                        </Column>
                        <Column field="guest_name" header="Guest Name">
                            <template #body="{ data }">
                                <span v-if="data.guest_name">{{ data.guest_name }}</span>
                                <span v-else class="text-gray-400">Not assigned</span>
                            </template>
                        </Column>
                        <Column field="guest_email" header="Guest Email">
                            <template #body="{ data }">
                                <span v-if="data.guest_email">{{ data.guest_email }}</span>
                                <span v-else class="text-gray-400">-</span>
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
                                    <Button
                                        v-if="data.status === 'Active'"
                                        label="Assign Guest"
                                        size="small"
                                        text
                                        @click="openGuestDialog(data)"
                                    />
                                    <a
                                        v-if="data.status === 'Active'"
                                        :href="`/student/tickets/${data.id}/download-qr`"
                                        download
                                    >
                                        <Button
                                            label="Download QR"
                                            icon="pi pi-download"
                                            size="small"
                                            severity="success"
                                            text
                                        />
                                    </a>
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>
        </div>

        <!-- Guest Assignment Dialog -->
        <Dialog v-model:visible="showGuestDialog" header="Assign Guest" :style="{ width: '500px' }" modal>
            <div class="space-y-4 py-4">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Guest Name <span class="text-red-500">*</span>
                    </label>
                    <InputText v-model="guestForm.guest_name" class="w-full" placeholder="Enter guest name" />
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Guest Email
                    </label>
                    <InputText
                        v-model="guestForm.guest_email"
                        type="email"
                        class="w-full"
                        placeholder="Enter guest email (optional)"
                    />
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" severity="secondary" text @click="showGuestDialog = false" />
                <Button label="Save" @click="updateGuest" />
            </template>
        </Dialog>
    </div>
</template>
