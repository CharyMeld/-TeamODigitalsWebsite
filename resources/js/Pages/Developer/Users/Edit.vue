<script setup>
import { useForm } from '@inertiajs/inertia-vue3'
import { Link } from '@inertiajs/inertia-vue3'

const props = defineProps({
  user: { type: Object, required: true }
})

// initialize form with existing data (exclude password)
const form = useForm({
  name: props.user.name || '',
  email: props.user.email || '',
  username: props.user.username || '',
  password: '',
  role: props.user.role || 'employee',
  employee_id: props.user.employee_id || '',
  status: props.user.status || 'active',
  gender: props.user.gender || '',
  date_of_birth: props.user.date_of_birth || '',
  marital_status: props.user.marital_status || '',
  phone: props.user.phone || '',
  address: props.user.address || '',
  emergency_contact: props.user.emergency_contact || '',
  local_government: props.user.local_government || '',
  state: props.user.state || '',
  country: props.user.country || 'Nigeria',
  department: props.user.department || '',
  salary: props.user.salary || '',
  hire_date: props.user.hire_date || '',
  profile_photo_path: null
})

function handlePhoto(e) {
  form.profile_photo_path = e.target.files[0] ?? null
}

function submit() {
  form.put(`/developer/users/${props.user.id}`)
}
</script>

<template>
  <div class="p-6 max-w-3xl">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-xl font-semibold">Edit User</h1>
      <Link href="/developer/users" class="text-sm text-gray-600">Back</Link>
    </div>

    <form @submit.prevent="submit" class="space-y-4 bg-white p-4 rounded shadow">
      <!-- reuse the same fields as create -->
      <div>
        <label>Name</label>
        <input v-model="form.name" class="border p-2 w-full" />
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label>Email</label>
          <input v-model="form.email" type="email" class="border p-2 w-full" />
        </div>
        <div>
          <label>Username</label>
          <input v-model="form.username" class="border p-2 w-full" />
        </div>
      </div>

      <div>
        <label>New password (leave blank to keep current)</label>
        <input v-model="form.password" type="password" class="border p-2 w-full" />
      </div>

      <!-- the rest of the fields (role, employee id, status, etc.) -->
      <!-- ...for brevity copy the same inputs as Create.vue but bound to form -->
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label>Role</label>
          <select v-model="form.role" class="border p-2 w-full">
            <option value="developer">Developer</option>
            <option value="superadmin">Superadmin</option>
            <option value="admin">Admin</option>
            <option value="employee">Employee</option>
          </select>
        </div>

        <div>
          <label>Employee ID</label>
          <input v-model="form.employee_id" class="border p-2 w-full" />
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label>Status</label>
          <select v-model="form.status" class="border p-2 w-full">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>

        <div>
          <label>Department</label>
          <input v-model="form.department" class="border p-2 w-full" />
        </div>
      </div>

      <div class="grid grid-cols-3 gap-4">
        <div>
          <label>Salary</label>
          <input v-model="form.salary" type="number" step="0.01" class="border p-2 w-full" />
        </div>

        <div>
          <label>Hire date</label>
          <input v-model="form.hire_date" type="date" class="border p-2 w-full" />
        </div>

        <div>
          <label>Profile photo</label>
          <input type="file" @change="handlePhoto" accept="image/*" class="border p-2 w-full" />
        </div>
      </div>

      <div class="flex gap-2">
        <button :disabled="form.processing" type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Update</button>
        <Link href="/developer/users" class="px-4 py-2 border rounded text-gray-700">Cancel</Link>
      </div>
    </form>
  </div>
</template>

