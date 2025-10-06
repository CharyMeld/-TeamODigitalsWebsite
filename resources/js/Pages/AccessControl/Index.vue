<script setup>
import { computed, ref } from 'vue'
import { useForm, router, usePage } from '@inertiajs/vue3'

const props = defineProps({
  users: Array,
  roles: Array,
  permissions: Array,
  rolePermissions: Object, // { [roleId]: ['perm.name', ...] }
})

const page = usePage()
const flash = ref(null)

// Get the current user's role to determine route prefix
const userRole = computed(() => page.props.auth?.user?.role || 'admin')
const routePrefix = computed(() => {
  if (userRole.value === 'developer') return 'developer.access'
  if (userRole.value === 'superadmin') return 'superadmin.access'
  return 'admin.access'
})

// ------- Users ⇆ Roles -------
const userRolesForms = {}
props.users.forEach(u => {
  userRolesForms[u.id] = useForm({
    roles: u.roles?.map(r => r.name) ?? []
  })
})

const allRoleNames = computed(() => props.roles.map(r => r.name))

function toggleUserRole(uId, roleName) {
  const f = userRolesForms[uId]
  const exists = f.roles.includes(roleName)
  f.roles = exists ? f.roles.filter(r => r !== roleName) : [...f.roles, roleName]
}

function saveUserRoles(user) {
  const f = userRolesForms[user.id]
  const routeName = `${routePrefix.value}.users.roles.sync`
  console.log('Saving user roles with route:', routeName)
  f.post(route(routeName, { user: user.id }), {
    preserveScroll: true,
    onSuccess: () => { flash.value = 'User roles saved.' }
  })
}

// ------- Roles ⇆ Permissions -------
const rolePermForms = {}
props.roles.forEach(r => {
  rolePermForms[r.id] = useForm({
    permissions: props.rolePermissions[r.id] ?? []
  })
})

const permissionNames = computed(() => props.permissions.map(p => p.name))

function toggleRolePermission(roleId, permName) {
  const f = rolePermForms[roleId]
  const exists = f.permissions.includes(permName)
  f.permissions = exists ? f.permissions.filter(p => p !== permName) : [...f.permissions, permName]
}

function saveRolePermissions(role) {
  const f = rolePermForms[role.id]
  const routeName = `${routePrefix.value}.roles.permissions.sync`
  console.log('Saving role permissions with route:', routeName)
  f.post(route(routeName, { role: role.id }), {
    preserveScroll: true,
    onSuccess: () => { flash.value = 'Role permissions saved.' }
  })
}

// ------- Create permission -------
const createPermissionForm = useForm({ name: '' })
function createPermission() {
  if (!createPermissionForm.name) return
  const routeName = `${routePrefix.value}.permissions.store`
  console.log('Creating permission with route:', routeName)
  createPermissionForm.post(route(routeName), {
    preserveScroll: true,
    onSuccess: () => {
      createPermissionForm.reset('name')
      router.reload({ only: ['permissions','rolePermissions'] })
      flash.value = 'Permission created.'
    }
  })
}
</script>

<template>
  <div class="max-w-7xl mx-auto py-10 px-6">
    <h1 class="text-3xl font-bold mb-8">Access Control</h1>

    <div v-if="flash" class="mb-6 rounded-md bg-green-100 text-green-800 px-4 py-3">
      {{ flash }}
    </div>

    <!-- Users ⇆ Roles -->
    <div class="bg-white shadow rounded-2xl p-6 mb-10">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold">Users & Roles</h2>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-left text-gray-600">
              <th class="p-3">User</th>
              <th class="p-3">Email</th>
              <th class="p-3">Roles</th>
              <th class="p-3 w-32"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="u in props.users" :key="u.id" class="border-t">
              <td class="p-3 font-medium">{{ u.name }}</td>
              <td class="p-3 text-gray-600">{{ u.email }}</td>
              <td class="p-3">
                <div class="flex flex-wrap gap-3">
                  <label v-for="r in allRoleNames" :key="r" class="inline-flex items-center gap-2">
                    <input
                      type="checkbox"
                      :checked="userRolesForms[u.id].roles.includes(r)"
                      @change="toggleUserRole(u.id, r)"
                      class="rounded border-gray-300"
                    />
                    <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-800">{{ r }}</span>
                  </label>
                </div>
              </td>
              <td class="p-3">
                <button
                  @click="saveUserRoles(u)"
                  class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700"
                >
                  Save
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Roles ⇆ Permissions -->
    <div class="bg-white shadow rounded-2xl p-6 mb-10">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold">Roles & Permissions</h2>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr>
              <th class="p-3 text-left">Role</th>
              <th
                v-for="perm in permissionNames"
                :key="perm"
                class="p-3 text-left whitespace-nowrap text-gray-600"
              >
                {{ perm }}
              </th>
              <th class="p-3 w-32"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="role in props.roles" :key="role.id" class="border-t align-top">
              <td class="p-3 font-medium whitespace-nowrap">{{ role.name }}</td>

              <td v-for="perm in permissionNames" :key="role.id + perm" class="p-3">
                <input
                  type="checkbox"
                  :checked="rolePermForms[role.id].permissions.includes(perm)"
                  @change="toggleRolePermission(role.id, perm)"
                  class="rounded border-gray-300"
                  :disabled="role.name === 'superadmin' && perm.endsWith('.delete')"
                  :title="role.name === 'superadmin' && perm.endsWith('.delete') ? 'Superadmin cannot have delete permissions' : ''"
                />
              </td>

              <td class="p-3">
                <button
                  @click="saveRolePermissions(role)"
                  class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700"
                >
                  Save
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create Permission -->
    <div class="bg-white shadow rounded-2xl p-6">
      <h2 class="text-xl font-semibold mb-4">Create Permission</h2>
      <div class="flex items-center gap-3">
        <input
          v-model="createPermissionForm.name"
          type="text"
          placeholder="e.g. invoices.approve"
          class="w-full rounded-lg border-gray-300"
        />
        <button
          @click="createPermission"
          class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700"
        >
          Add
        </button>
      </div>
      <p class="text-sm text-gray-500 mt-2">
        Tip: use a consistent naming convention like <code>resource.action</code> (e.g. <code>users.update</code>).
        Superadmin cannot create/hold <code>*.delete</code>.
      </p>
    </div>
  </div>
</template>

<style scoped>
table { border-collapse: separate; border-spacing: 0; }
th, td { vertical-align: top; }
</style>

