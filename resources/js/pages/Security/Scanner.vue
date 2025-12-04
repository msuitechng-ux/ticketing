<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import Button from 'primevue/button'
import Card from 'primevue/card'
import InputText from 'primevue/inputtext'
import Dropdown from 'primevue/dropdown'
import Message from 'primevue/message'
import { ref } from 'vue'

const props = defineProps<{
    ceremonies: any[]
    entryPoints: string[]
}>()

const ticketCode = ref('')
const entryPoint = ref(props.entryPoints[0])
const verificationResult = ref<any>(null)
const isVerifying = ref(false)
const showResult = ref(false)

const verifyTicket = async () => {
    if (!ticketCode.value.trim()) {
        return
    }

    isVerifying.value = true
    showResult.value = false

    try {
        const response = await fetch('/security/verify-by-code', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                ticket_code: ticketCode.value,
                entry_point: entryPoint.value,
                device_info: navigator.userAgent,
            }),
        })

        const data = await response.json()
        verificationResult.value = data
        showResult.value = true

        // Clear ticket code after verification
        ticketCode.value = ''

        // Auto-hide result after 5 seconds if successful
        if (data.success) {
            setTimeout(() => {
                showResult.value = false
            }, 5000)
        }
    } catch (error) {
        verificationResult.value = {
            success: false,
            message: 'Failed to verify ticket. Please try again.',
        }
        showResult.value = true
    } finally {
        isVerifying.value = false
    }
}

const handleKeyPress = (event: KeyboardEvent) => {
    if (event.key === 'Enter') {
        verifyTicket()
    }
}
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <Head title="QR Scanner" />

        <!-- Header -->
        <div class="bg-white shadow dark:bg-gray-800">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                            QR Code Scanner
                        </h1>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Verify ticket entry
                        </p>
                    </div>
                    <Link :href="`/security/dashboard`">
                        <Button label="Back to Dashboard" severity="secondary" outlined />
                    </Link>
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Scanner Card -->
            <Card class="mb-6 shadow-lg">
                <template #content>
                    <div class="space-y-6">
                        <!-- Entry Point Selection -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Entry Point
                            </label>
                            <Dropdown
                                v-model="entryPoint"
                                :options="entryPoints"
                                class="w-full"
                                placeholder="Select entry point"
                            />
                        </div>

                        <!-- Manual Ticket Code Entry -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Ticket Code
                            </label>
                            <div class="flex gap-3">
                                <InputText
                                    v-model="ticketCode"
                                    class="flex-1"
                                    placeholder="Enter or scan ticket code"
                                    @keypress="handleKeyPress"
                                    :disabled="isVerifying"
                                />
                                <Button
                                    label="Verify"
                                    icon="pi pi-check"
                                    @click="verifyTicket"
                                    :loading="isVerifying"
                                    :disabled="!ticketCode.trim()"
                                />
                            </div>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                <i class="pi pi-info-circle"></i>
                                Scan QR code with a barcode scanner or enter the ticket code manually
                            </p>
                        </div>

                        <!-- Verification Result -->
                        <div v-if="showResult && verificationResult" class="mt-6">
                            <Message
                                v-if="verificationResult.success"
                                severity="success"
                                :closable="false"
                            >
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <i class="pi pi-check-circle text-2xl"></i>
                                        <span class="text-lg font-semibold">{{ verificationResult.message }}</span>
                                    </div>
                                    <div v-if="verificationResult.ticket" class="ml-9 space-y-1 text-sm">
                                        <p><strong>Graduate:</strong> {{ verificationResult.ticket.graduate?.student_name }}</p>
                                        <p><strong>Ceremony:</strong> {{ verificationResult.ticket.graduate?.ceremony?.name }}</p>
                                        <p><strong>Guest:</strong> {{ verificationResult.ticket.guest_name || 'Not assigned' }}</p>
                                        <p><strong>Ticket Code:</strong> {{ verificationResult.ticket.ticket_code }}</p>
                                    </div>
                                </div>
                            </Message>

                            <Message
                                v-else
                                severity="error"
                                :closable="false"
                            >
                                <div class="flex items-center gap-2">
                                    <i class="pi pi-times-circle text-2xl"></i>
                                    <span class="text-lg font-semibold">{{ verificationResult.message }}</span>
                                </div>
                            </Message>
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Active Ceremonies -->
            <Card class="shadow-sm">
                <template #title>Active Ceremonies</template>
                <template #content>
                    <div v-if="ceremonies.length === 0" class="py-8 text-center text-gray-500">
                        No active ceremonies
                    </div>
                    <div v-else class="space-y-4">
                        <div
                            v-for="ceremony in ceremonies"
                            :key="ceremony.id"
                            class="flex items-center justify-between rounded-lg border border-gray-200 p-4 dark:border-gray-700"
                        >
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">
                                    {{ ceremony.name }}
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ new Date(ceremony.ceremony_date).toLocaleString() }}
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ ceremony.venue }}
                                </p>
                            </div>
                        </div>
                    </div>
                </template>
            </Card>
        </div>
    </div>
</template>
