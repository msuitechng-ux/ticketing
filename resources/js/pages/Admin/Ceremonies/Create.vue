<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import Button from 'primevue/button'
import Card from 'primevue/card'
import InputText from 'primevue/inputtext'
import Calendar from 'primevue/calendar'
import Textarea from 'primevue/textarea'
import InputNumber from 'primevue/inputnumber'
import Checkbox from 'primevue/checkbox'
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
}>()

const userMenu = ref()
const userMenuItems = ref([
    { label: 'Profile', icon: 'pi pi-user', command: () => router.visit('/settings/profile') },
    { separator: true },
    { label: 'Logout', icon: 'pi pi-sign-out', command: () => router.post('/logout') },
])

const form = ref({
    name: '',
    description: '',
    ceremony_date: null as Date | null,
    venue: '',
    venue_address: '',
    total_capacity: 500,
    base_tickets_per_graduate: 2,
    ticket_request_deadline: null as Date | null,
    redistribution_deadline: null as Date | null,
    is_active: true,
})

const submit = () => {
    router.post('/admin/ceremonies', form.value)
}

const userInitials = computed(() => {
    const names = props.auth.user.name.split(' ')
    return names.length >= 2 ? names[0][0] + names[names.length - 1][0] : names[0][0]
})
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
        <Head title="Create Ceremony" />

        <!-- Header -->
        <header class="sticky top-0 z-50 border-b border-gray-200 bg-white/80 backdrop-blur-md shadow-sm dark:border-gray-700 dark:bg-gray-800/80">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <Link href="/admin/ceremonies" class="flex items-center gap-3 transition-opacity hover:opacity-80">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 via-purple-600 to-pink-600 shadow-lg ring-2 ring-white/20">
                            <i class="pi pi-ticket text-xl text-white"></i>
                        </div>
                        <div class="hidden sm:block">
                            <h1 class="text-lg font-bold text-gray-900 dark:text-white">Create Ceremony</h1>
                            <p class="text-xs text-gray-600 dark:text-gray-400">New Event</p>
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
                            <Button type="submit" label="Create Ceremony" icon="pi pi-check" class="shadow-md" />
                            <Link href="/admin/ceremonies">
                                <Button label="Cancel" severity="secondary" outlined />
                            </Link>
                        </div>
                    </form>
                </template>
            </Card>
        </div>
    </div>
</template>
