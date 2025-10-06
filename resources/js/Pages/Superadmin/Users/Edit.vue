<script setup>
import { useForm } from '@inertiajs/inertia-vue3'

const props = defineProps({
  user: Object,
})

const form = useForm({
  name: props.user.name,
  email: props.user.email,
  password: '', // optional when editing
  role: props.user.role,
})

function submit() {
  form.put(route('superadmin.users.update', props.user.id))
}
</script>

<template>
  <div class="max-w-2xl mx-auto bg-white shadow rounded-lg p-6">
    <h2 class="text-xl font-bold mb-4">Edit User (Superadmin)</h2>

    <form @submit.prevent="submit">
      <!-- Name -->
      <div class="mb-4">
        <label class="block text-sm font-medium">Name</label>
        <input
          v-model="form.name"
          type="text"
          class="mt-1 block w-full border rounded p-2"
        />
        <div v-if="form.errors.name" class="text-red-500 text-sm">
          {{ form.errors.name }}
        </div>
      </div>

      <!-- Email -->
      <div class="mb-4">
        <label class="block text-sm font-medium">Email</label>
        <input
          v-model="form.email"
          type="email"
          class="mt-1 block w-full border rounded p-2"
        />
        <div v-if="form.errors.email" class="text-red-500 text-sm">
          {{ form.errors.email }}
        </div>
      </div>

      <!-- Password -->
      <div class="mb-4">
        <label class="block text-sm font-medium">Password (leave blank to keep existing)</label>
        <input
          v-model="form.password"
          type="password"
          class="mt-1 block w-full border rounded p-2"
        />
        <div v-if="form.errors.password" class="text-red-500 text-sm">
          {{ form.errors.password }}
        </div>
      </div>

      <!-- Role -->
      <div class="mb-4">
        <label class="block text-sm font-medium">Role</label>
        <select
          v-model="form.role"
          class="mt-1 block w-full border rounded p-2"
        >
          <option value="superadmin">Superadmin</option>
          <option value="developer">Developer</option>
          <option value="admin">Admin</option>
          <option value="employee">Employee</option>
        </select>
        <div v-if="form.errors.role" class="text-red-500 text-sm">
          {{ form.errors.role }}
        </div>
      </div>

      <!-- Submit -->
      <div class="flex justify-end">
        <button
          type="submit"
          class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700"
          :disabled="form.processing"
        >
          Update
        </button>
      </div>
    </form>
  </div>
</template>

