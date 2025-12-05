<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import Avatar from 'primevue/avatar'
import Button from 'primevue/button'
import Calendar from 'primevue/calendar'
import Card from 'primevue/card'
import Checkbox from 'primevue/checkbox'
import InputNumber from 'primevue/inputnumber'
import InputText from 'primevue/inputtext'
import Menu from 'primevue/menu'
import Textarea from 'primevue/textarea'

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
    }
}>()

const parseDate = (value: string | null) => (value ? new Date(value) : null)

const form = ref({
    name: props.ceremony.name,
    description: props.ceremony.description,
    ceremony_date: parseDate(props.ceremony.ceremony_date),
    venue: props.ceremony.venue,
    venue_address: props.ceremony.venue_address,
    total_capacity: props.ceremony.total_capacity,
    base_tickets_per_graduate: props.ceremony.base_tickets_per_graduate,
    ticket_request_deadline: parseDate(props.ceremony.ticket_request_deadline),
    redistribution_deadline: parseDate(props.ceremony.redistribution_deadline),
    is_active: props.ceremony.is_active,
})

const submit = () => {
    router.put(`/admin/ceremonies/${props.ceremony.id}`, form.value)
}

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
        <Head title="Edit Ceremony" />

        <!-- Header -->
        <header class="sticky top-0 z-50 border-b border-gray-200 bg-white/80 backdrop-blur-md shadow-sm dark:border-gray-700 dark:bg-gray-800/80">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <Link href="/admin/ceremonies" class="flex items-center gap-3 transition-opacity hover:opacity-80">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 via-purple-600 to-pink-600 shadow-lg ring-2 ring-white/20">
                            <i class="pi pi-ticket text-xl text-white"></i>
                        </div>
                        <div class="hidden sm:block">
                            <h1 class="text-lg font-bold text-gray-900 dark:text-white">Edit Ceremony</h1>
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

        <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
            <nav class="mb-6 text-sm text-gray-500 dark:text-gray-400">
                <Link href="/admin/dashboard" class="hover:text-gray-700 dark:hover:text-gray-200">Dashboard</Link>
                <span class="mx-2">/</span>
                <Link href="/admin/ceremonies" class="hover:text-gray-700 dark:hover:text-gray-200">Ceremonies</Link>
                <span class="mx-2">/</span>
                <span class="text-gray-700 dark:text-gray-200">Edit</span>
            </nav>
            <Card class="shadow-lg">
                <template #title>
                    <div class="flex items-center gap-2">
                        <i class="pi pi-calendar text-blue-600"></i>
                        <span>Ceremony Details</span>
                    </div>
                </template>
                <template #content>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label class="mb-2 block font-medium text-gray-700 dark:text-gray-300">Name *</label>
                            <InputText v-model="form.name" class="w-full" placeholder="e.g., Summer Graduation 2025" />
                        </div>

                        <div>
                            <label class="mb-2 block font-medium text-gray-700 dark:text-gray-300">Description</label>
                            <Textarea v-model="form.description" class="w-full" rows="3" />
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block font-medium text-gray-700 dark:text-gray-300">Ceremony Date *</label>
                                <Calendar v-model="form.ceremony_date" showTime showIcon class="w-full" />
                            </div>

                            <div>
                                <label class="mb-2 block font-medium text-gray-700 dark:text-gray-300">Total Capacity *</label>
                                <InputNumber v-model="form.total_capacity" class="w-full" />
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block font-medium text-gray-700 dark:text-gray-300">Venue *</label>
                            <InputText v-model="form.venue" class="w-full" />
                        </div>

                        <div>
                            <label class="mb-2 block font-medium text-gray-700 dark:text-gray-300">Venue Address</label>
                            <InputText v-model="form.venue_address" class="w-full" />
                        </div>

                        <div class="grid gap-6 md:grid-cols-3">
                            <div>
                                <label class="mb-2 block font-medium text-gray-700 dark:text-gray-300">Base Tickets Per Graduate *</label>
                                <InputNumber v-model="form.base_tickets_per_graduate" class="w-full" />
                            </div>

                            <div>
                                <label class="mb-2 block font-medium text-gray-700 dark:text-gray-300">Request Deadline</label>
                                <Calendar v-model="form.ticket_request_deadline" showTime showIcon class="w-full" />
                            </div>

                            <div>
                                <label class="mb-2 block font-medium text-gray-700 dark:text-gray-300">Redistribution Deadline</label>
                                <Calendar v-model="form.redistribution_deadline" showTime showIcon class="w-full" />
                            </div>
                        </div>

                        <div class="flex items-center">
                            <Checkbox v-model="form.is_active" inputId="is_active" binary />
                            <label for="is_active" class="ml-2 font-medium text-gray-700 dark:text-gray-300">
                                Active
                            </label>
                        </div>

                        <div class="flex gap-3">
                            <Button type="submit" label="Save Changes" icon="pi pi-check" class="shadow-md" />
                            <Link :href="`/admin/ceremonies/${props.ceremony.id}`">
                                <Button label="Cancel" severity="secondary" outlined />
                            </Link>
                        </div>
                    </form>
                </template>
            </Card>
        </div>
    </div>
</template>
