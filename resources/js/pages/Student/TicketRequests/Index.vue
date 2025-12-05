<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import Button from 'primevue/button'
import Card from 'primevue/card'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import InputNumber from 'primevue/inputnumber'
import Textarea from 'primevue/textarea'
import Tag from 'primevue/tag'

const props = defineProps<{
    ticketRequests: Array<any>
    graduate: any | null
    canRequestMore: boolean
}>()

const form = ref({
    requested_quantity: 1,
    reason: '',
})

const shareMessage = ref<string | null>(null)

const submit = () => {
    router.post('/student/ticket-requests', form.value, {
        onSuccess: () => {
            form.value = {
                requested_quantity: 1,
                reason: '',
            }
        },
    })
}

const copyShareLink = async () => {
    try {
        const shareUrl = `${window.location.origin}/student/ticket-requests`

        if (navigator.share) {
            await navigator.share({
                title: 'Request Graduation Tickets',
                text: 'Submit your extra ticket request.',
                url: shareUrl,
            })
            shareMessage.value = 'Share dialog opened.'

            return
        }

        await navigator.clipboard.writeText(shareUrl)
        shareMessage.value = 'Link copied to clipboard.'
    } catch (error) {
        shareMessage.value = 'Unable to share link.'
    }
}

const getStatusSeverity = (status: string) => {
    const map: Record<string, string> = {
        Pending: 'warn',
        Approved: 'success',
        'Partially Approved': 'info',
        Denied: 'danger',
        Waitlisted: 'secondary',
    }

    return map[status] ?? 'info'
}

const canSubmit = computed(() => props.canRequestMore && !!props.graduate)
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <Head title="Ticket Requests" />

        <!-- Header -->
        <div class="bg-white shadow dark:bg-gray-800">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-6 sm:px-6 lg:px-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Ticket Requests</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Request additional tickets and track their status
                    </p>
                </div>
                <div class="flex gap-3">
                    <Button label="Share Request Link" icon="pi pi-share-alt" outlined @click="copyShareLink" />
                    <Link href="/student/dashboard">
                        <Button label="Back to Dashboard" severity="secondary" outlined />
                    </Link>
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8 space-y-6">
            <p v-if="shareMessage" class="text-sm text-gray-600 dark:text-gray-300">
                {{ shareMessage }}
            </p>

            <Card class="shadow-sm">
                <template #title>Submit a Request</template>
                <template #content>
                    <div v-if="!graduate" class="text-gray-600 dark:text-gray-300">
                        No graduate profile found. Please contact the administrator to be assigned to a ceremony.
                    </div>
                    <form v-else class="space-y-4" @submit.prevent="submit">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Ceremony
                                </label>
                                <div class="rounded border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                                    {{ graduate.ceremony?.name || 'N/A' }}
                                </div>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Request Deadline
                                </label>
                                <div class="rounded border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                                    {{ graduate.ceremony?.ticket_request_deadline ? new Date(graduate.ceremony.ticket_request_deadline).toLocaleString() : 'No deadline set' }}
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Tickets Needed *
                                </label>
                                <InputNumber
                                    v-model="form.requested_quantity"
                                    class="w-full"
                                    :min="1"
                                    :max="10"
                                    :disabled="!canSubmit"
                                />
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Base Tickets Assigned
                                </label>
                                <div class="rounded border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                                    {{ graduate?.ceremony?.base_tickets_per_graduate || '-' }}
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Reason (optional)
                            </label>
                            <Textarea
                                v-model="form.reason"
                                rows="3"
                                class="w-full"
                                placeholder="Tell us why you need extra tickets"
                                :disabled="!canSubmit"
                            />
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <Button type="submit" label="Submit Request" icon="pi pi-send" :disabled="!canSubmit" />
                            <span v-if="!canSubmit" class="text-sm text-gray-500 dark:text-gray-400">
                                You cannot submit a request right now.
                            </span>
                        </div>
                    </form>
                </template>
            </Card>

            <Card class="shadow-sm">
                <template #title>My Requests</template>
                <template #content>
                    <DataTable :value="ticketRequests" stripedRows>
                        <template #empty>
                            <div class="py-8 text-center text-gray-500">No ticket requests yet</div>
                        </template>
                        <Column field="requested_quantity" header="Requested" />
                        <Column field="approved_quantity" header="Approved">
                            <template #body="{ data }">
                                {{ data.approved_quantity ?? '-' }}
                            </template>
                        </Column>
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
                        <Column field="reviewed_at" header="Reviewed">
                            <template #body="{ data }">
                                {{ data.reviewed_at ? new Date(data.reviewed_at).toLocaleString() : '-' }}
                            </template>
                        </Column>
                        <Column field="admin_notes" header="Admin Notes">
                            <template #body="{ data }">
                                {{ data.admin_notes || '-' }}
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>
        </div>
    </div>
</template>
