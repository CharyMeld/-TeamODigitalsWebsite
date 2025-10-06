<script setup>
import { Link } from '@inertiajs/inertia-vue3'
import { Inertia } from '@inertiajs/inertia'
import { ref } from 'vue'

const props = defineProps({ users: { type: Object, required: true } })
const usersList = Array.isArray(props.users) ? props.users : (props.users.data ?? props.users)
const search = ref('')

function destroy(id) {
  if (!confirm('Delete this user?')) return
  Inertia.delete(`/admin/users/${id}`)
}
</script>

<template>
  <div class="p-6">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-semibold">Admin — Users</h1>
      <div class="flex gap-2">
        <input v-model="search" placeholder="Search" class="border rounded px-2 py-1" />
        <button @click="Inertia.get('/admin/users', { search: search })" class="bg-gray-700 text-white px-3 py-1 rounded">Search</button>
        <!-- If admin should not create users, hide the button; included here but you can remove it -->
        <Link href="/admin/users/create" class="bg-blue-600 text-white px-3 py-1 rounded">Add New</Link>
      </div>
    </div>

    <div class="overflow-x-auto bg-white rounded shadow">
      <table class="min-w-full text-left">
        <thead>
          <tr>
            <th class="p-3 border">ID</th><th class="p-3 border">Name</th><th class="p-3 border">Email</th><th class="p-3 border">Role</th><th class="p-3 border">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in usersList" :key="user.id">
            <td class="p-2 border">{{ user.id }}</td>
            <td class="p-2 border">{{ user.name }}</td>
            <td class="p-2 border">{{ user.email }}</td>
            <td class="p-2 border">{{ user.role }}</td>
            <td class="p-2 border">
              <Link :href="`/admin/users/${user.id}`" class="text-green-600 mr-2">View</Link>
              <Link :href="`/admin/users/${user.id}/edit`" class="text-blue-600 mr-2">Edit</Link>
              <button @click="destroy(user.id)" class="text-red-600">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

