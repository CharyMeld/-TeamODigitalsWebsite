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
  form.post('/developer/users')
}
</script>

<template>
  <div class="p-6 max-w-3xl">
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-xl font-semibold">Create User (Developer)</h1>
      <Link href="/developer/users" class="text-sm text-gray-600">Back to list</Link>
    </div>

    <form @submit.prevent="submit" class="space-y-4 bg-white p-4 rounded shadow">
      <div>
        <label class="block text-sm font-medium">Name</label>
        <input v-model="form.name" class="border p-2 w-full" />
        <div v-if="form.errors.name" class="text-red-600 text-sm">{{ form.errors.name }}</div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium">Email</label>
          <input v-model="form.email" type="email" class="border p-2 w-full" />
          <div v-if="form.errors.email" class="text-red-600 text-sm">{{ form.errors.email }}</div>
        </div>

        <div>
          <label class="block text-sm font-medium">Username</label>
          <input v-model="form.username" class="border p-2 w-full" />
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label>Password</label>
          <input v-model="form.password" type="password" class="border p-2 w-full" />
        </div>

        <div>
          <label>Role</label>
          <select v-model="form.role" class="border p-2 w-full">
            <option value="developer">Developer</option>
            <option value="superadmin">Superadmin</option>
            <option value="admin">Admin</option>
            <option value="employee">Employee</option>
          </select>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label>Employee ID</label>
          <input v-model="form.employee_id" class="border p-2 w-full" />
        </div>

        <div>
          <label>Status</label>
          <select v-model="form.status" class="border p-2 w-full">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
      </div>

      <div class="grid grid-cols-3 gap-4">
        <div>
          <label>Gender</label>
          <select v-model="form.gender" class="border p-2 w-full">
            <option value="">-</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="other">Other</option>
          </select>
        </div>

        <div>
          <label>Date of birth</label>
          <input v-model="form.date_of_birth" type="date" class="border p-2 w-full" />
        </div>

        <div>
          <label>Marital status</label>
          <select v-model="form.marital_status" class="border p-2 w-full">
            <option value="">-</option>
            <option value="single">Single</option>
            <option value="married">Married</option>
            <option value="divorced">Divorced</option>
            <option value="widowed">Widowed</option>
          </select>
        </div>
      </div>

      <div>
        <label>Phone</label>
        <input v-model="form.phone" class="border p-2 w-full" />
      </div>

      <div>
        <label>Address</label>
        <textarea v-model="form.address" class="border p-2 w-full"></textarea>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label>Emergency contact</label>
          <input v-model="form.emergency_contact" class="border p-2 w-full" />
        </div>

        <div>
          <label>Local government</label>
          <input v-model="form.local_government" class="border p-2 w-full" />
        </div>
      </div>

      <div class="grid grid-cols-3 gap-4">
        <div>
          <label>State</label>
          <input v-model="form.state" class="border p-2 w-full" />
        </div>

        <div>
          <label>Country</label>
          <input v-model="form.country" class="border p-2 w-full" />
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
        <button :disabled="form.processing" type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Save</button>
        <Link href="/developer/users" class="px-4 py-2 border rounded text-gray-700">Cancel</Link>
      </div>
    </form>
  </div>
</template>

