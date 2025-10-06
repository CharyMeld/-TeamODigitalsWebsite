<script setup>
import { useForm } from '@inertiajs/inertia-vue3'
import { Link } from '@inertiajs/inertia-vue3'

const form = useForm({
  name: '',
  email: '',
  username: '',
  password: '',
  role: 'employee',
  employee_id: '',
  status: 'active',
  gender: '',
  date_of_birth: '',
  marital_status: '',
  phone: '',
  address: '',
  emergency_contact: '',
  local_government: '',
  state: '',
  country: 'Nigeria',
  department: '',
  salary: '',
  hire_date: '',
  profile_photo_path: null
})

function handlePhoto(e) {
  form.profile_photo_path = e.target.files[0] ?? null
}

function submit() {
  form.post('/superadmin/users')
}
</script>

<template>
  <div class="p-6 max-w-3xl">
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-xl font-semibold">Create User (Superadmin)</h1>
      <Link href="/superadmin/users" class="text-sm text-gray-600">Back to list</Link>
    </div>

    <!-- same form fields as Developer Create.vue -->
    <form @submit.prevent="submit" class="space-y-4 bg-white p-4 rounded shadow">
      <!-- fields identical to Developer Create.vue -->
      <!-- (for brevity copy them here) -->
      <div>
        <label class="block text-sm font-medium">Name</label>
        <input v-model="form.name" class="border p-2 w-full" />
      </div>

      <!-- rest omitted in this snippet: copy remaining inputs from Developer Create.vue -->
      <!-- Submit controls -->
      <div class="flex gap-2">
        <button :disabled="form.processing" type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Save</button>
        <Link href="/superadmin/users" class="px-4 py-2 border rounded text-gray-700">Cancel</Link>
      </div>
    </form>
  </div>
</template>

