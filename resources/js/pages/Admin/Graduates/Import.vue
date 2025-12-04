<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import Button from 'primevue/button'
import Card from 'primevue/card'
import Dropdown from 'primevue/dropdown'
import FileUpload from 'primevue/fileupload'
import Avatar from 'primevue/avatar'
import Menu from 'primevue/menu'
import Message from 'primevue/message'
import { ref, computed } from 'vue'

const props = defineProps<{
    auth: {
        user: {
            name: string
            email: string
        }
    }
    ceremonies: any[]
}>()

const userMenu = ref()
const userMenuItems = ref([
    { label: 'Profile', icon: 'pi pi-user', command: () => router.visit('/settings/profile') },
    { separator: true },
    { label: 'Logout', icon: 'pi pi-sign-out', command: () => router.post('/logout') },
])

const selectedCeremony = ref(null)
const uploadedFile = ref(null)

const userInitials = computed(() => {
    const names = props.auth.user.name.split(' ')
    return names.length >= 2 ? names[0][0] + names[names.length - 1][0] : names[0][0]
})

const onFileSelect = (event: any) => {
    uploadedFile.value = event.files[0]
}

const submit = () => {
    if (!selectedCeremony.value || !uploadedFile.value) {
        return
    }

    const formData = new FormData()
    formData.append('ceremony_id', selectedCeremony.value)
    formData.append('file', uploadedFile.value)

    router.post('/admin/graduates-import', formData, {
        preserveState: true,
    })
}
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
        <Head title="Import Graduates" />

        <!-- Header -->
        <header class="sticky top-0 z-50 border-b border-gray-200 bg-white/80 backdrop-blur-md shadow-sm dark:border-gray-700 dark:bg-gray-800/80">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <Link href="/admin/graduates" class="flex items-center gap-3 transition-opacity hover:opacity-80">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 via-purple-600 to-pink-600 shadow-lg ring-2 ring-white/20">
                            <i class="pi pi-ticket text-xl text-white"></i>
                        </div>
                        <div class="hidden sm:block">
                            <h1 class="text-lg font-bold text-gray-900 dark:text-white">Import Graduates</h1>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Bulk Upload</p>
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
            <!-- Instructions Card -->
            <Card class="mb-6 shadow-lg">
                <template #title>
                    <div class="flex items-center gap-2">
                        <i class="pi pi-info-circle text-blue-600"></i>
                        <span>CSV Format Instructions</span>
                    </div>
                </template>
                <template #content>
                    <Message severity="info" :closable="false">
                        <p class="mb-2 font-semibold">Your CSV file should have the following columns:</p>
                        <code class="block rounded bg-gray-100 p-3 text-sm dark:bg-gray-800">
                            student_id, student_name, degree_level, email, faculty, department
                        </code>
                        <p class="mt-3 text-sm">
                            <strong>Example:</strong><br />
                            NU2025001, John Smith, Undergraduate, john@example.com, Faculty of Business, Business Management
                        </p>
                    </Message>
                </template>
            </Card>

            <!-- Upload Form -->
            <Card class="shadow-lg">
                <template #title>
                    <div class="flex items-center gap-2">
                        <i class="pi pi-upload text-blue-600"></i>
                        <span>Upload CSV File</span>
                    </div>
                </template>
                <template #content>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label class="mb-2 block font-medium text-gray-700 dark:text-gray-300">
                                Select Ceremony *
                            </label>
                            <Dropdown
                                v-model="selectedCeremony"
                                :options="ceremonies"
                                optionLabel="name"
                                optionValue="id"
                                placeholder="Choose a ceremony"
                                class="w-full"
                            />
                        </div>

                        <div>
                            <label class="mb-2 block font-medium text-gray-700 dark:text-gray-300">
                                CSV File *
                            </label>
                            <FileUpload
                                mode="basic"
                                accept=".csv"
                                :maxFileSize="10000000"
                                @select="onFileSelect"
                                chooseLabel="Choose CSV File"
                                class="w-full"
                            />
                        </div>

                        <div class="flex gap-3">
                            <Button
                                type="submit"
                                label="Import Graduates"
                                icon="pi pi-upload"
                                class="shadow-md"
                                :disabled="!selectedCeremony || !uploadedFile"
                            />
                            <Link href="/admin/graduates">
                                <Button label="Cancel" severity="secondary" outlined />
                            </Link>
                        </div>
                    </form>
                </template>
            </Card>
        </div>
    </div>
</template>
