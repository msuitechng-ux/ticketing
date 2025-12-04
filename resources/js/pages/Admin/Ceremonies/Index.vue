<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import Button from 'primevue/button'
import Card from 'primevue/card'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'
import Avatar from 'primevue/avatar'
import Menu from 'primevue/menu'
import InputText from 'primevue/inputtext'
import { ref, computed } from 'vue'

const props = defineProps<{
    auth: {
        user: {
            name: string
            email: string
        }
    }
    ceremonies: {
        data: any[]
        links: any
        meta: any
    }
}>()

const userMenu = ref()
const searchQuery = ref('')

const userMenuItems = ref([
    { label: 'Profile', icon: 'pi pi-user', command: () => router.visit('/settings/profile') },
    { separator: true },
    { label: 'Logout', icon: 'pi pi-sign-out', command: () => router.post('/logout') },
])

const userInitials = computed(() => {
    const names = props.auth.user.name.split(' ')
    return names.length >= 2 ? names[0][0] + names[names.length - 1][0] : names[0][0]
})

const getStatusSeverity = (isActive: boolean) => {
    return isActive ? 'success' : 'danger'
}
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
        <Head title="Ceremonies" />

        <!-- Header -->
        <header class="sticky top-0 z-50 border-b border-gray-200 bg-white/80 backdrop-blur-md shadow-sm dark:border-gray-700 dark:bg-gray-800/80">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <Link href="/admin/dashboard" class="flex items-center gap-3 transition-opacity hover:opacity-80">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 via-purple-600 to-pink-600 shadow-lg ring-2 ring-white/20">
                            <i class="pi pi-ticket text-xl text-white"></i>
                        </div>
                        <div class="hidden sm:block">
                            <h1 class="text-lg font-bold text-gray-900 dark:text-white">Ceremonies</h1>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Manage Events</p>
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
            <!-- Page Header -->
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Ceremonies</h2>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Manage graduation ceremonies</p>
                </div>
                <Link href="/admin/ceremonies/create">
                    <Button label="Create Ceremony" icon="pi pi-plus" class="shadow-md" />
                </Link>
            </div>

            <!-- Ceremonies Table -->
            <Card class="shadow-lg">
                <template #content>
                    <DataTable :value="ceremonies.data" stripedRows paginator :rows="15">
                        <template #empty>
                            <div class="py-8 text-center text-gray-500">No ceremonies found</div>
                        </template>
                        <Column field="name" header="Name" sortable />
                        <Column field="ceremony_date" header="Date" sortable>
                            <template #body="{ data }">
                                {{ new Date(data.ceremony_date).toLocaleString() }}
                            </template>
                        </Column>
                        <Column field="venue" header="Venue" />
                        <Column field="total_capacity" header="Capacity" sortable />
                        <Column field="is_active" header="Status">
                            <template #body="{ data }">
                                <Tag :value="data.is_active ? 'Active' : 'Inactive'" :severity="getStatusSeverity(data.is_active)" />
                            </template>
                        </Column>
                        <Column header="Actions">
                            <template #body="{ data }">
                                <div class="flex gap-2">
                                    <Link :href="`/admin/ceremonies/${data.id}`">
                                        <Button label="View" size="small" text />
                                    </Link>
                                    <Link :href="`/admin/ceremonies/${data.id}/edit`">
                                        <Button label="Edit" size="small" severity="secondary" text />
                                    </Link>
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>
        </div>
    </div>
</template>
